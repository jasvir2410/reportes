<?php

namespace Cosapi\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $connection   = 'sapia';
    protected $table        = 'modules';
    protected $primaryKey   = 'id';
}
