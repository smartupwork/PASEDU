<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\Student;
use App\Utility;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class StudentImportAuditTest extends TestCase
{
    //use RefreshDatabase;
    //use WithoutMiddleware;

    // World Education Test Cases

    public function test_student_import_audit_listing_with_has_no_access() {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('import-audit'));
        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_student_import_audit_listing_with_has_access() {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');
        $response = $this->get(route('import-audit'));
        $response->assertSee('Student Import Audit')->assertOk();
    }

    public function test_student_import_audit_ajax()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.id', 'pas_partner.zoho_id', 'pas_partner.partner_name'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_imported_files AS if', 'if.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();


        $import_file = Db::table('pas_imported_files')
            ->where('partner_id', '=', $partner['id'])
            ->get()->first();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('imported-file-search', ['q' => $import_file->file]));

        $response->assertOk();
    }

    public function test_import_audit_template_download_with_file_not_exists(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $id = Db::table('pas_imported_files')->insertGetId([
            'file' => 'file-not-exists.xlsx',
            'date' => date('Y-m-d'),
            'partner_id' => $partner['id'],
            'added_by' => Auth::user()->id,
            'records_imported' => 2,
            'records_skiped' => 0,
            'file_size' => 200,
            'processing_time' => 200,
            'skipped_rows' => 200,
            'added_date' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('download-import-file', ['id' => pas_encrypt($id)]));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_import_audit_template_download_with_invalid_id(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('download-import-file', ['id' => pas_encrypt(0)]));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_import_audit_template_download_with_valid_file(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $import_audit = DB::table('pas_imported_files')
            ->inRandomOrder()
            ->where('file', '=', 'test.xlsx')
            ->value('id');

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('download-import-file', ['id' => pas_encrypt($import_audit)]));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_enrollment_bulk_import_popup_submit_with_empty_file_selected(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data = [
            'filename' => ''
        ];

        $response = $this->withSession(['partner_detail' => $partner])
            ->post(route('student-import-file'), $data);

        $response->assertJson(['status' => 'fail'])->assertOk();

    }

    public function test_enrollment_bulk_import_popup_submit_with_invalid_file_format(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $data = [
            'filename' => UploadedFile::fake()->image('institute-profile-logo.jpg')
        ];

        $response = $this->withSession(['partner_detail' => $partner])
            ->post(route('student-import-file'), $data);

        $response->assertJson(['status' => 'fail'])->assertOk();

    }

    public function test_enrollment_bulk_import_popup_submit(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('zoho_id', '=', '4838579000001948225')
            ->get()->first()->toArray();

        $this->uploadBulkImportStudentExcel();

        $file = new UploadedFile(
            'public/export/test_import_students.xlsx',
            'test_import_students.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $data = [
            'filename' => $file
        ];

        $response = $this->withSession(['partner_detail' => $partner])
            ->post(route('student-import-file'), $data);

        $response->assertJson(['status' => 'success'])->assertOk();

    }

    public function test_enrollment_bulk_when_lead_exists_in_zoho_import_popup_submit(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('zoho_id', '=', '4838579000001948225')
            ->get()->first()->toArray();

        $this->uploadBulkImportStudentExcel(true);

        $file = new UploadedFile(
            'public/export/test_import_students.xlsx',
            'test_import_students.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $data = [
            'filename' => $file
        ];

        $response = $this->withSession(['partner_detail' => $partner])
            ->post(route('student-import-file'), $data);

        $response->assertJson(['status' => 'success'])->assertOk();

    }

    private function uploadBulkImportStudentExcel($isLeads = false){
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'First Name');
        $sheet->setCellValue('B1', 'Last Name');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Program');
        $sheet->setCellValue('E1', 'Price Paid');
        $sheet->setCellValue('F1', 'Payment Type');
        $sheet->setCellValue('G1', 'Start Date');
        $sheet->setCellValue('H1', 'End Date');
        $sheet->setCellValue('I1', 'Phone');
        $sheet->setCellValue('J1', 'Street');
        $sheet->setCellValue('K1', 'City');
        $sheet->setCellValue('L1', 'State');
        $sheet->setCellValue('M1', 'ZIP');
        $sheet->setCellValue('N1', 'Country');

        $result = Student::getDummyData($isLeads);
        $rows = 2;
        if(count($result) > 0){
            foreach($result as $val){
                $sheet->setCellValue('A' . $rows, $val['A']);
                $sheet->setCellValue('B' . $rows, $val['B']);
                $sheet->setCellValue('C' . $rows, $val['C']);
                $sheet->setCellValue('D' . $rows, $val['D']);
                $sheet->setCellValue('E' . $rows, $val['E']);
                $sheet->setCellValue('F' . $rows, $val['F']);
                $sheet->setCellValue('G' . $rows, $val['G']);
                $sheet->setCellValue('H' . $rows, $val['H']);
                $sheet->setCellValue('I' . $rows, $val['I']);
                $sheet->setCellValue('J' . $rows, $val['J']);
                $sheet->setCellValue('K' . $rows, $val['K']);
                $sheet->setCellValue('L' . $rows, $val['L']);
                $sheet->setCellValue('M' . $rows, $val['M']);
                $sheet->setCellValue('N' . $rows, $val['N']);
                $rows++;
            }
        }

        $filename = "test_import_students.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save("public/export/".$filename);
    }

    public function test_student_import_audit_skipped_listing() {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $import_audit = DB::table('pas_imported_files')
            ->whereNotNull('partner_id')
            //->where('partner_id', '=', $partner['id'])
            ->where('file', '!=', 'test_import_students.xlsx')
            ->inRandomOrder()
            ->get()->first();

        $partner = Partner::select(['pas_partner.*'])
            ->join('pas_imported_files AS if', 'if.partner_id', '=', 'pas_partner.id')
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->where('pas_partner.id', '=', $import_audit->partner_id)
            //->inRandomOrder()
            ->get()->first()->toArray();

        $this->uploadBulkImportStudentExcel();

        /*$import_audit_id = DB::table('pas_imported_files')
            ->where('partner_id', '=', $partner['id'])
            ->where('file', '!=', 'test_import_students.xlsx')
            ->inRandomOrder()
            ->value('id');*/

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('import-skipped-record', ['list_type' => 'skipped', 'id' => pas_encrypt($import_audit->id)]));
        $response->assertOk();
//        $response->assertRedirect(route('dashboard'));
    }

    public function test_student_import_audit_skipped_with_file_not_exists() {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.id', 'pas_partner.zoho_id', 'pas_partner.partner_name'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_imported_files AS if', 'if.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $import_audit_id = Db::table('pas_imported_files')->insertGetId([
            'file' => 'file-not-exists.xlsx',
            'date' => date('Y-m-d'),
            'partner_id' => $partner['id'],
            'added_by' => Auth::user()->id,
            'records_imported' => 2,
            'records_skiped' => 0,
            'file_size' => 200,
            'processing_time' => 200,
            'skipped_rows' => 200,
            'added_date' => date('Y-m-d H:i:s'),
        ]);

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('import-skipped-record', ['list_type' => 'skipped', 'id' => pas_encrypt($import_audit_id)]));
        $response->assertRedirect(route('dashboard'));
    }

    public function test_student_import_audit_skipped_with_invalid_id() {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.id', 'pas_partner.zoho_id', 'pas_partner.partner_name'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_imported_files AS if', 'if.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('import-skipped-record', ['list_type' => 'skipped', 'id' => pas_encrypt(0)]));
        $response->assertRedirect(route('dashboard'));
    }

    public function test_student_import_audit_export_pdf_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.*'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_imported_files AS i', 'i.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('importaudit-export-pdf'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_student_import_audit_export_pdf_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.*'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_imported_files AS if', 'if.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('importaudit-export-pdf'));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_student_import_audit_export_pdf_with_no_record_found(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('importaudit-export-pdf', ['q' => 'no-record-found']));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_student_import_audit_export_excel_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('importaudit-export-excel'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_student_import_audit_export_excel_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('importaudit-export-excel'));

        $response->assertStatus(Response::HTTP_OK);
        //$this->assertFalse(preg_match('/(error|notice)/i', $response->content()) === false);

        //$export = new EmptyExport();

        //$response = ExcelFacade::download($export, 'filename.xlsx');

        //Excel::assertImported('importaudit_list.xlsx');

        //$this->assertInstanceOf(BinaryFileResponse::class, $response->getContent());
        //$this->assertEquals('attachment; filename=importaudit_list.xlsx', str_replace('"', '', $response->headers->get('Content-Disposition')));
    }

    public function test_student_import_audit_delete_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.*'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_imported_files AS if', 'if.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $import_file_id = Db::table('pas_imported_files')
            ->where('partner_id', '=', $partner['id'])
            ->value('id');

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('importaudit-delete', ['id' => pas_encrypt($import_file_id)]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_student_import_audit_delete_with_has_access(){
        $fake_user = Utility::addAdminUser();

        $this->actingAs($fake_user, 'web');

        $partner = Partner::select(['pas_partner.*'])
            ->where('pas_partner.status', '=', Utility::STATUS_ACTIVE)
            ->join('pas_imported_files AS if', 'if.partner_id', '=', 'pas_partner.id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $import_file_id = Db::table('pas_imported_files')
            ->where('partner_id', '=', $partner['id'])
            ->value('id');

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('importaudit-delete', ['id' => pas_encrypt($import_file_id)]));

        $response->assertJson([
            'status' => 'success'
        ])->assertStatus(Response::HTTP_OK);
    }
}