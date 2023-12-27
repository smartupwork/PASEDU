<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    use HasFactory;

    protected $table = 'pas_roles';

    const ROLE_TYPE_PARTNER = 'partner';
    const ROLE_TYPE_USER    = 'user';
}
