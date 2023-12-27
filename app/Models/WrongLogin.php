<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WrongLogin extends Model
{
    use HasFactory;

    protected $table = 'pas_wrong_login';

    public $timestamps = false;
}
