<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class UserMenu extends UserAccess
{
    public static function getUserMenu() {
        $all_menu = self::getAllMenu();
        if(User::isSuperAdmin()){
            return $all_menu;
        }

        $user_access_key = [];
        if(User::isPartner() || User::isMyUser()){
            $user_access = Db::table('pas_users_access')
                ->where('user_id', '=', Auth::user()->id)
                ->get()->all();

            foreach ($user_access as $access) {
                $user_access_key[$access->parent_menu][] = $access;
            }
        }else if(User::isWeUser()){
            $role_access = DB::table('pas_roles_access')->where('role_id', '=', Auth::user()->roleid)->get()->all();
            foreach ($role_access as $access) {
                $user_access_key[$access->parent_menu][] = $access;
            }
        }

        $user_menu = [];
        foreach ($all_menu as $parent_menu => $all_sub_menu) {
            if(isset($user_access_key[$parent_menu])){
                $user_menu[$parent_menu]['label'] = $all_sub_menu['label'];
                $user_menu[$parent_menu]['icon'] = $all_sub_menu['icon'];
                $user_menu[$parent_menu]['route'] = $all_sub_menu['route'];
                $user_menu[$parent_menu]['class'] = $all_sub_menu['class'];
                foreach ($all_sub_menu['sub_menu'] as $sub_menu) {
                    $user_sub_menu = array_column($user_access_key[$parent_menu], 'feature');
                    if(isset($sub_menu['role']) && in_array($sub_menu['role'], $user_sub_menu)){
                        $user_menu[$parent_menu]['sub_menu'][] = $sub_menu;
                    }
                }
            }
        }
        return $user_menu;
    }

    public static function getAllMenu(){

        $current_route = Route::currentRouteName();
        $user_menu[self::DASHBOARD]['label'] = 'Dashboard';
        $user_menu[self::DASHBOARD]['icon'] = 'fa fa-tachometer-alt';
        $user_menu[self::DASHBOARD]['route'] = '#';
        $user_menu[self::DASHBOARD]['class'] = ($current_route == 'dashboard' || $current_route == 'myinstitution' || $current_route == 'map-my-student' ? 'active':'');
        $user_menu[self::DASHBOARD]['sub_menu'][] = [
            'role' => self::HOME_DASHBOARD_ACCESS,
            'label' => 'My Dashboard',
            'route' => 'dashboard',
            'class' => ($current_route == 'dashboard' ? 'active':'')
        ];
        $user_menu[self::DASHBOARD]['sub_menu'][] = [
            'role' => self::MY_INSTITUTION_REQUEST_ACCESS,
            'label' => 'My Institution Request',
            'route' => 'myinstitution',
            'class' => ($current_route == 'myinstitution' ? 'active':'')
        ];
        $user_menu[self::DASHBOARD]['sub_menu'][] = [
            'role' => self::MAP_MY_STUDENTS_ACCESS,
            'label' => 'Map My Students',
            'route' => 'map-my-student',
            'class' => ($current_route == 'map-my-student' ? 'active':'')
        ];


        $user_menu[self::CATALOG_MANAGEMENT]['label'] = 'Catalog Management';
        $user_menu[self::CATALOG_MANAGEMENT]['icon'] = 'fas fa-copy';
        $user_menu[self::CATALOG_MANAGEMENT]['route'] = 'catalog-listing';
        $user_menu[self::CATALOG_MANAGEMENT]['class'] = ($current_route == 'catalog-listing' ? 'active':'');
        $user_menu[self::CATALOG_MANAGEMENT]['sub_menu'] = [];

        $user_menu[self::STUDENT_MANAGEMENT]['label'] = 'Student Management';
        $user_menu[self::STUDENT_MANAGEMENT]['icon'] = 'fa fa-graduation-cap';
        $user_menu[self::STUDENT_MANAGEMENT]['route'] = '#';
        $user_menu[self::STUDENT_MANAGEMENT]['class'] = ($current_route == 'student-list' || $current_route == 'student-add' || $current_route == 'student-detail' || $current_route == 'import-audit' || $current_route == 'student-enrollment' || $current_route == 'leads' ? 'active':'');
        $user_menu[self::STUDENT_MANAGEMENT]['sub_menu'][] = [
            'role' => self::STUDENT_MANAGEMENT_ACCESS,
            'label' => 'Student Enrollment',
            'route' => 'student-list',
            'class' => ($current_route == 'student-list' || $current_route == 'student-add' || $current_route == 'student-detail' ? 'active':'')
        ];
        $user_menu[self::STUDENT_MANAGEMENT]['sub_menu'][] = [
            'role' => self::STUDENT_IMPORT_AUDIT_ACCESS,
            'label' => 'Student Import Audit',
            'route' => 'import-audit',
            'class' => ($current_route == 'import-audit' ? 'active':'')
        ];
        $user_menu[self::STUDENT_MANAGEMENT]['sub_menu'][] = [
            'role' => self::STUDENT_ENROLLMENT_ACCESS,
            'label' => 'Student Dashboard',
            'route' => 'student-enrollment',
            'class' => ($current_route == 'student-enrollment' ? 'active':'')
        ];
        $user_menu[self::STUDENT_MANAGEMENT]['sub_menu'][] = [
            'role' => self::LEADS_ACCESS,
            'label' => 'Leads',
            'route' => 'leads',
            'class' => ($current_route == 'leads' ? 'active':'')
        ];

        $user_menu[self::MARKETING]['label'] = 'Marketing';
        $user_menu[self::MARKETING]['icon'] = 'fa far fa-chart-bar';
        $user_menu[self::MARKETING]['route'] = '#';
        $user_menu[self::MARKETING]['class'] = ($current_route == 'announcement' || $current_route == 'top-selling-programs' || $current_route == 'request-collateral' || $current_route == 'marketing-collateral' ? 'active':'');

        $user_menu[self::MARKETING]['sub_menu'][] = [
            'role' => self::NEWS_ACCESS,
            'label' => 'News',
            'route' => 'announcement',
            'class' => ($current_route == 'announcement' ? 'active':''),
            'params' => ['announce_type' => 'news']
        ];
        $user_menu[self::MARKETING]['sub_menu'][] = [
            'role' => self::ANNOUNCEMENTS_ACCESS,
            'label' => 'Announcements',
            'route' => 'announcement',
            'class' => ($current_route == 'announcement' ? 'active':''),
            'params' => ['announce_type' => 'announcements']
        ];
        $user_menu[self::MARKETING]['sub_menu'][] = [
            'role' => self::UPDATES_ACCESS,
            'label' => 'Updates',
            'route' => 'announcement',
            'class' => ($current_route == 'announcement' ? 'active':''),
            'params' => ['announce_type' => 'updates']
        ];
        $user_menu[self::MARKETING]['sub_menu'][] = [
            'role' => self::MY_TOP_SELLING_PROGRAMS_ACCESS,
            'label' => 'My Top Selling Programs',
            'route' => 'top-selling-programs',
            'class' => ($current_route == 'top-selling-programs' ? 'active':''),
        ];
        $user_menu[self::MARKETING]['sub_menu'][] = [
            'role' => self::REQUEST_COLLATERAL_ACCESS,
            'label' => 'Request Collateral',
            'route' => 'request-collateral',
            'class' => ($current_route == 'request-collateral' ? 'active':''),
        ];
        $user_menu[self::MARKETING]['sub_menu'][] = [
            'role' => self::MARKETING_COLLATERAL_ACCESS,
            'label' => 'Marketing Collateral',
            'route' => 'marketing-collateral',
            'class' => ($current_route == 'marketing-collateral' ? 'active':''),
        ];

        $user_menu[self::PARTNER_PROFILE]['label'] = 'Partner Profile';
        $user_menu[self::PARTNER_PROFILE]['icon'] = 'fas fa-address-book';
        $user_menu[self::PARTNER_PROFILE]['route'] = '#';
        $user_menu[self::PARTNER_PROFILE]['class'] = ($current_route == 'institute-profile' || $current_route == 'my-profile' ? 'active':'');
        $user_menu[self::PARTNER_PROFILE]['sub_menu'][] = [
            'role' => self::MY_INSTITUTION_PROFILE_ACCESS,
            'label' => 'My Institution Profile',
            'route' => 'institute-profile',
            'class' => ($current_route == 'institute-profile' ? 'active':''),
        ];
        $user_menu[self::PARTNER_PROFILE]['sub_menu'][] = [
            'role' => self::MY_PROFILE_ACCESS,
            'label' => 'My Profile',
            'route' => 'my-profile',
            'class' => ($current_route == 'my-profile' ? 'active':''),
        ];

        $user_menu[self::MY_WE_PROFILE]['label'] = 'My WE Profile';
        $user_menu[self::MY_WE_PROFILE]['icon'] = 'fas fa-copy';
        $user_menu[self::MY_WE_PROFILE]['route'] = 'edit-profile';
        $user_menu[self::MY_WE_PROFILE]['class'] = ($current_route == 'edit-profile' ? 'active':'');
        $user_menu[self::MY_WE_PROFILE]['sub_menu'] = [];


        $user_menu[self::PAS_ADMIN]['label'] = 'PAS Admin';
        $user_menu[self::PAS_ADMIN]['icon'] = 'far fa-user';
        $user_menu[self::PAS_ADMIN]['route'] = '#';
        $user_menu[self::PAS_ADMIN]['class'] = ($current_route == 'partner-users' || $current_route == 'we-users' || $current_route == 'configuration-email' || $current_route == 'system-email-logs' || $current_route == 'prestashop-banner' || $current_route == 'prestashop-banner-notification' || $current_route == 'hosted-sites-promotions' || $current_route == 'shop-creator' || $current_route == 'training-plan-creator' || $current_route == 'mycaa-training-plan-creator' ? 'active':'');
        $user_menu[self::PAS_ADMIN]['sub_menu'][] = [
            'role' => self::PARTNER_USERS_LIST_ACCESS,
            'label' => 'Partner User List',
            'route' => 'partner-users',
            'class' => ($current_route == 'partner-users' ? 'active':''),
        ];
        $user_menu[self::PAS_ADMIN]['sub_menu'][] = [
            'role' => self::WE_USERS_LIST_ACCESS,
            'label' => 'We User List',
            'route' => 'we-users',
            'class' => ($current_route == 'we-users' ? 'active':''),
        ];
        $user_menu[self::PAS_ADMIN]['sub_menu'][] = [
            'role' => self::CONFIGURATION_EMAIL_ACCESS,
            'label' => 'Configuration Email',
            'route' => 'configuration-email',
            'class' => ($current_route == 'configuration-email' ? 'active':''),
        ];
        $user_menu[self::PAS_ADMIN]['sub_menu'][] = [
            'role' => self::SYSTEM_EMAIL_LOGS_ACCESS,
            'label' => 'System Email Logs',
            'route' => 'system-email-logs',
            'class' => ($current_route == 'system-email-logs' ? 'active':''),
        ];
        $user_menu[self::PAS_ADMIN]['sub_menu'][] = [
            'role' => self::SYSTEM_EMAIL_LOGS_ACCESS,
            'label' => 'User Activity Logs',
            'route' => 'user-activity-logs',
            'class' => ($current_route == 'user-activity-logs' ? 'active':''),
        ];
        $user_menu[self::PAS_ADMIN]['sub_menu'][] = [
            'role' => self::SYSTEM_EMAIL_LOGS_ACCESS,
            'label' => 'Deleted Activity Logs',
            'route' => 'deleted-activity-logs',
            'class' => ($current_route == 'deleted-activity-log' ? 'active':''),
        ];

        //if(env('APP_ENV') != 'prod') {
            $user_menu[self::PAS_ADMIN]['sub_menu'][] = [
                'role' => self::PS_BANNER_LOG_ACCESS,
                'label' => 'Banner Manage',
                'route' => 'prestashop-banner',
                'class' => ($current_route == 'prestashop-banner' ? 'active' : ''),
            ];

            $user_menu[self::PAS_ADMIN]['sub_menu'][] = [
                'role' => self::PS_BANNER_NOTIFICATION_LOG_ACCESS,
                'label' => 'Banner Notification Manage',
                'route' => 'prestashop-banner-notification',
                'class' => ($current_route == 'prestashop-banner-notification' ? 'active' : ''),
            ];

            $user_menu[self::PAS_ADMIN]['sub_menu'][] = [
                'role' => self::PS_HOSTED_SITE_PROMOTION_ACCESS,
                'label' => 'Hosted Sites Promotions',
                'route' => 'hosted-sites-promotions',
                'class' => ($current_route == 'hosted-sites-promotions' ? 'active' : ''),
            ];

            $user_menu[self::PAS_ADMIN]['sub_menu'][] = [
                'role' => self::PS_SHOP_CREATOR_ACCESS,
                'label' => 'Shop Creator',
                'route' => 'shop-creator',
                'class' => ($current_route == 'shop-creator' ? 'active' : ''),
            ];

            $user_menu[self::PAS_ADMIN]['sub_menu'][] = [
                'role' => self::TRAINING_PLAN_CREATOR_ACCESS,
                'label' => 'Training Plan Creator',
                'route' => 'training-plan-creator',
                'class' => ($current_route == 'training-plan-creator' ? 'active' : ''),
            ];

            $user_menu[self::PAS_ADMIN]['sub_menu'][] = [
                'role' => self::MYCAA_TRAINING_PLAN_CREATOR_ACCESS,
                'label' => 'MyCAA Training Plan Creator',
                'route' => 'mycaa-training-plan-creator',
                'class' => ($current_route == 'mycaa-training-plan-creator' ? 'active' : ''),
            ];
       // }

        $user_menu[self::PARTNER_ADMIN]['label'] = 'Partner Admin';
        $user_menu[self::PARTNER_ADMIN]['icon'] = 'fa fa-users';
        $user_menu[self::PARTNER_ADMIN]['route'] = '#';
        $user_menu[self::PARTNER_ADMIN]['class'] = ($current_route == 'my-users'
        || $current_route == 'prestashop-optional-menu' || $current_route == 'prestashop-hosted-site' ? 'active':'');
        $user_menu[self::PARTNER_ADMIN]['sub_menu'][] = [
            'role' => self::PARTNER_ADMIN_ACCESS,
            'label' => 'My Users List',
            'route' => 'my-users',
            'class' => ($current_route == 'my-users' ? 'active':''),
        ];

        //if(env('APP_ENV') != 'prod'){
            $user_menu[self::PARTNER_ADMIN]['sub_menu'][] = [
                'role' => self::PARTNER_ADMIN_ACCESS,
                'label' => 'Optional Menu',
                'route' => 'prestashop-optional-menu',
                'class' => ($current_route == 'prestashop-optional-menu' ? 'active':''),
            ];

            $user_menu[self::PARTNER_ADMIN]['sub_menu'][] = [
                'role' => self::PARTNER_ADMIN_ACCESS,
                'label' => 'Hosted Site Info',
                'route' => 'prestashop-hosted-site',
                'class' => ($current_route == 'prestashop-hosted-site' ? 'active':''),
            ];
        //}

        $user_menu[self::PAS_CMS]['label'] = 'PAS CMS';
        $user_menu[self::PAS_CMS]['icon'] = 'fas fa-list-alt';
        $user_menu[self::PAS_CMS]['route'] = '#';
        $user_menu[self::PAS_CMS]['class'] = ($current_route == 'marketing-form' || $current_route == 'we-templates' ? 'active':'');
        $user_menu[self::PAS_CMS]['sub_menu'][] = [
            'role' => self::PARTNER_ANNOUNCEMENT_ACCESS,
            'label' => 'Partner Announcement',
            'route' => 'marketing-form',
            'class' => ($current_route == 'marketing-form' ? 'active':''),
        ];
        $user_menu[self::PAS_CMS]['sub_menu'][] = [
            'role' => self::WE_TEMPLATE_ACCESS,
            'label' => 'We Templates',
            'route' => 'we-templates',
            'class' => ($current_route == 'we-templates' ? 'active':''),
        ];

        return $user_menu;
    }
}
