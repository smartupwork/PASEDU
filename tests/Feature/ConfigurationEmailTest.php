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

class ConfigurationEmailTest extends TestCase
{
    //use RefreshDatabase;
    //use WithoutMiddleware;

    public function test_student_enrollment_listing_ajax_with_user_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('config-email-ajax'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_student_enrollment_listing_ajax_with_user_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('config-email-ajax'));

        $response->assertOk();
    }

    public function test_student_enrollment_listing_ajax_with_search_keyword()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('config-email-ajax', ['q' => 'test']));

        $response->assertOk();
    }

    public function test_we_templates_landing_page_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('configuration-email'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_configuration_email_listing_page_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('configuration-email'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Configuration Email');
    }

    public function test_configuration_email_update_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $email_template = EmailTemplates::whereNotNull('from_email')->inRandomOrder()->get()->first()->toArray();

        $data = [
            'id' => pas_encrypt($email_template['id']),
            'from_name' => $email_template['from_name'],
            'from_email' => $email_template['from_email'],
            'type' => $email_template['type'],
            'subject' => $email_template['subject'],
            'message' => $email_template['message'],
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('configsubmit'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_configuration_email_update_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $email_template = EmailTemplates::whereNotNull('from_email')->inRandomOrder()->get()->first()->toArray();

        $data = [
            'id' => pas_encrypt($email_template['id']),
            'from_name' => $email_template['from_name'],
            'from_email' => $email_template['from_email'],
            'type' => $email_template['type'],
            'subject' => $email_template['subject'],
            'message' => $email_template['message'],
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('configsubmit'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'success',
                'msg' => 'Record updated successfully.',
            ]);
    }

    public function test_configuration_email_update_without_enter_from_name(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $email_template = EmailTemplates::whereNotNull('from_email')->inRandomOrder()->get()->first()->toArray();

        $data = [
            'id' => pas_encrypt($email_template['id']),
            'from_name' => '',
            'from_email' => $email_template['from_email'],
            'type' => $email_template['type'],
            'subject' => $email_template['subject'],
            'message' => $email_template['message'],
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('configsubmit'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'fail',
            ]);
    }

    public function test_configuration_email_update_without_enter_from_email(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $email_template = EmailTemplates::whereNotNull('from_email')->inRandomOrder()->get()->first()->toArray();

        $data = [
            'id' => pas_encrypt($email_template['id']),
            'from_name' => $email_template['from_name'],
            'from_email' => '',
            'type' => $email_template['type'],
            'subject' => $email_template['subject'],
            'message' => $email_template['message'],
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('configsubmit'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'fail',
            ]);
    }

    public function test_configuration_email_update_without_enter_subject(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $email_template = EmailTemplates::whereNotNull('from_email')->inRandomOrder()->get()->first()->toArray();

        $data = [
            'id' => pas_encrypt($email_template['id']),
            'from_name' => $email_template['from_name'],
            'from_email' => $email_template['from_email'],
            'type' => $email_template['type'],
            'subject' => '',
            'message' => $email_template['message'],
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('configsubmit'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'fail',
            ]);
    }

    public function test_configuration_email_update_without_enter_message(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $email_template = EmailTemplates::whereNotNull('from_email')->inRandomOrder()->get()->first()->toArray();

        $data = [
            'id' => pas_encrypt($email_template['id']),
            'from_name' => $email_template['from_name'],
            'from_email' => $email_template['from_email'],
            'type' => $email_template['type'],
            'subject' => $email_template['subject'],
            'message' => '',
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('configsubmit'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'fail',
            ]);
    }

    public function test_configuration_email_edit_page_with_user_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $email_template = EmailTemplates::whereNotNull('from_email')->inRandomOrder()->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('configuration-email-edit', ['id' => pas_encrypt($email_template)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_configuration_email_edit_page_with_invalid_id(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('configuration-email-edit', ['id' => 2]));

        $response->assertRedirect(route('configuration-email'));
    }

    public function test_configuration_email_edit_page_with_wrong_id(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('configuration-email-edit', ['id' => pas_encrypt(1111111)]));

        $response->assertRedirect(route('configuration-email'));
    }


    public function test_configuration_email_edit_page_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $email_template = EmailTemplates::whereNotNull('from_email')->inRandomOrder()->value('id');
        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('configuration-email-edit', ['id' => pas_encrypt($email_template)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    private function addUserWithoutAnyAccess(){
        return User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => 1,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => null,
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);
    }
}