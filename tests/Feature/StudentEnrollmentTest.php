<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\StudentActivityProgress;
use App\Utility;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentEnrollmentTest extends TestCase
{
    //use RefreshDatabase;
    //use WithoutMiddleware;

    public function test_student_enrollment_listing_with_has_no_access() {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('student-enrollment'));
        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_student_enrollment_listing_with_has_access() {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('student-enrollment'));
        $response->assertSee('Student Dashboard')->assertOk();
    }

    public function test_student_enrollment_listing_ajax()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.*'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_enrollment AS e', 'e.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $enrollments = DB::table('pas_enrollment')
            ->where('partner_id', '=', $partner['id'])
            ->whereNotNull('program_name')
            ->whereNotNull('username')
            ->inRandomOrder()->get()->first();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('enrollment-search', [
                'q' => $enrollments->subject,
                'subject' => $enrollments->subject,
                'status' => $enrollments->status,
                'program' => $enrollments->program_name,
                'username' => $enrollments->username,
            ]));

        $response->assertOk();
    }

    public function test_export_pdf_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('enrollment-export-pdf'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_export_pdf_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.*'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_enrollment AS e', 'e.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $enrollments = DB::table('pas_enrollment')
            ->where('partner_id', '=', $partner['id'])
            ->whereNotNull('program_name')
            ->whereNotNull('username')
            ->inRandomOrder()->get()->first();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('enrollment-export-pdf', [
                'q' => $enrollments->subject,
                'subject' => $enrollments->subject,
                'status' => $enrollments->status,
                'program' => $enrollments->program_name,
                'username' => $enrollments->username,
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
            ->get(route('enrollment-export-pdf', ['no-record-found']));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_export_excel_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('enrollment-export-excel'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_export_excel_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.*'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_enrollment AS e', 'e.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $enrollments = DB::table('pas_enrollment')
            ->where('partner_id', '=', $partner['id'])
            ->whereNotNull('program_name')
            ->whereNotNull('username')
            ->inRandomOrder()->get()->first();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('enrollment-export-excel', [
                'q' => $enrollments->subject,
                'subject' => $enrollments->subject,
                'status' => $enrollments->status,
                'program' => $enrollments->program_name,
                'username' => $enrollments->username,
            ]));

        $response->assertStatus(Response::HTTP_OK);
    }

    // TODO CHANGE BECAUSE NEW REQUIREMENT

    public function test_student_activity_progress_report_popup_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('zoho_id', '=', '4838579000001948225')
            ->get()->first()->toArray();

        $enrollment_id = DB::table('pas_enrollment')
            ->where('partner_zoho_id', '=', 4838579000001948225)
            ->where('subject', '=', 'Khemraj Maurya')->value('id');

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('student-popup', [
                'enrollment_id' => pas_encrypt($enrollment_id),
                'student_name' => 'Khemraj Maurya',
                'activity_type' => 'activity-progress'
            ]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');

    }

    public function test_student_activity_progress_report_popup_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('zoho_id', '=', '4838579000001948225')
            ->get()->first()->toArray();

        $enrollment_id = DB::table('pas_enrollment')
            ->where('partner_zoho_id', '=', 4838579000001948225)
            ->where('subject', '=', 'Khemraj Maurya')->value('id');

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('student-popup', [
                'enrollment_id' => pas_encrypt($enrollment_id),
                'student_name' => 'Khemraj Maurya',
                'activity_type' => 'activity-progress'
            ]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Activity Progress Report');
    }


    public function test_student_program_progress_report_popup_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('zoho_id', '=', '4838579000001948225')
            ->get()->first()->toArray();

        $enrollment_id = DB::table('pas_enrollment')
            ->where('partner_zoho_id', '=', 4838579000001948225)
            ->where('subject', '=', 'Khemraj Maurya')->value('id');

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('student-popup', [
                'enrollment_id' => pas_encrypt($enrollment_id),
                'student_name' => 'Khemraj Maurya',
                'activity_type' => 'activity-log'
            ]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');

    }

    public function test_student_program_progress_report_popup_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('zoho_id', '=', '4838579000001948225')
            ->get()->first()->toArray();

        $enrollment_id = DB::table('pas_enrollment')
            ->where('partner_zoho_id', '=', 4838579000001948225)
            ->where('subject', '=', 'Khemraj Maurya')->value('id');

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('student-popup', [
                'enrollment_id' => pas_encrypt($enrollment_id),
                'student_name' => 'Khemraj Maurya',
                'activity_type' => 'activity-log'
            ]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Program Progress Report');
    }

    public function test_institute_progress_request_of_enrolment_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $this->withSession(['partner_detail' => Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray()]);

        $progress_request = [
            'ids' => '',
        ];

        $response = $this
            //->withSession(['partner_detail' => $partner])
            ->post(route('student-activity-report-save'), $progress_request);

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_institute_progress_request_of_enrolment_with_empty_report_type()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('zoho_id', '=', '4838579000001948225')
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        //Student::factory()->create();
        $enrollment_id = DB::table('pas_enrollment')->inRandomOrder()->value('id');
        $progress_request = [
            'activity_progress_id' => '',
            'enrollment_id' => pas_encrypt($enrollment_id),
            'activity_type' => 'activity-progress',
            'canvas_student_id' => null,
            'report_type' => '',
            'schedule_interval' => 'six-month',
            'scheduled_at' => null,
            'is_recurring' => 'on',
            'fetch_report_type' => 'date-range',
            'fetch_start_date' => '03/01/2022',
            'fetch_end_date' => '03/31/2022',
        ];

        $response = $this
            //->withSession(['partner_detail' => $partner])
            ->post(route('student-activity-report-save'), $progress_request);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
        //$response->dump();

    }

    public function test_institute_progress_request_of_enrolment_with_empty_fetch_report_type()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('zoho_id', '=', '4838579000001948225')
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        //Student::factory()->create();
        $enrollment_id = DB::table('pas_enrollment')->inRandomOrder()->value('id');
        $progress_request = [
            'activity_progress_id' => '',
            'enrollment_id' => pas_encrypt($enrollment_id),
            'activity_type' => 'activity-progress',
            'canvas_student_id' => null,
            'report_type' => '',
            'schedule_interval' => 'six-month',
            'scheduled_at' => null,
            'is_recurring' => 'on',
            'fetch_report_type' => null,
            'fetch_start_date' => null,
            'fetch_end_date' => null,
        ];

        $response = $this
            //->withSession(['partner_detail' => $partner])
            ->post(route('student-activity-report-save'), $progress_request);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
        //$response->dump();

    }

    public function test_generate_activity_progress_report_with_enrollment_not_exists()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('zoho_id', '=', '4838579000001948225')
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        $response = $this
            //->withSession(['partner_detail' => $partner])
            ->get(route('student-activity-log', ['enrollment_id' => 0]));

        $response->assertStatus(Response::HTTP_OK)->assertSee('Enrollment not found.');
        //$response->dump();

    }

    public function test_generate_activity_progress_report_with_canvas_student_not_exists()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('zoho_id', '=', '4838579000001948225')
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        //Student::factory()->create();
        $enrollment_id = DB::table('pas_enrollment')
            ->where('partner_zoho_id', '=', '4838579000000406034')
            ->where('subject', '=', 'Arnold Palmer')
            ->value('id');

        $response = $this
            //->withSession(['partner_detail' => $partner])
            ->get(route('student-activity-log', ['enrollment_id' => $enrollment_id]));

        $response->assertStatus(Response::HTTP_OK)->assertSee('Canvas user not found.');

    }

    public function test_institute_progress_request_of_enrolment_with_generate_activity_progress_report()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('zoho_id', '=', '4838579000001948225')
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        //Student::factory()->create();
        $enrollment_id = DB::table('pas_enrollment')
            ->where('partner_zoho_id', '=', '4838579000001948225')
            ->where('subject', '=', 'Khemraj Maurya')
            ->value('id');

        $response = $this
            //->withSession(['partner_detail' => $partner])
            ->get(route('student-activity-log', ['enrollment_id' => $enrollment_id]));

        $response->assertStatus(Response::HTTP_OK);
        //$response->dump();

    }

    public function test_institute_progress_request_of_enrolment_with_generate_program_progress_report()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('zoho_id', '=', '4838579000001948225')
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        //Student::factory()->create();
        $enrollment_id = DB::table('pas_enrollment')
            ->where('partner_zoho_id', '=', '4838579000001948225')
            ->where('subject', '=', 'Khemraj Maurya')
            ->value('id');

        $response = $this
            //->withSession(['partner_detail' => $partner])
            ->get(route('student-activity-progress', ['enrollment_id' => $enrollment_id]));

        $response->assertStatus(Response::HTTP_OK);
        //$response->dump();

    }

    public function test_institute_progress_request_of_enrolment_with_generate_report_date_range()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('zoho_id', '=', '4838579000001948225')
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        //Student::factory()->create();
        $enrollment_id = DB::table('pas_enrollment')->inRandomOrder()->value('id');
        $progress_request = [
            'activity_progress_id' => '',
            'enrollment_id' => pas_encrypt($enrollment_id),
            'activity_type' => 'activity-progress',
            'canvas_student_id' => 875,
            'report_type' => 'generate-report',
            'schedule_interval' => '',
            'scheduled_at' => null,
            'is_recurring' => 'on',
            'fetch_report_type' => 'date-range',
            'fetch_start_date' => '03/01/2022',
            'fetch_end_date' => '03/31/2022',
        ];

        $response = $this
            //->withSession(['partner_detail' => $partner])
            ->post(route('student-activity-report-save'), $progress_request);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
        //$response->dump();

    }

    public function test_institute_progress_request_of_enrolment_with_schedule_report_edit()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('zoho_id', '=', '4838579000001948225')
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        StudentActivityProgress::truncate();
        //Student::factory()->create();
        $activity_progress_id = DB::table('student_activity_progress')->where('partner_id', '=', 15)
            ->value('id');
        $enrollment_id = DB::table('pas_enrollment')->inRandomOrder()->value('id');
        $progress_request = [
            'activity_progress_id' => pas_encrypt($activity_progress_id),
            'enrollment_id' => pas_encrypt($enrollment_id),
            'activity_type' => 'activity-progress',
            'canvas_student_id' => null,
            'report_type' => 'generate-report',
            'schedule_interval' => '',
            'scheduled_at' => null,
            'is_recurring' => 'on',
            'fetch_report_type' => 'all',
            'fetch_start_date' => '',
            'fetch_end_date' => '',
        ];

        $response = $this
            //->withSession(['partner_detail' => $partner])
            ->post(route('student-activity-report-save'), $progress_request);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
        //$response->dump();

    }

    public function test_institute_progress_request_of_enrolment_with_schedule_interval_bi_week()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('zoho_id', '=', '4838579000001948225')
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        //Student::factory()->create();
        $enrollment_id = DB::table('pas_enrollment')->inRandomOrder()->value('id');
        $progress_request = [
            'activity_progress_id' => '',
            'enrollment_id' => pas_encrypt($enrollment_id),
            'activity_type' => 'activity-progress',
            'canvas_student_id' => null,
            'report_type' => 'schedule-report',
            'schedule_interval' => 'bi-week',
            'scheduled_at' => null,
            'is_recurring' => 'on',
            'fetch_report_type' => 'date-range',
            'fetch_start_date' => '03/01/2022',
            'fetch_end_date' => '03/31/2022',
        ];

        $response = $this
            //->withSession(['partner_detail' => $partner])
            ->post(route('student-activity-report-save'), $progress_request);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
        //$response->dump();

    }

    /*public function test_institute_progress_request_of_enrolment_with_schedule_interval_bi_week()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->where('zoho_id', '=', '4838579000001948225')
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        //Student::factory()->create();
        $enrollment_id = DB::table('pas_enrollment')->inRandomOrder()->value('id');
        $progress_request = [
            'activity_progress_id' => '',
            'enrollment_id' => pas_encrypt($enrollment_id),
            'activity_type' => 'generate-report',
            'canvas_student_id' => null,
            'report_type' => 'schedule-report',
            'schedule_interval' => 'bi-week',
            'scheduled_at' => null,
            'is_recurring' => 'on',
            'fetch_report_type' => 'date-range',
            'fetch_start_date' => '03/01/2022',
            'fetch_end_date' => '03/31/2022',
        ];

        $response = $this
            //->withSession(['partner_detail' => $partner])
            ->post(route('student-activity-report-save'), $progress_request);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'fail',
            ]);
        //$response->dump();

    }*/

}
