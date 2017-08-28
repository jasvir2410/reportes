<?php

namespace Cosapi\Models;

use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    protected $connection   = 'laravel';
    protected $table        = 'filters';
    protected $primaryKey   = 'id';

    public function column(){
        return $this->belongsTo('Cosapi\Models\Column');
    }

    public function condition(){
        return $this->belongsTo('Cosapi\Models\Condition');
    }

}
