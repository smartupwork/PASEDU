<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceBook extends Model
{
    use HasFactory;

    protected $table = 'pas_price_book';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'zoho_id',
        'owner',
        'description',
        'owner',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    //public $timestamps = false;

    public function programs() {
        return $this->hasMany('App\Models\PriceBookProgramMap', 'price_book_zoho_id', 'zoho_id');
    }


}
