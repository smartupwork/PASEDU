<?php

namespace App\Models;

//use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Utility;
use Faker\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'pas_users';

    const USER_TYPE_ADMIN   = 1;
    const USER_TYPE_PARTNER = 2;
    const USER_TYPE_WE_USER = 3;
    const USER_TYPE_MY_USER = 4;

    // Partner User Roles
    const ROLE_ACCOUNT_MANAGER          = 1;
    const ROLE_ACCOUNT_SUPPORT          = 2;
    CONST ROLE_REGISTRATION_ACCOUNT     = 3;

    // Portal User Roles
    const ROLE_MANAGER                  = 4;
    const ROLE_READ_ONLY                = 5;
    const ROLE_MARKETING_MANAGER        = 6;
    const ROLE_SALES_TEAM               = 7;

    const ACCESS_LEVEL_FULL             = 'full-access';
    const ACCESS_LEVEL_ACCOUNT_MANAGER  = 'account-manager';
    const ACCESS_LEVEL_ACCOUNT_SUPPORT  = 'account-support';
    const ACCESS_LEVEL_REGISTRATION_ACCOUNT_PARTNER  = 'registration-account-partner';

    const CY_ENROLLMENTS_REPORT = 'cy-enrollments';
    const CY_REVENUE_REPORT = 'cy-revenue';
    const ACTIVE_ENROLLMENTS_REPORT = 'active-enrollments';
    const LIFETIME_ENROLLMENTS_REPORT = 'lifetime-enrollments';
    const COMPLETION_RATE_REPORT = 'completion-rate';
    const CONVERSION_RATE_REPORT = 'conversion-rate';
    const RETENTION_RATE_REPORT = 'retention-rate';
    const LIFETIME_REVENUE_REPORT = 'lifetime-revenue';

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

    public static function isSuperAdmin(){
        return Auth::user()->user_type == self::USER_TYPE_ADMIN;
    }

    public static function isPartner(){
        return Auth::user()->user_type == self::USER_TYPE_PARTNER;
    }

    public static function isWeUser(){
        return Auth::user()->user_type == self::USER_TYPE_WE_USER;
    }

    public static function isMyUser(){
        return Auth::user()->user_type == self::USER_TYPE_MY_USER;
    }

    public static function getPartnerDetail($column = ''){
        if(Session::has('partner_detail')){
            $partner_detail = Session::get('partner_detail');
            if(!empty($column)){
                switch ($column){
                    case 'id':
                        return $partner_detail['id'];
                    case 'zoho_id':
                        return $partner_detail['zoho_id'];
                    case 'canvas_sub_account_id':
                        return $partner_detail['canvas_sub_account_id'];
                    case 'partner_name':
                        return $partner_detail['partner_name'];
                    case 'email':
                        return $partner_detail['email'];
                    case 'phone':
                        return $partner_detail['phone'];
                    case 'pi_email':
                        return $partner_detail['pi_email'];
                    case 'pi_phone':
                        return $partner_detail['pi_phone'];
                    case 'department':
                        return $partner_detail['department'];
                    case 'logo':
                        return $partner_detail['logo'];
                    case 'status':
                        return $partner_detail['status'];
                    case 'price_book_id':
                        return $partner_detail['price_book_id'];
                    case 'price_book_zoho_id':
                        return $partner_detail['price_book_zoho_id'];
                    case 'hosted_site':
                        return $partner_detail['hosted_site'];
                    case 'wia': // This code use only view file so it's not into coverage
                        return $partner_detail['wia'];
                    case 'mycaa': // This code use only view file so it's not into coverage
                        return $partner_detail['mycaa'];
                    default:
                        return $partner_detail;
                }
            }
            return $partner_detail;
        }
        return null;
    }

    public static function getHighlightReports($report = ''){
        $data = [
            self::CY_ENROLLMENTS_REPORT => 'CY Enrollments',
            self::CY_REVENUE_REPORT => 'CY Revenue',
            self::ACTIVE_ENROLLMENTS_REPORT => 'Active Enrollments',
            self::LIFETIME_ENROLLMENTS_REPORT => 'Lifetime Enrollments',
            self::COMPLETION_RATE_REPORT => 'Completion Rate',
            self::CONVERSION_RATE_REPORT => 'Conversion Rate',
            self::RETENTION_RATE_REPORT => 'Retention Rate',
            self::LIFETIME_REVENUE_REPORT => 'Lifetime Revenue'
        ];
        if(!empty($report) && isset($data[$report])){
            return $data[$report];
        }
        return $data;
    }
}
