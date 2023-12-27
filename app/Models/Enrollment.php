<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'pas_enrollment';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'zoho_id',
        'student_id',
        'student_zoho_id',
        'partner_id',
        'partner_zoho_id',
        'subject',
        'status',
        'grand_total',
        'start_date',
        'program_name',
        'program_zoho_id',
        'completion_date',
        'end_date',
        'end_final_gradedate',
        'username',
        'created_at',
        'updated_at',
    ];

    public $timestamps = false;
}
