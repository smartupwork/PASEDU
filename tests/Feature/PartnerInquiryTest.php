<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Utility;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PartnerInquiryTest extends TestCase
{

    public function test_partner_inquiry_tool_add_page(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('partner-inquiry-tool'));
        $response->assertSee('Partner Inquiry Tool')->assertOk();
    }

    public function test_partner_inquiry_submit_with_request_type_empty()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_user = [
            'request_type' => '',
            'request_reason' => 'Books/Supplies',
            'message' => "Unit Test Message",
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('partner-inquiry-submit'), $partner_user);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(["status" => 'fail']);
    }

    public function test_partner_inquiry_submit_with_request_reason_empty()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_user = [
            'request_type' => 'For Student',
            'request_reason' => '',
            'message' => "Unit Test Message",
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('partner-inquiry-submit'), $partner_user);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(["status" => 'fail']);
    }

    public function test_partner_inquiry_submit_with_message_empty()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_user = [
            'request_type' => 'For Student',
            'request_reason' => 'Books/Supplies',
            'message' => "",
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('partner-inquiry-submit'), $partner_user);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(["status" => 'fail']);
    }

    public function test_partner_inquiry_submit_successful()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_user = [
            'request_type' => 'For Student',
            'request_reason' => 'Books/Supplies',
            'message' => "Unit Test Message",
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('partner-inquiry-submit'), $partner_user);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(["status" => 'success']);
    }

    public function test_partner_inquiry_tool_listing_page(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('partner-inquiry-tool-list', [
            'request_type' => 'For Student',
            'request_reason' => 'Books/Supplies',
        ]));
        $response->assertSee('Partner Inquiry History')->assertOk();
    }

    public function test_system_email_log_listing_page_ajax()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $response = $this
            ->get(route('partner-inquiry-search', ['q' => 'Student', 'request_type' => 'For Student', 'request_reason' => 'Books/Supplies']));

        $response->assertStatus(Response::HTTP_OK)->assertOk();
    }

    public function test_export_excel_partner_inquiry(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_id = DB::table('pas_partner_inquiry')->inRandomOrder()->value('partner_id');
        $partner = Partner::where('id', '=', $partner_id)
            ->get()->first()->toArray();

        /*$user = User::factory()->create([
            'user_type' => User::USER_TYPE_WE_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);*/

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('partner-inquiry-export-to-excel'));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_export_pdf_partner_inquiry(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_id = DB::table('pas_partner_inquiry')->inRandomOrder()->value('partner_id');
        $partner = Partner::where('id', '=', $partner_id)
            ->get()->first()->toArray();

        /*$user = User::factory()->create([
            'user_type' => User::USER_TYPE_WE_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);*/

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('partner-inquiry-export-to-pdf'));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_export_pdf_partner_inquiry_with_no_record_found(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_id = DB::table('pas_partner_inquiry')->inRandomOrder()->value('partner_id');
        $partner = Partner::where('id', '=', $partner_id)
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('partner-inquiry-export-to-pdf', [
                'q' => 'no record found'
            ]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_delete_partner_inquiry_tool(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $inquiry = DB::table('pas_partner_inquiry')
            ->inRandomOrder()
            ->value('id');

        $response = $this
            ->get(route('partner-inquiry-delete', ['id' => pas_encrypt($inquiry)]));

        $response->assertStatus(Response::HTTP_OK)->assertJson(["status" => 'success']);;
    }

}