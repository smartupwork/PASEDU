<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\Program;
use App\Models\State;
use App\Models\Student;
use App\Models\User;
use App\Utility;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    //use RefreshDatabase;
    //use WithoutMiddleware;

    public function test_my_dashboard_with_user_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_dashboard_report', function($join){
                $join->on('pas_partner.id', '=', 'pas_dashboard_report.partner_id');
            })
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('dashboard'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');

    }

    public function test_my_dashboard_with_user_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_dashboard_report', function($join){
                $join->on('pas_partner.id', '=', 'pas_dashboard_report.partner_id');
            })
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('dashboard'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee($partner['partner_name']);

    }

    public function test_my_dashboard_with_user_has_no_report_data()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('id', '=', 15) // 15 ID has no report data for testing purpose
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('dashboard'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee($partner['partner_name']);

    }

    public function test_update_stats_icon_of_dashboard_with_user_has_no_access()
    {
        $fake_user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->email,
            'firstname' => 'Test',
            'lastname' => 'Partner',
            'phone' => Factory::create()->phoneNumber,
            'roleid' => 1,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => null,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'password_expired_at' => date('Y-m-d H:i:s', strtotime('+365 days')),
        ]);

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data['report'] = array_slice(array_keys(User::getHighlightReports()), 0, 4);

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('update-dashboard'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_update_stats_icon_of_dashboard_with_user_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data['report'] = array_slice(array_keys(User::getHighlightReports()), 0, 4);

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('update-dashboard'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    public function test_map_my_students()
    {
        $fake_user = Utility::addAdminUser();

        $this->withSession(['partner_detail' => Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray()]);

        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('map-my-student'));
        $response->assertSee('Map My Students')->assertOk();
    }

    public function test_refresh_session()
    {
        $fake_user = Utility::addAdminUser();

        $this->withSession(['partner_detail' => Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray()]);

        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('refresh-session'));
        $response->assertJson(['msg' => 'Session Updated.'])
            ->assertOk();
    }

}
