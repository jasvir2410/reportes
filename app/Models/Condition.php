<?php

namespace Cosapi\Models;

use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    protected $connection   = 'sapia';
    protected $table        = 'conditions';
    protected $primaryKey   = 'id';
}
