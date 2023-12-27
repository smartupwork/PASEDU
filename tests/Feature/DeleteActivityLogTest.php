<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Utility;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DeleteActivityLogTest extends TestCase
{
    //use RefreshDatabase;
    //use WithoutMiddleware;

    public function test_deleted_user_activity_log_listing_with_has_no_access(){
        $fake_user = Utility::addWeUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('deleted-activity-logs'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_deleted_user_activity_log_listing_page_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('deleted-activity-logs'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Deleted Activity Logs');
    }

    public function test_deleted_user_activity_log_listing_page_ajax_with_has_no_access()
    {
        $fake_user = Utility::addWeUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('deleted-activity-logs-ajax'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_deleted_user_activity_log_listing_page_ajax_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('deleted-activity-logs-ajax'));

        $response->assertOk();
    }

    public function test_deleted_user_activity_permanently_delete_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $activity_id = DB::table('pas_user_activity')
            ->where('action', 'delete')
            ->whereNotIn('url', ['cron-price-book-program-map'])
            ->orderBy('id', 'DESC')
            ->value('id');

        $response = $this
            ->get(route('delete-deleted-activity-log', ['id' => pas_encrypt($activity_id)]));

        $response->assertStatus(Response::HTTP_OK)->assertJson(["status" => 'success']);;
    }

    public function test_deleted_user_activity_revert_with_has_no_access(){
        $fake_user = Utility::addWeUserWithoutAnyAccess();

        $this->actingAs($fake_user, 'web');

        $activity_id = DB::table('pas_user_activity')
            ->where('action', 'delete')
            ->whereNotIn('url', ['cron-price-book-program-map'])
            ->orderBy('id', 'DESC')
            ->value('id');

        $response = $this
            ->get(route('deleted-activity-log-revert', ['id' => pas_encrypt($activity_id)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_deleted_user_activity_revert_my_user_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $activity_id = DB::table('pas_user_activity')
            ->where('action', 'delete')
            ->whereIn('url', ['my-user-delete'])
            //->whereNotIn('url', ['cron-price-book-program-map'])
            ->orderBy('id', 'DESC')
            ->value('id');

        $response = $this
            ->get(route('deleted-activity-log-revert', ['id' => pas_encrypt($activity_id)]));

        $response->assertStatus(Response::HTTP_OK)->assertJson(["status" => 'success']);;
    }

    public function test_deleted_user_activity_revert_partner_user_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $activity_id = DB::table('pas_user_activity')
            ->where('action', 'delete')
            ->whereIn('url', ['partner-users-delete'])
//            ->whereNotIn('url', ['cron-price-book-program-map'])
            ->orderBy('id', 'DESC')
            ->value('id');

        $response = $this
            ->get(route('deleted-activity-log-revert', ['id' => pas_encrypt($activity_id)]));

        $response->assertStatus(Response::HTTP_OK)->assertJson(["status" => 'success']);;
    }

    public function test_deleted_user_activity_revert_we_user_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $activity_id = DB::table('pas_user_activity')
            ->where('action', 'delete')
            ->whereIn('url', ['we-users-delete'])
//            ->whereNotIn('url', ['cron-price-book-program-map'])
            ->orderBy('id', 'DESC')
            ->value('id');

        $response = $this
            ->get(route('deleted-activity-log-revert', ['id' => pas_encrypt($activity_id)]));

        $response->assertStatus(Response::HTTP_OK)->assertJson(["status" => 'success']);;
    }
}