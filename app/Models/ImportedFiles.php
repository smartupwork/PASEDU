<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportedFiles extends Model
{
    use HasFactory;

    protected $table = 'pas_imported_files';

    public $timestamps = false;
}
