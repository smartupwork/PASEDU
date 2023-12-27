<?php
namespace App\Http\Controllers\Prestashop;
use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Partner;
use App\Models\Program;
use App\Utility;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Exception;
use Session;
use Config;
use Lang;
require base_path("vendor/autoload.php");
use Cookie;

class CourseController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCourseDetailApi(Request $request)
    {
        $course = DB::table('pas_program')
            //->select(['website_short_description', 'description', 'tag_line', 'prerequisites', 'outline', 'audience', 'certification'])
            ->where('zoho_id', '=', $request->zoho_id)
            ->get()->first();

        return response()->json(['status' => true, 'data' => $course]);
    }

    public function updateShopPrograms($id_shop, $start, $limit, $category_sync)
    {
        $shop = DB::connection('we_shop')
            ->table('ps_shop AS s')
            //->join('ps_shop_url AS su', 'su.id_shop', '=', 's.id_shop')
            ->where('s.id_shop', '=', $id_shop)
            ->where('s.active', '=', 1)
            ->where('s.id_shop_group', '!=', 3)
            ->get()->first();

        if ($shop) {
            echo '<pre> -> Partner: ' . $shop->name;
            $partner = Partner::with(['priceBook', 'priceBook.programs', 'priceBook.programs.program'])
                ->where('partner_name', '=', $shop->name)
                ->first();

            if ($partner && $partner->priceBook && $partner->priceBook->programs) {
                echo '<pre> --> Price Book: ' . $partner->priceBook->name;
                $pas_programs_ids = array_column($partner->priceBook->programs->toArray(), 'program_id');
                //$price_book_programs = $partner->priceBook->programs()->limit(2)->get()->all();

                $pas_programs = Program::with(['priceBookProgram'=> function($query) use($partner) {
                        $query->where('price_book_zoho_id','=', $partner->price_book_zoho_id);
                    }])
                    ->whereIn('id', $pas_programs_ids)
                    ->where('status', '=', 'Active')
                    ->where('displayed_on', '=', 'All')
                    //->where('is_copy', '=', 1)
                    ->skip($start)
                    ->limit($limit)
                    ->get()->all();
                //echo '<pre>';print_r($pas_programs);die;

                foreach ($pas_programs as $program) {
                    echo '<pre> ----> Course Name: ' . $program->name. '('.$program->code.')';
                    $ps_products = DB::connection('we_shop')
                        ->table('ps_product AS p')
                        //->join('ps_product_lang AS pl', 'pl.id_product', '=', 'p.id_product')
                        ->where('p.reference', '=', $program->code)
                        ->whereIn('p.id_shop_default', [$shop->id_shop, 1])
                        //->where('p.active', '=', 1)
                        ->get()->all();

                    if (count($ps_products) > 0) {

                        DB::connection('we_shop')->table('ps_product')
                            ->whereIn('id_product', array_column($ps_products, 'id_product'))
                            ->update([
                                'is_featured' => $program->is_featured,
                                'is_best_seller' => $program->is_best_seller,
                                'displayed_on' => $program->displayed_on,
                                'status' => $program->status,
                                'zoho_id' => $program->zoho_id,
                            ]);

                        DB::connection('we_shop')->table('ps_product_lang')
                            ->whereIn('id_product', array_column($ps_products, 'id_product'))
                            ->update([
                                'name' => $program->name,
                                'description' => json_encode(Program::loadPrestaShopJsonData($program->toArray()), JSON_UNESCAPED_SLASHES),
                                'description_short' => !empty($program->tag_line) ? $program->tag_line : '',
                            ]);

                        foreach ($ps_products as $ps_product) {
                            echo '<pre> ---------> Price: ' . $program->priceBookProgram->program_list_price;

                            //if ($program->program_list_price > 0) {
                                DB::connection('we_shop')->table('ps_product_shop')
                                    ->where('id_product', '=', $ps_product->id_product)
                                    ->where('id_shop', '=', $ps_product->id_shop_default)
                                    ->update([
                                        'price' => $program->priceBookProgram->program_list_price,
                                        'wholesale_price' => $program->priceBookProgram->program_list_price,
                                        'date_upd' => date('Y-m-d H:i:s'),
                                    ]);

                                if($category_sync == 1){
                                    $program_obj = new Program();
                                    $program_obj->saveCategoryProduct($program->toArray(), $ps_product->id_product);
                                }


                                //$program_obj->weProductSave($pas_program, $ps_product->id_product);
                            //}
                        }

                    }else{
                        echo '<pre> ---------> Price not found';
                    }
                }

                $client = new Client();
                $response = $client->get($_ENV['PRESTASHOP_BASE_URL'].'/modules/pasapi/cache-clear.php', [
                    'headers' => [
                        //'Authorization' => 'Bearer '. $this->access_token,
                        'Accept'        => 'application/json',
                    ],
                    'query_params' => [],
                ]);
                $response->getBody();

            }else{
                echo '<pre> Partner not found in PAS';
            }

        }
    }

    public function updateAffiliatePrograms($id_shop, $start, $limit, $category_sync)
    {
        $shop = DB::connection('we_shop')
            ->table('ps_shop AS s')
            //->join('ps_shop_url AS su', 'su.id_shop', '=', 's.id_shop')
            ->where('s.id_shop', '=', $id_shop)
            ->where('s.id_shop_group', '=', 3)
            ->where('s.active', '=', 1)
            ->get()->first();
        //dd($shop);
        if ($shop) {
            echo '<pre> -> Affiliate: ' . $shop->name;
            $partner = Affiliate::with(['priceBook', 'priceBook.programs', 'priceBook.programs.program'])
                ->where('affiliate_name', '=', $shop->name)
                ->first();

            if ($partner && $partner->priceBook && $partner->priceBook->programs) {
                echo '<pre> --> Price Book: ' . $partner->priceBook->name;
                $pas_programs_ids = array_column($partner->priceBook->programs->toArray(), 'program_id');
                //$price_book_programs = $partner->priceBook->programs()->limit(2)->get()->all();

                $pas_programs = Program::with(['priceBookProgram'=> function($query) use($partner) {
                    $query->where('price_book_zoho_id','=', $partner->price_book_zoho_id);
                }])
                    ->whereIn('id', $pas_programs_ids)
                    ->where('status', '=', 'Active')
                    ->where('displayed_on', '=', 'All')
                    //->where('is_copy', '=', 1)
                    ->skip($start)
                    ->limit($limit)
                    ->get()->all();
                //echo '<pre>';print_r($pas_programs);die;

                foreach ($pas_programs as $program) {
                    echo '<pre> ----> Course Name: ' . $program->name. '('.$program->code.')';
                    $ps_products = DB::connection('we_shop')
                        ->table('ps_product AS p')
                        //->join('ps_product_lang AS pl', 'pl.id_product', '=', 'p.id_product')
                        ->where('p.reference', '=', $program->code)
                        ->where('p.id_shop_default', '=', $shop->id_shop)
                        //->where('p.active', '=', 1)
                        ->get()->all();

                    if (count($ps_products) > 0) {

                        DB::connection('we_shop')->table('ps_product')
                            ->whereIn('id_product', array_column($ps_products, 'id_product'))
                            ->update([
                                'is_featured' => $program->is_featured,
                                'is_best_seller' => $program->is_best_seller,
                                'displayed_on' => $program->displayed_on,
                                'status' => $program->status,
                                'zoho_id' => $program->zoho_id,
                            ]);

                        DB::connection('we_shop')->table('ps_product_lang')
                            ->whereIn('id_product', array_column($ps_products, 'id_product'))
                            ->update([
                                'name' => $program->name,
                                'description' => json_encode(Program::loadPrestaShopJsonData($program->toArray()), JSON_UNESCAPED_SLASHES),
                                'description_short' => !empty($program->tag_line) ? $program->tag_line : '',
                            ]);

                        foreach ($ps_products as $ps_product) {
                            echo '<pre> ---------> Price: ' . $program->priceBookProgram->program_list_price;

                            //if ($program->program_list_price > 0) {
                            DB::connection('we_shop')->table('ps_product_shop')
                                ->where('id_product', '=', $ps_product->id_product)
                                //->where('id_shop', '=', $ps_product->id_shop_default)
                                ->update([
                                    'price' => $program->priceBookProgram->program_list_price,
                                    'wholesale_price' => $program->priceBookProgram->program_list_price,
                                    'date_upd' => date('Y-m-d H:i:s'),
                                ]);

                            if($category_sync == 1){
                                $program_obj = new Program();
                                $program_obj->saveCategoryProduct($program->toArray(), $ps_product->id_product);
                            }


                            //$program_obj->weProductSave($pas_program, $ps_product->id_product);
                            //}
                        }

                    }else{
                        echo '<pre> ---------> Price not found';
                    }
                }

                $client = new Client();
                $response = $client->get($_ENV['PRESTASHOP_BASE_URL'].'/modules/pasapi/cache-clear.php', [
                    'headers' => [
                        //'Authorization' => 'Bearer '. $this->access_token,
                        'Accept'        => 'application/json',
                    ],
                    'query_params' => [],
                ]);
                $response->getBody();

            }else{
                echo '<pre> Partner not found in PAS';
            }

        }
    }


    public function addAffiliatePrograms($id_shop, $start, $limit)
    {
        /*$program_type_id = Program::getProgramType('Professional Enrichment');
        $category_id = Program::getCategoryNew('Business and Professional', $program_type_id);
        dd($category_id);
die;*/
        $shop = DB::connection('we_shop')
            ->table('ps_shop AS s')
            //->join('ps_shop_url AS su', 'su.id_shop', '=', 's.id_shop')
            ->where('s.id_shop', '=', $id_shop)
            ->where('s.id_shop_group', '=', 3)
            ->where('s.active', '=', 1)
            ->get()->first();
        //dd($shop);
        if ($shop) {
            echo '<pre> -> Affiliate: ' . $shop->name;
            $partner = Affiliate::with(['priceBook', 'priceBook.programs', 'priceBook.programs.program'])
                ->where('affiliate_name', '=', $shop->name)
                ->first();

            if ($partner && $partner->priceBook && $partner->priceBook->programs) {
                echo '<pre> --> Price Book: ' . $partner->priceBook->name;
                $pas_programs_ids = array_column($partner->priceBook->programs->toArray(), 'program_id');
                //$price_book_programs = $partner->priceBook->programs()->limit(2)->get()->all();

                $pas_programs = Program::with(['priceBookProgram'=> function($query) use($partner) {
                    $query->where('price_book_zoho_id','=', $partner->price_book_zoho_id);
                }])
                    ->whereIn('id', $pas_programs_ids)
                    ->where('status', '=', 'Active')
                    ->where('displayed_on', '=', 'All')
                    //->where('is_copy', '=', 1)
                    ->skip($start)
                    ->limit($limit)
                    ->get()->all();
                //echo '<pre>';print_r($pas_programs);die;

                DB::connection('we_shop')->beginTransaction();
                try{
                    foreach ($pas_programs as $program) {
                        echo '<pre> ----> Course Name: ' . $program->name. '('.$program->code.')';
                        $ps_product_exists = DB::connection('we_shop')
                            ->table('ps_product AS p')
                            //->join('ps_product_lang AS pl', 'pl.id_product', '=', 'p.id_product')
                            ->where('p.reference', '=', $program->code)
                            ->where('p.id_shop_default', '=', $shop->id_shop)
                            //->where('p.active', '=', 1)
                            ->count('p.id_product');

                        if ($ps_product_exists == 0) {

                            $id_product = DB::connection('we_shop')->table('ps_product')
                                ->insertGetId([
                                    'id_supplier' => 0,
                                    'id_manufacturer' => 0,
                                    'id_category_default' => 17,
                                    'id_shop_default' => $id_shop,
                                    'id_tax_rules_group' => 1,
                                    'on_sale' => 0,
                                    'online_only' => 1,
                                    /*'quantity' => 0,
                                    'minimal_quantity' => 0,*/
                                    'price' => $program->code,
                                    'wholesale_price' => $program->code,
                                    'reference' => $program->code,
                                    'redirect_type' => 404,
                                    'out_of_stock' => 2,
                                    'active' => 1,
                                    'date_add' => date('Y-m-d H:i:s'),

                                    'is_virtual' => 1,
                                    'is_featured' => $program->is_featured,
                                    'is_best_seller' => $program->is_best_seller,
                                    'displayed_on' => $program->displayed_on,
                                    'status' => $program->status,
                                    'zoho_id' => $program->zoho_id,
                                ]);

                            if(!empty($id_product)){
                                DB::connection('we_shop')->table('ps_product_lang')
                                    ->insert([
                                        'id_product' => $id_product,
                                        'id_shop' => $id_shop,
                                        'id_lang' => 1,
                                        'name' => $program->name,
                                        'link_rewrite' => Utility::slugify($program->name).' '.Utility::slugify($program->code, '-', false),
                                        'description' => json_encode(Program::loadPrestaShopJsonData($program->toArray()), JSON_UNESCAPED_SLASHES),
                                        'description_short' => !empty($program->tag_line) ? $program->tag_line : '',
                                    ]);

                                echo '<pre> ---------> Price: ' . $program->priceBookProgram->program_list_price;

                                //if ($program->program_list_price > 0) {
                                DB::connection('we_shop')->table('ps_product_shop')
                                    ->insert([
                                        'id_product' => $id_product,
                                        'id_shop' => $id_shop,
                                        'id_category_default' => 2,
                                        'id_tax_rules_group' => 1,
                                        'active' => 1,
                                        'price' => $program->priceBookProgram->program_list_price,
                                        'wholesale_price' => $program->priceBookProgram->program_list_price,
                                        'date_add' => date('Y-m-d H:i:s'),
                                    ]);

                                Program::saveCategoryProductWithoutIsExist($program->toArray(), $id_product, $id_shop);
                            }else{
                                echo '<pre> ++++++++ Product not saved.';
                            }


                        }else{
                            echo '<pre> ---------> Product already exists.';
                        }
                    }

                    DB::connection('we_shop')->commit();

                    $client = new Client();
                    $response = $client->get($_ENV['PRESTASHOP_BASE_URL'].'/modules/pasapi/cache-clear.php', [
                        'headers' => [
                            //'Authorization' => 'Bearer '. $this->access_token,
                            'Accept'        => 'application/json',
                        ],
                        'query_params' => [],
                    ]);
                    $response->getBody();
                }catch (Exception $e){
                    DB::connection('we_shop')->rollBack();
                    dd($e);
                }
            }else{
                echo '<pre> Partner not found in PAS';
            }

        }
    }

    public function updateProductUrl(){
        DB::connection('we_shop')->beginTransaction();
        try{
            $products = DB::connection('we_shop')->table('ps_product')
                //->select(['ps_product_lang.id_product', 'ps_product_lang.name', 'ps_product.reference'])
                ->join('ps_product_lang', 'ps_product_lang.id_product', '=', 'ps_product.id_product')
                ->where('id_lang', '=', 1)
                ->get()->all();

            $sql = '';
            foreach ($products as $product) {
                $sql .= "UPDATE `ps_product_lang` SET `link_rewrite` = '".Utility::slugify($product->name).'-'.Utility::slugify($product->reference, '-', false)."' WHERE `id_product` = ".$product->id_product.";";
            }
            //die($sql);

            DB::connection('we_shop')->unprepared($sql);
            DB::connection('we_shop')->commit();

            $client = new Client();
            $response = $client->get($_ENV['PRESTASHOP_BASE_URL'].'/modules/pasapi/cache-clear.php', [
                'headers' => [
                    //'Authorization' => 'Bearer '. $this->access_token,
                    'Accept'        => 'application/json',
                ],
                'query_params' => [],
            ]);
            $response->getBody();

            die('DONE');
        }catch (Exception $e){
            DB::connection('we_shop')->rollBack();
        }
    }

    public function addPrestashopProductCategory(Request $request){
        $shop_name = $request->get('shop_name');

        if(empty($shop_name)){
            die('shop_name parameter is missing');
        }

        $id_shop = DB::connection('we_shop')->table('ps_shop')
            ->where('name', '=', $shop_name)->value('id_shop');

        if(empty($id_shop)){
            die('Shop not found in prestashop');
        }

        $partner_price_book_id = DB::table('pas_partner')
            ->where('partner_name', '=', $shop_name)->value('price_book_id');

        $programs = DB::table('pas_price_book_program_map AS pm')
            ->select(['p.code', 'p.name', 'p.program_type', 'p.category'])
            ->join('pas_program AS p', 'p.id','=','pm.program_id')
            ->where('pm.price_book_id', '=', $partner_price_book_id)
            ->get()->all();

        echo '<pre>';print_r($programs);

        $product_categories = [];
        $type_and_categories = [];
        foreach ($programs as $program) {
            $product_categories[$program->code] = [$program->program_type, $program->category];
            if(!isset($type_and_categories[$program->program_type])){
                $type_and_categories[$program->program_type][] = $program->category;
            }elseif(!in_array($program->category, $type_and_categories[$program->program_type])){
                $type_and_categories[$program->program_type][] = $program->category;
            }
        }

        //dd($product_categories);

        $p_sub_category = [];
        $p_category = [];
        foreach ($type_and_categories as $program_type => $categories){
            $program_type_id = Program::getProgramType($program_type, $id_shop);
            Program::categoryShopMap($program_type_id, $id_shop);
            $p_sub_category[$program_type] = $program_type_id;
            //dump(['program_type', $program_type_id => $program_type]);
            foreach ($categories as $category) {
                $id_category_id = Program::getCategoryNew($category, $program_type_id, $id_shop);
                Program::categoryShopMap($id_category_id, $id_shop);
                $p_category[$category] = $id_category_id;
                //dump(['category', $id_category_id => $category]);
            }

        }
        //dd([$p_sub_category, $p_category]);

        if(count($programs) > 0){
            //$program_codes = array_column($programs, 'code');
            foreach ($programs as $program) {
                $id_product = DB::connection('we_shop')->table('ps_product')
                    ->where('reference', '=', $program->code)
                    ->where('id_shop_default', '=', $id_shop)
                    ->value('id_product');
                echo '<pre>';print_r([
                    'code' => $program->code,
                    'id_product' => $id_product,
                ]);

                if(!empty($id_product)){
                    if(!empty($program->program_type) && isset($p_sub_category[$program->program_type])){
                        //echo '<pre>';print_r(['sub_category' => $p_sub_category[$program->program_type]]);
                        Program::categoryProductMap($p_sub_category[$program->program_type], $id_product, 1);
                    }

                    if(!empty($program->category) && isset($p_category[$program->category])){
                        //echo '<pre>';print_r(['category' => $p_category[$program->category]]);
                        Program::categoryProductMap($p_category[$program->category], $id_product, 2);
                    }
                }else{
                    echo '<pre>';print_r(['missing' => $program->code]);
                }

            }
        }
        Program::cacheClear();
    }
}
