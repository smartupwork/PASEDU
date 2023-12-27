<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListingSetting extends Model
{
    use HasFactory;

    protected $table = 'listing_setting';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'partner_id',
        'menu',
        'created_at',
        'updated_at',
    ];

    public $timestamps = false;

    public static function getListSetting($module)
    {
        $user_setting =  DB::table('listing_setting')
            ->where('user_id', '=', Auth::user()->id)
            //->where('partner_id', '=', User::getPartnerDetail('id'))
            ->where('module', '=', $module)->get()->first();

        return $user_setting;
    }

    public static function getLeadsDefaultListing(){
        $listing_setting = self::getListSetting('leads');

        $listing['column_position'] = [
            'sn_sort' => 0,
            'first_name_sort' => 1,
            'last_name_sort' => 2,
            'email_sort' => 3,
            'phone_sort' => 4,
            'country_sort' => 5,
            'name_of_requester_sort' => 6,
            'email_of_requester_sort' => 7,
            'partner_institution_sort' => 8,
            'address_sort' => 9,
            'inquiry_message_sort' => 10,
            'state_sort' => 11,
            'city_sort' => 12,
            'zip_sort' => 13,
            'interested_program_sort' => 14,
            'financing_needs_sort' => 15,
            'category_of_interest_sort' => 16,
            'timezone_sort' => 17,
        ];

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            //dd($menu['column_position']);
            /*$column_position = [];
            if($menu['column_position']){
                foreach ($menu['column_position'] as $column => $position){
                    $column_position[$position] = $column;
                }
                //dd($column_position);
                ksort($column_position);
            }*/

            $listing['column_position'] = $menu['column_position'];
        }

        $listing['all_columns'] = self::getLeadsAllColumns();
        $listing['user_columns'] = self::getLeadsVisibleColumns($listing_setting);

        return $listing;
    }

    public static function getLeadsVisibleColumns($listing_setting){
        $all_columns = self::getLeadsAllColumns();
        $user_columns = $all_columns;

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            if($menu['visible_columns']){
                $user_columns = $menu['visible_columns'];
            }
        }

        $new_list = [];
        foreach ($all_columns as $item) {
            if(!in_array($item, $user_columns)){
                $new_list[] = $item;
            }
        }
        //dd($new_list);
        return $new_list;
    }

    public static function getLeadsAllColumns()
    {

        return [
            'sn_sort',
            'inquiry_message_sort',
            'first_name_sort',
            'last_name_sort',
            'email_sort',
            'partner_institution_sort',
            'name_of_requester_sort',
            'email_of_requester_sort',
            'phone_sort',
            'address_sort',
            'country_sort',
            'state_sort',
            'city_sort',
            'zip_sort',
            'interested_program_sort',
            'financing_needs_sort',
            'category_of_interest_sort',
            'timezone_sort',
        ];

    }

    //student dashboard
    public static function getStudentDashboardDefaultListing(){

        $listing_setting = self::getListSetting('student_dashboard');


        $listing['column_position'] = [
            'subject_sort' => 0,
            'date_of_birth_sort' => 1,
            'social_security_number_sort' => 2,
            'partner_name_sort' => 3,
            'status_sort' => 4,
            'grand_total_sort' => 5,
            'start_date_sort' => 6,
            'activity_progress_sort' => 7,
            'activity_log_sort' => 8,
            'program_sort' => 9,
            'completion_date_sort' => 10,
            'end_date_sort' => 11,
            'final_grade_sort' => 12,
            'user_name_sort' => 13
        ];

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            //dd($menu['column_position']);
            /*$column_position = [];
            if($menu['column_position']){
                foreach ($menu['column_position'] as $column => $position){
                    $column_position[$position] = $column;
                }
                //dd($column_position);
                ksort($column_position);
            }*/

            $listing['column_position'] = $menu['column_position'];
        }

        $listing['all_columns'] = self::getStudentDashboardAllColumns();
        $listing['user_columns'] = self::getStudentDashboardVisibleColumns($listing_setting);

        return $listing;
    }

    public static function getStudentDashboardVisibleColumns($listing_setting){
        $all_columns = self::getStudentDashboardAllColumns();
        $user_columns = $all_columns;

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            if($menu['visible_columns']){
                $user_columns = $menu['visible_columns'];
            }
        }

        $new_list = [];
        foreach ($all_columns as $item) {
            if(!in_array($item, $user_columns)){
                $new_list[] = $item;
            }
        }
        //dd($new_list);
        return $new_list;
    }

    public static function getStudentDashboardAllColumns()
    {

        return [
            'subject_sort',
            'date_of_birth_sort',
            'social_security_number_sort',
            'partner_name_sort',
            'status_sort',
            'grand_total_sort',
            'start_date_sort',
            'activity_progress_sort',
            'activity_log_sort',
            'program_sort',
            'completion_date_sort',
            'end_date_sort',
            'final_grade_sort',
            'user_name_sort'
        ];

    }

    //student import
    public static function getStudentImportDefaultListing(){

        $listing_setting = self::getListSetting('student_import');

        $listing['column_position'] = [
            'checkbox_sort'=> 0,
            'file_name_sort' => 1,
            'import_date_sort' => 2,
            'import_time_sort' => 3,
            'file_size_sort' => 4,
            'processing_time_sort' => 5,
            'imported_records_sort' => 6,
            'skipped_records_sort' => 7,
            'records_imported_warning' => 8,
            'imported_by_sort' => 9
        ];

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            $listing['column_position'] = $menu['column_position'];
        }

        $listing['all_columns'] = self::getStudentImportAllColumns();
        $listing['user_columns'] = self::getStudentImportVisibleColumns($listing_setting);

        return $listing;
    }

    public static function getStudentImportVisibleColumns($listing_setting){
        $all_columns = self::getStudentImportAllColumns();
        $user_columns = $all_columns;

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            if($menu['visible_columns']){
                $user_columns = $menu['visible_columns'];
            }
        }

        $new_list = [];
        foreach ($all_columns as $item) {
            if(!in_array($item, $user_columns)){
                $new_list[] = $item;
            }
        }
        //dd($new_list);
        return $new_list;
    }

    public static function getStudentImportAllColumns()
    {

        return [
            'checkbox_sort',
            'file_name_sort',
            'import_date_sort',
            'import_time_sort',
            'file_size_sort',
            'processing_time_sort',
            'imported_records_sort',
            'skipped_records_sort',
            'imported_by_sort'
        ];

    }

    //Catalog management
    public static function getCatalogDefaultListing(){

        $listing_setting = self::getListSetting('catalog');

        $listing['column_position'] = [
            'program_sort'=> 0,
            'code_sort' => 1,
            'hours_sort' => 2,
            'srp_sort' => 3,
            'wholesale_sort' => 4,
            'type_sort' => 5,
            'status_sort' => 6,
            'description_sort' => 7,
            'exam_included_sort' => 8
        ];

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            $listing['column_position'] = $menu['column_position'];
        }

        $listing['all_columns'] = self::getCatalogAllColumns();
        $listing['user_columns'] = self::getCatalogVisibleColumns($listing_setting);

        return $listing;
    }

    public static function getCatalogVisibleColumns($listing_setting){
        $all_columns = self::getCatalogAllColumns();
        $user_columns = $all_columns;

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            if($menu['visible_columns']){
                $user_columns = $menu['visible_columns'];
            }
        }

        $new_list = [];
        foreach ($all_columns as $item) {
            if(!in_array($item, $user_columns)){
                $new_list[] = $item;
            }
        }
        //dd($new_list);
        return $new_list;
    }

    public static function getCatalogAllColumns()
    {

        return [
            'program_sort',
            'code_sort',
            'hours_sort',
            'srp_sort',
            'wholesale_sort',
            'type_sort',
            'status_sort',
            'description_sort',
            'exam_included_sort'
        ];

    }

    //My Institution Request
    public static function getInstitutionDefaultListing(){

        $listing_setting = self::getListSetting('my_institution');

        $listing['column_position'] = [
            'request_sort'=> 0,
            'request_date_sort' => 1,
            'request_time_sort' => 2,
            'request_by_sort' => 3,
            'name_sort' => 4,
            'program_sort' => 5,
            'username_sort' => 6,
            'purpose_sort' => 7,
            'desired_completion_date_sort' => 8,
            'meeting_proposed_date_sort' => 9,
            'request_status_sort' => 10
        ];

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            $listing['column_position'] = $menu['column_position'];
        }

        $listing['all_columns'] = self::getInstitutionAllColumns();
        $listing['user_columns'] = self::getInstitutionVisibleColumns($listing_setting);

        return $listing;
    }

    public static function getInstitutionVisibleColumns($listing_setting){
        $all_columns = self::getInstitutionAllColumns();
        $user_columns = $all_columns;

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            if($menu['visible_columns']){
                $user_columns = $menu['visible_columns'];
            }
        }

        $new_list = [];
        foreach ($all_columns as $item) {
            if(!in_array($item, $user_columns)){
                $new_list[] = $item;
            }
        }
        //dd($new_list);
        return $new_list;
    }

    public static function getInstitutionAllColumns()
    {

        return [
            'request_sort',
            'request_date_sort',
            'request_time_sort',
            'request_by_sort',
            'name_sort',
            'program_sort',
            'username_sort',
            'purpose_sort',
            'desired_completion_date_sort',
            'meeting_proposed_date_sort',
            'request_status_sort'
        ];

    }

    //Partner Users Lists
    public static function getPartnerUsersDefaultListing(){

        $listing_setting = self::getListSetting('partner_users');

        $listing['column_position'] = [
            'checkbox_sort'=> 0,
            'first_name_sort' => 1,
            'last_name_sort' => 2,
            'role_sort' => 3,
            'status_sort' => 4,
            'email_sort' => 5,
            'partner_institution_sort' => 6,
            'phone_sort' => 7,
            'partner_type_sort' => 8,
            'last_login_sort' => 9
        ];

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            $listing['column_position'] = $menu['column_position'];
        }

        $listing['all_columns'] = self::getPartnerUsersAllColumns();
        $listing['user_columns'] = self::getPartnerUsersVisibleColumns($listing_setting);

        return $listing;
    }

    public static function getPartnerUsersVisibleColumns($listing_setting){
        $all_columns = self::getPartnerUsersAllColumns();
        $user_columns = $all_columns;

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            if($menu['visible_columns']){
                $user_columns = $menu['visible_columns'];
            }
        }

        $new_list = [];
        foreach ($all_columns as $item) {
            if(!in_array($item, $user_columns)){
                $new_list[] = $item;
            }
        }
        //dd($new_list);
        return $new_list;
    }

    public static function getPartnerUsersAllColumns()
    {

        return [
            'checkbox_sort',
            'first_name_sort',
            'last_name_sort',
            'role_sort',
            'status_sort',
            'email_sort',
            'partner_institution_sort',
            'phone_sort',
            'partner_type_sort',
            'last_login_sort'
        ];

    }

    //We Users Lists
    public static function getWeUsersDefaultListing(){

        $listing_setting = self::getListSetting('we_users');

        $listing['column_position'] = [
            'checkbox_sort'=> 0,
            'first_name_sort' => 1,
            'last_name_sort' => 2,
            'email_sort' => 3,
            'phone_sort' => 4,
            'role_sort' => 5,
            'status_sort' => 6,
            'last_login_sort' => 7
        ];

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            $listing['column_position'] = $menu['column_position'];
        }

        $listing['all_columns'] = self::getWeUsersAllColumns();
        $listing['user_columns'] = self::getWeUsersVisibleColumns($listing_setting);

        return $listing;
    }

    public static function getWeUsersVisibleColumns($listing_setting){
        $all_columns = self::getWeUsersAllColumns();
        $user_columns = $all_columns;

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            if($menu['visible_columns']){
                $user_columns = $menu['visible_columns'];
            }
        }

        $new_list = [];
        foreach ($all_columns as $item) {
            if(!in_array($item, $user_columns)){
                $new_list[] = $item;
            }
        }
        //dd($new_list);
        return $new_list;
    }

    public static function getWeUsersAllColumns()
    {

        return [
            'checkbox_sort',
            'first_name_sort',
            'last_name_sort',
            'email_sort',
            'phone_sort',
            'role_sort',
            'status_sort',
            'last_login_sort'
        ];

    }

    //My Users Lists
    public static function getMyUsersDefaultListing(){

        $listing_setting = self::getListSetting('my_users');

        $listing['column_position'] = [
            'checkbox_sort'=> 0,
            'first_name_sort' => 1,
            'last_name_sort' => 2,
            'email_sort' => 3,
            'phone_sort' => 4,
            'role_sort' => 5,
            'status_sort' => 6
        ];

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            $listing['column_position'] = $menu['column_position'];
        }

        $listing['all_columns'] = self::getMyUsersAllColumns();
        $listing['user_columns'] = self::getMyUsersVisibleColumns($listing_setting);

        return $listing;
    }

    public static function getMyUsersVisibleColumns($listing_setting){
        $all_columns = self::getMyUsersAllColumns();
        $user_columns = $all_columns;

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            if($menu['visible_columns']){
                $user_columns = $menu['visible_columns'];
            }
        }

        $new_list = [];
        foreach ($all_columns as $item) {
            if(!in_array($item, $user_columns)){
                $new_list[] = $item;
            }
        }
        //dd($new_list);
        return $new_list;
    }

    public static function getMyUsersAllColumns()
    {

        return [
            'checkbox_sort',
            'first_name_sort',
            'last_name_sort',
            'email_sort',
            'phone_sort',
            'role_sort',
            'status_sort'
        ];

    }

    //Configuration Email Lists
    public static function getConfigurationEmailDefaultListing(){

        $listing_setting = self::getListSetting('configuration_email');

        $listing['column_position'] = [
            'type_sort'=> 0,
            'subject_sort' => 1,
            'description_sort' => 2
        ];

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            $listing['column_position'] = $menu['column_position'];
        }

        $listing['all_columns'] = self::getConfigurationEmailAllColumns();
        $listing['user_columns'] = self::getConfigurationEmailVisibleColumns($listing_setting);

        return $listing;
    }

    public static function getConfigurationEmailVisibleColumns($listing_setting){
        $all_columns = self::getConfigurationEmailAllColumns();
        $user_columns = $all_columns;

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            if($menu['visible_columns']){
                $user_columns = $menu['visible_columns'];
            }
        }

        $new_list = [];
        foreach ($all_columns as $item) {
            if(!in_array($item, $user_columns)){
                $new_list[] = $item;
            }
        }
        //dd($new_list);
        return $new_list;
    }

    public static function getConfigurationEmailAllColumns()
    {

        return [
            'type_sort',
            'subject_sort',
            'description_sort'
        ];

    }

    //Email Logs
    public static function getEmailLogsDefaultListing(){

        $listing_setting = self::getListSetting('email_logs');

        $listing['column_position'] = [
            'checkbox_sort'=> 0,
            'to_sort' => 1,
            'subject_sort' => 2,
            'date_sort' => 3
        ];

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            $listing['column_position'] = $menu['column_position'];
        }

        $listing['all_columns'] = self::getEmailLogsAllColumns();
        $listing['user_columns'] = self::getEmailLogsVisibleColumns($listing_setting);

        return $listing;
    }

    public static function getEmailLogsVisibleColumns($listing_setting){
        $all_columns = self::getEmailLogsAllColumns();
        $user_columns = $all_columns;

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            if($menu['visible_columns']){
                $user_columns = $menu['visible_columns'];
            }
        }

        $new_list = [];
        foreach ($all_columns as $item) {
            if(!in_array($item, $user_columns)){
                $new_list[] = $item;
            }
        }
        //dd($new_list);
        return $new_list;
    }

    public static function getEmailLogsAllColumns()
    {

        return [
            'checkbox_sort',
            'to_sort',
            'subject_sort',
            'date_sort'
        ];

    }

    //User Activity Logs
    public static function getUserActivityLogsDefaultListing(){

        $listing_setting = self::getListSetting('useractivity_logs');

        $listing['column_position'] = [
            'action_sort'=> 0,
            'log_type_sort' => 1,
            'ip_address_sort' => 2,
            'action_by_sort' => 3,
            'action_at_sort' => 4,
            'data_sort' => 5
        ];

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            $listing['column_position'] = $menu['column_position'];
        }

        $listing['all_columns'] = self::getUserActivityLogsAllColumns();
        $listing['user_columns'] = self::getUserActivityLogsVisibleColumns($listing_setting);

        return $listing;
    }

    public static function getUserActivityLogsVisibleColumns($listing_setting){
        $all_columns = self::getUserActivityLogsAllColumns();
        $user_columns = $all_columns;

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            if($menu['visible_columns']){
                $user_columns = $menu['visible_columns'];
            }
        }

        $new_list = [];
        foreach ($all_columns as $item) {
            if(!in_array($item, $user_columns)){
                $new_list[] = $item;
            }
        }
        //dd($new_list);
        return $new_list;
    }

    public static function getUserActivityLogsAllColumns()
    {

        return [
            'action_sort',
            'log_type_sort',
            'ip_address_sort',
            'action_by_sort',
            'action_at_sort',
            'data_sort'
        ];

    }

    //Student Enrollment
    public static function getStudentEnrollmentDefaultListing(){

        $listing_setting = self::getListSetting('student_enrollment');

        $listing['column_position'] = [
            'load_more_sort' => 0,
            'first_name_sort'=> 1,
            'last_name_sort' => 2,
            'email_sort' => 3,
            'program_sort' => 4,
            'srp_sort' => 5,
            'paid_price_sort' => 6,
            'payment_type_sort' => 7,
            'start_date_sort' => 8,
            'end_date_sort' => 9,
            'phone_sort' => 10,
            'street_sort' => 11,
            'city_sort' => 12,
            'state_sort' => 13,
            'zip_sort' => 14,
            'country_sort' => 15
        ];

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            $listing['column_position'] = $menu['column_position'];
        }

        $listing['all_columns'] = self::getStudentEnrollmentAllColumns();
        $listing['user_columns'] = self::getStudentEnrollmentVisibleColumns($listing_setting);

        return $listing;
    }

    public static function getStudentEnrollmentVisibleColumns($listing_setting){
        $all_columns = self::getStudentEnrollmentAllColumns();
        $user_columns = $all_columns;

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            if($menu['visible_columns']){
                $user_columns = $menu['visible_columns'];
            }
        }

        $new_list = [];
        foreach ($all_columns as $item) {
            if(!in_array($item, $user_columns)){
                $new_list[] = $item;
            }
        }
        //dd($new_list);
        return $new_list;
    }

    public static function getStudentEnrollmentAllColumns()
    {

        return [
            'load_more_sort',
            'first_name_sort',
            'last_name_sort',
            'email_sort',
            'program_sort',
            'srp_sort',
            'paid_price_sort',
            'payment_type_sort',
            'start_date_sort',
            'end_date_sort',
            'phone_sort',
            'street_sort',
            'city_sort',
            'state_sort',
            'zip_sort',
            'country_sort'
        ];

    }

    //Partner Inquiry history
    public static function getPartnerInquiryDefaultListing(){

        $listing_setting = self::getListSetting('partner_inquiry');

        $listing['column_position'] = [
            'checkbox_sort' => 0,
            'request_type_sort'=> 1,
            'request_date_sort' => 2,
            'request_reason_sort' => 3,
            'request_time_sort' => 4,
            'request_by_sort' => 5,
            'request_message_sort' => 6,
            'request_status_sort' => 7
        ];

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            $listing['column_position'] = $menu['column_position'];
        }

        $listing['all_columns'] = self::getPartnerInquiryAllColumns();
        $listing['user_columns'] = self::getPartnerInquiryVisibleColumns($listing_setting);

        return $listing;
    }

    public static function getPartnerInquiryVisibleColumns($listing_setting){
        $all_columns = self::getPartnerInquiryAllColumns();
        $user_columns = $all_columns;

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            if($menu['visible_columns']){
                $user_columns = $menu['visible_columns'];
            }
        }

        $new_list = [];
        foreach ($all_columns as $item) {
            if(!in_array($item, $user_columns)){
                $new_list[] = $item;
            }
        }
        //dd($new_list);
        return $new_list;
    }


    

    public static function getDeleteActivityLogsDefaultListing(){
        $listing_setting = self::getListSetting('deletedactivity');

        $listing['column_position'] = [
            'checkbox_sort' => 0,
            'what_deleted_sort' => 1,
            'ip_address_sort' => 2,
            'who_deleted_sort' => 3,
            'action_at_sort' => 4,
            'data_sort' => 5,
        ];

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            $listing['column_position'] = $menu['column_position'];
        }

        $listing['all_columns'] = self::getDeletedActivityAllColumns();
        $listing['user_columns'] = self::getDeletedActivityVisibleColumns($listing_setting);

        return $listing;
    }

    public static function getDeletedActivityVisibleColumns($listing_setting){
        $all_columns = self::getDeletedActivityAllColumns();
        $user_columns = $all_columns;

        if($listing_setting){
            $menu = json_decode($listing_setting->menu, true);
            if($menu['visible_columns']){
                $user_columns = $menu['visible_columns'];
            }
        }

        $new_list = [];
        foreach ($all_columns as $item) {
            if(!in_array($item, $user_columns)){
                $new_list[] = $item;
            }
        }
        //dd($new_list);
        return $new_list;
    }

    public static function getDeletedActivityAllColumns()
    {

        return [
            'checkbox_sort',
            'what_deleted_sort',
            'ip_address_sort',
            'who_deleted_sort',
            'action_at_sort',
            'data_sort',
        ];

    }

    public static function getPartnerInquiryAllColumns()
    {

        return [
            'checkbox_sort',
            'request_type_sort',
            'request_date_sort',
            'request_reason_sort',
            'request_time_sort',
            'request_by_sort',
            'request_message_sort',
            'request_status_sort'
        ];

    }

    public static function getLeadsDefaultListingBk($only_key = false){
        $list = [
            'inquiry_message' => [
                'label' => 'Inquiry Message',
                'default' => '-',
                'trim_text_after' => 15,
                'is_input' => false,
            ],
            'firstname' => [
                'label' => 'First Name',
                'default' => '-',
                'trim_text_after' => 15,
                'is_input' => false,
            ],
            'lastname' => [
                'label' => 'Last Name',
                'default' => '-',
                'trim_text_after' => 15,
                'is_input' => false,
            ],
            'email' => [
                'label' => 'Email',
                'default' => '-',
                'trim_text_after' => 15,
                'is_input' => false,
            ],
            'partner_institution' => [
                'label' => 'Partner Institution',
                'default' => '-',
                'trim_text_after' => 15,
                'is_input' => false,
            ],
            'name_of_requester' => [
                'label' => 'Name Of Requester',
                'default' => '-',
                'trim_text_after' => 15,
                'is_input' => false,
            ],
            'email_of_requester' => [
                'label' => 'Email Of Requester',
                'default' => '-',
                'trim_text_after' => 15,
                'is_input' => false,
            ],
            'phone' => [
                'label' => 'Phone',
                'default' => '-',
                'trim_text_after' => 15,
                'is_input' => false,
            ],
            'address' => [
                'label' => 'Address',
                'default' => '-',
                'trim_text_after' => 15,
                'is_input' => false,
            ],
            'country' => [
                'label' => 'Country',
                'default' => '-',
                'trim_text_after' => 15,
                'is_input' => false,
            ],
            'state' => [
                'label' => 'State',
                'default' => '-',
                'trim_text_after' => 15,
                'is_input' => false,
            ],
            'city' => [
                'label' => 'City',
                'default' => '-',
                'trim_text_after' => 15,
                'is_input' => false,
            ],
            'zip' => [
                'label' => 'Zip Code',
                'default' => '-',
                'trim_text_after' => 15,
                'is_input' => false,
            ],
            'interested_program' => [
                'label' => 'Interested Program',
                'default' => '-',
                'trim_text_after' => 15,
                'is_input' => false,
            ],
            'financing_needs' => [
                'label' => 'Financing Needs',
                'default' => '-',
                'trim_text_after' => 15,
                'is_input' => false,
            ],
            'category_of_interest' => [
                'label' => 'Category Of Interest',
                'default' => '-',
                'trim_text_after' => 15,
                'is_input' => false,
            ],
            'timezone' => [
                'label' => 'Time Zone',
                'default' => '-',
                'trim_text_after' => 15,
                'is_input' => false,
            ],
        ];

        if($only_key){
            return array_keys($list);
        }
        return $list;
    }
}
