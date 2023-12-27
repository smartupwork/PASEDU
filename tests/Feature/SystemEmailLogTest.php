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

class SystemEmailLogTest extends TestCase
{
    //use RefreshDatabase;
    //use WithoutMiddleware;

    public function test_system_email_log_listing_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('system-email-logs'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_system_email_log_listing_page_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('system-email-logs'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('System Email Logs');
    }

    public function test_system_email_log_listing_page_ajax_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get('/dashboard/systememaillogs/ajax');

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_system_email_log_listing_page_ajax_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        DB::table('pas_email_logs')->insert([
            'from_email' => 'festus@academyofwe.com',
            'to_email' => 'partners@worldeducation.net',
            'subject' => 'Test Subject',
            'message' => 'Test Message',
            'added_date' => date('Y-m-d H:i:s'),
        ]);

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get('/dashboard/systememaillogs/ajax?q=test');

        $response->assertOk();
    }

    public function test_system_email_log_edit_page_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $email_log = DB::table('pas_email_logs')
            ->inRandomOrder()
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('system-email-logs-edit', ['id' => pas_encrypt($email_log)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_system_email_log_edit_page_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $email_log = DB::table('pas_email_logs')
            ->inRandomOrder()
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('system-email-logs-edit', ['id' => pas_encrypt($email_log)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Email Type');
    }

    public function test_system_email_log_edit_page_with_invalid_id(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('system-email-logs-edit', ['id' => pas_encrypt(0)]));

        $response->assertRedirect(route('system-email-logs'));
    }

    public function test_system_email_log_edit_page_with_record_not_exists(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('system-email-logs-edit', ['id' => pas_encrypt(111111111)]));

        $response->assertRedirect(route('system-email-logs'));
    }

    public function test_system_email_log_update_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $email_log = DB::table('pas_email_logs')
            ->inRandomOrder()
            ->get()
            ->first();

        $data = [
            'id' => pas_encrypt($email_log->id),
            'to_email' => $email_log->to_email,
            'date' => $email_log->added_date,
            'subject' => $email_log->subject,
            'message' => $email_log->message,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('emaillogssubmit'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_system_email_log_update_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $email_log = DB::table('pas_email_logs')
            ->inRandomOrder()
            ->get()
            ->first();

        $data = [
            'id' => pas_encrypt($email_log->id),
            'to_email' => $email_log->to_email,
            'date' => $email_log->added_date,
            'subject' => $email_log->subject,
            'message' => $email_log->message,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('emaillogssubmit'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    public function test_system_email_log_update_with_empty_subject(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data = [
            'id' => pas_encrypt(1),
            'to_email' => 'test@gmail.com',
            'date' => date('Y-m-d'),
            'subject' => '',
            'message' => 'Test Message',
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('emaillogssubmit'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'fail',
            ]);
    }

    public function test_system_email_log_update_with_empty_to_email(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data = [
            'id' => pas_encrypt(1),
            'to_email' => '',
            'date' => date('Y-m-d'),
            'subject' => 'Test Sub',
            'message' => 'Test Message',
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('emaillogssubmit'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'fail',
            ]);
    }

    public function test_system_email_log_update_with_empty_message(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data = [
            'id' => pas_encrypt(1),
            'to_email' => 'test@gmail.com',
            'date' => date('Y-m-d'),
            'subject' => 'Test Sub',
            'message' => '',
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('emaillogssubmit'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'fail',
            ]);
    }

    public function test_system_email_log_delete_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $system_email = DB::table('pas_email_logs')
            ->inRandomOrder()
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('system-email-logs-delete', ['id' => pas_encrypt($system_email)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_system_email_log_delete_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $system_email = DB::table('pas_email_logs')
            ->inRandomOrder()
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('system-email-logs-delete', ['id' => pas_encrypt($system_email)]));

        $response->assertJson(['status' => 'success'])->assertStatus(Response::HTTP_OK);
    }
}