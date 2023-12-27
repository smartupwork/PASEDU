<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\Roles;
use App\Models\User;
use App\Utility;
use Faker\Factory;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PartnerProfileTest extends TestCase
{
    //use RefreshDatabase;
    //use WithoutMiddleware;

    public function test_my_profile_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('my-profile'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');

    }

    public function test_my_profile_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('my-profile'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('My Profile');

    }

    public function test_edit_my_we_profile_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('edit-profile'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');

    }

    public function test_edit_my_we_profile_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('edit-profile'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('My Profile');

    }

    public function test_my_we_profile_submit_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data = [
            'ids' => $fake_user->id,
            'photo' => UploadedFile::fake()->image('avatar.jpg'),
            'firstname' => $fake_user->firstname,
            'lastname' => $fake_user->lastname,
            'email' => $fake_user->email,
            'phone' => $fake_user->phone,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('edit-profile-submit'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');

    }

    public function test_my_we_profile_submit_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data = [
            'ids' => $fake_user->id,
            'photo' => UploadedFile::fake()->image('avatar.jpg'),
            'firstname' => $fake_user->firstname,
            'lastname' => $fake_user->lastname,
            'email' => $fake_user->email,
            'phone' => $fake_user->phone,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('edit-profile-submit'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'success'
            ]);

    }

    public function test_my_we_profile_submit_with_empty_phone(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data = [
            'ids' => $fake_user->id,
            'photo' => UploadedFile::fake()->image('avatar.jpg'),
            'firstname' => $fake_user->firstname,
            'lastname' => $fake_user->lastname,
            'email' => $fake_user->email,
            'phone' => '',
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('edit-profile-submit'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'fail'
            ]);

    }

    public function test_my_profile_submit_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data = [
            'ids' => $fake_user->id,
            'photo' => UploadedFile::fake()->image('avatar.jpg'),
            'firstname' => $fake_user->firstname,
            'lastname' => $fake_user->lastname,
            'email' => $fake_user->email,
            'phone' => $fake_user->phone,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('profilesubmit'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');

    }

    public function test_my_profile_submit_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data = [
            'ids' => $fake_user->id,
            'photo' => UploadedFile::fake()->image('avatar.jpg'),
            'firstname' => $fake_user->firstname,
            'lastname' => $fake_user->lastname,
            'email' => $fake_user->email,
            'phone' => $fake_user->phone,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('profilesubmit'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'success'
            ]);

    }

    public function test_my_profile_submit_with_empty_photo(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data = [
            'ids' => $fake_user->id,
            'photo' => '',
            'firstname' => $fake_user->firstname,
            'lastname' => $fake_user->lastname,
            'email' => $fake_user->email,
            'phone' => $fake_user->phone,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('profilesubmit'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'fail'
            ]);

    }

    public function test_institute_profile_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('institute-profile'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('My Institution Profile');

    }

    public function test_institute_profile_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('institute-profile'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');

    }

    public function test_my_institute_profile_logo_update_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('update-institute-logo'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_my_institute_profile_logo_update_with_empty_image(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        //Storage::fake('public/images');
        $data = [
            'display_name' => $partner['partner_name'],
            'logo' => '',
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('update-institute-logo'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);

    }

    public function test_my_institute_profile_logo_update_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        //Storage::fake('public/images');
        $data = [
            'display_name' => $partner['partner_name'],
            'logo' => UploadedFile::fake()->image('institute-profile-logo.jpg'),
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('update-institute-logo'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);

    }

    public function test_my_profile_institute_contact_information_update_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data = [
            'contact_name' => Factory::create()->name,
            'title' => Factory::create()->title,
            'phone' => Factory::create()->phoneNumber,
            'email' => Factory::create()->safeEmail,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('update-institute-contact'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }


    public function test_my_profile_institute_contact_information_update_with_validation_error(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data = [
            'contact_name' => '',
            'title' => '',
            'phone' => '',
            'email' => '',
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('update-institute-contact'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'fail',
            ]);
    }


    public function test_my_profile_institute_contact_information_update_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data = [
            'contact_name' => Factory::create()->name,
            'title' => Factory::create()->title,
            'phone' => Factory::create()->phoneNumber,
            'email' => Factory::create()->safeEmail,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('update-institute-contact'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'success',
            ]);
    }

    public function test_my_profile_institute_address_information_update_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data = [
            'street' => Factory::create()->address,
            'city' => Factory::create()->city,
            'state' => Factory::create()->state,
            'zip_code' => Factory::create()->postcode,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('update-institute-address'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_my_profile_institute_address_information_update_with_error_validation(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data = [
            'street' => '',
            'city' => '',
            'state' => '',
            'zip_code' => '',
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('update-institute-address'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'fail',
            ]);
    }

    public function test_my_profile_institute_address_information_update_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data = [
            'street' => Factory::create()->address,
            'city' => Factory::create()->city,
            'state' => Factory::create()->state,
            'zip_code' => Factory::create()->postcode,
        ];

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('update-institute-address'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'success',
            ]);
    }

}
