<?php

namespace Cosapi\Models;

use Illuminate\Database\Eloquent\Model;

class Column extends Model
{
    protected $connection   = 'sapia';
    protected $table        = 'columns';
    protected $primaryKey   = 'id';

    public function module(){
        return $this->belongsTo('Cosapi\Models\Module');
    }
}
