<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PasUsers extends  Authenticatable
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_type',
        'firstname',
        'lastname',
        'email',
        'password',
        'roleid',
        'status',
        'photo',
        'phone',
        'partner',
        'partner_type',
        'augusoft_campus',
        'access_level',
        'access_feature',
    ];

    public $timestamps = false;

}
