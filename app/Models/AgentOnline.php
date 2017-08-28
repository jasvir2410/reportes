<?php

namespace Cosapi\Models;

use Illuminate\Database\Eloquent\Model;

class AgentOnline extends Model
{
    protected $connection   = 'laravel';
    protected $table        = 'agent_online';
    public $timestamps      = false;

    protected $fillable = ['agent_user_id','agent_role','agent_name','event_name','event_time','event_id'];

}
