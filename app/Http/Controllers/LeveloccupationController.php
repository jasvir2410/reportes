<?php

namespace Cosapi\Http\Controllers;

use Illuminate\Http\Request;
use Cosapi\Http\Requests;
use Cosapi\Collector\Collector;
use Cosapi\Models\DetalleEventosHistory;
use Cosapi\Models\Eventos;
use Cosapi\Models\User;
use Illuminate\Support\Facades\DB;

class LeveloccupationController extends CosapiController
{

    /**
     * [index Función que retorna vista y datos del Level Of Occupation]
     * @param  Request $request [Parametro que recepcion datos enviados por POST]
     * @return [view]           [View o Array con datos del Level Of Occupation]
     */
    public function index(Request $request)
    {
        if ($request->ajax()){
            if ($request->fecha_evento){
                $NivelOcupationHour         = $this->NivelOcupationHour($request->fecha_evento);
                $nivelocupacionBuilderView  = $this->nivelocupacionBuilderView($NivelOcupationHour[0],$NivelOcupationHour[1],$NivelOcupationHour[2]);
                $nivelocupacionCollection   = $this->nivelocupacionCollection($nivelocupacionBuilderView);
                $list_level_occupation      = $this->FormatDatatable($nivelocupacionCollection);


                return $list_level_occupation;
            }else{
                return view('elements/index')->with(array(
                    'routeReport'           => 'elements.level_of_occupation.level_of_occupation',
                    'titleReport'           => 'Report of Level Occupation',
                    'viewButtonSearch'      => true,
                    'viewHourSearch'        => false,
                    'viewDateSearch'        => true,
                    'viewDateSingleSearch'  => false,
                    'exportReport'          => 'export_level_occupation',
                    'nameRouteController'   => 'level_of_occupation'
                ));
            }
        }
    }

    /**
     * [export Función que permite exportar los datos de la tabla]
     * @param  Request $request [Recepciona datos enviado por POST]
     * @return [array]          [Ubicaciones de los archivos a exportar]
     */
    public function export(Request $request){
        $export_contestated  = call_user_func_array([$this,'export_'.$request->format_export], [$request->days]);
        return $export_contestated;
    }

    /**
     * [NivelOcupationHour Función que calcula la acantida de Tiempo entre horas para poder realiza el calculo del Level Of Occupation]
     * @param [string] $day_consult [Fecha la cual se va a realizar la consulta]
     */
    protected function NivelOcupationHour($day_consult){
        $array_day_consult              = explode(' - ', $day_consult);
        $before_date                    = 0;
        $before_event                   = 0;
        $before_hour                    = 0;
        $before_time                    = 0;
        $before_user                    = 0;
        $ocupacion                      = [];
        $Listhour                       = listHoursInterval();

        $query_detalle_eventos          = DetalleEventosHistory::Select_fechamod()
                                                    ->with('evento')
                                                    ->whereBetween(DB::raw("DATE(fecha_evento)"),$array_day_consult)
                                                    ->OrderBy('user_id', 'asc')
                                                    ->OrderBy('fecha_evento', 'asc')
                                                    ->get()->toArray();

        $Usuarios                       = User::get()->toArray();
        $Eventos                        = Eventos::Select()->where('estado_id','=','1')->get();
        $Eventos_Auxiliares             = Eventos::Select('id')->where('eventos_auxiliares','=','1' )->get();
        $Cosapi_Eventos                 = Eventos::Select('id')->where('cosapi_eventos','=','1' )->get();
        $Claro_Eventos                  = Eventos::Select('id')->where('claro_eventos','=','1' )->get();
        $Array_days                     = ArrayDays($day_consult);
        $cantidad_detalles              = count($query_detalle_eventos);

        if($array_day_consult[0] < date('Y-m-d') && $array_day_consult[1] < date('Y-m-d')) {
            foreach ($query_detalle_eventos as $key => $detalle_eventos) {

                $fechaMod = $detalle_eventos['fechamod'];
                $userID = $detalle_eventos['user_id'];
                $hourMod = $detalle_eventos['hourmod'];
                $timeMod = $detalle_eventos['timemod'];
                $eventoID =  $detalle_eventos['evento_id'];

                $second_timemod = convertHourExactToSeconds($timeMod);
                $array_hour = explode(':', $hourMod);
                $hour = $array_hour[0] * 3600;
                $minute = $array_hour[1] * 60;
                $second = $hour + $minute;

                if (!isset($new_detalle[$fechaMod][$userID])) {

                    if (isset($new_detalle)) {
                        $new_detalle = $this->nivel_ocupacion($day_consult, $new_detalle, $Usuarios, $Eventos_Auxiliares, $Cosapi_Eventos, $Claro_Eventos, $Eventos, $before_hour, $before_user, $before_date);
                    }

                    $new_detalle[$fechaMod][$userID][$hourMod]['evento_ini'] = $eventoID;

                    $new_detalle[$fechaMod][$userID][$hourMod][1] = 0;

                    if ($query_detalle_eventos[$key + 1]['user_id'] != $userID or $query_detalle_eventos[$key + 1]['fechamod'] != $fechaMod) {
                        if ($query_detalle_eventos[$key - 1]['evento_id'] != 15) {
                            $new_detalle[$fechaMod][$userID][$hourMod][1] = 1800;
                        }
                    }

                } else {

                    if (!isset($new_detalle[$fechaMod][$userID][$hourMod])) {
                        $new_detalle[$fechaMod][$userID][$hourMod]['evento_ini'] = $eventoID;
                        $new_detalle[$fechaMod][$userID][$hourMod][1] = $second_timemod - $second;
                        $before_second_timemod = convertHourExactToSeconds($before_hour . ':00', 1800);

                        if (!isset($new_detalle[$before_date][$before_user][$before_hour][$before_event])) {
                            $new_detalle[$before_date][$before_user][$before_hour][$before_event] = 0;
                        }

                        $new_detalle[$before_date][$before_user][$before_hour][$before_event] = $new_detalle[$before_date][$before_user][$before_hour][$before_event] + ($before_second_timemod - $before_time);

                        $new_detalle = $this->nivel_ocupacion($day_consult, $new_detalle, $Usuarios, $Eventos_Auxiliares, $Cosapi_Eventos, $Claro_Eventos, $Eventos, $before_hour, $before_user, $before_date);


                    } else {
                        if (!isset($new_detalle[$before_date][$before_user][$before_hour][$before_event])) {
                            $new_detalle[$before_date][$before_user][$before_hour][$before_event] = 0;
                        }
                        $new_detalle[$before_date][$before_user][$before_hour][$before_event] = $new_detalle[$before_date][$before_user][$before_hour][$before_event] + ($second_timemod - $before_time);
                    }
                }

                if (($key + 1) == $cantidad_detalles) {
                    $new_detalle = $this->nivel_ocupacion($day_consult, $new_detalle, $Usuarios, $Eventos_Auxiliares, $Cosapi_Eventos, $Claro_Eventos, $Eventos, $hourMod, $userID, $fechaMod);
                }

                $new_detalle[$fechaMod][$userID][$hourMod]['evento_fin'] = $eventoID;
                $new_detalle[$fechaMod][$userID][$hourMod]['hour'] = $hourMod;
                $before_event = $eventoID;
                $before_time = $second_timemod;
                $before_date = $fechaMod;
                $before_hour = $hourMod;
                $before_user = $userID;

            }


            foreach ($Array_days as $keyd => $days) {
                foreach ($Usuarios as $keyu => $usuario) {
                    $posicion = 0;
                    $fechaMod = $days['fechamod'];
                    $userID = $usuario['id'];
                    $hourMod = $hour['hourmod'];
                    foreach ($Listhour as $keyh => $hour) {
                        //EXISTE REGISTRO
                        if (isset($new_detalle[$fechaMod][$userID][$hourMod])) {
                            //COMIENZA CON UNA HORA DIFERENTE A 00:00
                            if ($hourMod != '00:00' and $posicion == 0) {
                                $evento_ini = $new_detalle[$fechaMod][$userID][$hourMod]['evento_ini'];
                                $listarentreHoras = listarentreHoras('00:00', $hourMod);

                                //EVENTO INICIAL DEL REGISTRO EXISTENTE ES DIFERENTE A "LOGUIN"
                                if ($evento_ini != 11) {
                                    foreach ($listarentreHoras as $hour_faltante) {
                                        $hourFaltante = $hour_faltante['hour'];
                                        foreach ($Eventos as $evento) {
                                            $new_detalle[$fechaMod][$userID][$hourFaltante][$evento['id']] = 0;
                                        }
                                        $new_detalle[$fechaMod][$userID][$hourFaltante][1] = 1800;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['evento_ini'] = 1;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['evento_fin'] = 1;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['logueo'] = 1800;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['outbound'] = 0;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['acw'] = 0;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['indbound'] = 0;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['auxiliares'] = 0;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['hour'] = $hourFaltante;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['88'] = 0;
                                    }
                                } else {
                                    // EVENTO INICIAL DEL REGISTRO ES IGUAL A "LOGIN"
                                    foreach ($listarentreHoras as $hour_faltante) {
                                        $hourFaltante = $hour_faltante['hour'];
                                        foreach ($Eventos as $evento) {
                                            $new_detalle[$fechaMod][$userID][$hourFaltante][$evento['id']] = 0;
                                        }
                                        $new_detalle[$fechaMod][$userID][$hourFaltante][15] = 1800;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['evento_ini'] = 15;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['evento_fin'] = 15;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['logueo'] = 0;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['outbound'] = 0;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['acw'] = 0;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['indbound'] = 0;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['auxiliares'] = 0;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['hour'] = $hourFaltante;
                                        $new_detalle[$fechaMod][$userID][$hourFaltante]['88'] = 0;
                                    }
                                }
                            } else {

                                //REGISTRO EXISTENTE CON HORA DIFERENTE A 23:30
                                if ($hourMod != '23:30') {

                                    //SIGUIENTE REGISTRO NO EXISTE
                                    if (!isset($new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']])) {

                                        $evento_fin = $new_detalle[$fechaMod][$userID][$hourMod]['evento_fin'];

                                        //EL ULTIMO EVENTO DEL REGISTRO EXISTENTE ES "DESCONECTADO"
                                        if ($evento_fin == 15) {

                                            $datetime = $fechaMod . ' ' . $Listhour[$keyh + 1]['hourmod'] . ':00';

                                            if ($datetime <= date("Y-m-d H:i:s")) {

                                                foreach ($Eventos as $evento) {
                                                    $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']][$evento['id']] = 0;
                                                }
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']][15] = 1800;
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['evento_ini'] = 15;
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['evento_fin'] = 15;
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['logueo'] = 0;
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['outbound'] = 0;
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['acw'] = 0;
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['indbound'] = 0;
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['auxiliares'] = 0;
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['hour'] = $Listhour[$keyh + 1]['hourmod'];
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['88'] = 0;
                                            }

                                        } else {
                                            //EL ULTIMO EVENTO DEL REGISTRO EXISTENTE ES DIFERENTE A "DESCONECTADO"

                                            $datetime = $fechaMod . ' ' . $Listhour[$keyh + 1]['hourmod'] . ':00';

                                            if ($datetime <= date("Y-m-d H:i:s")) {

                                                foreach ($Eventos as $evento) {
                                                    $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']][$evento['id']] = 0;
                                                }
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']][$evento_fin] = 1800;
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['evento_ini'] = $evento_fin;
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['evento_fin'] = $evento_fin;
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['logueo'] = 1800;
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['outbound'] = 0;
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['acw'] = 0;
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['indbound'] = 0;
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['auxiliares'] = 0;

                                                foreach ($Eventos_Auxiliares as $eventos_auxiliar) {
                                                    if ($eventos_auxiliar['id'] == $evento_fin) {
                                                        $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['auxiliares'] = 1800;
                                                    }
                                                }

                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['hour'] = $Listhour[$keyh + 1]['hourmod'];
                                                $new_detalle[$fechaMod][$userID][$Listhour[$keyh + 1]['hourmod']]['88'] = 0;
                                            }
                                        }
                                    }
                                }
                            }
                            $posicion++;
                        }
                    }
                }
            }

            $ocupacion = [];
            foreach ($Array_days as $days) {
                foreach ($Listhour as $hour) {

                    $fechaMod = $days['fechamod'];
                    $hourMod = $hour['hourmod'];

                    $datetime = $fechaMod . ' ' . $hourMod . ':00';

                    if ($datetime <= date("Y-m-d H:i:s")) {

                        foreach ($Usuarios as $usuario) {
                            $userID = $usuario['id'];
                            if (isset($new_detalle[$fechaMod][$userID][$hourMod])) {
                                if (!isset($ocupacion [$fechaMod][$hourMod])) {
                                    $ocupacion[$fechaMod][$hourMod]['cantidad'] = 1;
                                    $ocupacion[$fechaMod][$hourMod]['total_indbound'] = 0;
                                    $ocupacion[$fechaMod][$hourMod]['total_outbound'] = 0;
                                    $ocupacion[$fechaMod][$hourMod]['total_acw'] = 0;
                                    $ocupacion[$fechaMod][$hourMod]['total_outbound'] = 0;
                                    $ocupacion[$fechaMod][$hourMod]['total_auxiliares'] = 0;
                                    $ocupacion[$fechaMod][$hourMod]['total_logueo'] = 0;
                                }
                                $ocupacion[$fechaMod][$hourMod]['cantidad'] = $ocupacion [$fechaMod][$hourMod]['cantidad'] + 1;
                                $ocupacion[$fechaMod][$hourMod]['total_indbound'] = $ocupacion[$fechaMod][$hourMod]['total_indbound'] + $new_detalle[$fechaMod][$userID][$hourMod]['indbound'];
                                $ocupacion[$fechaMod][$hourMod]['total_acw'] = $ocupacion[$fechaMod][$hourMod]['total_acw'] + $new_detalle[$fechaMod][$userID][$hourMod]['acw'];
                                $ocupacion[$fechaMod][$hourMod]['total_outbound'] = $ocupacion[$fechaMod][$hourMod]['total_outbound'] + $new_detalle[$fechaMod][$userID][$hourMod]['outbound'];
                                $ocupacion[$fechaMod][$hourMod]['total_auxiliares'] = $ocupacion[$fechaMod][$hourMod]['total_auxiliares'] + $new_detalle[$fechaMod][$userID][$hourMod]['auxiliares'];
                                $ocupacion[$fechaMod][$hourMod]['total_logueo'] = $ocupacion[$fechaMod][$hourMod]['total_logueo'] + $new_detalle[$fechaMod][$userID][$hourMod]['logueo'];
                            }
                        }

                        $formula_inferior = ($ocupacion[$fechaMod][$hourMod]['total_logueo'] - $ocupacion[$fechaMod][$hourMod]['total_auxiliares']);


                        if ($formula_inferior != 0) {
                            $ocupacion[$fechaMod][$hourMod]['total_cosapi'] = round(($ocupacion[$fechaMod][$hourMod]['total_indbound'] + $ocupacion[$fechaMod][$hourMod]['total_outbound'] + $ocupacion[$fechaMod][$hourMod]['total_acw']) / ($formula_inferior) * 100, 2);
                        } else {
                            $ocupacion[$fechaMod][$hourMod]['total_cosapi'] = round(0, 2);
                        }
                        $ocupacion[$fechaMod][$hourMod]['hora'] = $hourMod;
                        $ocupacion[$fechaMod][$hourMod]['fecha'] = $fechaMod;
                    }
                }
            }
        }

        //$nivelocupacion = $this->nivelocupacionCollection($ocupacion, $Array_days, $Listhour);

        return array($ocupacion,$Array_days, $Listhour);
    }



    /**
     * [nivel_ocupacion Función que calcula el Level Of Occupation]
     * @param  [string] $day_consult        [Fecha de consulta, Ejem: '2016-10-22 - 2016-10-25']
     * @param  [string] $new_detalle        [Array con los tiempo acumulados por evento del agente]
     * @param  [int]    $Usuarios           [Id del Usuario]
     * @param  [string] $Eventos_Auxiliares [Id de los eventos auxiliares]
     * @param  [string] $Cosapi_Eventos     [Id de los eventos que considera Cosapi para el nivel de ocupacion]
     * @param  [string] $Claro_Eventos      [Id de los eventos que considera Claro para el nivel de ocupacion ]
     * @param  [string] $Eventos            [Id de todos los eventos activos]
     * @param  [string] $before_hour        [Hora anterior]
     * @param  [string] $before_user        [Usuario anterior]
     * @param  [string] $before_date        [Fecha anterior]
     * @return [array]                      [Array con datos de Level Of Occupation]
     */
    protected function nivel_ocupacion($day_consult,$new_detalle,$Usuarios,$Eventos_Auxiliares,$Cosapi_Eventos,$Claro_Eventos,$Eventos,$before_hour,$before_user,$before_date){
       $indbound   = 0;
       $auxiliares = 0;
       $cosapi     = 0;
       $claro      = 0;
       $logueo     = 0;
       $acw        = 0;
       $outbound   = 0;


        if (!empty($day_consult) && !empty($Usuarios[$before_user-1]['username']) && !empty($before_hour)){

            //CALCULANDO TIEMPOS DE LLAMADAS ENTRANTES
            $inboundCalls           = new IncomingCallsController();
            $result_tiempoatendida  = $inboundCalls->query_calls($day_consult,'calls_completed',$Usuarios[$before_user-1]['username'],$before_hour);

            if (isset($result_tiempoatendida) or count($result_tiempoatendida) != 0){
                foreach ($result_tiempoatendida as $tiempoatendida) {
                    if($tiempoatendida['hourmod'] == $tiempoatendida['hour_final']){
                        $indbound = $indbound + abs($tiempoatendida['info2']);
                    }else if($tiempoatendida['hourmod'] != $tiempoatendida['hour_final'] and $tiempoatendida['hour_final'] > $tiempoatendida['hourmod']){
                        $second_registro = convertHourExactToSeconds($tiempoatendida['timemod']);
                        if($tiempoatendida['hour_final'] != '00:00') {
                            $second_tope = convertHourExactToSeconds($tiempoatendida['hour_final'] . ':00');
                        }else{
                            $second_tope = 86400;
                        }
                        $second_duracion=$second_tope-$second_registro;
                        $indbound = $indbound + abs($second_duracion);
                    }
                }

                // 88 = Inbound sacado de la tabla "Queue_stats_mv"
                $new_detalle[$before_date][$before_user][$before_hour]['88'] = $indbound;

                //  CALCULANDO TIEMPOS AUXILIARES
                foreach ($Eventos_Auxiliares as $eventos_auxiliar){
                    if(!isset($new_detalle[$before_date][$before_user][$before_hour][$eventos_auxiliar['id']])){
                        $new_detalle[$before_date][$before_user][$before_hour][$eventos_auxiliar['id']] = 0;
                    }
                    $auxiliares = $auxiliares + $new_detalle[$before_date][$before_user][$before_hour][$eventos_auxiliar['id']];
                }

                // CALCULANDO TIEMPOS COSAPI
                foreach ($Cosapi_Eventos as $cosapi_evento){
                    if(!isset($new_detalle[$before_date][$before_user][$before_hour][$cosapi_evento['id']])){
                        $new_detalle[$before_date][$before_user][$before_hour][$cosapi_evento['id']] = 0;
                    }

                    if($cosapi_evento['id'] != 8){
                        $cosapi = $cosapi + $new_detalle[$before_date][$before_user][$before_hour][$cosapi_evento['id']];
                    }
                }

                // CALCULANDO TIEMPOS CLARO
                foreach ($Claro_Eventos as $claro_evento){
                    if(!isset($new_detalle[$before_date][$before_user][$before_hour][$claro_evento['id']])){
                        $new_detalle[$before_date][$before_user][$before_hour][$claro_evento['id']] = 0;
                    }

                    if($claro_evento['id'] != 8){
                        $claro = $claro + $new_detalle[$before_date][$before_user][$before_hour][$claro_evento['id']];
                    }
                }

                // CALCULANDO TIEMPOS LOGUEO
                foreach ($Eventos as $evento){
                    if($evento['id'] != 8){
                        if(!isset($new_detalle[$before_date][$before_user][$before_hour][$evento['id']])){
                            $new_detalle[$before_date][$before_user][$before_hour][$evento['id']] = 0;
                        }
                        $logueo = $logueo + $new_detalle[$before_date][$before_user][$before_hour][$evento['id']];
                    }

                    if($evento['id'] == 10){
                        if(!isset($new_detalle[$before_date][$before_user][$before_hour][$evento['id']])){
                            $new_detalle[$before_date][$before_user][$before_hour][$evento['id']] = 0;
                        }
                        $acw = $acw + $new_detalle[$before_date][$before_user][$before_hour][$evento['id']];
                    }


                    if($evento['id'] == 9){
                        if(!isset($new_detalle[$before_date][$before_user][$before_hour][$evento['id']])){
                            $new_detalle[$before_date][$before_user][$before_hour][$evento['id']] = 0;
                        }
                        $outbound = $outbound + $new_detalle[$before_date][$before_user][$before_hour][$evento['id']];
                    }

                }
            }

        }



       $new_detalle[$before_date][$before_user][$before_hour]['indbound']     = $indbound;
       $new_detalle[$before_date][$before_user][$before_hour]['acw']          = $acw;
       $new_detalle[$before_date][$before_user][$before_hour]['outbound']     = $outbound;
       $new_detalle[$before_date][$before_user][$before_hour]['auxiliares']   = $auxiliares;
       $new_detalle[$before_date][$before_user][$before_hour]['logueo']       = $logueo + $indbound;

        return $new_detalle;
    }


    /**
     * [nivelocupacionCollection Función que transforma el array en collection]
     * @param  [array]          $ocupacion  [Array con los datos del reporte de Level Of Occupation]
     * @param  [array]          $Array_days [Array con todos los días que conforman el rango de fecha en consulta]
     * @param  [array]          $Listhour   [Array con rango de hora cada 30 minutos desde las 0 horas hasta las  23:30]
     * @return [colllection]                [Collection con datos para el reporte de Level Of Ocupation]
     */
    protected function nivelocupacionCollection($builderview){
        $outgoingcollection                 = new Collector;
        foreach ($builderview as $view) {
            $outgoingcollection->push([
                'date'                => $view['date'],
                'hour'                => $view['hour'],
                'indbound'            => $view['indbound'],
                'acw'                 => $view['acw'],
                'outbound'            => $view['outbound'],
                'auxiliares'          => $view['auxiliares'],
                'logueo'              => $view['logueo'],
                'occupation_cosapi'   => $view['occupation_cosapi']
            ]);
        }

        return $outgoingcollection;
    }

    protected function nivelocupacionBuilderView($ocupacion,$Array_days,$Listhour){
        $posicion = 0;
        foreach($Array_days as $days){
            foreach($Listhour as $hour){
                $datetime = $days['fechamod'].' '.$hour['hourmod'].':00';

                if($datetime <= date("Y-m-d H:i:s") && $ocupacion != []){
                    $builderview[$posicion]['date']              = $ocupacion[$days['fechamod']][$hour['hourmod']]['fecha'];
                    $builderview[$posicion]['hour']              = $hour['name'];
                    $builderview[$posicion]['indbound']          = conversorSegundosHoras($ocupacion[$days['fechamod']][$hour['hourmod']]['total_indbound'], false);
                    $builderview[$posicion]['acw']               = conversorSegundosHoras($ocupacion[$days['fechamod']][$hour['hourmod']]['total_acw'], false);
                    $builderview[$posicion]['outbound']          = conversorSegundosHoras($ocupacion[$days['fechamod']][$hour['hourmod']]['total_outbound'], false);
                    $builderview[$posicion]['auxiliares']        = conversorSegundosHoras($ocupacion[$days['fechamod']][$hour['hourmod']]['total_auxiliares'], false);
                    $builderview[$posicion]['logueo']            = conversorSegundosHoras($ocupacion[$days['fechamod']][$hour['hourmod']]['total_logueo'], false);
                    $builderview[$posicion]['occupation_cosapi'] = $ocupacion[$days['fechamod']][$hour['hourmod']]['total_cosapi'];

                }else{
                    $builderview[$posicion]['date']              = $days['fechamod'];
                    $builderview[$posicion]['hour']              = $hour['name'];
                    $builderview[$posicion]['indbound']          =  conversorSegundosHoras(0, false);
                    $builderview[$posicion]['acw']               = conversorSegundosHoras(0, false);
                    $builderview[$posicion]['outbound']          = conversorSegundosHoras(0, false);
                    $builderview[$posicion]['auxiliares']        = conversorSegundosHoras(0, false);
                    $builderview[$posicion]['logueo']            = conversorSegundosHoras(0, false);
                    $builderview[$posicion]['occupation_cosapi'] = 0;
                }
                $posicion ++;
            }
        }

        if(!isset($builderview)){
            $builderview = [];
        }

        return $builderview;
    }

    /**
     * [export_csv Function que retorna la ubicación de los datos a exportar en CSV]
     * @param  [string] $days [Fecha de la consulta]
     * @return [array]        [Array con la ubicación donde se a guardado el archivo exportado en CSV]
     */
    protected function export_csv($days){
        $filename               = 'level_occupation_'.time();
        $NivelOcupationHour=$this->NivelOcupationHour($days);
        $builderview = $this->nivelocupacionBuilderView($NivelOcupationHour[0],$NivelOcupationHour[1],$NivelOcupationHour[2]);
        $this->BuilderExport($builderview,$filename,'csv','exports');

        $data = [
            'succes'    => true,
            'path'      => ['http://'.$_SERVER['HTTP_HOST'].'/exports/'.$filename.'.csv']
        ];

        return $data;
    }

    /**
     * [export_excel Function que retorna la ubicación de los datos a exportar en Excel]
     * @param  [string] $days [Fecha de la consulta]
     * @return [array]        [Array con la ubicación donde se a guardado el archivo exportado en Excel]
     */
    protected function export_excel($days){
        $filename               = 'level_occupation_'.time();
        $NivelOcupationHour = $this->NivelOcupationHour($days);
        $builderview = $this->nivelocupacionBuilderView($NivelOcupationHour[0],$NivelOcupationHour[1],$NivelOcupationHour[2]);
        $this->BuilderExport($builderview,$filename,'xlsx','exports');

        $data = [
            'succes'    => true,
            'path'      => ['http://'.$_SERVER['HTTP_HOST'].'/exports/'.$filename.'.xlsx']
        ];

        return $data;
    }

}
