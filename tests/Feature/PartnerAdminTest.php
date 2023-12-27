<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\Roles;
use App\Models\User;
use App\Utility;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PartnerAdminTest extends TestCase
{
    //use RefreshDatabase;
    //use WithoutMiddleware;

    public function test_my_user_listing_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('my-users'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_my_user_listing_with_user_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');
        $response = $this->call('GET', route('my-users'));

        $response->assertSee('My Users List')->assertOk();
    }

    public function test_my_user_listing_ajax_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get('/dashboard/myusers/ajax');

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_my_user_listing_ajax_with_user_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

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
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s')
        ]);

        $response = $this->get('/dashboard/myusers/ajax');

        $response->assertOk();
    }

    public function test_my_user_listing_ajax_with_search_keyword()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get('/dashboard/myusers/ajax?q=test');

        $response->assertOk();
    }

    public function test_my_user_listing_ajax_with_advance_search()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get('/dashboard/myusers/ajax?q=test&fname=test&lname=test&role=test&status=test&phone=test&email=test');

        $response->assertOk();
    }

    public function test_my_user_add_new_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('my-user-form'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_my_user_add_new_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
                        ->withSession(['partner_detail' => $partner])
                        ->get(route('my-user-form'));


        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('My User Entry');
    }

    public function test_my_user_add_submit_with_account_support(){
        /*DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('pas_users_access')->truncate();
        DB::table('pas_users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');*/


        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $we_user = [
            'user_type' => User::USER_TYPE_ADMIN,
            'fname' => Factory::create()->firstName,
            'lname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'role' => User::ROLE_ACCOUNT_SUPPORT,
            'status' => Utility::STATUS_ACTIVE,
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s'),
            'photo' => UploadedFile::fake()->image('institute-profile-logo.jpg'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('myusersubmit'), $we_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
                'msg' => 'Record added successfully.'
            ]);
    }

    public function test_my_user_add_submit_with_account_manager(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $we_user = [
            'user_type' => User::USER_TYPE_ADMIN,
            'fname' => Factory::create()->firstName,
            'lname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'role' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s')
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('myusersubmit'), $we_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
                'msg' => 'Record added successfully.'
            ]);
    }

    public function test_my_user_add_submit_with_role_registration_account(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $we_user = [
            'user_type' => User::USER_TYPE_ADMIN,
            'fname' => Factory::create()->firstName,
            'lname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'role' => User::ROLE_REGISTRATION_ACCOUNT,
            'status' => Utility::STATUS_ACTIVE,
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s')
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('myusersubmit'), $we_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
                'msg' => 'Record added successfully.'
            ]);
    }

    public function test_my_user_add_submit_with_duplicate_email(){
        $fake_user = Utility::addAdminUser();
        //dd($fake_user->attributesToArray());
        $this->actingAs($fake_user, 'web');

        $my_user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => User::USER_TYPE_PARTNER,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s')
        ]);

//dd($my_user);
        $we_user = [
            'user_type' => $my_user->user_type,
            'fname' => $my_user->firstname,
            'lname' => $my_user->lastname,
            'email' => $my_user->email,
            'phone' => $my_user->phone,
            'role' => $my_user->roleid,
            'old_role' => $my_user->roleid,
            'status' => Utility::STATUS_ACTIVE,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner])
            ->post(route('myusersubmit'), $we_user)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_edit_my_user_form_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('my-user-edit', ['id' => 0]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_edit_my_user_form_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $my_user = DB::table('pas_users')
            ->where('user_type', '=', User::USER_TYPE_MY_USER)
            //->where('roleid', '=', User::ROLE_ACCOUNT_MANAGER)
            ->get()->first();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('my-user-edit', ['id' => pas_encrypt($my_user->id)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_edit_my_user_form_with_invalid_id(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('my-user-edit', ['id' => 000]));

        $response->assertRedirect(route('my-users'));
    }

    public function test_edit_my_user_form_with_no_record_exists_id(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('my-user-edit', ['id' => pas_encrypt(011111)]));

        $response->assertRedirect(route('my-users'));
    }

    public function test_my_user_edit_submit_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $my_user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => User::USER_TYPE_PARTNER,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s')
        ]);

//dd($my_user);
        $we_user = [
            'id' => pas_encrypt($my_user->id),
            'user_type' => $my_user->user_type,
            'fname' => $my_user->firstname,
            'lname' => $my_user->lastname,
            'email' => $my_user->email,
            'phone' => $my_user->phone,
            'role' => $my_user->roleid,
            'old_role' => $my_user->roleid,
            'status' => Utility::STATUS_ACTIVE,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('myusersubmit'), $we_user);

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_my_user_edit_submit_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        //dd($fake_user->attributesToArray());
        $this->actingAs($fake_user, 'web');

        $my_user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => User::USER_TYPE_PARTNER,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s')
//            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            //'remember_token' => Str::random(10),
        ]);

//dd($my_user);
        $we_user = [
            'id' => pas_encrypt($my_user->id),
            'user_type' => $my_user->user_type,
            'fname' => $my_user->firstname,
            'lname' => $my_user->lastname,
            'email' => $my_user->email,
            'phone' => $my_user->phone,
            'role' => $my_user->roleid,
            'old_role' => $my_user->roleid,
            'status' => Utility::STATUS_ACTIVE,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner])
            ->post(route('myusersubmit'), $we_user)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_my_user_edit_submit_with_empty_first_name(){
        $fake_user = Utility::addAdminUser();
        //dd($fake_user->attributesToArray());
        $this->actingAs($fake_user, 'web');

        $my_user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => User::USER_TYPE_PARTNER,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s')
//            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            //'remember_token' => Str::random(10),
        ]);

//dd($my_user);
        $we_user = [
            'id' => pas_encrypt($my_user->id),
            'user_type' => $my_user->user_type,
            'fname' => '',
            'lname' => $my_user->lastname,
            'email' => $my_user->email,
            'phone' => $my_user->phone,
            'role' => $my_user->roleid,
            'old_role' => $my_user->roleid,
            'status' => Utility::STATUS_ACTIVE,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner])
            ->post(route('myusersubmit'), $we_user)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_my_user_edit_submit_with_empty_last_name(){
        $fake_user = Utility::addAdminUser();
        //dd($fake_user->attributesToArray());
        $this->actingAs($fake_user, 'web');

        $my_user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => User::USER_TYPE_PARTNER,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s')
//            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            //'remember_token' => Str::random(10),
        ]);

//dd($my_user);
        $we_user = [
            'id' => pas_encrypt($my_user->id),
            'user_type' => $my_user->user_type,
            'fname' => $my_user->firstname,
            'lname' => '',
            'email' => $my_user->email,
            'phone' => $my_user->phone,
            'role' => $my_user->roleid,
            'old_role' => $my_user->roleid,
            'status' => Utility::STATUS_ACTIVE,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner])
            ->post(route('myusersubmit'), $we_user)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_my_user_edit_submit_with_empty_email(){
        $fake_user = Utility::addAdminUser();
        //dd($fake_user->attributesToArray());
        $this->actingAs($fake_user, 'web');

        $my_user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => User::USER_TYPE_PARTNER,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s')
        ]);

//dd($my_user);
        $we_user = [
            'id' => pas_encrypt($my_user->id),
            'user_type' => $my_user->user_type,
            'fname' => $my_user->firstname,
            'lname' => $my_user->lastname,
            'email' => '',
            'phone' => $my_user->phone,
            'role' => $my_user->roleid,
            'old_role' => $my_user->roleid,
            'status' => Utility::STATUS_ACTIVE,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner])
            ->post(route('myusersubmit'), $we_user)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_my_user_edit_submit_with_invalid_email(){
        $fake_user = Utility::addAdminUser();
        //dd($fake_user->attributesToArray());
        $this->actingAs($fake_user, 'web');

        $my_user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => User::USER_TYPE_PARTNER,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s')
        ]);

//dd($my_user);
        $we_user = [
            'id' => pas_encrypt($my_user->id),
            'user_type' => $my_user->user_type,
            'fname' => $my_user->firstname,
            'lname' => $my_user->lastname,
            'email' => 'test',
            'phone' => $my_user->phone,
            'role' => $my_user->roleid,
            'old_role' => $my_user->roleid,
            'status' => Utility::STATUS_ACTIVE,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner])
            ->post(route('myusersubmit'), $we_user)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_my_user_edit_submit_with_empty_phone(){
        $fake_user = Utility::addAdminUser();
        //dd($fake_user->attributesToArray());
        $this->actingAs($fake_user, 'web');

        $my_user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => User::USER_TYPE_PARTNER,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s')
        ]);

//dd($my_user);
        $we_user = [
            'id' => pas_encrypt($my_user->id),
            'user_type' => $my_user->user_type,
            'fname' => $my_user->firstname,
            'lname' => $my_user->lastname,
            'email' => $my_user->email,
            'phone' => '',
            'role' => $my_user->roleid,
            'old_role' => $my_user->roleid,
            'status' => Utility::STATUS_ACTIVE,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner])
            ->post(route('myusersubmit'), $we_user)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_my_user_edit_submit_with_empty_role(){
        $fake_user = Utility::addAdminUser();
        //dd($fake_user->attributesToArray());
        $this->actingAs($fake_user, 'web');

        $my_user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => User::USER_TYPE_PARTNER,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s')
        ]);

//dd($my_user);
        $we_user = [
            'id' => pas_encrypt($my_user->id),
            'user_type' => $my_user->user_type,
            'fname' => $my_user->firstname,
            'lname' => $my_user->lastname,
            'email' => $my_user->email,
            'phone' => $my_user->phone,
            'role' => '',
            'old_role' => '',
            'status' => Utility::STATUS_ACTIVE,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner])
            ->post(route('myusersubmit'), $we_user)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_my_user_edit_submit_with_empty_status(){
        $fake_user = Utility::addAdminUser();
        //dd($fake_user->attributesToArray());
        $this->actingAs($fake_user, 'web');

        $my_user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => User::USER_TYPE_PARTNER,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s')
        ]);

//dd($my_user);
        $we_user = [
            'id' => pas_encrypt($my_user->id),
            'user_type' => $my_user->user_type,
            'fname' => $my_user->firstname,
            'lname' => $my_user->lastname,
            'email' => $my_user->email,
            'phone' => $my_user->phone,
            'role' => $my_user->roleid,
            'old_role' => $my_user->roleid,
            'status' => '',
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner])
            ->post(route('myusersubmit'), $we_user)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }


    public function test_my_user_edit_submit_with_photo_upload(){
        $fake_user = Utility::addAdminUser();
        //dd($fake_user->attributesToArray());
        $this->actingAs($fake_user, 'web');

        $my_user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_SUPPORT,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => User::USER_TYPE_PARTNER,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(15)->format('Y-m-d H:i:s')
        ]);

//dd($my_user);
        $we_user = [
            'id' => pas_encrypt($my_user->id),
            'user_type' => $my_user->user_type,
            'fname' => $my_user->firstname,
            'lname' => $my_user->lastname,
            'email' => $my_user->email,
            'phone' => $my_user->phone,
            'role' => $my_user->roleid,
            'old_role' => User::ROLE_ACCOUNT_MANAGER,
            'status' => $my_user->status,
            'photo' => UploadedFile::fake()->image('institute-profile-logo.jpg'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner])
            ->post(route('myusersubmit'), $we_user)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_edit_my_user_permission_form_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user_id = DB::table('pas_users')->inRandomOrder()->value('id');
        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('my-users-permission', ['id' => pas_encrypt($user_id)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_edit_my_user_permission_form_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $my_user_access = DB::table('pas_users')->select(['pas_users.id', 'pas_users.partner_id', 'roleid', 'user_type', 'access_level'])
            //->where('partner_id', '=', $partner['id'])
            ->whereNotNull('partner_id')
            ->join('pas_users_access', 'pas_users_access.user_id', '=', 'pas_users.id')
            ->get()->first();

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('id', '=', $my_user_access->partner_id)
            ->inRandomOrder()
            ->get()->first()->toArray();

        //$user_id = DB::table('pas_users')->inRandomOrder()->value('id');
        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('my-users-permission', [
                'id' => pas_encrypt($my_user_access->id)
            ]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Manage My User Permissions');
    }

    public function test_edit_my_user_fetch_access_form_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $my_user_access = DB::table('pas_users')->select(['pas_users.id', 'pas_users.partner_id', 'roleid', 'user_type', 'access_level'])
            ->whereNotNull('partner_id')
            ->join('pas_users_access', 'pas_users_access.user_id', '=', 'pas_users.id')
            ->get()->first();

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('id', '=', $my_user_access->partner_id)
            ->inRandomOrder()
            ->get()->first()->toArray();

        //$user_id = DB::table('pas_users')->inRandomOrder()->value('id');
        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('my-users-fetch-access', [
                'uid' => pas_encrypt($my_user_access->id),
                'ur' => $my_user_access->roleid,
                'ut' => $my_user_access->user_type,
                'al' => $my_user_access->access_level,
            ]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_edit_my_user_fetch_access_form_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $my_user_access = DB::table('pas_users')->select(['pas_users.id', 'pas_users.partner_id', 'roleid', 'user_type', 'access_level'])
            ->whereNotNull('partner_id')
            ->join('pas_users_access', 'pas_users_access.user_id', '=', 'pas_users.id')
            ->get()->first();

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('id', '=', $my_user_access->partner_id)
            ->inRandomOrder()
            ->get()->first()->toArray();

        //$user_id = DB::table('pas_users')->inRandomOrder()->value('id');
        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('my-users-fetch-access', [
                'uid' => pas_encrypt($my_user_access->id),
                'ur' => $my_user_access->roleid,
                'ut' => $my_user_access->user_type,
                'al' => $my_user_access->access_level,
            ]));

        $response->assertStatus(Response::HTTP_OK)->assertSee('Features and Access');
    }




    public function test_edit_my_user_permission_submit_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('mpermissionsubmit'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_edit_my_user_permission_submit_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $my_user = DB::table('pas_users')
            ->where('user_type', '=', User::USER_TYPE_MY_USER)
            //->where('roleid', '=', User::ROLE_ACCOUNT_MANAGER)
            ->inRandomOrder()
            ->get()->first();

        //$my_user_access = DB::table('pas_users_access')
        //   ->where('user_id', '=', $my_user_id)
        //    ->get()->all();


        $data['ids'] = $my_user->id;
        $data['firstname'] = $my_user->firstname;
        $data['lastname'] = $my_user->lastname;
        $data['email'] = $my_user->email;
        $data['access_level'] = $my_user->email;

        $data['feature']['STATS_ACCESS']['feature'] = 'STATS_ACCESS';
        $data['feature']['STATS_ACCESS']['parent_menu'] = 'DASHBOARD';
        $data['feature']['STATS_ACCESS']['opt']['view'] = 1;
        $data['feature']['STATS_ACCESS']['opt']['download'] = 1;
        $data['feature']['STATS_ACCESS']['opt']['add'] = 1;

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('mpermissionsubmit', $data));


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }


    public function test_edit_my_user_permission_submit_with_epty_access_level(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $my_user = DB::table('pas_users')
            ->where('user_type', '=', User::USER_TYPE_MY_USER)
            //->where('roleid', '=', User::ROLE_ACCOUNT_MANAGER)
            ->inRandomOrder()
            ->get()->first();

        //$my_user_access = DB::table('pas_users_access')
          //  ->where('user_id', '=', $my_user_id)
            //->get()->all();


        $data['ids'] = $my_user->id;
        $data['firstname'] = $my_user->firstname;
        $data['lastname'] = $my_user->lastname;
        $data['email'] = $my_user->email;
        $data['access_level'] = '';

        $data['feature']['STATS_ACCESS']['feature'] = 'STATS_ACCESS';
        $data['feature']['STATS_ACCESS']['parent_menu'] = 'DASHBOARD';
        $data['feature']['STATS_ACCESS']['opt']['view'] = 1;
        $data['feature']['STATS_ACCESS']['opt']['download'] = 1;
        $data['feature']['STATS_ACCESS']['opt']['add'] = 1;

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('mpermissionsubmit', $data));


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }



    public function test_export_pdf_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('my-users-export-pdf'));

        $response->assertSee('Access Denied')
            ->assertStatus(Response::HTTP_OK);
    }

    public function test_export_pdf_with_search_keyword(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_SUPPORT,
            'status' => Utility::STATUS_ACTIVE,
            'partner_id' => $partner['id'],
            //'partner_type' => 2,
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('my-users-export-pdf', [
                'q' => $user->firstname,
                'fname' => $user->firstname,
                'lname' => $user->lastname,
                'role' => $user->roleid,
                'status' => $user->status,
                'email' => $user->email,
                'phone' => $user->phone,
            ]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_export_pdf_with_no_record_found(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('my-users-export-pdf', [
                'q' => 'no-record-found',
            ]));

        $response->assertStatus(Response::HTTP_OK);
    }



    public function test_export_excel_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('my-users-export-excel'));

        $response->assertSee('Access Denied')
            ->assertStatus(Response::HTTP_OK);
    }

    public function test_export_excel_with_search_keyword(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_SUPPORT,
            'status' => Utility::STATUS_ACTIVE,
            'partner_id' => $partner['id'],
            //'partner_type' => 2,
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('my-users-export-excel', [
                'q' => $user->firstname,
                'fname' => $user->firstname,
                'lname' => $user->lastname,
                'role' => $user->roleid,
                'status' => $user->status,
                'email' => $user->email,
                'phone' => $user->phone,
            ]));

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function test_delete_my_user_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $my_user = DB::table('pas_users')
            ->where('user_type', '=', User::USER_TYPE_MY_USER)
            ->where('roleid', '=', User::ROLE_ACCOUNT_MANAGER)
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('my-user-delete', ['id' => pas_encrypt($my_user)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }


    public function test_delete_my_user_with_user_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $my_user = DB::table('pas_users')
            ->where('user_type', '=', User::USER_TYPE_MY_USER)
            ->where('roleid', '=', User::ROLE_ACCOUNT_MANAGER)
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('my-user-delete', ['id' => pas_encrypt($my_user)]));

        $response->assertStatus(Response::HTTP_OK);
    }
}
