<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use App\Utility;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PasAdminTest extends TestCase
{
    //use RefreshDatabase;
    //use WithoutMiddleware;

    public function test_list_of_partner_user_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('partner-users'));
        $response->assertSee('Partner Users List')->assertOk();
    }

    public function test_list_of_partner_user_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');
        $response = $this->call('GET', route('partner-users'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_partner_user_listing_ajax_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get('/dashboard/partnerusers/ajax');

        $response->assertOk();
    }

    public function test_partner_user_listing_ajax_with_key_word()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get('/dashboard/partnerusers/ajax?q=test');

        $response->assertOk();
    }

    public function test_partner_user_listing_ajax_with_advance_search_active_status()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get('/dashboard/partnerusers/ajax?q=active&fname=test&lname=test&role=test&status=test&phone=test&email=test&partner=test&partner_type=test');

        $response->assertOk();
    }

    public function test_partner_user_listing_ajax_with_advance_search_locked_status()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $user = User::factory()->create([
            'user_type' => 1,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'partner_id' => DB::table('pas_partner')->inRandomOrder()->value('id'),
            'roleid' => DB::table('pas_roles')->where('role_type', '=','partner')->inRandomOrder()->value('id'),
            'partner_type' => DB::table('pas_partner_type')->inRandomOrder()->value('id'),
        ]);

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get('/dashboard/partnerusers/ajax?q=locked&fname='.$user['firstname'].'&lname='.$user['lastname'].'&role='.$user['roleid'].'&status=locked&phone='.$user['phone'].'&email='.$user['email'].'&partner='.$user['partner_id'].'&partner_type='.$user['firstname'].'');

        $response->assertOk();
    }

    public function test_partner_user_listing_ajax_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get('/dashboard/partnerusers/ajax');

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_partner_user_add_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('partner-users-add'));


        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_partner_user_add_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('partner-users-add'));


        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Partner User Entry');
    }

    public function test_add_partner_user_form_submit_with_has_no_access(){
        //DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        //DB::table('pas_users')->truncate();
        //DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $we_user = [
            'user_type' => 1,
            'firstname' => 'World',
            'lastname' => 'Education',
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => '+919876543210',
            'status' => Utility::STATUS_ACTIVE,
            'partner_id' => DB::table('pas_partner')->inRandomOrder()->value('id'),
            'role' => DB::table('pas_roles')->where('role_type', '=','partner')->inRandomOrder()->value('id'),
            'partner_type' => DB::table('pas_partner_type')->inRandomOrder()->value('id'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
                        ->post(route('partneruserssubmit'), $we_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_add_partner_user_form_submit_with_registration_account(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $we_user = [
            'user_type' => 1,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'partner_id' => DB::table('pas_partner')->inRandomOrder()->value('id'),
            'role' => User::ROLE_REGISTRATION_ACCOUNT,
            'partner_type' => DB::table('pas_partner_type')->inRandomOrder()->value('id'),
            'photo' => UploadedFile::fake()->image('institute-profile-logo.jpg'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
                        ->post(route('partneruserssubmit'), $we_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success'
            ]);

    }

    public function test_add_partner_user_form_submit_with_account_support(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $we_user = [
            'user_type' => 1,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'partner_id' => DB::table('pas_partner')->inRandomOrder()->value('id'),
            'role' => User::ROLE_ACCOUNT_SUPPORT,
            'partner_type' => DB::table('pas_partner_type')->inRandomOrder()->value('id'),
            'photo' => UploadedFile::fake()->image('institute-profile-logo.jpg'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->post(route('partneruserssubmit'), $we_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success'
            ]);

    }

    public function test_add_partner_user_form_submit_with_account_manager(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $we_user = [
            'user_type' => 1,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'partner_id' => DB::table('pas_partner')->inRandomOrder()->value('id'),
            'role' => User::ROLE_ACCOUNT_MANAGER,
            'partner_type' => DB::table('pas_partner_type')->inRandomOrder()->value('id'),
            'photo' => UploadedFile::fake()->image('institute-profile-logo.jpg'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->post(route('partneruserssubmit'), $we_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success'
            ]);

    }

    public function test_add_partner_user_form_submit_with_empty_first_name(){

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $we_user = [
            'user_type' => 1,
            'firstname' => '',
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'partner_id' => DB::table('pas_partner')->inRandomOrder()->value('id'),
            'role' => DB::table('pas_roles')->where('role_type', '=','partner')->inRandomOrder()->value('id'),
            'partner_type' => DB::table('pas_partner_type')->inRandomOrder()->value('id'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->post(route('partneruserssubmit'), $we_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);

    }

    public function test_add_partner_user_form_submit_with_empty_last_name(){

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $we_user = [
            'user_type' => 1,
            'firstname' => Factory::create()->firstName,
            'lastname' => '',
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'partner_id' => DB::table('pas_partner')->inRandomOrder()->value('id'),
            'role' => DB::table('pas_roles')->where('role_type', '=','partner')->inRandomOrder()->value('id'),
            'partner_type' => DB::table('pas_partner_type')->inRandomOrder()->value('id'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->post(route('partneruserssubmit'), $we_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);

    }

    public function test_add_partner_user_form_submit_with_empty_role(){

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $we_user = [
            'user_type' => 1,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'partner_id' => DB::table('pas_partner')->inRandomOrder()->value('id'),
            'role' => '',
            'partner_type' => DB::table('pas_partner_type')->inRandomOrder()->value('id'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->post(route('partneruserssubmit'), $we_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);

    }

    public function test_add_partner_user_form_submit_with_empty_email(){

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $we_user = [
            'user_type' => 1,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => '',
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'partner_id' => DB::table('pas_partner')->inRandomOrder()->value('id'),
            'role' => DB::table('pas_roles')->where('role_type', '=','partner')->inRandomOrder()->value('id'),
            'partner_type' => DB::table('pas_partner_type')->inRandomOrder()->value('id'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->post(route('partneruserssubmit'), $we_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);

    }

    public function test_add_partner_user_form_submit_with_invalid_email(){

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $we_user = [
            'user_type' => 1,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => 'test',
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'partner_id' => DB::table('pas_partner')->inRandomOrder()->value('id'),
            'role' => DB::table('pas_roles')->where('role_type', '=','partner')->inRandomOrder()->value('id'),
            'partner_type' => DB::table('pas_partner_type')->inRandomOrder()->value('id'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->post(route('partneruserssubmit'), $we_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);

    }

    public function test_add_partner_user_form_submit_with_empty_phone(){

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $we_user = [
            'user_type' => 1,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => '',
            'status' => Utility::STATUS_ACTIVE,
            'partner_id' => DB::table('pas_partner')->inRandomOrder()->value('id'),
            'role' => DB::table('pas_roles')->where('role_type', '=','partner')->inRandomOrder()->value('id'),
            'partner_type' => DB::table('pas_partner_type')->inRandomOrder()->value('id'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->post(route('partneruserssubmit'), $we_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);

    }

    public function test_add_partner_user_form_submit_with_empty_status(){

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $we_user = [
            'user_type' => 1,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => '',
            'partner_id' => DB::table('pas_partner')->inRandomOrder()->value('id'),
            'role' => DB::table('pas_roles')->where('role_type', '=','partner')->inRandomOrder()->value('id'),
            'partner_type' => DB::table('pas_partner_type')->inRandomOrder()->value('id'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->post(route('partneruserssubmit'), $we_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);

    }

    public function test_add_partner_user_form_submit_with_empty_partner(){

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $we_user = [
            'user_type' => 1,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => 1,
            'partner_id' => '',
            'role' => DB::table('pas_roles')->where('role_type', '=','partner')->inRandomOrder()->value('id'),
            'partner_type' => DB::table('pas_partner_type')->inRandomOrder()->value('id'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->post(route('partneruserssubmit'), $we_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);

    }

    public function test_add_partner_user_form_submit_with_empty_partner_type(){

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $we_user = [
            'user_type' => 1,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => 1,
            'partner_id' => DB::table('pas_partner')->inRandomOrder()->value('id'),
            'role' => DB::table('pas_roles')->where('role_type', '=','partner')->inRandomOrder()->value('id'),
            'partner_type' => '',
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->post(route('partneruserssubmit'), $we_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);

    }


    public function test_edit_partner_user_form_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $my_user = DB::table('pas_users')
            ->where('user_type', '=', User::USER_TYPE_PARTNER)
            //->where('roleid', '=', User::ROLE_ACCOUNT_MANAGER)
            ->get()->first();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('partner-users-edit', ['id' => pas_encrypt($my_user->id)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_edit_partner_user_form_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $my_user = DB::table('pas_users')
            ->where('user_type', '=', User::USER_TYPE_PARTNER)
            //->where('roleid', '=', User::ROLE_ACCOUNT_MANAGER)
            ->get()->first();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('partner-users-edit', ['id' => pas_encrypt($my_user->id)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_edit_partner_user_form_with_invalid_id(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('partner-users-edit', ['id' => pas_encrypt('0')]));

        $response->assertRedirect(route('partner-users'));
    }

    public function test_edit_partner_user_form_with_id_does_not_exists(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('partner-users-edit', ['id' => pas_encrypt(111111)]));

        $response->assertRedirect(route('partner-users'));
    }

    public function test_update_partner_user_submit(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $partner_user_new = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'partner_id' => $partner['id'],
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'partner_type' => DB::table('pas_partner_type')->inRandomOrder()->value('id'),
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(40)->format('Y-m-d H:i:s'),
        ]);

//dd($my_user);
        $we_user = [
            'id' => pas_encrypt($partner_user_new->id),
            'user_type' => $partner_user_new->user_type,
            'firstname' => $partner_user_new->firstname,
            'lastname' => $partner_user_new->lastname,
            'email' => $partner_user_new->email,
            'phone' => $partner_user_new->phone,
            'role' => User::ROLE_ACCOUNT_SUPPORT,
            'old_role' => $partner_user_new->roleid,
            'partner_id' => $partner_user_new->partner_id,
            'partner_type' => $partner_user_new->partner_type,
            'status' => Utility::STATUS_ACTIVE,
        ];

        $this->withSession(['partner_detail' => $partner])
            ->post(route('partneruserssubmit'), $we_user)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_add_partner_user_submit_with_duplicate_email(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $partner_user_new = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'partner_id' => $partner['id'],
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'partner_type' => DB::table('pas_partner_type')->inRandomOrder()->value('id'),
            'last_wrong_attempted_at' => Carbon::now()->subMinutes(40)->format('Y-m-d H:i:s'),
        ]);

//dd($my_user);
        $we_user = [
            'user_type' => $partner_user_new->user_type,
            'firstname' => $partner_user_new->firstname,
            'lastname' => $partner_user_new->lastname,
            'email' => $partner_user_new->email,
            'phone' => $partner_user_new->phone,
            'role' => User::ROLE_ACCOUNT_MANAGER,
            'old_role' => $partner_user_new->roleid,
            'partner_id' => $partner_user_new->partner_id,
            'partner_type' => $partner_user_new->partner_type,
            'status' => Utility::STATUS_ACTIVE,
        ];

        $this->withSession(['partner_detail' => $partner])
            ->post(route('partneruserssubmit'), $we_user)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
    }

    public function test_partner_user_permission_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_user = DB::table('pas_users')->where('user_type', '=', User::USER_TYPE_MY_USER)->get()->first();

        $response = $this->get(route('partner-permission', ['id' => pas_encrypt($partner_user->id)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_partner_user_permission_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('partner-permission', ['id' => pas_encrypt(1)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_partner_user_permission_with_invalid_id()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('partner-permission', ['id' => pas_encrypt(0)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_partner_user_fetch_permission_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_user = DB::table('pas_users')
            ->where('user_type', '=', User::USER_TYPE_MY_USER)
            ->whereNotNull('access_level')
            ->get()->first();

        $response = $this->get(route('partner-fetchaccess', [
            'uid' => pas_encrypt($partner_user->id),
            'ur' => $partner_user->roleid,
            'ut' => $partner_user->user_type,
            'al' => $partner_user->access_level,
        ]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_partner_user_fetch_permission_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('partner-fetchaccess', ['id' => pas_encrypt(1)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_export_partner_pdf_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        User::factory()->create([
            'user_type' => User::USER_TYPE_PARTNER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => User::USER_TYPE_PARTNER,
            'partner_id' => $partner['id'],
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);

        $response = $this->withSession(['partner_detail' => $partner])
            ->get('/dashboard/partnerusers/exportpdf');

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_export_pdf_with_search_keyword(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user = User::factory()->count(5)->create([
            'user_type' => User::USER_TYPE_PARTNER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => User::USER_TYPE_PARTNER,
            'partner_id' => $partner['id'],
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);

        $response = $this->withSession(['partner_detail' => $partner])
            ->get('/dashboard/partnerusers/exportpdf?q=locked&fname='.$user[0]['firstname'].'&lname='.$user[0]['lastname'].'&role='.$user[0]['role_id'].'&status=locked&phone='.$user[0]['phone'].'&email='.$user[0]['email'].'&partner='.$user[0]['partner_id'].'&partner_type='.$user[0]['firstname'].'');

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_export_partner_pdf_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user = User::factory()->count(5)->create([
            'user_type' => User::USER_TYPE_PARTNER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_ACCOUNT_MANAGER,
            'status' => rand(0,1),
            'partner_type' => User::USER_TYPE_PARTNER,
            'partner_id' => $partner['id'],
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);

        $response = $this->withSession(['partner_detail' => $partner])
            ->get('/dashboard/partnerusers/exportpdf?q='.$user[0]['firstname'].'&fname='.$user[0]['firstname'].'&lname='.$user[0]['lastname'].'&role='.$user[0]['roleid'].'&status=active&email='.$user[0]['email'].'&phone='.$user[0]['phone'].'&partner='.$user[0]['partner_id'].'&partner_type='.$user[0]['partner_type'].'');

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_export_partner_excel_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get('/dashboard/partnerusers/exportexcel');

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    /*public function test_export_excel_with_search_keyword_active(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get('/dashboard/partnerusers/exportexcel?q=active');

        $response->assertStatus(Response::HTTP_OK);
    }*/

    /*public function test_export_excel_with_search_keyword_locked(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get('/dashboard/partnerusers/exportexcel?q=locked');

        $response->assertStatus(Response::HTTP_OK);
    }*/

    public function test_export_partner_excel_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('partnerusers-export-excel'));
        /*[
            'q' => $user->firstname,
            'fname' => $user->firstname,
            'lname' => $user->lastname,
            'role' => $user->roleid,
            'status' => 1,
            'email' => $user->email,
            'phone' => $user->phone,
            'partner' => $user->partner_id,
            'partner_type' => $user->partner_type]*/

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_delete_partner_user_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $my_user = DB::table('pas_users')
            ->where('user_type', '=', User::USER_TYPE_MY_USER)
            ->where('email', '!=', 'krmp@xoomwebdevelopment.com')
            ->get()
            ->first();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('partner-users-delete', ['id' => pas_encrypt($my_user->id)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_delete_partner_user_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $my_user = User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => Factory::create()->unique()->email,
            'firstname' => Factory::create()->firstName(),
            'lastname' => Factory::create()->lastName(),
            'phone' => Factory::create()->phoneNumber,
            'roleid' => 1,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => null,
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('partner-users-delete', ['id' => pas_encrypt($my_user->id)]));

        $response->assertStatus(Response::HTTP_OK);
    }
}