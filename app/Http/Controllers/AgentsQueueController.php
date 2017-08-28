<?php

namespace Cosapi\Http\Controllers;

use Cosapi\Models\Queue;
use Cosapi\Models\User_Queue;
use Illuminate\Http\Request;
use Cosapi\Http\Requests;
use DB;

class AgentsQueueController extends CosapiController
{

    /**
     * [Función que llama a la vista principal del módulo]
     * @param Request $request [Retorna datos por POST]
     * @return view [Retorna la vista principal del módulo]
     */
    public function index(Request $request)
    {
        if ($request->ajax()){
            $Agents_queue = $this->query_agents_queue();
            return view('elements/agents_queue/index')->with(array(
                'Users'           => $Agents_queue['Users'],
                'Colas'           => $Agents_queue['Colas'],
                'list_users'      => 'all'
            ));
        }
    }

    /**
     *
     * [Función que llama a la vista de la plantilla de asignamiento, listando solo los usuarios filtrados]
     * @param Request $request
     * @return view [Retorna la vista de la plantilla de asignamiento]
     */
    public function search_users(Request $request)
    {
        if ($request->ajax()){
            if (isset($request->id_usuario) && is_array ($request->id_usuario) == false ){
                //Conversión a tipo Array del parametro enviado para la integración con el Crear Usuario
                $request->id_usuario = array($request->id_usuario);
            }

            $Agents_queue = $this->query_agents_queue($request->id_usuario);

            if(is_array ($request->id_usuario) == false){
                //En caso de que no haya filtrado usuarios
                $request->id_usuario = 'all';
            }else{
                //En caso de que se haya realizado algún filtrado
                $request->id_usuario = $this->users_convert_Array($request->id_usuario);
            }

            return view('elements/agents_queue/agents_queue')->with(array(
                'Users'           => $Agents_queue['Users'],
                'Colas'           => $Agents_queue['Colas'],
                'list_users'      => $request->id_usuario
            ));
        }
    }

    /**
     * [Función que devuelve la información de que colas se encuentran los diferentes usuarios]
     * @param Request $request [Retorna datos por POST]
     * @return Array $UsersCola  [Retorna un array con datos de las Colas asignada a los Usuarios]
     */
    public function mark_form(Request $request)
    {
        if ($request->ajax()){
            $UsersCola = User_Queue::select()->get()->toArray();
            return $UsersCola;
        }
    }

    /**
     * [Función que devuelve la información de los Usuarios y las Colas del proyecto]
     * @return Array $Agents_queue [Devuelve arrays con información de Usuario y Colas registrados en el sistema]
     */
    protected function query_agents_queue($id_usuario = ''){


        $Users                  = $this->query_user($id_usuario);
        $Colas                  = Queue::select()
                                        ->with('estrategia','prioridad')
                                        ->where('estado_id','=',1)
                                        ->orderBy('name')
                                        ->get()
                                        ->toArray();
        $Agents_queue['Users']  = $Users;
        $Agents_queue['Colas']  = $Colas;
        return $Agents_queue;
    }

    /**
     * [Función que guarda la información de a que colas a sido asignado cada Usuario y genera el archivo para
     * el "queues_editore" del Asterisk]
     * @param Request $request [Retorna datos por POST]
     */
    public function assign_queue(Request $request){
        $Agents_queue   = $this->query_agents_queue();
        $queues_editors = [];
        foreach($Agents_queue['Colas'] as $cola){
            foreach($Agents_queue['Users'] as $user){
                // Arma el name_checked es el name de la etiqueta checkbox el cual se almacena el user_id+'_'+queue_id
                $name_checked   = $user['id'].'_'.$cola['id'];
                $name_prioridad = $user['id'];

                if(isset($request->$name_checked)){
                    $queues_editors[$user['id']]['id_user']  = $user['id'];
                    $queues_editors[$user['id']]['cola'][$cola['id']]= $cola['id'].'_'.$request->$name_prioridad;
                }
            }
        }

        if($request->list_users == 'all'){
            //En caso de no realizar un filtro limpiamos lo que hay en la tabla para poder
            //guardar las nuevo estado de asignaciones
            \DB::table('users_queues')->delete();
        }else{
            $request->list_users = explode('-',$request->list_users);
            //En caso de que haya realizado un filtro eliminamos los datos correpondientes a
            //los usuarios, para registrar sus nuevas asignaciones
            \DB::table('users_queues')
                ->whereIn('user_id', $request->list_users)
                ->delete();
        }

        foreach($queues_editors as $key => $queues_editor){
            foreach($queues_editor['cola'] as $key => $queue){
                $assignar_usuario = explode('_',$queue);
                //Registramos las nuevas asignaciones realizadas
                \DB::table('users_queues')->insert(array(
                    'user_id'       => $queues_editor['id_user'],
                    'queue_id'      => $assignar_usuario[0],
                    'priority'      => $assignar_usuario[1]
                ));
            }
        }

    }

    /**
     * [Función para el llenado del filtro de usuarios]
     * @param Request $request
     * @return Lista de usuarios del proyecto
     */
    public function list_users(Request $request)
    {
        if ($request->ajax()) {
            $nombre = $request->name ?: '';
            $Users = $this->query_user_search($nombre);

            $valid_users = [];
            foreach ($Users as $id => $User) {
                $valid_users[] = ['id' => $User->id, 'text' => $User->primer_nombre.' '.$User->apellido_paterno.' '.$User->apellido_materno];
            }

            return response()->json($valid_users);
        }
    }


    /**
     * [Función para la conversion de la lista de Usuarios en una cadena textual]
     * @param $list_users       [Array con lista de usuarios]
     * @return string  $users   [Texto concadenado con  '-' de la información obtenida en el array]
     */
    protected function users_convert_Array($list_users){
        $users='';
        foreach($list_users as $list_user){
            $users=$users.'-'.$list_user;
        }
        $users=substr($users, 1);
        return $users;
    }
}
