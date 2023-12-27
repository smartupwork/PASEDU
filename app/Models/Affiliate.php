<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    use HasFactory;

    protected $table = 'pas_affiliate';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'zoho_id',
        'affiliate_name',
        'status',
    ];

    public $timestamps = true;


    public function priceBook() {
        return $this->hasOne('App\Models\PriceBook', 'zoho_id', 'price_book_zoho_id');
    }

}
