<?php

namespace Tests\Feature;

use App\Models\LoginActivity;
use App\Models\Partner;
use App\Models\User;
use App\Models\WrongLogin;
use App\Utility;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    //use RefreshDatabase;

    public function test_catalog_listing_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('catalog-listing'));

        $response->assertSee('Access Denied')->assertStatus(200);
    }

    public function test_catalog_listing_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('catalog-listing'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Catalog Management');
    }

    public function test_catalog_ajax_listing_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('catalog-search'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_catalog_ajax_listing_with_has_access()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->whereNotNull('price_book_id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        $catalog = DB::table('pas_program')->where([
            ['status', '=', 'Active'],
            ['displayed_on', '=', 'All'],
            ['service_item_not_program', '=', 0],
        ])->get()->first();

        $response = $this
            ->withSession(['partner_detail' => $partner])
            ->get(route('catalog-search', [
                'q' => $catalog->name,
                'program_name' => $catalog->name,
                'course_code' => $catalog->code,
                'status' => $catalog->status,
                'program_type' => $catalog->program_type,
                'certification_included' => $catalog->certification_included,
            ]));

        $response->assertOk();
    }

    public function test_add_program_into_price_book_with_has_no_access()
    {
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::whereNotNull('price_book_id')
            ->inRandomOrder()
            ->get()->first()->toArray();

        if($partner){
            $available_programs = DB::table('pas_price_book')
                ->select(['pas_price_book_program_map.program_id'])
                ->leftJoin('pas_price_book_program_map', 'pas_price_book.id', '!=', 'pas_price_book_program_map.price_book_id')
                ->where('price_book_id', '=', $partner['price_book_id'])
                ->get()->all();

            $partner_programs = array_column($available_programs, 'program_id');
            //dump($partner_programs);
            $program = Db::table('pas_program')->inRandomOrder()->whereNotIn('id', $partner_programs)->first();
            if($program){
                $data['id'] = pas_encrypt($program->id);
                $data['zoho_id'] = $program->zoho_id;
                $data['action'] = 'add';
                $data['list_price'] = $program->unite_price;
                //dump($data);

                $response = $this->withSession(['partner_detail' => $partner])
                    ->post('/catalog/change-status', $data);
                $response->assertStatus(Response::HTTP_OK);
                //$response->dump();
            }else{
                $response = $this->withSession(['partner_detail' => $partner])
                    ->post('/catalog/change-status', []);

                $response->assertStatus(Response::HTTP_OK)
                    ->assertSee('Access Denied');
            }
        }
    }

    public function test_add_program_into_price_book_with_has_price_book()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::whereNotNull('price_book_id')
            ->where('zoho_id', '=', '4838579000001948225')
            //->inRandomOrder()
            ->get()->first();

        if($partner){
            /*$available_programs = DB::table('pas_price_book')
                ->select(['pas_price_book_program_map.program_id'])
                ->leftJoin('pas_price_book_program_map', 'pas_price_book.id', '!=', 'pas_price_book_program_map.price_book_id')
                ->where('price_book_id', '=', $partner['price_book_id'])
                ->get()->all();

            $partner_programs = array_column($available_programs, 'program_id');*/
            //dump($partner_programs);
            $program = Db::table('pas_program')
                //->inRandomOrder()
                ->where('zoho_id', '=', '4838579000000569087')
                //->whereNotIn('id', $partner_programs)
                ->first();
            if($program){
                $data['id'] = pas_encrypt($program->id);
                $data['zoho_id'] = $program->zoho_id;
                $data['action'] = 'add';
                $data['list_price'] = $program->unite_price;

                $response = $this->withSession(['partner_detail' => $partner->toArray()])
                    ->post('/catalog/change-status', $data);
                $response->assertStatus(Response::HTTP_OK);
                //$response->dump();
            } else {
                $response = $this->withSession(['partner_detail' => $partner->toArray()])
                    ->post('/catalog/change-status', []);
                $response->assertStatus(Response::HTTP_OK);
            }
        }

    }

    public function test_add_program_into_price_book_with_partner_sub_account_not_into_canvas()
    {
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::whereNotNull('price_book_id')
            ->where('zoho_id', '=', '4838579000001948267')
            //->inRandomOrder()
            ->get()->first();

        if($partner){
            //dump($partner_programs);
            $program = Db::table('pas_program')
                ->where('zoho_id', '=', '4838579000000569086')
                ->first();
            if($program){
                $data['id'] = pas_encrypt($program->id);
                $data['zoho_id'] = $program->zoho_id;
                $data['action'] = 'add';
                $data['list_price'] = $program->unite_price;

                $response = $this->withSession(['partner_detail' => $partner->toArray()])
                    ->post(route('catalog-change-status'), $data);
                $response->assertStatus(Response::HTTP_OK);
                //$response->dump();
            } else {
                $response = $this->withSession(['partner_detail' => $partner->toArray()])
                    ->post('/catalog/change-status', []);
                $response->assertStatus(Response::HTTP_OK);
            }
        }

    }

    public function test_add_program_into_price_book_with_has_no_price_book()
    {
        $partner = Partner::whereNotNull('price_book_id')
            ->where('zoho_id', '=', '4838579000001948225')
            //->inRandomOrder()
            ->get()->first();

        if($partner){
            $available_programs = DB::table('pas_price_book')
                ->select(['pas_price_book_program_map.program_id'])
                ->leftJoin('pas_price_book_program_map', 'pas_price_book.id', '!=', 'pas_price_book_program_map.price_book_id')
                ->where('price_book_id', '=', $partner->price_book_id)
                ->get()->all();

            $partner_programs = array_column($available_programs, 'program_id');
            //dump($partner_programs);
            $program = Db::table('pas_program')->inRandomOrder()->whereNotIn('id', $partner_programs)->first();
            if($program){
                $data['id'] = pas_encrypt($program->id);
                $data['zoho_id'] = $program->zoho_id;
                $data['action'] = 'add';
                $data['list_price'] = $program->unite_price;
                //dump($data);

                $fake_user = Utility::addAdminUser();
                $this->actingAs($fake_user, 'web');

                $response = $this->withSession(['partner_detail' => $partner->toArray()])
                    ->post('/catalog/change-status', $data);
                $response->assertStatus(Response::HTTP_OK);
                //$response->dump();
            }
        }

    }

    public function test_remove_program_into_price_book()
    {

        /*$available_program = DB::table('pas_price_book_program_map')
            ->select([DB::raw('pas_partner.id AS partner_id'), 'pas_program.id', 'pas_program.zoho_id', 'pas_program.unite_price'])
            ->leftJoin('pas_partner', 'pas_partner.price_book_id', '=', 'pas_partner.price_book_id')
            ->leftJoin('pas_program', 'pas_price_book_program_map.program_id', '=', 'pas_program.id')
            ->where('pas_partner.zoho_id', '=', 4838579000001948225)
            ->where('pas_program.zoho_id', '=', 4838579000000569087)
            //->inRandomOrder()
            ->get()->first();*/

        //if($available_program){
        $partner = Partner::whereNotNull('price_book_id')
            ->where('zoho_id', '=', '4838579000001948225')
            //->inRandomOrder()
            ->get()->first()->toArray();

            $data['id'] = pas_encrypt(9);
            $data['zoho_id'] = "4838579000000569087";
            $data['action'] = 'remove';
            $data['list_price'] = 1595;

            $fake_user = Utility::addAdminUser();
            $this->actingAs($fake_user, 'web');

            $response = $this->withSession(['partner_detail' => $partner])
                ->post('/catalog/change-status', $data);
            $response->assertStatus(Response::HTTP_OK);
            //$response->dump();
        //}
    }


    public function test_export_pdf_catalog_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('catalog-export-pdf'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_export_pdf_catalog_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->whereNotNull('price_book_id')
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('catalog-export-pdf'));

        $response->assertStatus(Response::HTTP_OK);
        //$response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_pdf_catalog_with_has_no_record_found(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('catalog-export-pdf', ['q' => 'no-record-found']));

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_export_excel_catalog_with_has_no_access(){
        $fake_user = Utility::addUserWithoutAnyAccess();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('catalog-export-excel'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertSee('Access Denied');
    }

    public function test_export_excel_catalog_with_has_access(){
        $fake_user = Utility::addAdminUser();
        $this->actingAs($fake_user, 'web');

        $partner = Partner::where('status', '=', Utility::STATUS_ACTIVE)
            ->inRandomOrder()
            ->whereNotNull('price_book_id')
            ->get()->first()->toArray();

        $response = $this->withSession(['partner_detail' => $partner])
            ->get(route('catalog-export-excel'));

        $response->assertStatus(Response::HTTP_OK);
        //Excel::assertStored('catalog_lists.xlsx', 'public');
    }

}
