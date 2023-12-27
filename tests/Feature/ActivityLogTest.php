<?php

namespace Tests\Feature;

use App\Models\EmailTemplates;
use App\Models\Partner;
use App\Models\Roles;
use App\Models\User;
use App\Utility;
use Faker\Factory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    //use RefreshDatabase;
    //use WithoutMiddleware;

    public function test_user_activity_log_listing_with_has_no_access(){
        $fake_user = Utility::addWeUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-logs'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_user_activity_log_listing_page_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-logs'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('User Activity Logs');
    }

    public function test_user_activity_log_listing_page_ajax_with_has_no_access()
    {
        $fake_user = Utility::addWeUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-logs-ajax'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_user_activity_log_listing_page_ajax_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-logs-ajax', ['q' => 'test', 'action' => 'Login', 'breadcrumb' => 'Login Logout']));

        $response->assertOk();
    }

    public function test_user_activity_log_listing_page_ajax_without_filter()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-logs-ajax'));

        $response->assertOk();
    }


    public function test_user_activity_log_view_with_has_no_access(){
        $fake_user = Utility::addWeUserWithoutAnyAccess();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->inRandomOrder()
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_user_activity_log_view_student_store_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'student-store')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_my_institution_update_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'myinstitution-update')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_update_dashboard_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'update-dashboard')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_catalog_change_status_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'catalog-change-status')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_student_import_file_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'student-import-file')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_leads_submit_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'leadssubmit')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_marketing_collateral_store_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'marketing-collateral-store')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_update_institute_logo_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'update-institute-logo')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_update_institute_contact_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'update-institute-contact')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_update_institute_address_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'update-institute-address')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_profile_submit_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'profilesubmit')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_we_users_submit_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'weuserssubmit')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_we_users_delete_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'we-users-delete')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_my_user_submit_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'myusersubmit')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_my_user_delete_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'my-user-delete')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_config_submit_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'configsubmit')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_update_news_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'update-news')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_partner_users_submit_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'partneruserssubmit')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_user_activity_log_view_partner_users_delete_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_activity = DB::table('pas_user_activity')
            ->where('url', '=', 'partner-users-delete')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('user-activity-log-view', ['id' => pas_encrypt($user_activity)]));

        $response->assertStatus(Response::HTTP_OK);
    }

}