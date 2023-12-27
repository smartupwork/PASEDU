<?php

namespace Tests\Feature;

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

class StudentManagementTest extends TestCase
{
    //use RefreshDatabase;
    //use WithoutMiddleware;

    public function test_enrollment_add_submit_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        //$students = [];
        for($i = 0; $i < 1; $i++){
            //$student = Student::factory()->create();
            $program = Program::select(['id', 'unite_price'])->inRandomOrder()->get()->first()->toArray();
            $state_and_country = State::select(['id', 'country_id'])->inRandomOrder()->get()->first()->toArray();

            $students['student'][] = [
                'first_name' => Factory::create()->firstName,
                'last_name' => Factory::create()->firstName,
                'email' => Factory::create()->unique()->safeEmail,
                'phone' => Factory::create()->phoneNumber,
                'program_id' => $program['id'],
                //'payment_amount' => $program['unite_price'],
                'payment_type' => Student::getPaymentType(rand(1,18)),
                'start_date' => Carbon::now()->addMonths(2)->format('m/d/Y'),
                'end_date' => Carbon::now()->addMonths(4)->format('m/d/Y'),
                'street' => null,
                'city' => null,
                'state' => $state_and_country['id'],
                'country' => $state_and_country['country_id'],
                'zip' => null
            ];

            $response = $this
                ->post(route('student-store'), $students);

            $response->assertStatus(Response::HTTP_OK)
                ->assertSee('Access Denied');
            //$response->dump();

        }
    }

    public function test_enrollment_add_submit_with_has_access()
    {
        /*DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('pas_student')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');*/

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        $students = [];
        for($i = 0; $i < 1; $i++){
            //$student = Student::factory()->create();
            $program = Program::select(['id', 'unite_price'])->whereNotNull('duration_type')
                ->whereNotNull('duration_value')->inRandomOrder()->get()->first()->toArray();

            $state_and_country = State::select(['id', 'country_id'])->inRandomOrder()->get()->first()->toArray();

            $students['student'][] = [
                'first_name' => Factory::create()->firstName,
                'last_name' => Factory::create()->firstName,
                'email' => Factory::create()->unique()->safeEmail,
                'phone' => Factory::create()->phoneNumber,
                'program_id' => $program['id'],
                //'payment_amount' => $program['unite_price'],
                'price_paid' => $program['unite_price'],
                'payment_type' => Student::getPaymentType(rand(1,18)),
                'start_date' => Carbon::now()->addMonths(2)->format('m/d/Y'),
                'end_date' => null,
                'street' => null,
                'city' => null,
                'state' => $state_and_country['id'],
                'country' => $state_and_country['country_id'],
                'zip' => null
            ];

            $response = $this
                ->post(route('student-store'), $students);

            $response->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    "status" => 'success',
                    'msg' => 'Data added successfully.'
                ]);
            //$response->dump();

        }
    }

    public function test_enrollment_add_submit_when_lead_exists_in_zoho_with_has_access()
    {
        /*DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('pas_student')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');*/

        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        $students = [];
        for($i = 0; $i < 1; $i++){
            //$student = Student::factory()->create();
            $program = Program::select(['id', 'unite_price'])->inRandomOrder()->get()->first()->toArray();
            $state_and_country = State::select(['id', 'country_id'])->inRandomOrder()->get()->first()->toArray();

            $students['student'][] = [
                'first_name' => Factory::create()->firstName,
                'last_name' => Factory::create()->lastName,
                'email' => 'example@gmail.com',
                'phone' => Factory::create()->phoneNumber,
                'program_id' => $program['id'],
                //'payment_amount' => $program['unite_price'],
                'price_paid' => $program['unite_price'],
                'payment_type' => Student::getPaymentType(rand(1,18)),
                'start_date' => Carbon::now()->addMonths(2)->format('m/d/Y'),
                'end_date' => Carbon::now()->addMonths(4)->format('m/d/Y'),
                'street' => null,
                'city' => null,
                'state' => $state_and_country['id'],
                'country' => $state_and_country['country_id'],
                'zip' => null
            ];

            $response = $this
                ->post(route('student-store'), $students);

            $response->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    "status" => 'success',
                    'msg' => 'Data added successfully.'
                ]);
            //$response->dump();

        }
    }


    public function test_enrollment_add_submit_with_validation_error()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        $students = [];
        for($i = 0; $i < 1; $i++){
            //$student = Student::factory()->create();
            $students['student'][] = [
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'phone' => '',
                'program_id' => '',
                'price_paid' => '',
                //'payment_amount' => $program['unite_price'],
                'payment_type' => '',
                'start_date' => '',
                'end_date' => '',
                'street' => null,
                'city' => null,
                'state' => '',
                'country' => '',
                'zip' => null
            ];

            $response = $this
                ->post(route('student-store'), $students);

            $response->assertStatus(Response::HTTP_OK)
                ->assertJson([
                    "status" => 'fail',
                ]);
            //$response->dump();

        }
    }

    public function test_enrollment_add_submit_with_duplicate_email_validation_error()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        $students = [];
        $program = Program::select(['id', 'unite_price'])->inRandomOrder()->get()->first()->toArray();
        $state_and_country = State::select(['id', 'country_id'])->inRandomOrder()->get()->first()->toArray();

        $email = Factory::create()->unique()->safeEmail;

        for($i = 0; $i < 2; $i++){
            //$student = Student::factory()->create();

            $students['student'][] = [
                'first_name' => Factory::create()->firstName,
                'last_name' => Factory::create()->lastName,
                'email' => $email,
                'phone' => Factory::create()->phoneNumber,
                'program_id' => $program['id'],
                'price_paid' => '200',
                //'payment_amount' => $program['unite_price'],
                'payment_type' => Student::getPaymentType(rand(1,18)),
                'start_date' => Carbon::now()->addMonths(2)->format('m/d/Y'),
                'end_date' => Carbon::now()->addMonths(4)->format('m/d/Y'),
                'street' => null,
                'city' => null,
                'state' => $state_and_country['id'],
                'country' => $state_and_country['country_id'],
                'zip' => null,
                'duplicate_allow' => 0
            ];
        }

        $response = $this
            ->post(route('student-store'), $students);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
        //$response->dump();

    }

    public function test_student_listing_ajax_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('student-search'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_student_listing_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('student-list'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_student_listing_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('student-list'));
        $response->assertSee('Student Management')->assertOk();
    }

    public function test_student_listing_ajax()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select('pas_partner.id', DB::raw('AES_DECRYPT(first_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS first_name'), DB::raw('AES_DECRYPT(last_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS last_name'), DB::raw('AES_DECRYPT(s.email, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS email'), DB::raw('AES_DECRYPT(s.phone, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS phone'), 's.payment_type', Db::raw('DATE_FORMAT(s.start_date, "'.Utility::DEFAULT_DATE_FORMAT_MYSQL.'") AS start_date'), 's.payment_amount', 'attachment', 'pas_program.name AS program_name')
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_student AS s', 'partner_id', '=', 'pas_partner.id')
            ->leftJoin('pas_program', function($join){
                $join->on('s.program_id', '=', 'pas_program.id');
            })
            ->inRandomOrder()
            ->get()->first()->toArray();

        //$students = Student::factory()->count(2)->create();
        //dd(['q' => $partner['first_name'], 'email' => $partner['email']]);
        //dd(route('student-search', ['q' => $partner['first_name'], 'email' => $partner['email']]));
        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('student-search', [
                'q' => $partner['first_name'],
                'email' => $partner['email'],
                'fname' => $partner['first_name'],
                'lname' => $partner['last_name'],
                'program' => $partner['program_name'],
                'sdate' => $partner['start_date'],
                'type' => $partner['payment_type'],
                'sort_column' => 'first_name',
                'sort_order' => 'asc',
            ]));

        $response->assertOk();
    }

    public function test_student_listing_ajax_load_more_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('student-load-more'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_student_listing_ajax_load_more_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select('pas_partner.id', DB::raw('AES_DECRYPT(first_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS first_name'), DB::raw('AES_DECRYPT(last_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS last_name'), DB::raw('AES_DECRYPT(s.email, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS email'), DB::raw('AES_DECRYPT(s.phone, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS phone'), 's.payment_type', Db::raw('DATE_FORMAT(s.start_date, "'.Utility::DEFAULT_DATE_FORMAT_MYSQL.'") AS start_date'), 's.payment_amount', 'attachment', 'pas_program.name AS program_name')
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_student AS s', 'partner_id', '=', 'pas_partner.id')
            ->leftJoin('pas_program', function($join){
                $join->on('s.program_id', '=', 'pas_program.id');
            })
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('student-load-more', [
                'sort_column' => 'first_name',
                'sort_order' => 'asc',
            ]));

        $response->assertOk();
    }

    public function test_enrollment_add_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('student-add'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');

    }

    public function test_enrollment_add_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('student-add'));

        $response->assertSee('Student Enrollment')->assertOk();

    }

    public function test_template_download_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('student-template-file'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_template_download_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('student-template-file'));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_enrollment_detail_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $student = DB::table('pas_student')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('student-detail', ['id' => pas_encrypt($student)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_enrollment_detail_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $student = DB::table('pas_student')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('student-detail', ['id' => pas_encrypt($student)]));

        $response->assertSee('Student Enrollment')
            ->assertStatus(Response::HTTP_OK);
    }

    public function test_export_pdf_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('student-export-pdf'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_export_pdf_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select('pas_partner.id', DB::raw('AES_DECRYPT(first_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS first_name'), DB::raw('AES_DECRYPT(last_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS last_name'), DB::raw('AES_DECRYPT(s.email, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS email'), DB::raw('AES_DECRYPT(s.phone, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS phone'), 's.payment_type', Db::raw('DATE_FORMAT(s.start_date, "'.Utility::DEFAULT_DATE_FORMAT_MYSQL.'") AS start_date'), 's.payment_amount', 'attachment', 'pas_program.name AS program_name')
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_student AS s', 'partner_id', '=', 'pas_partner.id')
            ->leftJoin('pas_program', function($join){
                $join->on('s.program_id', '=', 'pas_program.id');
            })
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('student-export-pdf', [
                'sort_column' => 's.start_date',
                'sort_order' => 'desc',
                'q' => $partner['first_name'],
                'email' => $partner['email'],
                'fname' => $partner['first_name'],
                'lname' => $partner['last_name'],
                'program' => $partner['program_name'],
                'sdate' => $partner['start_date'],
                'type' => $partner['payment_type'],
            ]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_export_pdf_with_no_record_found(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select('pas_partner.id', DB::raw('AES_DECRYPT(first_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS first_name'), DB::raw('AES_DECRYPT(last_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS last_name'), DB::raw('AES_DECRYPT(s.email, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS email'), DB::raw('AES_DECRYPT(s.phone, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS phone'), 's.payment_type', Db::raw('DATE_FORMAT(s.start_date, "'.Utility::DEFAULT_DATE_FORMAT_MYSQL.'") AS start_date'), 's.payment_amount', 'attachment', 'pas_program.name AS program_name')
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_student AS s', 'partner_id', '=', 'pas_partner.id')
            ->leftJoin('pas_program', function($join){
                $join->on('s.program_id', '=', 'pas_program.id');
            })
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('student-export-pdf', [
                'q' => 'no-record-found',
                'sort_column' => 's.start_date',
                'sort_order' => 'desc',
            ]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_export_excel_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner]);

        $response->get(route('student-export-excel'))
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_export_excel_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $student = DB::table('pas_student')->inRandomOrder()->first();
        $partner = Partner::where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->where('id', '=', $student->partner_id)
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('student-export-excel', [
                'sort_column' => 's.start_date',
                'sort_order' => 'desc',
                /*'q' => $partner['first_name'],
                'email' => $partner['email'],
                'fname' => $partner['first_name'],
                'lname' => $partner['last_name'],
                'program' => $partner['program_name'],
                'sdate' => $partner['start_date'],
                'type' => $partner['payment_type'],*/
            ]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_map_my_student_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select('pas_partner.*')
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_schedule AS s', 'partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('map-my-student'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_map_my_student_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select('pas_partner.*')
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_schedule AS s', 'partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('map-my-student'));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_set_partner_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->post(route('set-partner'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_set_partner_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->post(route('set-partner', ['partner' => $partner['zoho_id']]));

        $response->assertJson(['status' => true])->assertOk();
    }

    public function test_enrollment_delete(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $my_user = DB::table('pas_student')
            ->value('id');

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('student-delete', ['id' => pas_encrypt($my_user)]));

        $response->assertStatus(Response::HTTP_OK);
    }

}
