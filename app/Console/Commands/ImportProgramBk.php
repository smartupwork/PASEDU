<?php

namespace App\Console\Commands;

use App\Models\Program;
use App\ZohoHelper;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Prestashop;
use Protechstudio\PrestashopWebService\PrestaShopWebserviceException;

class ImportProgramBk extends Command
{
    const OFF_SET = 0;
    const LIMIT = 200;

    private $off_set = self::OFF_SET;
    private $limit = self::LIMIT;
    private $page = 1;

    private $data = [
        'insert' => [],
        'update' => [],
    ];

    private $existing_programs = [];
    private $existing_categories = [];
    private $ps_shop = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importProgram:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Partner from ZOHO server.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /*$opt['resource'] = 'categories';
        //$opt['filter[id_shop]'] = 111;
        $opt['display'] = 'full';
        $categories = Prestashop::get($opt);
        $categories_arr = json_encode($categories);
        $categories_arr = json_decode($categories_arr, true);

        foreach ($categories_arr['categories']['category'] as $item) {
            if(is_string($item['name']['language'])){
                $this->existing_categories[$item['name']['language']] = $item['id'];
            }
        }*/

        $this->existing_programs = DB::table('pas_program')->pluck('name', 'zoho_id')->toArray();

        $opt['resource'] = 'shops';
        $opt['display'] = 'full';
        $opt['filter[id_shop_group]'] = 2;
        $shops = Prestashop::get($opt);

        $shops_arr = json_encode($shops);
        $shops_arr = json_decode($shops_arr, true);

        if(isset($shops_arr['shops']['shop'])){
            $this->ps_shop = $shops_arr['shops']['shop'];
        }

        $this->getPrograms();
        $this->info('Total records Inserted('.count($this->data['insert']).') and Updated('.count($this->data['update']).').');
    }

    private function getPrograms(){
        //$before_70_min = Carbon::now('GMT-5')->subMinutes(70)->format('c');

        $zoho_programs = ZohoHelper::getInstance()->fetch('Products', ['Product_Name', 'Product_Category', 'Product_Code', 'Hours', 'Duration', 'Retail_Wholesale', 'Owner', 'Program_Type', 'Unit_Price', 'Vendor_Name', 'Program_Status', 'Displayed_On', 'Service_Item_not_program', 'Description', 'Certification_Included', 'Layout', 'Featured_Course', 'Best_Seller', 'Tag_Line', 'Prerequisites', 'Outline', 'Externship_Included', 'Approved_Offering', 'Language', 'CE_Units'], $this->page, $this->limit);
//dd($zoho_programs);
        if($zoho_programs['status'] == 'error'){
            $this->error($zoho_programs['message']);
            die;
        }

        /*$zoho_program = array_column($zoho_program, 'id', 'id');
        $our_db_program = array_column($our_db_program, 'zoho_id', 'id');
        $to_be_added = array_diff($zoho_program, $our_db_program);
        $to_be_deleted = array_diff($our_db_program, $zoho_program);
        dump([$to_be_added, $to_be_deleted]);die;*/

        //dump($zoho_programs['data']);die;
        if(count($zoho_programs['data']) > 0){
            foreach ($zoho_programs['data']['data'] as $zoho_program) {
                $duration_type = null;
                $duration_value = null;
                if(!empty($zoho_program['Duration'])){
                    $arr_duration = explode(' ', $zoho_program['Duration']);
                    if(is_array($arr_duration)){
                        if(count($arr_duration) == 2){
                            $duration_value = trim($arr_duration[0]);
                            $duration_type = strtolower(trim($arr_duration[1]));
                        }else if(count($arr_duration) == 3){
                            $duration_value = trim($arr_duration[0]);
                            $duration_type = strtolower(trim($arr_duration[2]));
                        }

                    }
                }

                $zoho_data = [
                    'name' => addslashes($zoho_program['Product_Name']),
                    'zoho_id' => $zoho_program['id'],
                    'category' => addslashes($zoho_program['Product_Category']),
                    'program_type' => addslashes($zoho_program['Program_Type']),
                    'code' => addslashes($zoho_program['Product_Code']),
                    'hours' => $zoho_program['Hours'],
                    'duration_type' => $duration_type,
                    'duration_value' => $duration_value,
                    'retail_wholesale' => addslashes($zoho_program['Retail_Wholesale']),
                    'description' => addslashes($zoho_program['Description']),
                    'service_item_not_program' => $zoho_program['Service_Item_not_program'] ? 1:0,
                    'displayed_on' => addslashes($zoho_program['Displayed_On']),
                    'unite_price' => $zoho_program['Unit_Price'],
                    'certification_included' => addslashes($zoho_program['Certification_Included']),
                    'status' => addslashes($zoho_program['Program_Status']),
                    'layout' => isset($zoho_program['Layout']['name']) ? $zoho_program['Layout']['name']:null,
                    'is_featured' => $zoho_program['Featured_Course'] ? 1:0,
                    'is_best_seller' => $zoho_program['Best_Seller'] ? 1:0,
                    'tag_line' => addslashes($zoho_program['Tag_Line']),
                    'prerequisites' => addslashes($zoho_program['Prerequisites']),
                    'outline' => addslashes($zoho_program['Outline']),
                    'externship_included' => addslashes($zoho_program['Externship_Included']),
                    'approved_offering' => json_encode($zoho_program['Approved_Offering']),
                    'language' => $zoho_program['Language'],
                    'ce_units' => $zoho_program['CE_Units'],
                ];

                if(isset($this->existing_programs[$zoho_program['id']])){
                    $zoho_data['updated_at'] = date('Y-m-d H:i:s');
                    $this->data['update'][] = $zoho_data;
                }else {
                    $zoho_data['created_at'] = date('Y-m-d H:i:s');
                    $this->data['insert'][] = $zoho_data;
                }
            }

            if ($zoho_programs['data']['info']['more_records']) {
                $this->off_set = $this->off_set == 0 ? $this->limit : $this->off_set + $this->limit;
                $this->page += 1;
                $this->getPrograms();
                ///$this->info($partner['partner_name'].' find more.');
            }else{
                //dd($this->data);
                if(count($this->data['insert']) > 0){
                    foreach ($this->data['insert'] as $zoho_program) {
                        DB::table('pas_program')->insert($zoho_program);
                        $this->savePrestaShopProduct($zoho_program);
                    }
                }
                if(count($this->data['update']) > 0){
                    foreach ($this->data['update'] as $zoho_program) {
                        DB::table('pas_program')->where([["zoho_id", '=', $zoho_program['zoho_id']]])->update($zoho_program);
                        $this->savePrestaShopProduct($zoho_program);
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

            }

        }
    }

    private function savePrestaShopProduct($zoho_product){
        try{
            $opt['resource'] = 'products';
            $opt['display'] = 'full';
            $opt['filter[reference]'] = $zoho_product['code'];
            $product = Prestashop::get($opt);

            $product_arr = json_encode($product);
            $product_arr = json_decode($product_arr, true);
            //dump($product_arr);

            $data = [
                'name'=> $zoho_product['name'],
                'description'=> json_encode($zoho_product),
                'description_short'=> $zoho_product['tag_line'],
                //'link_rewrite'=>'pas-product-2',
                'reference' => $zoho_product['code'],
                'id_category_default' => 2,
                'id_tax_rules_group' => 1,
                'minimal_quantity' => 1,
                'available_for_order' => 1,
                'is_virtual' => 1,
                'show_price' => 1,
                'state' => true,
                'price' => $zoho_product['unite_price'],
                'wholesale_price' => $zoho_product['retail_wholesale'],
                'active' => true,
                /*'associations' => [
                    'categories' => [
                        [ 'category' => ['id' => 2] ],
                        [ 'category' => ['id' => 16] ],
                        [ 'category' => ['id' => 17] ],
                        [ 'category' => ['id' => 20] ],
                    ],
                ]*/
            ];

            if( !isset($product_arr['products']['product']) ){
                $this->info('('.$zoho_product['name'].') product to be added');
                //dd($zoho_product['name']);
                $xmlSchema = Prestashop::getSchema('products'); //returns a SimpleXMLElement instance with the desired schema

                //$xmlSchema->associations->categories->category->id = 17;
                //$xmlSchema->associations->product_features->product_feature->id = 17;
                //dd($data);
                $postXml = Prestashop::fillSchema($xmlSchema, $data);
//dd($postXml);

                $opt['resource'] = 'products';
                $opt['postXml'] = $postXml->asXml();

                $xml = Prestashop::add($opt);
                if($xml){
                    $this->info('('.$zoho_product['name'].') product added successfully');
                    $product_add_arr = json_encode($xml);
                    $product_add_arr = json_decode($product_add_arr, true);
                    //dd($product_add_arr);
                    $product_shop_data = [];
                    $category_product_data = [];
                    if(count($this->ps_shop) > 0){
                        foreach ($this->ps_shop as $shop) {

                            $partner_program = DB::table('pas_partner')
                                ->select(['pas_partner.partner_name', 'pas_program.id', 'pas_program.name', 'pas_program.zoho_id', 'program_list_price'])
                                ->join('pas_price_book_program_map', 'pas_price_book_program_map.price_book_zoho_id', '=', 'pas_partner.price_book_zoho_id')
                                ->join('pas_program', 'pas_program.zoho_id', '=', 'pas_price_book_program_map.program_zoho_id')
                                ->where('pas_partner.partner_name', '=', $shop['name'])
                                ->where('pas_program.name', '=', $product_add_arr['product']['name'])
                                ->get()->first();
                            /*if($partner_program){
                                dump($partner_program);
                            }*/

                            $is_exists = DB::connection('we_shop')->table('ps_product_shop')
                                ->where('id_product', '=', $product_add_arr['product']['id'])
                                ->where('id_shop', '=', $shop['id'])
                                ->count('id_product');

                            if($is_exists == 0){
                                $product_shop_data[] = [
                                    'id_product' => $product_add_arr['product']['id'],
                                    'id_shop' => $shop['id'],
                                    'price' => $partner_program ? $partner_program->program_list_price: null,
                                    'wholesale_price' => $partner_program ? $partner_program->program_list_price: null,
                                    'redirect_type' => 404,
                                    'id_category_default' => 2,
                                    'active' => 1,
                                    'is_best_selling' => $zoho_product['is_best_seller'],
                                ];
                                //dump($product_shop_data);
                            }

                        }
                    }
                    //dump($product_shop_data);
                    if(count($product_shop_data) > 0){
                        DB::connection('we_shop')->table('ps_product_shop')
                            ->insert($product_shop_data);
                    }

                    $program_type[$shop['id']] = $this->saveCategoryProduct($shop, $zoho_product, $product_add_arr['product']);
                }
            }else{
                $this->info('('.$zoho_product['name'].') product to be updated');

                /*$data['id'] = $product_arr['products']['product']['id'];
                $data['id_shop_default'] = 1;

                $xmlSchema = Prestashop::getSchema('products'); //returns a SimpleXMLElement instance with the desired schema

                $putXml = Prestashop::fillSchema($xmlSchema, $data);

                $edit_opt['resource'] = 'products';
                $edit_opt['id'] = $product_arr['products']['product']['id'];
                $edit_opt['putXml'] = $putXml->asXml();

                $xml = Prestashop::edit($edit_opt);
                if($xml) {
                    $this->info('(' . $zoho_product['name'] . ') product updated successfully.');
                }


                $product_add_arr = json_encode($xml);
                $product_add_arr = json_decode($product_add_arr, true);*/
                //dd($product_add_arr);
                $product_shop_data = [];

                if(count($this->ps_shop) > 0){

                    //$shop_ids = array_column($this->ps_shop, 'id');
                    DB::connection('we_shop')->table('ps_product_lang')
                        ->where('id_product', $product_arr['products']['product']['id'])
                        //->whereIn('id_category', $shop_ids)
                        ->update(['name' => $data['name'], 'description' => $data['description'], 'description_short' => $data['description_short']]);

                    /*DB::connection('we_shop')->table('ps_product_shop')
                        ->where('id_product', $product_arr['products']['product']['id'])
                        //->whereIn('id_category', $shop_ids)
                        ->update(['name' => $data['name'], 'description' => $data['description'], 'description_short' => $data['description_short']]);*/


                    foreach ($this->ps_shop as $shop) {

                        $partner_program = DB::table('pas_partner')
                            ->select(['pas_partner.partner_name', 'pas_program.id', 'pas_program.name', 'pas_program.zoho_id', 'program_list_price'])
                            ->join('pas_price_book_program_map', 'pas_price_book_program_map.price_book_zoho_id', '=', 'pas_partner.price_book_zoho_id')
                            ->join('pas_program', 'pas_program.zoho_id', '=', 'pas_price_book_program_map.program_zoho_id')
                            ->where('pas_partner.partner_name', '=', $shop['name'])
                            ->where('pas_program.name', '=', $data['name'])
                            ->get()->first();
                        if($partner_program){
                            dump($partner_program);
                        }

                        $is_exists = DB::connection('we_shop')->table('ps_product_shop')
                            ->where('id_product', '=', $product_arr['products']['product']['id'])
                            ->where('id_shop', '=', $shop['id'])
                            ->count('id_product');

                        $product_shop_data = [
                            'id_product' => $product_arr['products']['product']['id'],
                            'id_shop' => $shop['id'],
                            'price' => $partner_program ? $partner_program->program_list_price: null,
                            'wholesale_price' => $partner_program ? $partner_program->program_list_price: null,
                            'redirect_type' => 404,
                            'id_category_default' => 2,
                            'active' => 1,
                            'is_best_selling' => $zoho_product['is_best_seller'],
                        ];

                        if($is_exists > 0){
                            DB::connection('we_shop')->table('ps_product_shop')
                                ->where('id_product', '=', $product_arr['products']['product']['id'])
                                ->where('id_shop', '=', $shop['id'])
                                ->update($product_shop_data);
                        }else{
                            DB::connection('we_shop')->table('ps_product_shop')
                                ->insert($product_shop_data);
                        }

                    }
                }

                $program_type[$shop['id']] = $this->saveCategoryProduct($shop, $zoho_product, $product_arr['products']['product'], false);

            }
        }catch (PrestaShopWebserviceException $e){
            echo '<pre>';print_r($e);die;
        }

    }

    private function saveCategoryProduct($shop, $zoho_product, $product_add_arr, $isNewRecord = true){

        DB::connection('we_shop')->table('ps_category_product')
            //->where('id_category', '=', [2, 16])
            ->where('id_product', '=', $product_add_arr['id'])
            ->delete();

        $category_product_data[] = [
            'id_category' => 2,
            'id_product' => $product_add_arr['id'],
            'position' => 0,
        ];

        if(!empty($zoho_product['program_type'])){
            $category_id = $this->getCategory($shop, $zoho_product['program_type'], 1);
            $count = DB::connection('we_shop')->table('ps_category_product')
                ->where('id_category', '=', $category_id)
                ->where('id_product', '=', $product_add_arr['id'])
                ->count();
            if($count == 0){
                $category_product_data[] = [
                    'id_category' => $category_id,
                    'id_product' => $product_add_arr['id'],
                    'position' => 1,
                ];
            }
        }

        if(!empty($zoho_product['category'])){
            $category_id = $this->getCategory($shop, $zoho_product['category']);
            $count = DB::connection('we_shop')->table('ps_category_product')
                ->where('id_category', '=', $category_id)
                ->where('id_product', '=', $product_add_arr['id'])
                ->count();
            if($count == 0){
                $category_product_data[] = [
                    'id_category' => $category_id,
                    'id_product' => $product_add_arr['id'],
                    'position' => 2,
                ];
            }
        }


        if ($zoho_product['is_featured']) {
            $category_product_data[] = [
                'id_category' => 16,
                'id_product' => $product_add_arr['id'],
                'position' => 3,
            ];
        }

        //if(count($category_product_data) > 0){
        DB::connection('we_shop')->table('ps_category_product')
            ->insert($category_product_data);
        //return DB::getPdo()->lastInsertId();
        //}
    }

    private function getCategory($shop, $category_name){
        //$product['program_type'];
        $is_exists = DB::connection('we_shop')->table('ps_category_lang')
            ->select(['ps_category_shop.id_category', 'ps_category_shop.id_shop', 'ps_category_lang.name'])
            ->join('ps_category_shop', 'ps_category_shop.id_category', '=', 'ps_category_lang.id_category')
            ->where('name', '=', $category_name)
            ->where('ps_category_shop.id_shop', '=', $shop['id'])
            ->orderBy('ps_category_lang.id_category')
            ->get()->first();


        /*$opt['resource'] = 'categories';
        $opt['filter[id_shop]'] = $shop['id'];
        $opt['filter[name]'] = $category_name;
        $opt['display'] = 'full';
        $categories = Prestashop::get($opt);
        $categories_arr = json_encode($categories);
        $categories_arr = json_decode($categories_arr, true);

        if(isset($categories_arr['categories']['category'][0]['name']['language'])){
            return $categories_arr['categories']['category'][0]['name']['language'];
        }*/

        if($is_exists){
            return $is_exists->id_category;
        }else{
            $data = [
                'name'=> $category_name,
                'active' => true,
                'id_parent' => 0,
                'id_shop_default' => 1,
                'link_rewrite' => Str::slug($category_name),
                'is_root_category' => 0,
            ];

            $xmlSchema = Prestashop::getSchema('categories');

            $postXml = Prestashop::fillSchema($xmlSchema, $data, true);


            $opt['resource'] = 'categories';
            $opt['postXml'] = $postXml->asXml();

            $xml = Prestashop::add($opt);
            if($xml){
                $category_add_arr = json_encode($xml);
                $category_add_arr = json_decode($category_add_arr, true);
                //dd($category_add_arr);
                if(isset($category_add_arr['category']['id'])){
                    return $category_add_arr['category']['id'];
                }
            }
            return null;

        }

    }
}
