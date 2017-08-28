<?php

namespace Cosapi\Http\Controllers;

use Illuminate\Http\Request;

use Cosapi\Collector\Collector;
use Cosapi\Http\Requests;
use Cosapi\Models\User;
use Cosapi\Models\Ubigeos;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Image;
use DB;
use Excel;

class UserController extends CosapiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if ($request->fecha_evento) {
                return $this->list_users();
            } else {
                return view('elements/index')->with(array(
                    'routeReport' => 'elements.list_users.list_user',
                    'titleReport' => 'List Users',
                    'viewButtonSearch' => false,
                    'viewHourSearch' => false,
                    'viewDateSearch' => true,
                    'viewDateSingleSearch'  => false,
                    'exportReport' => 'export_list_user',
                    'nameRouteController' => 'list_users'
                ));
            }
        }
    }

    public function changeProfile(Request $request){
        return view('elements/profile_users/profile_users');
    }

    public function uploadPerfil(Request $request){
        if ($request->ajax()) {

            if(\Input::file('imgAvatar')){
                \File::makeDirectory(public_path().'/storage/', $mode = 0777, true, true);
                if(\Input::get('imgAvatarOriginal') == 'default_avatar.png') { $imageOriginal = ''; }else{ $imageOriginal = \Input::get('imgAvatarOriginal'); }
                $image = \Input::file('imgAvatar');
                if (strstr($image->getMimeType(), 'image/')) {
                    $filename = \Input::get('userName') . time() . '.jpg';
                    $filenamedelete = public_path('storage\\') . $imageOriginal;
                    \File::delete($filenamedelete);
                    Image::make($image)->resize(680, 680)->save(public_path('storage/') . $filename);
                }else{
                    return 'NotImage';
                }
            }else{
                $filename = \Input::get('imgAvatarOriginal');
            }

            $idProfile          = \Input::get('idProfile');
            $userId             = \Input::get('userId');
            $numberDni          = \Input::get('numberDni');
            $numberTelephone    = \Input::get('numberTelephone');
            $idSex              = \Input::get('idSex');
            $birthdate          = \Input::get('birthdate');
            $firstName          = \Input::get('firstName');
            $secondName         = \Input::get('secondName');
            $firstLastName      = \Input::get('firstLastName');
            $secondLastName     = \Input::get('secondLastName');
            $idDepartamento     = \Input::get('idDepartamento');

            if(!\Input::get('idProvincia') || \Input::get('idProvincia') == '' || \Input::get('idProvincia') == null){ $idProvincia = '';  }else{ $idProvincia = \Input::get('idProvincia'); }
            if(!\Input::get('idDistrito') || \Input::get('idDistrito') == '' || \Input::get('idDistrito') == null){ $idDistrito = '';  }else{ $idDistrito = \Input::get('idDistrito'); }

            DB::table('users')
            ->where('id',$userId)
            ->update([
                'primer_nombre'     => $firstName,
                'segundo_nombre'    => $secondName,
                'apellido_paterno'  => $firstLastName,
                'apellido_materno'  => $secondLastName
            ]);

            $idUbigeo = $this->getUbigeo($idDepartamento,$idProvincia,$idDistrito);
            
            if($idUbigeo){
                $ubigeo = $idUbigeo[0]['ubigeo'];
            }else{
                $ubigeo = '10000';
            }

            DB::statement('REPLACE INTO users_profile (id,user_id,dni,telefono,Sexo,fecha_nacimiento,avatar,ubigeo_id)'
                .' VALUES '
                .'("'.$idProfile.'","'.$userId.'","'.$numberDni.'","'.$numberTelephone.'","'.$idSex.'","'.$birthdate.'","'.$filename.'","'.$ubigeo.'")');

        }
        return 'Ok';
    }

    public function viewUser(Request $request){
        if ($request->ajax()) {
            $resultado = User::Select()
                ->with('userProfile')
                ->where('id', $request->userID)
                ->get()
                ->toArray();
        }

        if($resultado[0]['user_profile'] == null) $resultado[0]['user_profile']['avatar'] = 'default_avatar.png';

        return $resultado;

    }

    public function viewUbigeo(Request $request){
        if($request->ajax()) {
            $resultado = Ubigeos::Select()
                ->where('ubigeo', $request->idUbigeo)
                ->groupBy('departamento')
                ->orderby('departamento','asc')
                ->get()
                ->toArray();
        }

        return $resultado;
    }

    public function getUbigeo($departamento,$provincia,$distrito){
        $resultado = Ubigeos::Select('ubigeo')
            ->where('departamento','=',$departamento)
            ->where('provincia','=',$provincia)
            ->where('distrito','=',$distrito)
            ->get()
            ->toArray();

        return $resultado;
    }

    public function viewDepartamento(Request $request){
        if($request->ajax()) {
            $resultado = Ubigeos::Select('departamento')
                ->groupBy('departamento')
                ->orderby('departamento','asc')
                ->get()
                ->toArray();
        }

        return $resultado;
    }

    public function viewProvincia(Request $request){
        if($request->ajax()) {
            $resultado = Ubigeos::Select('provincia')
                ->where('departamento', $request->Departamento)
                ->groupBy('provincia')
                ->orderby('provincia','asc')
                ->get()
                ->toArray();
        }

        return $resultado;
    }

    public function viewDistrito(Request $request){
        if($request->ajax()) {
            $resultado = Ubigeos::Select('distrito')
                ->where('provincia', $request->Provincia)
                ->groupBy('distrito')
                ->orderby('distrito','asc')
                ->get()
                ->toArray();
        }

        return $resultado;
    }

    public function modifyPassword(Request $request){
        if ($request->ajax()){
            if ($request->newPassword){
                $resultado = DB::table('users')
                    ->where('id',$request->userId)
                    ->update([
                        'password'          => Hash::make($request->newPassword),
                        'change_password'   => 1
                    ]);

                return (string)$resultado;
            }
        }
    }

    public function modifyRole(Request $request){
        if ($request->ajax()){
            if ($request->nameRole){

                $resultado = DB::table('users')
                    ->where('id',$request->userId)
                    ->update([
                        'role'    => $request->nameRole,
                    ]);

                if($resultado == true && $this->UserId ==  $request->userId){
                    Session::put('UserRole'   ,Auth::user()->role   );
                }
                return response()->json($resultado);
            }
        }
    }

    public function changeStatus(Request $request){
        if ($request->ajax()){
            if ($request->userID){

                $resultado = DB::table('users')
                    ->where('id',$request->userID)
                    ->update([
                        'estado_id'    => $request->estadoID,
                    ]);

                return response()->json($resultado);
            }
        }
    }

    public function createUser(Request $request){
        if ($request->ajax()){
            if ($request->primerNombre){

                $resultado = DB::table('users')->insert([
                        'primer_nombre'    => $request->primerNombre,
                        'segundo_nombre'   => $request->segundoNombre,
                        'apellido_paterno' => $request->apellidoPaterno,
                        'apellido_materno' => $request->apellidoMaterno,
                        'username'         => $request->userName,
                        'email'            => $request->email,
                        'password'         => Hash::make($request->nuevaContraseña),
                        'role'             => $request->role,
                        'estado_id'        => 1
                    ]);

                return (string)$resultado;
            }
        }
    }

    public function export(Request $request){
        $export_outgoing  = call_user_func_array([$this,'export_'.$request->format_export], [$request->days]);
        return $export_outgoing;
    }

    public function list_users(){
        $query_user_list        = $this->user_list_query();
        $builderview            = $this->builderview($query_user_list);
        $outgoingcollection     = $this->outgoingcollection($builderview);
        $list_users             = $this->FormatDatatable($outgoingcollection);
        return $list_users;
    }

    protected function user_list_query(){
        $user_list_query            = User::select()
            ->get();
        return $user_list_query;
    }

    protected function builderview($user_list_query,$type=''){
        $posicion = 0;
        foreach ($user_list_query as $query) {
            $estado = ($query['estado_id'] == 1) ? "Activo":"Inactivo";
            $builderview[$posicion]['Id']                       = $query['id'];
            $builderview[$posicion]['First Name']               = $query['primer_nombre'];
            $builderview[$posicion]['Second Name']              = $query['segundo_nombre'];
            $builderview[$posicion]['Last Name']                = $query['apellido_paterno'];
            $builderview[$posicion]['Second Last Name']         = $query['apellido_materno'];
            $builderview[$posicion]['Role']                     = $query['role'];
            $builderview[$posicion]['Username']                 = $query['username'];
            $builderview[$posicion]['Estado']                   = $estado;
            $posicion ++;
        }

        if(!isset($builderview)){
            $builderview = [];
        }

        return $builderview;
    }

    protected function outgoingcollection($builderview){
        $outgoingcollection                 = new Collector();
        foreach ($builderview as $view) {

            $outgoingcollection->push([
                'Id'                    => $view['Id'],
                'First Name'            => $view['First Name'],
                'Second Name'           => $view['Second Name'],
                'Last Name'             => $view['Last Name'],
                'Second Last Name'      => $view['Second Last Name'],
                'Username'              => $view['Username'],
                'Role'                  => $view['Role'],
                'Estado'                => $view['Estado'],
                'Change Rol'            => '<a class="btn btn-success btn-xs" onclick="changeRol('. $view['Id'] .')"><i class="fa fa-user"></i></a>',
                'Change Password'       => '<a class="btn btn-danger btn-xs" onclick="changePassword('. $view['Id'] .',true)"><i class="fa fa-key"></i></a>',
                'Change Status'         => '<a class="btn btn-info btn-xs" onclick="changeStatusUser('. $view['Id'] .', \''. $view['Estado'] .'\')"><i class="fa fa-refresh"></i></a>'
            ]);

        }
        return $outgoingcollection;
    }

    protected function BuilderExport($array,$namefile,$format,$location){
        Excel::create($namefile, function($excel) use($array,$namefile) {

            $excel->sheet($namefile, function($sheet) use($array) {
                $sheet->fromArray($array);
            });

        })->store($format,$location);
    }

    protected function export_csv(){
        $filename               = 'list_users_'.time();
        $builderview = $this->builderview($this->user_list_query(),'export');
        $this->BuilderExport($builderview,$filename,'csv','exports');

        $data = [
            'succes'    => true,
            'path'      => ['http://'.$_SERVER['HTTP_HOST'].'/exports/'.$filename.'.csv']
        ];

        return $data;
    }

    protected function export_excel(){
        $filename               = 'list_users_'.time();
        $builderview = $this->builderview($this->user_list_query(),'export');
        $this->BuilderExport($builderview,$filename,'xlsx','exports');

        $data = [
            'succes'    => true,
            'path'      => ['http://'.$_SERVER['HTTP_HOST'].'/exports/'.$filename.'.xlsx']
        ];

        return $data;
    }
}
