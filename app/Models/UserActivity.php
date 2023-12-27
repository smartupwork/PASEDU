<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use HasFactory;

    protected $table = 'pas_user_activity';

    public $timestamps = false;

    public static function getPageDetail($uri){
        $page_detail =  [
            'student-store' => [
                'route' => 'student-store',
                'heading' => 'Student Management',
                'template' => 'student-enrollment-view',
                'breadcrumb' => ['Student Management', 'Student Enrollment']
            ],
            'student-import-file' => [
                'route' => 'student-import-file',
                'heading' => 'Student Mass Enrollment',
                'template' => 'student-enrollment-view',
                'breadcrumb' => ['Student Management', 'Student Mass Enrollment']
            ],
            'leadssubmit' => [
                'route' => 'leadssubmit',
                'heading' => 'Leads',
                'template' => 'leads-submit',
                'breadcrumb' => ['Student Management', 'Leads']
            ],
            'myinstitution-update' => [
                'route' => 'myinstitution-update',
                'heading' => 'My Institution Request',
                'template' => 'institute-request-view',
                'breadcrumb' => ['Dashboard', 'My Institution Request']
            ],
            'update-dashboard' => [
                'route' => 'update-dashboard',
                'heading' => 'Dashboard',
                'template' => 'my-dashboard-view',
                'breadcrumb' => ['Dashboard', 'My Dashboard']
            ],
            'catalog-change-status' => [
                'route' => 'catalog-change-status',
                'heading' => 'Catalog Management',
                'template' => 'catalog-change-status',
                'breadcrumb' => ['Catalog Management']
            ],
            'marketing-collateral-store' => [
                'route' => 'marketing-collateral-store',
                'heading' => 'Request Collateral',
                'template' => 'marketing-collateral-store',
                'breadcrumb' => ['Marketing', 'Request Collateral']
            ],
            'update-institute-logo' => [
                'route' => 'update-institute-logo',
                'heading' => 'Institute Logo Update',
                'template' => 'update-institute-logo',
                'breadcrumb' => ['Partner Profile', 'My Institution Profile', 'Logo Update']
            ],
            'update-institute-contact' => [
                'route' => 'update-institute-contact',
                'heading' => 'Institute Contact Update',
                'template' => 'update-institute-contact',
                'breadcrumb' => ['Partner Profile', 'My Institution Profile', 'MyCAA Contact & Title']
            ],
            'update-institute-address' => [
                'route' => 'update-institute-address',
                'heading' => 'Institute Address Update',
                'template' => 'update-institute-address',
                'breadcrumb' => ['Partner Profile', 'My Institution Profile', 'Address of MyCAA']
            ],
            'profilesubmit' => [
                'route' => 'profilesubmit',
                'heading' => 'My Profile',
                'template' => 'profilesubmit',
                'breadcrumb' => ['Partner Profile', 'My Profile Logo']
            ],
            'partneruserssubmit' => [
                'route' => 'partneruserssubmit',
                'heading' => 'Partner User Add/Edit',
                'template' => 'partner-user-save',
                'breadcrumb' => ['PAS Admin', 'Partner User Add/Update']
            ],
            'partner-users-delete' => [
                'route' => 'partner-users-delete',
                'heading' => 'Partner User Delete',
                'template' => 'partner-users-delete',
                'breadcrumb' => ['PAS Admin', 'Partner User Delete']
            ],
            'weuserssubmit' => [
                'route' => 'weuserssubmit',
                'heading' => 'WE User Add/Edit',
                'template' => 'we-user-save',
                'breadcrumb' => ['PAS Admin', 'WE User Add/Update']
            ],
            'we-users-delete' => [
                'route' => 'we-users-delete',
                'heading' => 'WE User Delete',
                'template' => 'we-users-delete',
                'breadcrumb' => ['PAS Admin', 'WE User Delete']
            ],
            'myusersubmit' => [
                'route' => 'myusersubmit',
                'heading' => 'My User Add/Edit',
                'template' => 'my-user-save',
                'breadcrumb' => ['Partner Admin', 'My User Add/Update']
            ],
            'my-user-delete' => [
                'route' => 'my-user-delete',
                'heading' => 'My User Delete',
                'template' => 'my-users-delete',
                'breadcrumb' => ['Partner Admin', 'My User Delete']
            ],
            'configsubmit' => [
                'route' => 'configsubmit',
                'heading' => 'Configuration Email Update',
                'template' => 'config-save',
                'breadcrumb' => ['PAS Admin', 'Configuration Email Update']
            ],
            'update-news' => [
                'route' => 'marketing-store',
                'heading' => 'Partner News',
                'template' => 'marketing-listing',
                'breadcrumb' => ['PAS CMS', 'Partner NEWS']
            ],
            'update-announcements' => [
                'route' => 'marketing-store',
                'heading' => 'Partner Announcements ',
                'template' => 'marketing-listing',
                'breadcrumb' => ['PAS CMS', 'Partner Announcements']
            ],
            'update-updates' => [
                'route' => 'marketing-store',
                'heading' => 'Partner Updates',
                'template' => 'marketing-listing',
                'breadcrumb' => ['PAS CMS', 'Partner Updates']
            ],
            'store-template' => [
                'route' => 'store-template',
                'heading' => 'WE Templates',
                'template' => 'we-template',
                'breadcrumb' => ['PAS CMS', 'WE Templates']
            ],
            'cron-partner' => [
                'route' => 'cron-partner',
                'heading' => 'Partner create/update via CRON',
                'template' => 'cron-partner',
                'breadcrumb' => ['CRON', 'Partner Create/Update']
            ],
            'cron-program' => [
                'route' => 'cron-program',
                'heading' => 'Program create/update via CRON',
                'template' => 'cron-program',
                'breadcrumb' => ['CRON', 'Program Create/Update']
            ],
            'cron-enrollment' => [
                'route' => 'cron-enrollment',
                'heading' => 'Enrollment create/update via CRON',
                'template' => 'cron-enrollment',
                'breadcrumb' => ['CRON', 'Enrollment Create/Update']
            ],
            'cron-deals' => [
                'route' => 'cron-deals',
                'heading' => 'Deals create/update via CRON',
                'template' => 'cron-deals',
                'breadcrumb' => ['CRON', 'Deals Create/Update']
            ],
            'cron-schedule' => [
                'route' => 'cron-schedule',
                'heading' => 'Schedule create/update via CRON',
                'template' => 'cron-schedule',
                'breadcrumb' => ['CRON', 'Schedule Create/Update']
            ],
            'cron-leads' => [
                'route' => 'cron-leads',
                'heading' => 'Schedule create/update via CRON',
                'template' => 'cron-leads',
                'breadcrumb' => ['CRON', 'Leads Create/Update']
            ],
            'cron-price-book' => [
                'route' => 'cron-price-book',
                'heading' => 'Price Book create/update via CRON',
                'template' => 'cron-price-book',
                'breadcrumb' => ['CRON', 'Price Book Create/Update']
            ],
            /*'cron-price-book-program-map' => [
                'route' => 'cron-price-book-program-map',
                'heading' => 'Price Book Program Map create/update via CRON',
                'template' => 'cron-price-book-program-map',
                'breadcrumb' => ['CRON', 'Price Book Program Map Update']
            ],*/
        ];
        if(isset($page_detail[$uri])){
            return $page_detail[$uri];
        }
        return null;
    }

    public static function getActions(){
        return [
            'update' => 'Update',
            'create' => 'Create',
            'delete' => 'Delete',
            'fetch' => 'Fetch',
        ];
    }

}
