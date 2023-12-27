<?php

namespace App\Console\Commands;

use App\ZohoHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Prestashop;

class ImportProduct extends Command
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
    protected $signature = 'importProduct:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Programs from ZOHO server.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $opt['resource'] = 'categories';
        //$opt['filter[id_shop]'] = 111;
        $opt['display'] = 'full';
        $categories = Prestashop::get($opt);
        $categories_arr = json_encode($categories);
        $categories_arr = json_decode($categories_arr, true);

        foreach ($categories_arr['categories']['category'] as $item) {
            if(is_string($item['name']['language'])){
                $this->existing_categories[$item['name']['language']] = $item['id'];
            }
        }

        //echo '<pre>';print_r($this->existing_categories);die;

        $this->existing_programs = DB::table('pas_product')->pluck('name', 'zoho_id')->toArray();

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

        $zoho_programs = ZohoHelper::getInstance()->fetch('ProductsNew', ['Name', 'Category', 'Code', 'Hours', 'Duration', 'Wholesale', 'Owner', 'Type1', 'Vendor', 'Status', 'Displayed_On', 'Description', 'Tag_Line', 'Certification', 'Featured_Course', 'Best_Seller', 'SRP', 'Level', 'Language', 'Prerequisites', 'Outline', 'Optional_Externship_Included', 'Eligible_Funding'], $this->page, $this->limit);
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
                    'name' => addslashes($zoho_program['Name']),
                    'zoho_id' => $zoho_program['id'],
                    'category' => addslashes($zoho_program['Category']),
                    'program_type' => count($zoho_program['Type1']) > 0 ? json_encode($zoho_program['Type1']):null,
                    'code' => addslashes($zoho_program['Code']),
                    'hours' => $zoho_program['Hours'],
                    'duration_type' => $duration_type,
                    'duration_value' => $duration_value,
                    'wholesale' => addslashes($zoho_program['Wholesale']),
                    'description' => addslashes($zoho_program['Tag_Line']),
                    //'service_item_not_program' => $zoho_program['Service_Item_not_program'] ? 1:0,
                    'displayed_on' => addslashes($zoho_program['Displayed_On']),
                    'unite_price' => $zoho_program['SRP'],
                    'certification' => addslashes($zoho_program['Certification']),
                    'level' => addslashes($zoho_program['Level']),
                    'language' => addslashes($zoho_program['Language']),
                    'prerequisites' => addslashes($zoho_program['Prerequisites']),
                    'outline' => addslashes($zoho_program['Outline']),
                    'optional_externship_included' => addslashes($zoho_program['Optional_Externship_Included']),
                    'eligible_funding' => json_encode($zoho_program['Eligible_Funding']),
                    'status' => addslashes($zoho_program['Status']),
                    //'layout' => isset($zoho_program['Layout']['name']) ? $zoho_program['Layout']['name']:null,
                    'is_featured' => $zoho_program['Featured_Course'] ? 1:0,
                    'is_best_seller' => $zoho_program['Best_Seller'] ? 1:0,
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
                        DB::table('pas_product')->insert($zoho_program);
                        $last_id = DB::getPdo()->lastInsertId();
                        $this->data['insert']['id'] = $last_id;
                        $this->createPrestaShopProduct($zoho_program);
                    }
                }
                if(count($this->data['update']) > 0){
                    foreach ($this->data['update'] as $zoho_program) {
                        DB::table('pas_product')->where([["zoho_id", '=', $zoho_program['zoho_id']]])->update($zoho_program);
                        //if($zoho_program['program_type']){
                            $this->createPrestaShopProduct($zoho_program);
                        //}

                    }
                }
            }

        }
    }

    private function createPrestaShopProduct($zoho_product){

        $opt['resource'] = 'products';
        $opt['display'] = 'full';
        $opt['filter[reference]'] = $zoho_product['code'];
        $product = Prestashop::get($opt);

        $product_arr = json_encode($product);
        $product_arr = json_decode($product_arr, true);
        //dd($product_arr);

        if( !isset($product_arr['products']['product']) ){
            $xmlSchema = Prestashop::getSchema('products'); //returns a SimpleXMLElement instance with the desired schema

            $data = [
                'name'=> $zoho_product['name'],
                'description'=> json_encode($zoho_product),
                'description_short'=> $zoho_product['description'],
                //'link_rewrite'=>'pas-product-2',
                'reference' => $zoho_product['code'],
                'id_category_default' => 2,
                'id_tax_rules_group' => 1,
                'minimal_quantity' => 1,
                'available_for_order' => 1,
                'is_virtual' => 1,
                'show_price' => 1,
                'state' => true,
                'price' => 200,
                'active' => true,
            ];
//dump($data);
            //$xmlSchema->associations->categories->category->id = 2;
            //$xmlSchema->associations->categories->category->id = 17;
            //$xmlSchema->associations->product_features->product_feature->id = 17;

            $postXml = Prestashop::fillSchema($xmlSchema, $data);
//dd($postXml);
            $opt['resource'] = 'products';
            $opt['postXml'] = $postXml->asXml();

            $xml = Prestashop::add($opt);
            if($xml){
                $this->info('Prestashop product inserted');
                $product_arr = json_encode($xml);
                $product_arr = json_decode($product_arr, true);
                //dd($product_arr);
                $product_shop_data = [];
                $category_product_data = [];
                if(count($this->ps_shop) > 0){
                    foreach ($this->ps_shop as $shop) {
                        $product_shop_data[] = [
                            'id_product' => $product_arr['product']['id'],
                            'id_shop' => $shop['id'],
                            'price' => $zoho_product['unite_price'],
                            'wholesale_price' => $zoho_product['wholesale'],
                            'redirect_type' => 404,
                            'id_category_default' => 2,
                            'active' => 1,
                            'is_best_selling' => $zoho_product['is_best_seller'],
                        ];
                    }
                }
                //dump($product_shop_data);
                if(count($product_shop_data) > 0){
                    DB::connection('we_shop')->table('ps_product_shop')
                        ->insert($product_shop_data);
                }

                $category_product_data[] = [
                    'id_category' => 2,
                    'id_product' => $product_arr['product']['id'],
                    'position' => 0,
                ];

                $counter = 1;
                if(!empty($zoho_product['program_type'])){
                    $cat_arr = json_decode($zoho_product['program_type'], true);

                    if(count($cat_arr) > 0){ foreach ($cat_arr as $index => $cat){
                        if(isset($this->existing_categories[$cat])){
                            $category_product_data[] = [
                                'id_category' => $this->existing_categories[$cat],
                                'id_product' => $product_arr['product']['id'],
                                'position' => $counter,
                            ];
                            $counter++;
                        }
                    }}
                }

                if($zoho_product['is_featured']){
                    $category_product_data[] = [
                        'id_category' => 16,
                        'id_product' => $product_arr['product']['id'],
                        'position' => $counter,
                    ];
                }

                if(count($category_product_data) > 0){
                    DB::connection('we_shop')->table('ps_category_product')
                        ->insert($category_product_data);
                }
            }
        }
    }
}
