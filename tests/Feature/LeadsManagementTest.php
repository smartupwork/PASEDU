<?php

namespace Tests\Feature;

use App\Models\Leads;
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

class LeadsManagementTest extends TestCase
{
    //use RefreshDatabase;
    //use WithoutMiddleware;

    public function test_leads_listing_with_has_no_access() {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('leads'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_leads_listing_with_has_access() {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('leads'));

        $response->assertSee('Leads')->assertOk();
    }

    public function test_leads_add_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('leads-add'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_leads_add_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('leads-add'));

        $response->assertSee('Leads Entry')->assertOk();
    }

    public function test_leads_add_submit_with_has_no_access() {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $leads = [];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('leadssubmit'), $leads);

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_leads_add_submit_with_empty_partner() {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $state_and_country = State::select(['id', 'country_id'])->inRandomOrder()->get()->first()->toArray();

        $leads = [
            'partner_institution' => '',
            'name_requester' => $fake_user['firstname'],
            'email_requester' => $fake_user['email'],
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'address' => null,
            'message' => null,
            'phone' => Factory::create()->phoneNumber,
            'city' => null,
            'state' => $state_and_country['id'],
            'country' => $state_and_country['country_id'],
            'zip' => (string) rand(111111, 999999),
            'interested_program' => Program::inRandomOrder()->value('id'),
            'financing_needs' => Leads::COI_NA,
            'category_interest' => Leads::COI_NA,
            'time_zone' => DB::table('pas_timezone')->inRandomOrder()->value('id'),
        ];

        $response = $this
            ->post(route('leadssubmit'), $leads);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_leads_add_submit_with_empty_name_requester() {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $state_and_country = State::select(['id', 'country_id'])->inRandomOrder()->get()->first()->toArray();

        $leads = [
            'partner_institution' => $partner['id'],
            'name_requester' => '',
            'email_requester' => $fake_user['email'],
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'address' => null,
            'message' => null,
            'phone' => Factory::create()->phoneNumber,
            'city' => null,
            'state' => $state_and_country['id'],
            'country' => $state_and_country['country_id'],
            'zip' => (string) rand(111111, 999999),
            'interested_program' => 1,
            'financing_needs' => Leads::COI_NA,
            'category_interest' => Leads::COI_NA,
            'time_zone' => 2,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('leadssubmit'), $leads);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_leads_add_submit_with_empty_email_requester() {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $state_and_country = State::select(['id', 'country_id'])->inRandomOrder()->get()->first()->toArray();

        $leads = [
            'partner_institution' => $partner['id'],
            'name_requester' => $fake_user['firstname'],
            'email_requester' => '',
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'address' => null,
            'message' => null,
            'phone' => Factory::create()->phoneNumber,
            'city' => null,
            'state' => $state_and_country['id'],
            'country' => $state_and_country['country_id'],
            'zip' => (string) rand(111111, 999999),
            'interested_program' => 1,
            'financing_needs' => Leads::COI_NA,
            'category_interest' => Leads::COI_NA,
            'time_zone' => 2,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('leadssubmit'), $leads);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_leads_add_submit_with_invalid_email_requester() {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $state_and_country = State::select(['id', 'country_id'])->inRandomOrder()->get()->first()->toArray();

        $leads = [
            'partner_institution' => $partner['id'],
            'name_requester' => $fake_user['firstname'],
            'email_requester' => 'invalid-email',
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'address' => null,
            'message' => null,
            'phone' => Factory::create()->phoneNumber,
            'city' => null,
            'state' => $state_and_country['id'],
            'country' => $state_and_country['country_id'],
            'zip' => (string) rand(111111, 999999),
            'interested_program' => 1,
            'financing_needs' => Leads::COI_NA,
            'category_interest' => Leads::COI_NA,
            'time_zone' => 2,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('leadssubmit'), $leads);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_leads_add_submit_with_empty_first_name() {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $state_and_country = State::select(['id', 'country_id'])->inRandomOrder()->get()->first()->toArray();

        $leads = [
            'partner_institution' => $partner['id'],
            'name_requester' => $fake_user['firstname'],
            'email_requester' => $fake_user['email'],
            'firstname' => '',
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'address' => null,
            'message' => null,
            'phone' => Factory::create()->phoneNumber,
            'city' => null,
            'state' => $state_and_country['id'],
            'country' => $state_and_country['country_id'],
            'zip' => (string) rand(111111, 999999),
            'interested_program' => 1,
            'financing_needs' => Leads::COI_NA,
            'category_interest' => Leads::COI_NA,
            'time_zone' => 2,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('leadssubmit'), $leads);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_leads_add_submit_with_empty_last_name() {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $state_and_country = State::select(['id', 'country_id'])->inRandomOrder()->get()->first()->toArray();

        $leads = [
            'partner_institution' => $partner['id'],
            'name_requester' => $fake_user['firstname'],
            'email_requester' => $fake_user['email'],
            'firstname' => Factory::create()->firstName,
            'lastname' => '',
            'email' => Factory::create()->unique()->safeEmail,
            'address' => null,
            'message' => null,
            'phone' => Factory::create()->phoneNumber,
            'city' => null,
            'state' => $state_and_country['id'],
            'country' => $state_and_country['country_id'],
            'zip' => (string) rand(111111, 999999),
            'interested_program' => 1,
            'financing_needs' => Leads::COI_NA,
            'category_interest' => Leads::COI_NA,
            'time_zone' => 2,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('leadssubmit'), $leads);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_leads_add_submit_with_empty_email() {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $state_and_country = State::select(['id', 'country_id'])->inRandomOrder()->get()->first()->toArray();

        $leads = [
            'partner_institution' => $partner['id'],
            'name_requester' => $fake_user['firstname'],
            'email_requester' => $fake_user['email'],
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => '',
            'address' => null,
            'message' => null,
            'phone' => Factory::create()->phoneNumber,
            'city' => null,
            'state' => $state_and_country['id'],
            'country' => $state_and_country['country_id'],
            'zip' => (string) rand(111111, 999999),
            'interested_program' => 1,
            'financing_needs' => Leads::COI_NA,
            'category_interest' => Leads::COI_NA,
            'time_zone' => 2,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('leadssubmit'), $leads);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_leads_add_submit_with_invalid_email() {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $state_and_country = State::select(['id', 'country_id'])->inRandomOrder()->get()->first()->toArray();

        $leads = [
            'partner_institution' => $partner['id'],
            'name_requester' => $fake_user['firstname'],
            'email_requester' => $fake_user['email'],
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => 'invalid-email',
            'address' => null,
            'message' => null,
            'phone' => Factory::create()->phoneNumber,
            'city' => null,
            'state' => $state_and_country['id'],
            'country' => $state_and_country['country_id'],
            'zip' => (string) rand(111111, 999999),
            'interested_program' => 1,
            'financing_needs' => Leads::COI_NA,
            'category_interest' => Leads::COI_NA,
            'time_zone' => 2,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('leadssubmit'), $leads);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_leads_add_submit_with_has_access() {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('pas_leads')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $state_and_country = State::select(['id', 'country_id'])->inRandomOrder()->get()->first()->toArray();

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $leads = [
            'partner_institution' => (string) $partner['id'],
            'name_requester' => $fake_user['firstname'],
            'email_requester' => $fake_user['email'],
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'address' => null,
            'message' => null,
            'phone' => Factory::create()->phoneNumber,
            'city' => null,
            'state' => $state_and_country['id'],
            'country' => $state_and_country['country_id'],
            'zip' => (string) rand(111111, 999999),
            'interested_program' => Program::inRandomOrder()->value('id'),
            'financing_needs' => Leads::COI_NA,
            'category_interest' => Leads::COI_NA,
            'time_zone' => DB::table('pas_timezone')->inRandomOrder()->value('id'),
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('leadssubmit'), $leads);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_leads_update_submit_with_has_access() {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $state_and_country = State::select(['id', 'country_id'])->inRandomOrder()->get()->first()->toArray();

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $leads = [
            'id' => pas_encrypt(DB::table('pas_leads')->inRandomOrder()->value('id')),
            'partner_institution' => (string) $partner['id'],
            'name_requester' => $fake_user['firstname'],
            'email_requester' => $fake_user['email'],
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'address' => null,
            'message' => null,
            'phone' => Factory::create()->phoneNumber,
            'city' => null,
            'state' => $state_and_country['id'],
            'country' => $state_and_country['country_id'],
            'zip' => (string) rand(111111, 999999),
            'interested_program' => Program::inRandomOrder()->value('id'),
            'financing_needs' => Leads::COI_NA,
            'category_interest' => Leads::COI_NA,
            'time_zone' => DB::table('pas_timezone')->inRandomOrder()->value('id'),
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('leadssubmit'), $leads);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_leads_listing_ajax_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('leads-search', ['q' => 'test']));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_leads_listing_ajax_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.id', 'pas_partner.zoho_id', 'pas_partner.partner_name'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_leads AS l', 'l.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();


        $leads = DB::table('pas_leads')->inRandomOrder()->get()->first();
        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('leads-search', [
                'q' => $leads->firstname,
                'firstname' => $leads->firstname,
                'lastname' => $leads->lastname,
                'email' => $leads->email,
                'partner_institution' => $leads->partner_institution,
                'name_requester' => $leads->name_of_requester,
                'email_requester' => $leads->email_of_requester,
            ]));

        $response->assertOk();
    }

    public function test_leads_detail_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.*'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_leads AS l', 'l.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $leads = DB::table('pas_leads')
            ->where('partner_id', '=', $partner['id'])
            ->inRandomOrder()->get()->first();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('leads-view', ['id' => pas_encrypt($leads->id)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_leads_detail_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.*'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_leads AS l', 'l.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $leads = DB::table('pas_leads')
            ->where('partner_id', '=', $partner['id'])
            ->inRandomOrder()->get()->first();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('leads-view', ['id' => pas_encrypt($leads->id)]));

        $response->assertSee('Leads View')
            ->assertStatus(Response::HTTP_OK);
    }

    public function test_leads_detail_with_invalid_id(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.id', 'pas_partner.zoho_id', 'pas_partner.partner_name'])
            //->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_leads AS l', 'l.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('leads-view', ['id' => 1]));

        $response->assertRedirect();
    }

    public function test_leads_export_pdf_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.*'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_leads AS l', 'l.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $leads = DB::table('pas_leads')
            ->where('partner_id', '=', $partner['id'])
            ->inRandomOrder()->get()->first();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('leads-export-to-pdf', [
                'q' => $leads->firstname,
                'firstname' => $leads->firstname,
                'lastname' => $leads->lastname,
                'email' => $leads->email,
                'partner_institution' => $leads->partner_institution,
                'name_requester' => $leads->name_of_requester,
                'email_requester' => $leads->email_of_requester,
            ]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_leads_export_pdf_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.id', 'pas_partner.zoho_id', 'pas_partner.partner_name'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_leads AS l', 'l.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('leads-export-to-pdf'));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_leads_export_pdf_with_record_not_found(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.id', 'pas_partner.zoho_id', 'pas_partner.partner_name'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_leads AS l', 'l.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('leads-export-to-pdf', [
                'q' => 'record-not-found',
            ]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_leads_export_excel_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.*'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_leads AS l', 'l.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('leads-export-to-excel'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }


    public function test_leads_export_excel_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.*'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_leads AS l', 'l.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('leads-export-to-excel'));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_leads_enrollment_delete_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $my_user = DB::table('pas_leads')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('leads-delete', ['id' => pas_encrypt($my_user)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_leads_enrollment_delete_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $my_user = DB::table('pas_leads')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('leads-delete', ['id' => pas_encrypt($my_user)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    /*public function test_leads_add_submit_with_zoho_error() {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $state_and_country = State::select(['id', 'country_id'])->inRandomOrder()->get()->first()->toArray();

        $partner = Partner::select(['pas_partner.id', 'pas_partner.zoho_id', 'pas_partner.partner_name'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_leads AS l', 'l.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $leads = DB::table('pas_leads')
            ->where('partner_id', '=', $partner['id'])
            ->inRandomOrder()->get()->first();

        DB::table('pas_leads')->where('id', '=', $leads->id)->delete();

        $leads = [
            'partner_institution' => $leads->partner_id,
            'name_requester' => $leads->name_of_requester,
            'email_requester' => $leads->email_of_requester,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => $leads->email,
            'address' => null,
            'message' => null,
            'phone' => Factory::create()->phoneNumber,
            'city' => null,
            'state' => $state_and_country['id'],
            'country' => $state_and_country['country_id'],
            'zip' => (string) rand(111111, 999999),
            'interested_program' => Program::inRandomOrder()->value('id'),
            'financing_needs' => Leads::COI_NA,
            'category_interest' => Leads::COI_NA,
            'time_zone' => DB::table('pas_timezone')->inRandomOrder()->value('id'),
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('leadssubmit'), $leads);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }*/

}
