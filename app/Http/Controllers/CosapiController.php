<?php

namespace Cosapi\Http\Controllers;
use Cosapi\Models\Anexo;
use Cosapi\Models\Queue;
use Cosapi\Models\User;
use Illuminate\Http\Request;
use Cosapi\Facades\phpAMI;
use Cosapi\Http\Requests;

use Illuminate\Support\Facades\DB;
use Cosapi\Models\DetalleEventos;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

use Datatables;
use Excel;
use Carbon\Carbon;

class CosapiController extends Controller
{
    function __construct()
    {
        $type_password = 0;

        if (!Session::has('UserId')) {
            Session::put('UserId'   ,Auth::user()->id   );
        }

        if (!Session::has('UserRole')) {
            Session::put('UserRole'   ,Auth::user()->role   );
        }

        if (!Session::has('UserName')) {
            Session::put('UserName'   ,Auth::user()->primer_nombre.' '.Auth::user()->apellido_paterno );
        }

        if (!Session::has('UserSystem')) {
            Session::put('UserSystem'   ,Auth::user()->username);
        }


        // Tipo de password modificado
        if(Auth::user()->password == '$2y$10$9TTAuZKJgHLvDfDlvt2fY.vWlj2EqwG6iGLJ.zuaxbqaA3.EBXPOW'){
            $type_password = 1; // Tipo de password por defecto
        }
        Session::put('UserPassword'   ,$type_password);


        $Anexos = Anexo::select('name')->where('user_id','=',Auth::user()->id )->get()->toArray();
        if(count($Anexos) != 0){
            $name_anexo = $Anexos[0]['name'];
            Session::put('UserAnexo'   ,$name_anexo);
        }else{
            Session::put('UserAnexo'   ,'Sin Anexo'   );
        }

        $this->UserId       = Session::get('UserId')        ;
        $this->UserRole     = Session::get('UserRole')      ;
        $this->UserName     = Session::get('UserName')      ;
        $this->UserSystem   = Session::get('UserSystem')    ;
        $this->UserPassword = Session::get('UserPassword')  ;
        $this->UserAnexo    = Session::get('UserAnexo')     ;
    }

    /**
     * [MostrarFechaActual Función que permite obtener la Fecha Actual]
     */
    protected  function MostrarFechaActual(){
        $date = Carbon::now();
        $date = $date->format('Y-m-d');
        return $date;
    }

    /**
     * [MostrarSoloHora Función que permite extraer la hora de la fecha completa '2016-10-25 12:55:42' => '12:55:42']
     * @param [datetime] $fecha [Fecha en formato Datetime, Ejem: '2016-10-02 15:45:44']
     */
    protected  function MostrarSoloHora($fecha){
        $date='00:00:00';
        if( ! empty($fecha)){
            $date = Carbon::parse($fecha);
            $date = $date->toTimeString();
        }
        return $date;
    }

    /**
     * [MostrarSoloFecha Función que permite extraer la fecha de la fecha completa '2016-10-25 12:55:42' => '2016-10-25']
     * @param [datetime] $fecha [Fecha en formato Datetime, Ejem: '2016-10-02 15:45:44']
     */
    protected  function MostrarSoloFecha($fecha){
        if( ! empty($fecha)){
            $date = Carbon::parse($fecha);
            $date = $date->toDateString();
            return $date;
        }
    }

    /**
     * [FormatDatatable Función que retorna en formato Datatable]
     * @param [Collection] $collection [Collection con los datos a mostrar en los reportes]
     */
    protected function FormatDatatable($collection)
    {
        return Datatables::of($collection)
                        ->make(true);
    }

    /**
     * [BuilderExport Función que permite crear archivoz para exportar]
     * @param [Array]  $array    [Manda los datos a exportar]
     * @param [String] $namefile [Nombre del archivo a generar]
     * @param [String] $format   [Formato en que va a exportar Ejmple: CSV,Excel]
     * @param [Array]  $location [Ubicación del archivo exportado]
     */
    protected function BuilderExport($array,$namefile,$format,$location){
        Excel::create($namefile, function($excel) use($array,$namefile) {

            $excel->sheet($namefile, function($sheet) use($array) {
                $sheet->fromArray($array);
            });

        })->store($format,$location);
    }

    /**
     * [get_ultimo_evento Función que permite obtener el último eventodel usuario]
     * @param  [int]   $user_id [Id del usuario]
     * @return [Array]          [Array con el ultimo evento realizado por el agente]
     */
    protected function get_ultimo_evento ($user_id)
    {

        $ultimo_evento   = DetalleEventos::Select('evento_id')
                         ->where('user_id','=',$user_id)
                         ->orderby ('fecha_evento', 'desc')
                         ->first();

        return $ultimo_evento;
    }


    /**
     * [registrar_eventos description]
     * @param  [string] $evento_id     [El id del evento de pausa que acciono el agente via web.]
     * @param  [string] $user_id       [Capturar el ID del usuario conectado]
     * @param  [string] $anexo         [El anexo del agente]
     * @param  [string] $fecha_evento  [La fecha de la realización del evento (Nivel de Ocupacion)]
     * @param  [string] $fecha_really  [La fecha real de realización del evento]
     * @param  [string] $observaciones [Obersvación del evento]
     */
    public function registrar_eventos($evento_id,$user_id,$anexo = '',$fecha_evento = '',$fecha_really = '',$observaciones = '')
    {
        /** Guarda Eventos  */
        if($fecha_evento == ''){ $fecha_evento = Carbon::now(); }else{ $fecha_evento = $fecha_evento; }
        if($fecha_really == ''){ $fecha_really = Carbon::now(); }else{ $fecha_really = $fecha_really; }

        \DB::table('detalle_eventos')->insert(array(
            'evento_id'     => $evento_id,
            'user_id'       => $user_id,
            'fecha_evento'  => $fecha_evento,
            'date_really'   => Carbon::now(),
            'anexo'         => $anexo,
            'observaciones' => $observaciones
        ));

    }

    /**
     * [Función que devuelte la lista de vdn de las Colas]
     * @return Array $list_queues
     */
    protected function list_vdn(){
        $list_queues                                = [];
        $query_queues                               = Queue::select()->get()->toArray();
        foreach($query_queues as $queues){
            $list_queues[$queues['name']]['vdn']    = $queues['vdn'];
        }
        return $list_queues;
    }

    /**
     * [Funcion que retorna las Colas que pueden visualzarce ]
     * @return array $queues_proyect [Array con la colas]
     */
    protected function queues_proyect(){
        $queues_proyect     = [];
        $posicion           = 0;
        $query_queues       = Queue::select()->get()->toArray();
        foreach($query_queues as $queues){
            $queues_proyect[$posicion] = $queues['name'];
            $posicion++;
        }
        return $queues_proyect;
    }

    /**
     * [Función que lista usuarios del sistema]
     * @param $id_usuario   [Array con id de los usuarios a mostrar información]
     * @return mixed        [Array con datos de los usuarios solicitados]
     */
    protected function query_user($id_usuario){
        $Users                  = User::select()
                                        ->filtro_usuarios($id_usuario)
                                        ->where('estado_id','=','1')
                                        ->orderBy('primer_nombre')
                                        ->orderBy('apellido_paterno')
                                        ->orderBy('apellido_paterno')
                                        ->get()
                                        ->toArray();
        return $Users;
    }

    /**
     * [Función que muestra la lista de usuarios cuyo nombre coinciden con el caracter ingresado]
     * @param string $nombre [Palabra a buscar que debe incluir en el nombre de la persona]
     * @return mixed         [Lista de usuarios que coinciden con la busqueda]
     */
    protected function query_user_search($nombre = ''){
        $Users                  = User::select('id','primer_nombre','apellido_paterno','apellido_materno')
                                        ->where(DB::raw('CONCAT(primer_nombre," ",apellido_paterno," ",apellido_materno)'), 'like', '%'.$nombre.'%')
                                        ->orderBy('primer_nombre')
                                        ->orderBy('apellido_paterno')
                                        ->orderBy('apellido_paterno')
                                        ->get();

        return $Users;
    }

    /**
     * [Función que lista datos de las colas]
     * @return array [Array con datos de cada cola registrada en el sistema]
     */
    protected function list_queue(){
        $list_prioridad     = [];
        $Colas              = Queue::select()
                                    ->with('estrategia','prioridad')
                                    ->where('estado_id','=',1)
                                    ->get()->toArray();
        foreach($Colas as $Cola){
            $list_prioridad[$Cola['id']]['id']         = $Cola['id'];
            $list_prioridad[$Cola['id']]['name']       = $Cola['name'];
            $list_prioridad[$Cola['id']]['estrategia'] = $Cola['estrategia']['name'];
            $list_prioridad[$Cola['id']]['prioridad']  = $Cola['prioridad']['name'];
            $list_prioridad[$Cola['id']]['vdn']        = $Cola['vdn'];
        }
        return $list_prioridad;
    }

    /**
     * @param $evento_id : El id del evento de pausa que acciono el agente via web.
     * @param $user_id   : Capturar el ID del usuario conectado.
     */
    public function register_event($evento_id, $user_id, $user_rol, $anexo = '', $fecha_evento = '', $observaciones = '', $date_really ='')
    {
        /** Guarda Eventos  */
        if($fecha_evento == '')     { $fecha_evento = Carbon::now(); } else { $fecha_evento = $fecha_evento; }
        if($date_really  == null)   { $date_really  = null; } else { $date_really  = Carbon::now(); }

        \DB::table('detalle_eventos')->insert(array(
            'evento_id'     => $evento_id,
            'user_id'       => $user_id,
            'fecha_evento'  => $fecha_evento,
            'date_really'   => $date_really,
            'anexo'         => $anexo,
            'observaciones' => $observaciones,
            'ip_cliente'    => $_SERVER['REMOTE_ADDR'],
            'user_rol'      => $user_rol
        ));

    }


    public function register_agent_dashboard($user_name,$anexo)
    {
        \DB::table('agent_online')->insert(array(
            'name_agent'     => 'Agent/'.$user_name,
            'number_annexed' => $anexo,
            'name_event'     => 'Login'
        ));

    }

    /**
     * [Function que devuelve datos de el primer logueo realizado por el usuario]
     * @return array
     */
    protected function UltimateEventLogin(){
        $users      = DetalleEventos::Select(DB::raw('count(*) as cant_event,id,date_really,fecha_evento'))
            ->where('user_id','=',$this->UserId)
            ->where('evento_id','=',11)
            ->where(DB::raw('DATE(fecha_evento)'),'=',date('Y-m-d'))
            ->orderBy('id', 'asc')
            ->get();
        foreach ($users as $user) {
            $cant_event     = $user->cant_event;
            $date_really    = $user->date_really;
            $fecha_evento   = $user->fecha_evento;
            $id             = $user->id;
        }

        return array($cant_event,$date_really, $fecha_evento, $id);
    }

    /**
     * [Funcion para obtener el tamaño de los anexos]
     */
    protected function lengthAnnexed(){
        $lengthAnnexed = Anexo::select(DB::raw('LENGTH(name) AS annexed_length'))
                                ->groupBy(DB::raw('LENGTH(name)'))
                                ->get()
                                ->toArray();
        return $lengthAnnexed;
    }

    /**
     * [Funcion para crear una carpeta y no crearla si ya existe]
     */
    public function makeDirectory($path, $mode = 0777, $recursive = false, $force = false)
    {
        if ($force){
            return @mkdir($path, $mode, $recursive);
        }else{
            return mkdir($path, $mode, $recursive);
        }
    }

}