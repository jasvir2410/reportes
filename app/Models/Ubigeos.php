<?php

namespace Cosapi\Models;

use Illuminate\Database\Eloquent\Model;

class Ubigeos extends Model
{
    protected $connection   = 'sapia';
    protected $table 		= 'ubigeos';
    protected $primaryKey   = 'ubigeo';
}
