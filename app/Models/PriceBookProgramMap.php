<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceBookProgramMap extends Model
{
    use HasFactory;

    protected $table = 'pas_price_book_program_map';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'price_book_id',
        'price_book_zoho_id',
        'program_id',
        'program_zoho_id',
    ];

    public $timestamps = false;

    public function program() {
        return $this->hasOne('App\Models\Program', 'zoho_id', 'program_zoho_id');
    }

    /*public function programDisplayAll() {
        return $this->hasOne('App\Models\Program', 'zoho_id', 'program_zoho_id')
            ->where('status', '=', 'Active')
            ->where('displayed_on', '=', 'All');;
    }

    public function partners() {
        return $this->hasMany('App\Models\Partner', 'price_book_zoho_id', 'price_book_zoho_id');
    }*/
}
