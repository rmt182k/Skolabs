<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuRole extends Model
{
    protected $table = 'menu_roles';
    protected $fillable = [
        'menu_id',
        'role_id',
        'can_view',
        'can_create',
        'can_update',
        'can_delete',
    ];
}
