<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menus';
    protected $fillable = [
        'name',
        'route',
        'icon',
        'parent_id',
        'order',
    ];
}
