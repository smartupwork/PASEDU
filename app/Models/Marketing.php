<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marketing extends Model
{
    use HasFactory;

    protected $table = 'pas_marketing';

    const MARKETING_TYPE_NEWS = 'news';
    const MARKETING_TYPE_ANNOUNCEMENTS = 'announcements';
    const MARKETING_TYPE_UPDATES = 'updates';

    const DID_YOU_KNOW = 'Did you know?';
    const INDUSTRY_TRENDS = 'Industry Trends';
    const OTHER_NEWS = 'Other News';
    const PRESIDENTS_CORNER = 'President\'s Corner';
    const EXECUTIVE_COMMITTEE_MESSAGE = 'Executive Committee Message';
    const NEW_PRODUCTS = 'New Products';
    const PRODUCT_UPDATES = 'Product Updates';
    const SERVICES_UPDATES = 'Services Updates';
    const NEW_MARKETING_COLLATERAL = 'New Marketing Collateral';
    const NEW_BLOG_SM_POSTS = 'New Blog/SM posts';
    const NEW_UPLOADED_WEBINARS = 'New Uploaded Webinars';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'marketing_type',
        'title',
        'description',
        'status',
        /*'created_by',
        'created_at',
        'updated_by',
        'updated_at',*/
    ];

    /*public static function getMarketingType(){
        return [
            self::MARKETING_TYPE_NEWS => 'News',
            self::MARKETING_TYPE_ANNOUNCEMENTS => 'Announcements',
            self::MARKETING_TYPE_UPDATES => 'Updates',
        ];
    }*/

    public static function getNewsTitles(){
        return [
            'dyk' => self::DID_YOU_KNOW,
            'it' => self::INDUSTRY_TRENDS,
            'on' => self::OTHER_NEWS,
            'pc' => self::PRESIDENTS_CORNER,
            'ecm' => self::EXECUTIVE_COMMITTEE_MESSAGE,
            'np' => self::NEW_PRODUCTS,
            'pu' => self::PRODUCT_UPDATES,
            'su' => self::SERVICES_UPDATES,
            'nmc' => self::NEW_MARKETING_COLLATERAL,
            'nbsP' => self::NEW_BLOG_SM_POSTS,
            'nuw' => self::NEW_UPLOADED_WEBINARS,
        ];
    }

    public static function getUpdatesTitles(){
        return [
            'np' => self::NEW_PRODUCTS,
            'pu' => self::PRODUCT_UPDATES,
            'su' => self::SERVICES_UPDATES,
            'nmc' => self::NEW_MARKETING_COLLATERAL,
            'nbsP' => self::NEW_BLOG_SM_POSTS,
            'nuw' => self::NEW_UPLOADED_WEBINARS,
        ];
    }
}
