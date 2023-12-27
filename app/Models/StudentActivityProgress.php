<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentActivityProgress extends Model
{
    use HasFactory;

    protected $table = 'student_activity_progress';

    public $timestamps = false;
}
