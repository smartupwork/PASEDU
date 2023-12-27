<?php

namespace Tests\Feature;

use App\Models\EmailTemplates;
use App\Models\Partner;
use App\Models\Roles;
use App\Models\User;
use App\Users;
use App\Utility;
use Faker\Factory;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WeUserTest extends TestCase
{
    //use RefreshDatabase;
    //use WithoutMiddleware;

    // World Education Test Cases

    public function test_listing_world_education_user_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('we-users'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_listing_world_education_user_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('we-users'));

        $response->assertSee('WE Users List')->assertOk();
    }

    public function test_add_world_education_user_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('we-users-add'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_add_world_education_user_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('we-users-add'));


        $response->assertSee('WE User Entry')->assertOk();

    }

    public function test_add_world_education_user_submit_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner_user = [
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'role' => DB::table('pas_roles')->where([['role_type', '=', Roles::ROLE_TYPE_USER], ['status', '=', Utility::STATUS_ACTIVE]])->inRandomOrder()->value('id'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('weuserssubmit'), $partner_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_add_world_education_user_submit_with_empty_first_name(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_user = [
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => '',
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'role' => User::ROLE_ACCOUNT_MANAGER,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('weuserssubmit'), $partner_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(["status" => 'fail',]);
    }

    public function test_add_world_education_user_submit_with_empty_last_name(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_user = [
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName,
            'lastname' => '',
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'role' => User::ROLE_ACCOUNT_MANAGER,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('weuserssubmit'), $partner_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(["status" => 'fail',]);
    }

    public function test_add_world_education_user_submit_with_empty_role(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_user = [
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'role' => '',
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('weuserssubmit'), $partner_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(["status" => 'fail',]);
    }

    public function test_add_world_education_user_submit_with_empty_email(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_user = [
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => '',
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'role' => User::ROLE_ACCOUNT_MANAGER,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('weuserssubmit'), $partner_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(["status" => 'fail',]);
    }

    public function test_add_world_education_user_submit_with_invalid_email(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_user = [
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => 'invalid-email',
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'role' => User::ROLE_ACCOUNT_MANAGER,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('weuserssubmit'), $partner_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(["status" => 'fail',]);
    }

    public function test_add_world_education_user_submit_with_empty_phone(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_user = [
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => '',
            'status' => Utility::STATUS_ACTIVE,
            'role' => User::ROLE_ACCOUNT_MANAGER,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('weuserssubmit'), $partner_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(["status" => 'fail',]);
    }

    public function test_add_world_education_user_submit_with_empty_status(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_user = [
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => '',
            'role' => User::ROLE_ACCOUNT_MANAGER,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('weuserssubmit'), $partner_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(["status" => 'fail',]);
    }

    public function test_add_world_education_user_submit_with_image_upload(){
        /*DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('pas_users_access')->truncate();
        DB::table('pas_users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');*/

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_user = [
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'role' => User::ROLE_ACCOUNT_MANAGER,
            'photo' => UploadedFile::fake()->image('we-user-profile-pic.jpg'),
            'last_wrong_attempted_at' => date('Y-m-d H:i:s'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('weuserssubmit'), $partner_user);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(["status" => 'success']);
    }

    public function test_add_world_education_user_submit_with_duplicate_email(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $user_email = DB::table('pas_users')
            ->where('user_type', '=', User::USER_TYPE_WE_USER)
            //->whereNotNull('last_wrong_attempted_at')
            ->value('email');

        $partner_user = [
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => $user_email,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'role' => User::ROLE_ACCOUNT_MANAGER
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('weuserssubmit'), $partner_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(["status" => 'fail']);

    }

    public function test_add_world_education_user_submit_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner_user = [
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'role' => User::ROLE_ACCOUNT_MANAGER,
            'last_wrong_attempted_at' => date('Y-m-d H:i:s'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('weuserssubmit'), $partner_user);


        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);

    }

    public function test_edit_we_user_form_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $we_user = User::factory()->create([
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('we-users-edit', ['id' => pas_encrypt($we_user->id)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_edit_we_user_form_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $we_user = User::factory()->create([
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('we-users-edit', ['id' => pas_encrypt($we_user->id)]));

        $response->assertSee('WE User Entry')->assertStatus(Response::HTTP_OK);
    }

    public function test_edit_we_user_form_with_invalid_id(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('we-users-edit', ['id' => pas_encrypt(1111111)]));

        $response->assertRedirect(route('we-users'));
    }

    public function test_edit_we_user_form_with_record_not_exists(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('we-users-edit', ['id' => pas_encrypt(111177777777)]));

        $response->assertRedirect(route('we-users'));
    }

    public function test_we_user_edit_submit_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        //dd($fake_user->attributesToArray());
        $this->actingAs($fake_user, 'web');

        $my_user = User::factory()->create([
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);

//dd($my_user);
        $we_user = [
            'id' => pas_encrypt($my_user->id),
            'user_type' => $my_user->user_type,
            'firstname' => $my_user->firstname,
            'lastname' => $my_user->lastname,
            'email' => $my_user->email,
            'phone' => $my_user->phone,
            'role' => $my_user->roleid,
            'old_role' => $my_user->roleid,
            'status' => Utility::STATUS_ACTIVE,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->post(route('weuserssubmit'), $we_user);

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');

    }

    public function test_we_user_edit_submit_with_has_access(){
        $fake_user = Utility::addAdminUser();
        //dd($fake_user->attributesToArray());
        $this->actingAs($fake_user, 'web');

        $my_user = User::factory()->create([
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'last_wrong_attempted_at' => date('Y-m-d H:i:s'),
        ]);

//dd($my_user);
        $we_user = [
            'id' => pas_encrypt($my_user->id),
            'user_type' => $my_user->user_type,
            'firstname' => $my_user->firstname,
            'lastname' => $my_user->lastname,
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
            ->post(route('weuserssubmit'), $we_user)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_world_education_user_listing_ajax_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');
        $response = $this->get('/dashboard/weusers/ajax');

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }


    public function test_world_education_user_listing_ajax_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get('/dashboard/weusers/ajax');

        $response->assertOk();
    }

    public function test_world_education_user_listing_ajax_with_key_active_word()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        User::factory()->create([
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName.' test',
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'roleid' => User::ROLE_MANAGER,
        ]);

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get('/dashboard/weusers/ajax?q=active');

        $response->assertOk();
    }

    public function test_world_education_user_listing_ajax_with_locked_key_word()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        User::factory()->create([
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName.' test',
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'roleid' => User::ROLE_MANAGER,
        ]);

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get('/dashboard/weusers/ajax?q=locked');

        $response->assertOk();
    }

    public function test_world_education_user_listing_ajax_with_advance_search_active_status()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $user = User::factory()->create([
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_ACTIVE,
            'roleid' => User::ROLE_MANAGER,
            'last_active' => date('Y-m-d H:i:s'),
        ]);

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get('/dashboard/weusers/ajax?fname='.$user['firstname'].'&lname='.$user['lastname'].'&role='.$user['roleid'].'&status=active&phone='.$user['phone'].'&email='.$user['email'].'');

        $response->assertOk();
    }

    public function test_world_education_user_listing_ajax_with_advance_search_locked_status()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $user = User::factory()->create([
            'user_type' => User::USER_TYPE_WE_USER,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'email' => Factory::create()->unique()->safeEmail,
            'phone' => Factory::create()->phoneNumber,
            'status' => Utility::STATUS_LOCKED,
            'roleid' => User::ROLE_MANAGER,
            'last_active' => date('Y-m-d H:i:s'),
        ]);

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get('/dashboard/weusers/ajax?fname='.$user['firstname'].'&lname='.$user['lastname'].'&role='.$user['roleid'].'&status=locked&phone='.$user['phone'].'&email='.$user['email'].'');

        $response->assertOk();
    }

    public function test_export_pdf_we_users_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get('/dashboard/weusers/exportpdf');

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_export_pdf_we_users_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user = User::factory()->count(2)->create([
            'user_type' => User::USER_TYPE_WE_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);

        $response = $this->withSession(['partner_detail' => $partner])
            ->get('/dashboard/weusers/exportpdf?q=active&fname='.$user[0]['firstname'].'&lname='.$user[0]['lastname'].'&role='.$user[0]['roleid'].'&status='.Utility::STATUS_ACTIVE.'&phone='.$user[0]['phone'].'&email='.$user[0]['email'].'');

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_export_pdf_we_users_with_locked_status(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user = User::factory()->count(2)->create([
            'user_type' => User::USER_TYPE_WE_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_MANAGER,
            'status' => Utility::STATUS_LOCKED,
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);

        $response = $this->withSession(['partner_detail' => $partner])
            ->get('/dashboard/weusers/exportpdf?q=locked&fname='.$user[0]['firstname'].'&lname='.$user[0]['lastname'].'&role='.$user[0]['roleid'].'&status='.Utility::STATUS_LOCKED.'&phone='.$user[0]['phone'].'&email='.$user[0]['email'].'');

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_export_pdf_we_users_with_active_status(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user = User::factory()->count(2)->create([
            'user_type' => User::USER_TYPE_WE_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);

        $response = $this->withSession(['partner_detail' => $partner])
            ->get('/dashboard/weusers/exportpdf?q=active&fname='.$user[0]['firstname'].'&lname='.$user[0]['lastname'].'&role='.$user[0]['roleid'].'&status='.Utility::STATUS_ACTIVE.'&phone='.$user[0]['phone'].'&email='.$user[0]['email'].'');

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_export_pdf_we_users_with_no_record_found(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get('/dashboard/weusers/exportpdf?q=no-record');

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_export_excel_we_users_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get('/dashboard/weusers/exportexcel');

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_export_excel_we_users_with_active_status(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user = User::factory()->create([
            'user_type' => User::USER_TYPE_WE_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_MANAGER,
            'status' => Utility::STATUS_ACTIVE,
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);


        $response = $this->withSession(['partner_detail' => $partner])
            ->get('/dashboard/weusers/exportexcel?q=active&fname='.$user->firstname.'&lname='.$user->lastname.'&role='.$user->roleid.'&status='.Utility::STATUS_ACTIVE.'&phone='.$user->phone.'&email='.$user->email.'');

        $response->assertSee('WE User Entry')->assertStatus(Response::HTTP_OK);
    }


    public function test_export_excel_we_users_with_locked_status(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $user = User::factory()->count(2)->create([
            'user_type' => User::USER_TYPE_WE_USER,
            'email' => Factory::create()->unique()->safeEmail,
            'firstname' => Factory::create()->firstName,
            'lastname' => Factory::create()->lastName,
            'phone' => Factory::create()->phoneNumber,
            'roleid' => User::ROLE_MANAGER,
            'status' => Utility::STATUS_LOCKED,
            'partner_type' => User::USER_TYPE_PARTNER,
            'partner_id' => $partner['id'],
            'first_login' => 1,
            'password' => md5('Info@12345'),
        ]);


        $response = $this->withSession(['partner_detail' => $partner])
            ->get('/dashboard/weusers/exportexcel?q=locked&fname='.$user[0]['firstname'].'&lname='.$user[0]['lastname'].'&role='.$user[0]['roleid'].'&status='.Utility::STATUS_LOCKED.'&phone='.$user[0]['phone'].'&email='.$user[0]['email'].'');

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_export_excel_we_users_with_no_record_found(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get('/dashboard/weusers/exportexcel?q=no-record');

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_delete_my_user_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $my_user = DB::table('pas_users')
            ->where('user_type', '=', User::USER_TYPE_WE_USER)
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('we-users-delete', ['id' => pas_encrypt($my_user)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_delete_my_user_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $my_user = DB::table('pas_users')
            ->where('user_type', '=', User::USER_TYPE_WE_USER)
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('we-users-delete', ['id' => pas_encrypt($my_user)]));

        $response->assertStatus(Response::HTTP_OK);
    }
}