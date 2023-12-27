<?php

namespace Tests\Feature;
use App\Models\Partner;
use App\Models\User;
use App\Utility;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MarketingTest extends TestCase
{
    //use RefreshDatabase;
    //use WithoutMiddleware;

    public function test_request_collateral_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::join('pas_enrollment', function($join){
            $join->on('partner_id', '=', 'pas_partner.id');
        })
            ->join('pas_program', function($join){
                $join->on('pas_enrollment.program_zoho_id', '=', 'pas_program.zoho_id');
            })
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);
        $response = $this->get(route('request-collateral'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_request_collateral_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::join('pas_enrollment', function($join){
            $join->on('partner_id', '=', 'pas_partner.id');
        })
            ->join('pas_program', function($join){
                $join->on('pas_enrollment.program_zoho_id', '=', 'pas_program.zoho_id');
            })
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);
        $response = $this->get(route('request-collateral'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Request Collateral');
    }

    public function test_top_10_selling_programs_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::join('pas_enrollment', function($join){
                $join->on('partner_id', '=', 'pas_partner.id');
            })
            ->join('pas_program', function($join){
                $join->on('pas_enrollment.program_zoho_id', '=', 'pas_program.zoho_id');
            })
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);
        $response = $this->get(route('top-selling-programs'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_top_10_selling_programs_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::join('pas_enrollment', function($join){
            $join->on('partner_id', '=', 'pas_partner.id');
        })
            ->join('pas_program', function($join){
                $join->on('pas_enrollment.program_zoho_id', '=', 'pas_program.zoho_id');
            })
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);
        $response = $this->get(route('top-selling-programs'));
        $response->assertSee('My Top Selling Programs')->assertOk();
    }

    public function test_request_collateral_with_validation_error(){
//        DB::table('pas_marketing_collateral')->truncate();
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('price_book_id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        if($partner){
            //$this->session(['partner_detail' => $partner]);

            $collateral = [
                'contact_name' => '',
                'contact_email' => '',
                'partner_name' => '',
                'event_date' => null,
                'target_audience' => null,
                'intended_outcome' => null,
                'branding' => null,
                'due_date' => null,
                'desired_completion_date' => null,
                'meeting_proposed_date' => null,
                'project_type' => null,
                'program_id' => null,
                'description' => null,
                'additional_notes' => null,
                'purpose' => null,
                /*'remember_me' => 1,
                'agree_with' => 1,*/
            ];


            //$data['student'][0];
            $response = $this->withSession(['partner_detail' => $partner])
                            ->post(route('marketing-collateral-store'), $collateral);

            $response->assertStatus(Response::HTTP_OK)
                ->assertJson(["status" => 'fail']);
        }
    }

    public function test_request_collateral(){
        //DB::table('pas_marketing_collateral')->truncate();
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('price_book_id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        if($partner){
            //$this->session(['partner_detail' => $partner]);

            User::factory()->count(2)->create([
                'user_type' => User::USER_TYPE_MY_USER,
                'email' => Factory::create()->unique()->safeEmail,
                'firstname' => Factory::create()->firstName,
                'lastname' => Factory::create()->lastName,
                'phone' => Factory::create()->phoneNumber,
                'roleid' => User::ROLE_ACCOUNT_MANAGER,
                'status' => Utility::STATUS_ACTIVE,
                'partner_id' => $partner['id'],
                'partner_type' => User::USER_TYPE_PARTNER,
                'first_login' => 1,
                'password' => md5('Info@12345'),
            ]);

            $collateral = [
                'contact_name' => Factory::create()->firstName,
                'contact_email' => Factory::create()->unique()->safeEmail,
                'partner_name' => $partner['partner_name'],
                'event_date' => null,
                'target_audience' => null,
                'intended_outcome' => null,
                'branding' => null,
                'due_date' => null,
                'desired_completion_date' => null,
                'meeting_proposed_date' => null,
                'project_type' => null,
                'program_id' => null,
                'description' => null,
                'additional_notes' => null,
                'purpose' => null,
                'remember_me' => 1,
                'agree_with' => 1,
                'is_requested_material' => 0,
                'project_type' => 1,
                'description' => 'Unit Testing',
                'desired_completion_date' => date('m/d/Y', strtotime('+30 days')),
            ];


            //$data['student'][0];
            $response = $this->withSession(['partner_detail' => $partner])
                ->post(route('marketing-collateral-store'), $collateral);

            /*$response->assertSessionDoesntHaveErrors([
                'contact_name',
                'contact_email',
                'partner_name'
            ]);
            $response->assertSessionHasNoErrors();*/

            $response->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    "status" => 'success',
                    'msg' => 'Data added successfully.'
                ]);
        }
    }

    public function test_marketing_collateral_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('marketing-collateral'));

        $response->assertSee('Access Denied')->assertStatus(200);
    }

    public function test_marketing_collateral_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('marketing-collateral'));

        $response->assertSee('Marketing Collateral')->assertStatus(200);
    }


    public function test_marketing_collateral_marketing_category_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('marketing-collateral-marketing-category', ['course_marketing' => 'course-marketing']));

        $response->assertSee('Access Denied')->assertOk();
    }

    public function test_marketing_collateral_marketing_category_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('marketing-collateral-marketing-category', ['course_marketing' => 'course-marketing']));

        $response->assertSee('Course Marketing Material')->assertOk();
    }

    public function test_course_marketing_course_marketing_category_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('course-marketing-course-marketing-category', ['course_marketing' => 'baking-and-pastry']));

        $response->assertSee('Access Denied')->assertOk();
    }

    public function test_course_marketing_course_marketing_category_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('course-marketing-course-marketing-category', ['course_marketing' => 'baking-and-pastry']));

        $response->assertOk();
    }

    public function test_course_marketing_course_marketing_category_with_not_found_category()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('course-marketing-course-marketing-category', ['course_marketing' => 'not-found']));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_funding_source_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('marketing-collateral-funding-sources'));

        $response->assertSee('Funding Source')->assertOk();
    }

    public function test_funding_source_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('marketing-collateral-funding-sources'));

        $response->assertSee('Access Denied')->assertOk();
    }

    public function test_social_media_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('marketing-collateral-social-media'));

        $response->assertSee('Social Media')->assertOk();
    }

    public function test_social_media_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('marketing-collateral-social-media'));

        $response->assertSee('Access Denied')->assertOk();
    }

}
