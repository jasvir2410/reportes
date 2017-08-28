<?php

namespace Cosapi\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Queue_Log extends Model
{
    protected $connection   = 'laravel';
    protected $table        = 'queue_stats_mv';
    protected $primaryKey   = 'id';



    public function scopeFiltro_id_cdr($query,$value)
    {
        if(	! empty($value))
        {
			return $query->wherein('id_cdr', $value);
        }
    }

    public function scopeFiltro_events($query,$events)
    {

        if( ! empty($events))
        {
            return    $query->whereIn('event', $events);
        }

    }

    public function scopeFiltro_days($query,$days)
    {

        if( ! empty($days))
        {
            return    $query->whereBetween(DB::raw("DATE(datetime)"),$days);
        }

    }


    public function scopeSelect_fechamod($query,$rank_hour = 30)
    {
        return $query->Select(DB::raw("*,DATE(datetime) as fechamod, TIME(datetime) AS timemod,  DATE_FORMAT((DATE_SUB(DATETIME, INTERVAL ( MINUTE(DATETIME)%$rank_hour )MINUTE)), '%H:%i') AS hourmod, DATE_FORMAT((DATE_SUB(DATE_FORMAT(DATE_ADD(DATETIME, INTERVAL info2 SECOND), '%Y-%m-%d %H:%i:%s'),INTERVAL ( MINUTE( DATE_FORMAT(DATE_ADD(DATETIME, INTERVAL info2 SECOND), '%Y-%m-%d %H:%i:%s'))%$rank_hour) MINUTE)), '%H:%i') AS hour_final " ));
    }


    public function scopeFiltro_hours($query,$hours)
    {

        if($hours != '')
        {
            return    $query->where(DB::raw("DATE_FORMAT((DATE_SUB(DATETIME, INTERVAL ( MINUTE(DATETIME)%30 )MINUTE)), '%H:%i')" ),'like',$hours);
        }

    }


    public function scopeFiltro_user_rol($query,$rol,$user_name)
    {

        if( $rol == 'user' || $rol == 'backoffice')
        {
            return    $query->where('agent','=','Agent/'.$user_name);
        }

    }

    public function scopeFiltro_Time($query, $metrica){
        if($metrica['action'] == 'true'){
            return $query->where(DB::raw('CAST(info1 AS INTEGER)'),$metrica["symbol"], $metrica["time"]);
        }
    }

    public function scopeFiltro_tabla($query){
        $module_list    = '';
        $filters        = Filter::select()->with('column','condition')->where('estado_id','1')->get()->toArray();
        $modules        = Module::select()->get()->toArray();

        foreach ($modules as $module){ $module_list[$module['id']]=$module['name']; }

        foreach($filters as $key => $filter){
            if($module_list[$filter['column']['module_id']] == 'Queue_Log'){

                //Funcion que se le aplica a la columna de la tabla
                $column = ($filter['column']['apply'] != null)? $filter['column']['apply'].'('.$filter['column']['name'].')' : $filter['column']['name'];

                //Funcion que aplica el usuario a la columna de la tabla
                $column = ($filter['apply'] != null)? $filter['apply'].'('.$column.')' : $column;

                $query  = $query->where(DB::raw($column), $filter['condition']['name'], $filter['value']);
            }
        }

        return $query;
    }

    function scopeFiltro_usernameSearch($query, $username){
        if($username != ''){
            return    $query->where('agent','=','Agent/'.$username);
        }
    }
}
