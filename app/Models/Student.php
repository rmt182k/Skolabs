<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    // Define the table associated with the model (optional if it follows Laravel's naming conventions)
    protected $table = 'students';

    protected $guarded = [];
}
