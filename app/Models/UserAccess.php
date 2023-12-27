<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserAccess extends Model
{

    use HasFactory;

    protected $table = 'pas_users_access';

    public $timestamps = false;

    // Parent Menu

    const DASHBOARD             = 'DASHBOARD';
    const CATALOG_MANAGEMENT    = 'CATALOG_MANAGEMENT';
    const STUDENT_MANAGEMENT    = 'STUDENT_MANAGEMENT';
    const HOSTED_SITE_MANAGEMENT= 'HOSTED_SITE_MANAGEMENT';
    const MARKETING             = 'MARKETING';
    const PARTNER_PROFILE       = 'PARTNER_PROFILE';
    const MY_WE_PROFILE         = 'MY_WE_PROFILE';
    const PAS_ADMIN             = 'PAS_ADMIN';
    const PARTNER_ADMIN         = 'PARTNER_ADMIN';
    const PAS_CMS               = 'PAS_CMS';

    // Sub Menu
    const HOME_DASHBOARD_ACCESS             = 'HOME_DASHBOARD_ACCESS';
    const STATS_ACCESS                      = 'STATS_ACCESS';

    const CATALOG_MANAGEMENT_ACCESS         = 'CATALOG_MANAGEMENT_ACCESS';
    const STUDENT_PII_ACCESS                = 'STUDENT_PII_ACCESS';

    const NEWS_ACCESS                       = 'NEWS_ACCESS';
    const ANNOUNCEMENTS_ACCESS              = 'ANNOUNCEMENTS_ACCESS';
    const UPDATES_ACCESS                    = 'UPDATES_ACCESS';
    const MY_TOP_SELLING_PROGRAMS_ACCESS    = 'MY_TOP_SELLING_PROGRAMS_ACCESS';
    const REQUEST_COLLATERAL_ACCESS         = 'REQUEST_COLLATERAL_ACCESS';
    const MARKETING_COLLATERAL_ACCESS       = 'MARKETING_COLLATERAL_ACCESS';

    const MY_INSTITUTION_PROFILE_ACCESS     = 'MY_INSTITUTION_PROFILE_ACCESS';
    const MY_PROFILE_ACCESS                 = 'MY_PROFILE_ACCESS';

    const PARTNER_USERS_LIST_ACCESS         = 'PARTNER_USERS_LIST_ACCESS';
    const WE_USERS_LIST_ACCESS              = 'WE_USERS_LIST_ACCESS';
    const CONFIGURATION_EMAIL_ACCESS        = 'CONFIGURATION_EMAIL_ACCESS';
    const SYSTEM_EMAIL_LOGS_ACCESS          = 'SYSTEM_EMAIL_LOGS_ACCESS';
    const USER_ACTIVITY_LOG_ACCESS          = 'USER_ACTIVITY_LOG_ACCESS';

    const PS_BANNER_LOG_ACCESS              = 'PS_BANNER_LOG_ACCESS';
    const PS_BANNER_NOTIFICATION_LOG_ACCESS = 'PS_BANNER_NOTIFICATION_LOG_ACCESS';
    const PS_HOSTED_SITE_PROMOTION_ACCESS   = 'PS_HOSTED_SITE_PROMOTION_ACCESS';
    const PS_SHOP_CREATOR_ACCESS            = 'PS_SHOP_CREATOR_ACCESS';
    const TRAINING_PLAN_CREATOR_ACCESS      = 'TRAINING_PLAN_CREATOR_ACCESS';
    const MYCAA_TRAINING_PLAN_CREATOR_ACCESS= 'MYCAA_TRAINING_PLAN_CREATOR_ACCESS';

    const MY_WE_PROFILE_ACCESS               = 'MY_WE_PROFILE_ACCESS';

    const MY_INSTITUTION_REQUEST_ACCESS     = 'MY_INSTITUTION_REQUEST_ACCESS';
    const MAP_MY_STUDENTS_ACCESS            = 'MAP_MY_STUDENTS_ACCESS';

    const STUDENT_MANAGEMENT_ACCESS         = 'STUDENT_MANAGEMENT_ACCESS';
    const STUDENT_IMPORT_AUDIT_ACCESS       = 'STUDENT_IMPORT_AUDIT_ACCESS';
    const STUDENT_ENROLLMENT_ACCESS         = 'STUDENT_ENROLLMENT_ACCESS';
    const LEADS_ACCESS                      = 'LEADS_ACCESS';

    const HOSTED_SITE_MANAGEMENT_ACCESS     = 'HOSTED_SITE_MANAGEMENT_ACCESS';
    //const MARKETING_ACCESS                  = 'MARKETING_ACCESS';

    const PARTNER_ANNOUNCEMENT_ACCESS       = 'PARTNER_ANNOUNCEMENT_ACCESS';
    const WE_TEMPLATE_ACCESS                = 'WE_TEMPLATE_ACCESS';


    const PAS_ADMIN_ACCESS                  = 'PAS_ADMIN_ACCESS';
    const PARTNER_ADMIN_ACCESS              = 'PARTNER_ADMIN_ACCESS';
    const PAS_CMS_ACCESS                    = 'PAS_CMS_ACCESS';

    public static function getUserAccessFeatures($user_id, $user_type, $role_id, $access_level = null){
        $all_features = self::getAllFeaturesByAccessLevel($access_level);
        $user_access = null;
        if($user_type == User::USER_TYPE_PARTNER || $user_type == User::USER_TYPE_MY_USER){
            $user_access = DB::table('pas_users_access')
                ->where('user_id', '=', $user_id)
                ->get();
        } elseif($user_type == User::USER_TYPE_WE_USER){
            $user_access = DB::table('pas_roles_access')
                ->where('role_id', '=', $role_id)
                ->where('access_level', '=', $access_level)
                ->get();
        }

        if(count($user_access) == 0){
            return false;
        }

        $user_access_key = [];
        if(isset($user_access)) { foreach ($user_access as $user_acces){
            $user_access_key[$user_acces->feature] = $user_acces;
        }}

        if($user_access_key){
            foreach ($all_features as $feature_key => $feature) {
                if(isset($user_access_key[$feature_key])){
                    if(isset($feature['options'])){
                        foreach ($feature['options'] as $opt_key => $option) {
                            if($opt_key == 'view'){
                                    $all_features[$feature_key]['options']['view']['is_checked'] = (boolean) $user_access_key[$feature_key]->can_view;
                            }else if($opt_key == 'download'){
                                $all_features[$feature_key]['options']['download']['is_checked'] = (boolean) $user_access_key[$feature_key]->can_download;
                            }else if($opt_key == 'add'){
                                $all_features[$feature_key]['options']['add']['is_checked'] =
                                    (boolean) $user_access_key[$feature_key]->can_add;
                            }
                        }
                    }
                }else{
                    $all_features[$feature_key]['is_checked'] = false;
                    if(isset($all_features[$feature_key]['options']['view'])){
                        $all_features[$feature_key]['options']['view']['is_checked'] = false;
                    }
                    if(isset($all_features[$feature_key]['options']['download'])){
                        $all_features[$feature_key]['options']['download']['is_checked'] = false;
                    }
                    if(isset($all_features[$feature_key]['options']['add'])){
                        $all_features[$feature_key]['options']['add']['is_checked'] = false;
                    }
                }
            }
        }
        return $all_features;
    }

    public static function getAllFeaturesByAccessLevel($access_level){
        $user_access_features = DB::table('pas_roles_access')
            ->where('access_level', '=', $access_level)
            ->get();

        $user_access_arr = [];
        foreach ($user_access_features as $key => $user_access) {
            $user_access_arr[$user_access->feature]['label'] = self::getMenuLabel($user_access->feature);
            $user_access_arr[$user_access->feature]['is_checked'] = true;
            $user_access_arr[$user_access->feature]['parent_menu'] = $user_access->parent_menu;

            if(self::HOME_DASHBOARD_ACCESS != $user_access->feature && self::MAP_MY_STUDENTS_ACCESS != $user_access->feature && self::NEWS_ACCESS != $user_access->feature && self::ANNOUNCEMENTS_ACCESS != $user_access->feature && self::UPDATES_ACCESS != $user_access->feature && self::MY_TOP_SELLING_PROGRAMS_ACCESS != $user_access->feature){
                $user_access_arr[$user_access->feature]['options']['view']['label'] = 'View';
                $user_access_arr[$user_access->feature]['options']['view']['id'] = $user_access->feature.'_view';
                $user_access_arr[$user_access->feature]['options']['view']['is_checked'] = (boolean) $user_access->can_view;
                if(is_numeric($user_access->can_download)){
                    $user_access_arr[$user_access->feature]['options']['download']['label'] = 'Download/Request';
                    $user_access_arr[$user_access->feature]['options']['download']['id'] = $user_access->feature.'_download';
                    $user_access_arr[$user_access->feature]['options']['download']['is_checked'] = (boolean) $user_access->can_download;
                }

                if(is_numeric($user_access->can_add)) {
                    $user_access_arr[$user_access->feature]['options']['add']['label'] = 'Add';
                    $user_access_arr[$user_access->feature]['options']['add']['id'] = $user_access->feature . '_add';
                    $user_access_arr[$user_access->feature]['options']['add']['is_checked'] = (boolean)$user_access->can_add;
                }
            }

        }
        return $user_access_arr;
    }

    public static function getMenuLabel($key = null){
        $menu_label = [
            self::HOME_DASHBOARD_ACCESS => 'Dashboard',
            self::STATS_ACCESS => 'Stats',
            self::MY_INSTITUTION_REQUEST_ACCESS => 'My Institution Request',
            self::MAP_MY_STUDENTS_ACCESS => 'Map My Student',
            self::CATALOG_MANAGEMENT_ACCESS => 'Catalog Management',
            self::STUDENT_PII_ACCESS => 'Student PII (DOB and SSN)',
            self::STUDENT_MANAGEMENT_ACCESS => 'Student Management',
            self::STUDENT_ENROLLMENT_ACCESS => 'Student Enrollment',
            self::STUDENT_IMPORT_AUDIT_ACCESS => 'Student Import Audit',
            self::LEADS_ACCESS => 'Leads',
            self::NEWS_ACCESS => 'News',
            self::ANNOUNCEMENTS_ACCESS => 'Announcements',
            self::UPDATES_ACCESS => 'Updates',
            self::MY_TOP_SELLING_PROGRAMS_ACCESS => 'My Top Selling Program',
            self::REQUEST_COLLATERAL_ACCESS => 'Request Collateral',
            self::MARKETING_COLLATERAL_ACCESS => 'Marketing Collateral',
            self::MY_INSTITUTION_PROFILE_ACCESS => 'Institution Profile',

            self::PARTNER_USERS_LIST_ACCESS => 'Partner User List',
            self::WE_USERS_LIST_ACCESS => 'WE User List',
            self::CONFIGURATION_EMAIL_ACCESS => 'Configuration Email',
            self::SYSTEM_EMAIL_LOGS_ACCESS => 'System Email Logs',

            self::MY_PROFILE_ACCESS => 'My Profile',

            self::MY_WE_PROFILE_ACCESS => 'My WE Profile',

            self::PARTNER_ADMIN_ACCESS => 'Partner Admin',

            self::PARTNER_ANNOUNCEMENT_ACCESS => 'Partner Announcements',
            self::WE_TEMPLATE_ACCESS => 'We Template',
        ];
        if($key && isset($menu_label[$key])){
             return $menu_label[$key];
        }
        return '';
    }

    public static function hasAccess($access_name, $access_type){
        if(User::isSuperAdmin()){
            return true;
        }

        $all_user_features = UserAccess::getUserAccessFeatures(Auth::user()->id, Auth::user()->user_type, Auth::user()->roleid);

        if(isset($all_user_features[$access_name]) && $all_user_features[$access_name]){
            if(isset($all_user_features[$access_name]['options'][$access_type])){
                return $all_user_features[$access_name]['options'][$access_type]['is_checked'];
            }else if(isset($all_user_features[$access_name]['is_checked'])){
                return $all_user_features[$access_name]['is_checked'];
            }
        }
        return false;
    }
}
