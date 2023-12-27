<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Partner;
use App\Utility;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MyInstituteRequestTest extends TestCase
{

//    public function test_institute_progress_request_of_student_with_has_no_access()
//    {
//        $fake_user = Utility::addUserWithoutAnyAccess();
//        $this->actingAs($fake_user, 'web');
//
//        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
//            ->where('zoho_id', '=', 4838579000001948225)
//            ->get()->first()->toArray();
//
//        $this->withSession(['partner_detail' => $partner]);
//
//        $progress_request = [
//            'ids' => pas_encrypt(1),
//            'typical' => 'Yes',
//            'occurrence' => 'Monthly',
//        ];
//        $response = $this->post(route('student-request-store'), $progress_request);
//
//        $response->assertStatus(Response::HTTP_OK)
//            ->assertSee('Access Denied');
//        //$response->dump();
//    }
//
//    public function test_institute_progress_request_of_student_with_has_access()
//    {
//        /*DB::statement('SET FOREIGN_KEY_CHECKS=0;');
//        DB::table('pas_student')->truncate();
//        DB::table('pas_marketing_collateral')->truncate();
//        DB::table('student_progress_report')->truncate();
//        DB::statement('SET FOREIGN_KEY_CHECKS=1;');*/
//
//        $fake_user = Utility::addAdminUser();
//        $this->actingAs($fake_user, 'web');
//
//        $enrollment = Enrollment::whereNotNull('partner_id')
//            ->inRandomOrder()
//            ->get()->first();
//
//        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
//            ->where('id', '=', $enrollment->partner_id)
//            ->get()->first()->toArray();
//
//        $this->withSession(['partner_detail' => $partner]);
//
//        /*$enrollment = Enrollment::where('partner_id', '=', $partner['id'])
//            ->inRandomOrder()
//            ->get()->first();*/
//
//        $progress_request = [];
//        if($enrollment){
//            $progress_request = [
//                'ids' => pas_encrypt($enrollment->id),
//                'typical' => 'Yes',
//                'request_type' => 1,
//                'occurrence' => 'Monthly',
//            ];
//        }
//
//        $response = $this
//            //->withSession(['partner_detail' => $partner])
//            ->post(route('student-request-store'), $progress_request);
//
//        $response->assertStatus(Response::HTTP_OK)
//            ->assertJson([
//                "status" => 'success',
//            ]);
//        //$response->dump();
//
//    }

    public function test_institute_request_status_update()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        $student_pro_repo = Db::table('student_progress_report')->inRandomOrder()
            //->where('partner_id', '=', $partner['id'])
            ->get()->first();

        $status_update = [];
        for($i = 1; $i <= 3; $i++) {
            $status_update['ids'][] = $student_pro_repo->id;
            $status_update['old_status'][] = $student_pro_repo->status;
            $status_update['status'][] = $i;
        }

        $response = $this
            //->withSession(['partner_detail' => $partner])
            ->post(route('myinstitution-update'), $status_update);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
        //$response->dump();

    }

    /*public function test_institute_request_status_cancelled_update()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        $student_pro_repo = Db::table('student_progress_report')->inRandomOrder()->get()->first();
        $status_update = [];
        foreach ($student_pro_repo as $request) {
            $status_update['ids'][] = $request->id;
            $status_update['old_status'][] = $request->status;
            $status_update['status'][] = 2;
        }

        $response = $this
            //->withSession(['partner_detail' => $partner])
            ->post(route('myinstitution-update'), $status_update);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
        //$response->dump();

    }

    public function test_institute_request_status_completed_update()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        $student_pro_repo = Db::table('student_progress_report')->inRandomOrder()->get()->first();
        $status_update = [];
        foreach ($student_pro_repo as $request) {
            $status_update['ids'][] = $request->id;
            $status_update['old_status'][] = $request->status;
            $status_update['status'][] = 3;
        }

        $response = $this
            //->withSession(['partner_detail' => $partner])
            ->post(route('myinstitution-update'), $status_update);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
        //$response->dump();

    }*/

    public function test_institute_request_status_not_selected_update()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $this->withSession(['partner_detail' => $partner]);

        $student_pro_repo = Db::table('student_progress_report')->inRandomOrder()->get()->first();
        $status_update = [];

        for($i = 1; $i <= 3; $i++) {
            $status_update['ids'][] = $student_pro_repo->id;
            $status_update['old_status'][] = $student_pro_repo->status;
            $status_update['status'][] = $i;
        }

        $response = $this
            //->withSession(['partner_detail' => $partner])
            ->post(route('myinstitution-update'), $status_update);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                "status" => 'success',
            ]);
        //$response->dump();

    }

    public function test_my_institute_request_listing_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $response = $this->get(route('myinstitution'));
        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_my_institute_request_listing_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('myinstitution'));
        $response->assertSee('My Institution Request')->assertOk();
    }

    public function test_student_enrollment_listing_ajax_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('myinstitution-search'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_student_enrollment_listing_ajax_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.id', 'pas_partner.zoho_id', 'pas_partner.partner_name'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_student AS s', 's.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('myinstitution-search', [
                'q' => 'Student',
            ]));

        $response->assertOk();
    }


    public function test_export_pdf_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.*'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_student AS s', 's.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('myinstitution-export-pdf'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_export_pdf_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.*'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_student AS s', 's.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('myinstitution-export-pdf'));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_export_pdf_with_has_no_record_found(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('myinstitution-export-pdf', ['q' => 'no-record-found']));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_export_excel_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.*'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_student AS s', 's.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('myinstitution-export-excel', ['q' => 'Student']));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_export_excel_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.*'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_student AS s', 's.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('myinstitution-export-excel'));

        $response->assertStatus(Response::HTTP_OK);
    }

}
