<?php

namespace Cosapi\Http\Controllers;

use Cosapi\Models\AgentOnline;
use Cosapi\Models\Anexo;
use Cosapi\Models\DetalleEventos;
use Cosapi\Models\User;
use Cosapi\Models\User_Queue;
use Illuminate\Http\Request;
use Cosapi\Http\Requests;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Support\Facades\Session;

class AdminController extends CosapiController
{
    protected $UserId;
    protected $UserRole;
    protected $UserName;
    protected $UserSystem;
    protected $UserPassword;
    protected $UserAnexo;
    protected $QueueAdd;
    protected $ChangeRole;
    protected $AssistanceUser;
    protected $statusAddAgentDashboard;

    public function __construct(){
        Session::put('UserId'       ,Auth::user()->id   );
        Session::put('UserRole'     ,Auth::user()->role   );
        Session::put('UserName'     ,Auth::user()->primer_nombre.' '.Auth::user()->apellido_paterno );
        Session::put('UserSystem'   ,Auth::user()->username);
        Session::put('ChangeRole'   ,Auth::user()->change_role);

        $Users = User::select('change_password')->where('id','=',Auth::user()->id )->get()->toArray();
        Session::put('UserPassword'     ,$Users[0]['change_password']);

        $Anexos = Anexo::select('name')->where('user_id','=',Auth::user()->id )->get()->toArray();
        if(count($Anexos) != 0){
            $name_anexo = $Anexos[0]['name'];
            Session::put('UserAnexo',$name_anexo);
        }else{
            Session::put('UserAnexo', 0 );
        }

        if (!Session::has('QueueAdd'))  Session::put('QueueAdd',false );

        $this->UserId                           = Session::get('UserId')        ;
        $this->UserRole                         = Session::get('UserRole')      ;
        $this->UserName                         = Session::get('UserName')      ;
        $this->UserSystem                       = Session::get('UserSystem')    ;
        $this->UserPassword                     = Session::get('UserPassword')  ;
        $this->UserAnexo                        = Session::get('UserAnexo')     ;
        $this->QueueAdd                         = Session::get('QueueAdd')      ;
        $this->ChangeRole                       = Session::get('ChangeRole')    ;


        $ultimate_event_login = $this->UltimateEventLogin();
        if($ultimate_event_login[0] == 1 && $ultimate_event_login[1]== null){
            //Redirecciona a la ventan de marcaciones
            Session::put('AssistanceUser'    , true);
        }else{
            if($ultimate_event_login[2] >= date('Y-m-d H:i:s')){
                //Redirecciona a la ventana de espera
                $date = date_create($ultimate_event_login[2]);
                Session::put('AssistanceUser'    , 'stand_by&'.date_format($date,"H:i:s"));
            }else{
                Session::put('AssistanceUser'    , false);
            }
        }
        $this->AssistanceUser   = Session::get('AssistanceUser');

        $cantidadColasAsignadas = User_Queue::select(DB::raw('COUNT(1) AS cantidadColasAsignadas'))->where('user_id',$this->UserId )->get()->toArray();
        ($cantidadColasAsignadas[0]['cantidadColasAsignadas'] > 0 )? $cantidadColasAsignadas = true : $cantidadColasAsignadas = false ;

        Session::put('quantityQueueAssign'     ,$cantidadColasAsignadas);
        $this->quantityQueueAssign = Session::get('quantityQueueAssign');
    }

	/**
	 * [index Función que carga la base del AdminLTE]
	 * @return [view] [Returna la vista base del Admin LTE]
	 */
    public function index()
    {
        return view('/front-admin')->with(array(
            'anexo'     =>  $this->UserAnexo,
            'password'  =>  $this->UserPassword,
            'role'      =>  $this->UserRole
        ));
    }

    public function working()
    {
        return view('/layout/recursos/working');
    }

    public function setQueueAdd(Request $request)
    {
        if($request->ajax()){
            if ($request->QueueAdd){
                ($request->QueueAdd == 'true')? $this->QueueAdd = true : $this->QueueAdd = false;
                Session::put('QueueAdd'     ,$this->QueueAdd   );
                return response()->json($this->QueueAdd);
            }
        }
    }

    public function getVariablesGlobals()
    {
        $requiredAnnexed = ($this->UserRole == 'user')? true : false;
        $assistanceNextHour = '';
        if(!is_bool($this->AssistanceUser)){
            $assistanceNextHour = explode('&',$this->AssistanceUser);
            $assistanceNextHour = $assistanceNextHour[1];
        }
        return response()->json([
            'getUserId'                 => $this->UserId,
            'getUsername'               => $this->UserSystem,
            'getRole'                   => $this->UserRole,
            'getNameComplete'           => $this->UserName,
            'statusChangePassword'      => $this->UserPassword,
            'statusChangeAssistance'    => $this->AssistanceUser,
            'statusQueueAddAsterisk'    => $this->QueueAdd,
            'getRemoteIp'               => $_SERVER['REMOTE_ADDR'],
            'requiredAnnexed'           => $requiredAnnexed,
            'hourServer'                => date('H:i:s'),
            'textDateServer'            => date('d-m-w'),
            'dateServer'                => date('Y-m-d'),
            'annexed'                   => $this->UserAnexo,
            'assistanceNextHour'        => $assistanceNextHour,
            'quantityQueueAssign'       => $this->quantityQueueAssign
        ], 200);
    }

    function  updateStatusAddAgentDashboard(){
        Session::put('statusAddAgentDashboard',true );
    }

    function getStatusAddAgentDashboard (){
        $AgentOnline = AgentOnline::select(DB::raw('COUNT(*) AS count_agent'))->where('agent_name', $this->UserSystem)->get()->toArray();
        $existAgent = $AgentOnline[0]['count_agent'];
        $exits = '';
        if($existAgent != 0){
            $exits = true;
        } else{
            AgentOnline::updateOrCreate(
                ['agent_user_id' => Auth::user()->id],
                [
                    'agent_name' => Auth::user()->username,
                    'agent_role'    => Auth::user()->role,
                    'event_name'    => 'Login',
                    'event_id'      => 11,
                    'event_time'    => number_format(microtime(true)*1000,0,'.','')
                ]);
            $exits = false;
        }
        return response()->json(['statusAddAgentDashboard'   => $exits]);
    }

    function getAgentDashboard(){
        $AgentOnline = AgentOnline::select()->where('agent_user_id','=',$this->UserId )->get()->toArray();
        return response()->json(['statusAddAgentDashboard'   => $AgentOnline[0]]);
    }
}
