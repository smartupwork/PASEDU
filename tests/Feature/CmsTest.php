<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use App\Utility;
use Database\Factories\UserFactory;
use Faker\Factory;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\InvalidArgumentException;
use Tests\TestCase;

class CmsTest extends TestCase
{
    //use RefreshDatabase;
    //use WithoutMiddleware;

    public function test_we_templates_landing_page_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
                        ->withSession(['partner_detail' => $partner])
                        ->get(route('we-templates'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_we_templates_landing_page_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('we-templates'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('We Templates');
    }

    public function test_course_marketing_listing_page_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('course-marketing'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_course_marketing_listing_page_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('course-marketing'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Course Marketing Material');
    }

    public function test_course_marketing_category_listing_page_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('course-marketing-category', ['course_marketing' => 'baking-and-pastry']));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_course_marketing_category_listing_page_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('course-marketing-category', ['course_marketing' => 'baking-and-pastry']));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Baking and Pastry');
    }

    public function test_funding_source_listing_page_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('funding-sources'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_funding_source_listing_page_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('funding-sources'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Funding Source');
    }

    public function test_social_media_listing_page_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('social-media'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_social_media_listing_page_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('social-media'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Social Media');
    }


    public function test_add_partner_announcement_news_with_user_has_access(){
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        //DB::table('pas_marketing_partner_map')->truncate();
        DB::table('pas_marketing')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $data['market_type'] = 'news';
        $data['news'][] = [
            'title' => 'Did you know?',
            'marketing_type' => 'news',
            'slug' => 'dyk',
            'description' => 'Unit Test description for did you know',
            'status' => 1,
            'old_status' => 1,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
                        ->withSession(['partner_detail' => $partner])
                        ->post(route('marketing-store'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_update_partner_announcement_news(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $news = DB::table('pas_marketing')->where('marketing_type', '=', 'news')->get()->first();

        $data['market_type'] = 'news';
        $data['news'][] = [
            'id' => $news->id,
            'title' => 'Did you know?',
            'marketing_type' => 'news',
            'slug' => 'dyk',
            'description' => 'Unit Test description for did you know '.rand(000, 999),
            'status' => 1,
            'old_status' => 1,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('marketing-store'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_add_partner_announcement(){

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $data['market_type'] = 'announcements';
        $data['announcements'][] = [
            'title' => 'WE is now HECVAT Certified',
            'marketing_type' => 'announcements',
            'slug' => 'dyk',
            'description' => 'Unit Test description for announcement',
            'status' => 1,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('marketing-store'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_update_partner_announcement(){

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $announcement = DB::table('pas_marketing')
            ->where('marketing_type', '=', 'announcements')
            ->get()->first();

        $data['market_type'] = 'announcements';
        $data['announcements'][] = [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'marketing_type' => 'announcements',
            'slug' => $announcement->slug,
            'description' => $announcement->description.' '.rand(111,999),
            'status' => 1,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('marketing-store'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_add_partner_announcement_updates(){

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $data['market_type'] = 'updates';
        $data['updates'][] = [
            'title' => 'New Products',
            'marketing_type' => 'updates',
            'slug' => 'dyk',
            'description' => 'Unit Test description for New Products',
            'status' => 1,
            'old_status' => 1,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('marketing-store'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_update_partner_announcement_updates(){

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $updates = DB::table('pas_marketing')
            ->where('marketing_type', '=', 'updates')
            ->get()->first();

        $data['market_type'] = 'updates';
        $data['updates'][] = [
            'id' => $updates->id,
            'title' => $updates->title,
            'marketing_type' => 'updates',
            'slug' => $updates->slug,
            'description' => 'Unit Test description for New Products'.time(),
            'status' => 1,
            'old_status' => 1,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('marketing-store'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_marketing_form_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('marketing-form'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_marketing_form_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('marketing-form'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Partner Announcements');
    }

    public function test_news_listing()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');
        $response = $this->call('GET', route('announcement', ['announce_type' => 'news']));

        $response->assertSee('News')->assertSee('News')->assertOk();
    }

    public function test_news_listing_if_user_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();

        $this->actingAs($fake_user, 'web');
        $response = $this->call('GET', route('announcement', ['announce_type' => 'news']));

        $response
            //->assertSee('News')
            ->assertStatus(Response::HTTP_FORBIDDEN);


        //$this->assertResponseOk();
    }

    public function test_announcement_listing_if_user_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();

        $this->actingAs($fake_user, 'web');
        $response = $this->call('GET', route('announcement', ['announce_type' => 'announcements']));

        $response
            //->assertSee('News')
            ->assertStatus(Response::HTTP_FORBIDDEN);


        //$this->assertResponseOk();
    }

    public function test_updates_listing_if_user_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();

        $this->actingAs($fake_user, 'web');
        $response = $this->call('GET', route('announcement', ['announce_type' => 'updates']));

        $response
            //->assertSee('News')
            ->assertStatus(Response::HTTP_FORBIDDEN);


        //$this->assertResponseOk();
    }


    /*public function test_announcement_listing()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('announcement', ['announce_type' => 'announcement']));
        $response->assertSee('Announcements')->assertStatus(200);
    }

    public function test_updates_listing()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('announcement', ['announce_type' => 'updates']));
        $response->assertSee('Updates')->assertStatus(200);
    }*/

    public function test_delete_partner_announcement_news(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $data['market_type'] = 'news';
        $data['news'][] = [
            'id' => DB::table('pas_marketing')->where('marketing_type', '=', 'news')->value('id'),
            'description' => '',
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('marketing-store'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_delete_partner_announcement(){

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $announcement = DB::table('pas_marketing')
            ->where('marketing_type', '=', 'announcements')
            ->get()->first();

        $data['market_type'] = 'announcements';
        $data['announcements'][] = [
            'id' => $announcement->id,
            'title' => '',
            'marketing_type' => 'announcements',
            'slug' => $announcement->slug,
            'description' => '',
            'status' => 1,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('marketing-store'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_delete_partner_announcement_updates(){

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $updates = DB::table('pas_marketing')
            ->where('marketing_type', '=', 'updates')
            ->get()->first();

        $data['market_type'] = 'updates';
        $data['updates'][] = [
            'id' => $updates->id,
            'title' => $updates->title,
            'marketing_type' => 'updates',
            'slug' => $updates->slug,
            'description' => '',
            'status' => 1,
            'old_status' => 1,
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('marketing-store'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
    }

    public function test_add_we_template_course_marketing_with_user_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('store-template'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }


    public function test_add_we_template_course_marketing_without_category_selection(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        //Storage::fake('public/images');
        $data = [
            'category' => '',
            'media_file' => UploadedFile::fake()->image('avatar.jpg'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('store-template'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);

    }

    public function test_add_we_template_course_marketing_without_template_selection(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        //Storage::fake('public/images');
        $data = [
            'category' => 'Test',
            'media_file' => '',
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('store-template'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);

    }

    public function test_add_we_template_course_marketing_with_user_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $marketing_category = Db::table('pas_marketing_category')->where('category_type', '=', 'course-marketing')->inRandomOrder()->value('slug');

        $parent_category = Db::table('pas_marketing_category')->where('slug', '=', $marketing_category)->inRandomOrder()->value('id');

        //Storage::fake('public/images');
        $data = [
            'category' => $parent_category,
            'media_file' => UploadedFile::fake()->image('avatar.jpg'),
        ];

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('store-template'), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
                'msg' => 'Template uploaded successfully.'
            ]);

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
