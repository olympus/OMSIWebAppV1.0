<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Role extends Model
{
    protected $table = 'roles';

    public function scopeWhereIn($query, $column, $values)
    {
        return $query->whereIn($column, $values);
    }
}
