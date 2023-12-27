<?php

namespace Tests\Feature;

use App\Models\LoginActivity;
use App\Models\Partner;
use App\Models\User;
use App\Models\WrongLogin;
use App\Utility;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    //use RefreshDatabase;

    public function test_open_login_page_if_user_already_logged_in()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');
        $response = $this->call('GET', route('login'));

        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_open_login_page_if_user_not_logged_in()
    {
        $response = $this->call('GET', route('login'));
        $response->assertOk();
    }

    public function test_user_can_login_using_the_login_form()
    {
        /*DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Db::table('pas_leads')->truncate();// Using user id as a foreign key
        Db::table('pas_login_activity')->truncate();// Using user id as a foreign key
        Db::table('pas_users_access')->truncate();
        Db::table('pas_wrong_login')->truncate();
        Db::table('pas_users')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');*/

        $user = Utility::addAdminUser();
        $this->actingAs($user, 'web');

        $userData = [
            "email" => $user->email,
            "password" => "Info@12345",
            "agree" => "1",
        ];

        WrongLogin::factory()->count(4)->create([
            'user_id' => $user->id,
            'attempt_time' => date("Y-m-d H:i:s"),
            'ip_address' => Utility::getClientIp(),
        ]);

        $response = $this->post('/submit', $userData);

        $this->assertAuthenticated();
        //$response->assertRedirect('/'); // In case while redirect with PHP after login

        $response->assertStatus(Response::HTTP_OK);
        //$response->dump();
            /*->assertJson([
                "status" => 'success',
                'message' => 'logged in successfully'
            ]);*/

        //$response->assertStatus(Response::HTTP_OK);
    }

    public function test_partner_user_can_login_using_the_login_using_correct_credentials()
    {
        /*DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Db::table('pas_leads')->truncate();// Using user id as a foreign key
        Db::table('pas_login_activity')->truncate();// Using user id as a foreign key
        Db::table('pas_users_access')->truncate();
        Db::table('pas_wrong_login')->truncate();
        Db::table('pas_users')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');*/

        $user = User::factory()->create([
            'user_type' => User::USER_TYPE_PARTNER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_id' => DB::table('pas_partner')->inRandomOrder()->value('id'),
            'partner_type' => DB::table('pas_partner_type')->inRandomOrder()->value('id'),
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);
        $this->actingAs($user, 'web');

        $userData = [
            "email" => $user->email,
            "password" => "Info@12345",
            "agree" => "1",
        ];

        $response = $this->withCookie('authcookies-'.$user->id, '1')
            ->post('/submit', $userData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
                //'message' => 'Sorry! You can allowed login only on 3 devices at the same time.'
            ]);
    }

    public function test_login_restrict_if_user_already_logged_in_more_than_three_device()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        LoginActivity::factory()->count(3)->create();

        $userData = [
            "email" => $fake_user['email'],
            "password" => "Info@12345",
            "agree" => "1",
        ];

        $response = $this->post('/submit', $userData);

        $this->assertAuthenticated();
        //$response->assertRedirect('/'); // In case while redirect with PHP after login

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
                //'message' => 'Sorry! You can allowed login only on 3 devices at the same time.'
            ]);

        //$response->assertStatus(Response::HTTP_OK);
    }

    public function test_login_with_password_length_below_10_characters()
    {
        $userData = [
            "email" => "doe@example.com",
            "password" => "demo12345"
        ];

        $response = $this->post('/submit', $userData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
                'message' => 'Password must be at least 10 chars long.'
            ]);
    }

    public function test_login_with_empty_email()
    {
        $userData = [
            "email" => "",
            "password" => "demo12345"
        ];

        $response = $this->post('/submit', $userData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_login_with_invalid_email()
    {
        $userData = [
            "email" => "rajneesh",
            "password" => "demo12345"
        ];

        $response = $this->post('/submit', $userData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_login_with_empty_password()
    {
        $userData = [
            "email" => "rajneesh@example.com",
            "password" => ""
        ];

        $response = $this->post('/submit', $userData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_password_length_greater_then_10_characters_in_login()
    {
        $userData = [
            "email" => "doe@example.com",
            "password" => "Demo@1234567890Test"
        ];

        $response = $this->post('/submit', $userData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_uncheck_agree_term_of_use_in_login()
    {
        $userData = [
            "email" => "doe@example.com",
            "password" => "Demo@12345"
        ];

        $response = $this->post('/submit', $userData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_wrong_attempt_less_then_three_times_in_login()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $userData = [
            "email" => $fake_user['email'],
            "password" => "Info@12345678",
            "agree" => "1",
        ];

        WrongLogin::factory()->count(2)->create();

        $response = $this->post('/submit', $userData);

        //$this->assertAuthenticated();

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
                //'message' => 'You have attempt 3 wrong password now your account has been locked for 30 minutes.'
            ]);
    }

    public function test_wrong_attempt_three_times_in_login()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $userData = [
            "email" => $fake_user['email'],
            "password" => "Info@12345678",
            "agree" => "1",
        ];

        WrongLogin::factory()->count(4)->create();

        $response = $this->post('/submit', $userData);

        //$this->assertAuthenticated();

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
                //'message' => 'You have attempt 3 wrong password now your account has been locked for 30 minutes.'
            ]);
    }

    public function test_login_with_block_account_for_30_minutes()
    {
        $fake_user = User::factory()->count(4)->create([
            'user_type' => User::USER_TYPE_ADMIN,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => 1,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => null,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'last_wrong_attempted_at' => date('Y-m-d H:i:s'),
        ]);

        $this->actingAs($fake_user[0], 'web');

        $userData = [
            "email" => $fake_user[0]['email'],
            "password" => "Info@12345",
            "agree" => "1",
        ];

        WrongLogin::factory()->count(4)->create();

        $response = $this->post('/submit', $userData);

        //$this->assertAuthenticated();

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
                //'message' => 'You have attempt 3 wrong password now your account has been locked for 30 minutes.'
            ]);
    }

    public function test_login_with_unblock_account_after_30_minutes()
    {
        $fake_user = User::factory()->count(4)->create([
            'user_type' => User::USER_TYPE_ADMIN,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => 1,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => null,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(40)->format('Y-m-d H:i:s'),
        ]);

        $this->actingAs($fake_user[0], 'web');

        $userData = [
            "email" => $fake_user[0]['email'],
            "password" => "Info@12345",
            "agree" => "1",
        ];

        WrongLogin::factory()->count(4)->create();

        $response = $this->post('/submit', $userData);

        //$this->assertAuthenticated();

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
                //'message' => 'You have attempt 3 wrong password now your account has been locked for 30 minutes.'
            ]);
    }

    public function test_reset_password_page()
    {
        $response = $this->get('/index/reset');
        $response->assertSee('Forgot your Password?')->assertStatus(Response::HTTP_OK);
    }

    public function test_reset_password_page_with_login()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $response = $this->get('/index/reset');
        $response->assertStatus(Response::HTTP_FOUND);
    }

    public function test_reset_password_request_submit()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $response = $this->post(route('resetpass'), ["email" => $fake_user->email]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_reset_password_request_submit_with_empty_email()
    {
        $response = $this->post(route('resetpass'), ["email" => '']);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_reset_password_request_submit_with_wrong_email()
    {
        $response = $this->post(route('resetpass'), ["email" => 'rajneesh@example.com']);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_change_password_page()
    {
        $response = $this->get('/index/changepass');
        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_change_password_with_empty_otp()
    {
        $response = $this->post(route('changepassword'), [
            "otp" => '',
            "pass" => 'Info@12345',
            "cpass" => 'Info@12345',
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_change_password_with_wrong_otp()
    {
        $response = $this->post(route('changepassword'), [
            "otp" => '2232',
            "pass" => 'Info@12345',
            "cpass" => 'Info@12345',
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_change_password_with_wrong_confirm_password()
    {
        $response = $this->post(route('changepassword'), [
            "otp" => '',
            "pass" => 'Info@12345',
            "cpass" => 'Info@123456',
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_change_password_with_with_out_special_character_password()
    {
        $user = DB::table('pas_users')
            ->where('otp', '!=', 0)
            ->whereNotNull('otp')->get()->first();

        $response = $this->post(route('changepassword'), [
            "otp" => $user->otp,
            "pass" => 'Info1234567',
            "cpass" => 'Info1234567',
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_change_password_submit()
    {
        $user = DB::table('pas_users')
            ->where('otp', '!=', 0)
            ->whereNotNull('otp')->get()->first();

        $response = $this->post(route('changepassword'), [
            "otp" => $user->otp,
            "pass" => 'Info@12345',
            "cpass" => 'Info@12345',
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_email_authentication()
    {
        $user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => null,
            'first_login' => 2,
            'password' => md5('Info@12345'),
        ]);

        $response = $this->withSession(['uid' => $user->id])
            ->get('/index/emailauthentication');

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_send_code()
    {
        $user = DB::table('pas_users')
            ->where('first_login', '>', 0)
            ->get()->first();

        $response = $this->post(route('sendcode'), [
            "sids" => $user->id
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_send_code_submit_with_empty_login_code()
    {
        $user = DB::table('pas_users')
            ->where('login_code', '!=', 0)
            ->whereNotNull('login_code')->get()->first();

        $response = $this->withSession(['uid' => $user->id])
            ->post(route('submitcode'), [
                "logincode" => '',
                "remember_me" => 1,
            ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_send_code_submit_with_invalid_login_code()
    {
        $user = DB::table('pas_users')
            ->where('login_code', '!=', 0)
            ->whereNotNull('login_code')->get()->first();

        $response = $this->withSession(['uid' => $user->id])
            ->post(route('submitcode'), [
                "logincode" => '111111',
                "remember_me" => 1,
            ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_send_code_submit()
    {
        $user = DB::table('pas_users')
            ->where('login_code', '!=', 0)
            ->whereNotNull('login_code')->get()->first();

        $response = $this->withSession(['uid' => $user->id])
            ->post(route('submitcode'), [
                "logincode" => $user->login_code,
                "remember_me" => 1,
            ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_send_code_while_first_login()
    {
        $user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => null,
            'first_login' => 2,
            'password' => md5('Info@12345'),
        ]);

        $response = $this->post(route('sendcode'), [
            "sids" => $user->id,
            "remember_me" => 1,
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_first_change_password()
    {
        $fake_user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => null,
            'first_login' => 0,
            'password' => md5('Info@12345'),
        ]);

        $this->actingAs($fake_user, 'web');

        $response = $this->get('/index/firstchangepass');
        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_first_change_password_with_not_logged_in()
    {
        $response = $this->get('/index/firstchangepass');
        $response->assertRedirect('/');
    }

    public function test_first_change_password_with_change_the_password_already_done()
    {
        $fake_user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => null,
            'first_login' => 2,
            'password' => md5('Info@12345'),
            'password_expired_at' => date('Y-m-d H:i:s', strtotime('+10 days')),
        ]);

        $this->actingAs($fake_user, 'web');

        $response = $this->get('/index/firstchangepass');
        $response->assertRedirect(route('dashboard'));
    }

    public function test_change_password_on_first_login_submit()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => null,
            'first_login' => 2,
            'password' => md5('Info@12345'),
        ]);

        $response = $this->post(route('firstchangepasswordsubmit'), [
            "sids" => $user->id,
            "pass" => 'Info@12345',
            "cpass" => 'Info@12345',
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_change_password_on_first_login_submit_with_passowrd_validation_fail()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => null,
            'first_login' => 2,
            'password' => md5('Info@12345'),
        ]);

        $response = $this->post(route('firstchangepasswordsubmit'), [
            "sids" => $user->id,
            "pass" => 'info12345',
            "cpass" => 'info12345',
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_change_password()
    {
        $response = $this->get('/index/changepass');
        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_check_token_expired()
    {
        $response = $this->post(route('emptyxhr'));
        $response->assertJson(['status' => 'success'])->assertStatus(Response::HTTP_OK);
    }

    public function test_term_use_page()
    {
        $response = $this->get('/index/termuse');
        $response->assertSee('Terms and Conditions of Use')->assertStatus(Response::HTTP_OK);
    }

    public function test_login_support_page()
    {
        $response = $this->get('/index/loginsupport');
        $response->assertSee('If you have trouble logging')->assertStatus(Response::HTTP_OK);
    }

    public function test_logout()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('logout'));

        $response->assertStatus(Response::HTTP_FOUND);
    }

}
