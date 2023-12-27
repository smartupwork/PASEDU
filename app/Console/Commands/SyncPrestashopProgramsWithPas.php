<?php

namespace App\Console\Commands;

use App\EmailHelper;
use App\EmailRequest;
use App\Models\PriceBook;
use App\Models\Program;
use App\Utility;
use App\ZohoHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Matrix\Exception;

class SyncPrestashopProgramsWithPas extends Command
{

    const OFF_SET = 0;
    const LIMIT = 200;

    private $price_book_partners = [];
    private $ps_shops = [];
    private $partner_syncing = [];

    private $ps_product = [
        'insert' => [],
        'update' => [],
        'delete' => [],
    ];

    private $ps_product_shop = [
        'insert' => [],
        'update' => [],
        'delete' => [],
    ];

    private $user_log = [
        'insert' => [],
        'update' => [],
        'delete' => [],
    ];


    private $is_cache_clear = false;
    private $is_rebuild_index = false;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncPrestashopProgramsWithPas:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Price Book Program Map from ZOHO server.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //$after_one_hours = date('Y-m-d H:i:s', strtotime('+1 hours'));
        try{

            dump('Current Time: '.date('Y-m-d H:i:s'));

            /*$syncing_price_book = DB::table('pas_price_book')
                ->where('sync_status','=', 1)
                ->pluck('name')->toArray();

            if(count($syncing_price_book) > 0){
                $this->warn('Price book already syncing '.implode(', ', $syncing_price_book));
                return false;
            }*/

            $this->ps_shops = DB::connection('we_shop')
                ->table('ps_shop')
                //->select(['id_shop', 'name'])
                //->where('active', '=', 1)
                ->pluck('id_shop', 'name')->toArray();

            $partner_price_books = DB::table('pas_price_book AS pb')
                ->join('pas_partner AS p', 'p.price_book_zoho_id', '=', 'pb.zoho_id')
                ->select(['p.id', 'p.price_book_zoho_id', 'p.partner_name AS name', DB::raw('2 AS shop_group')])
                ->where('p.price_book_zoho_id', '!=', '')
                ->where('p.sync_at', '<', date('Y-m-d H:i:s'))
                ->whereIn('p.sync_status', [0, 2])
                ->where('p.partner_type', '=', 'Active')
                ->where('p.sync_ps_product', '!=', 1)
                ->where('pb.status', '=', 1)
                //->where('p.id', '=', 111111)
                ->orderBy('p.sync_status', 'ASC')
                ->limit(10)
                ->get()->all();

            $this->info('Total Partners: '.count($partner_price_books));

            $affiliate_price_books = [];
            if(count($partner_price_books) == 0){
                $affiliate_price_books = DB::table('pas_price_book AS pb')
                    ->join('pas_affiliate AS a', 'a.price_book_zoho_id', '=', 'pb.zoho_id')
                    ->select(['a.id', 'a.price_book_zoho_id', 'a.affiliate_name AS name', DB::raw('3 AS shop_group')])
                    ->where('price_book_zoho_id', '!=', '')
                    ->where('a.sync_at', '<', date('Y-m-d H:i:s'))
                    ->whereIn('a.sync_status', [0, 2])
                    ->where('a.sync_ps_product', '!=', 1)
                    ->where('pb.status', '=', 1)
                    //->where('a.status', '=', 1)
                    //->where('a.id', '=', 87)
                    ->orderBy('a.sync_status', 'ASC')
                    ->limit(10)
                    ->get()->all();
            }

            $this->info('Total Affiliates: '.count($affiliate_price_books));

            $partners = array_merge($partner_price_books, $affiliate_price_books);
            //dd($partners);

            foreach ($partners as $partner_price_book) {
                $pas_programs = DB::table('pas_price_book_program_map AS pm')
                    ->select(['p.name', 'p.code', 'p.tag_line', 'p.is_featured', 'p.is_best_seller', 'p.displayed_on', 'p.status', 'pm.program_list_price', 'p.zoho_id', 'p.program_type', 'p.category', 'p.sub_title', 'p.occupation', 'p.hours', 'p.duration_value', 'p.duration_type', 'p.language', 'p.level', 'p.is_best_seller', 'p.website_short_description', 'p.mycaa_description', 'p.outline', 'p.prerequisites', 'p.required_materials', 'p.certification', 'p.approved_offering', 'p.prepares_for_certification', 'p.prepares_for_certification', 'p.general_features_and_benefits', 'p.career_description', 'p.certification_inclusion', 'p.description', 'p.duration_type', 'p.duration_value', 'p.feature_tag_line', 'p.feature_tag_line', 'p.median_salary', 'p.job_growth', 'p.right_career', 'p.learning_objectives', 'p.support_description', 'p.retail_wholesale', 'p.unite_price', 'p.retail_wholesale', 'p.service_item_not_program', 'p.certification_included', 'p.externship_included', 'p.ce_units', 'p.delivery_methods_available', 'p.avg_completion_time', 'p.technical_requirements', 'p.average_completion', 'p.accreditation', 'p.layout'])
                    ->join('pas_program AS p', 'p.zoho_id', 'pm.program_zoho_id')
                    //->select(['p.zoho_id', 'pm.program_list_price'])
                    ->where('pm.price_book_zoho_id', '=', $partner_price_book->price_book_zoho_id)
                    ->where('p.status', '=', 'Active')
                    ->where('p.displayed_on', '=', 'All')
                    ->get()->all();
                //dd($pas_programs);

                $partner_name = $partner_price_book->name;
                if($partner_price_book->name == 'World Education'){
                    $partner_name = 'Unbound Library';
                }

                $this->price_book_partners[$partner_price_book->price_book_zoho_id][] = [
                    'id' => $partner_price_book->id,
                    'shop_group' => $partner_price_book->shop_group,
                    'partner_name' => $partner_name,
                    'programs' => $pas_programs,
                ];
            }

            //dd($this->price_book_partners);

            if(count($this->price_book_partners) > 0) {

                $this->importPriceBookPrograms();

                $leeds_data['action_via'] = 'cron';
                $leeds_data['url'] = 'cron-sync-prestashop-program';
                $leeds_data['ip_address'] = Utility::getClientIp();
                $leeds_data['session_id'] = Session::getId();
                $leeds_data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');

                if(count($this->user_log['delete']) > 0){
                    $leeds_data['action'] = 'delete';
                    $leeds_data['new_data'] = json_encode($this->user_log['delete']);
                    DB::table('pas_user_activity')->insert($leeds_data);
                }
                if(count($this->user_log['insert']) > 0){
                    $leeds_data['action'] = 'create';
                    $leeds_data['new_data'] = json_encode($this->user_log['insert']);
                    DB::table('pas_user_activity')->insert($leeds_data);
                }

            }else{
                $this->info('Price Book not found.');
            }
        }catch (Exception $e){
            dd($e->getMessage());
        }

    }

    private function importPriceBookPrograms(){
            try{
                //dump($this->price_book_partners);
                foreach ($this->price_book_partners as $price_book_zoho_id => $partners) {
                    //dd($partners_name['programs']);

                    $program_zoho_ids = [];
                    foreach ($partners as $partner) {
                        $this->partner_syncing['id'] = $partner['id'];
                        $this->partner_syncing['shop_group'] = $partner['shop_group'];

                        if($partner['shop_group'] == 3){
                            DB::table('pas_affiliate')
                                ->where('id', '=', $partner['id'])
                                ->update(['sync_status' => 1]);
                        }elseif($partner['shop_group'] == 2){
                            DB::table('pas_partner')
                                ->where('id', '=', $partner['id'])
                                ->update(['sync_status' => 1]);
                        }

                        if (isset($this->ps_shops[$partner['partner_name']])) {
                            $ps_product_new = [];
                            $ps_product_shop = [];

                            $this->warn('Partner IN PAS : ' . $partner['partner_name'].' ('.$partner['id'].')');
                            $this->info('Programs In PAS: ' . count($partner['programs']));

                            $ps_products = DB::connection('we_shop')
                                ->table('ps_product')
//                                ->select(['id_shop_default', 'id_product', 'zoho_id'])
                                ->where('id_shop_default', '=', $this->ps_shops[$partner['partner_name']])
                                ->where('zoho_id', '!=', '')
                                ->pluck('id_product', 'zoho_id')
                                ->toArray();
                            //dd($ps_products);

                            foreach ($partner['programs'] as $index => $pas_product) {

                                $product_shop = [
                                    //'id_product' => $id_product,
                                    'id_shop' => $this->ps_shops[$partner['partner_name']],
                                    'id_category_default' => 2,
                                    'id_tax_rules_group' => 1,
                                    'active' => 1,
                                    'price' => $pas_product->program_list_price,
                                    'wholesale_price' => $pas_product->program_list_price,
                                    'indexed' => 0,
                                    'date_add' => date('Y-m-d H:i:s')
                                ];

                                $program_zoho_ids[] = $pas_product->zoho_id;
                                if(isset($ps_products[$pas_product->zoho_id])){
                                    $id_product = $ps_products[$pas_product->zoho_id];
                                    $product_shop['id_product'] = $id_product;
                                    $ps_product_shop[] = $product_shop;
                                }else {
                                    $ps_product_new[$index]['ps_product'] = [
                                        'id_supplier' => 0,
                                        'id_manufacturer' => 0,
                                        'id_category_default' => 17,
                                        'id_shop_default' => $this->ps_shops[$partner['partner_name']],
                                        'id_tax_rules_group' => 1,
                                        'on_sale' => 0,
                                        'online_only' => 1,
                                        /*'quantity' => 0,
                                        'minimal_quantity' => 0,*/
                                        'price' => $pas_product->code,
                                        'wholesale_price' => $pas_product->code,
                                        'reference' => $pas_product->code,
                                        'redirect_type' => 404,
                                        'out_of_stock' => 2,
                                        'active' => 1,
                                        'date_add' => date('Y-m-d H:i:s'),
                                        'is_virtual' => 1,
                                        'is_featured' => $pas_product->is_featured,
                                        'is_best_seller' => $pas_product->is_best_seller,
                                        'displayed_on' => $pas_product->displayed_on,
                                        'status' => $pas_product->status,
                                        'zoho_id' => $pas_product->zoho_id,
                                    ];


                                    $ps_product_new[$index]['ps_product_lang'] = [
                                        'id_product' => null,
                                        'id_shop' => $this->ps_shops[$partner['partner_name']],
                                        'id_lang' => 1,
                                        'name' => $pas_product->name,
                                        'link_rewrite' => Utility::slugify($pas_product->name).'-'.Utility::slugify($pas_product->code, '-', false),
                                        'description' => json_encode(Program::loadPrestaShopJsonData((array) $pas_product)),
                                        'description_short' => !empty($pas_product->tag_line) ? $pas_product->tag_line : '',
                                    ];

                                    $product_shop['id_product'] = null;
                                    $ps_product_new[$index]['ps_product_shop'] = $product_shop;
                                    $ps_product_new[$index]['ps_product_category'] = [
                                        'program_type' => $pas_product->program_type,
                                        'category' => $pas_product->category
                                    ];

                                }

                            }

                            if(count($ps_product_shop) > 0){
                                $this->info('Total Imported into '.count($ps_product_shop));

                                try{
                                    DB::connection('we_shop')->beginTransaction();
                                    DB::connection('we_shop')
                                        ->table('ps_product_shop')
                                        ->where('id_shop', '=', $this->ps_shops[$partner['partner_name']])
                                        ->delete();

                                    DB::connection('we_shop')
                                        ->table('ps_product_shop')
                                        ->insert($ps_product_shop);

                                    $this->is_cache_clear = true;
                                    DB::connection('we_shop')->commit();
                                }catch (Exception $e){
                                    DB::connection('we_shop')->rollBack();
                                    $this->is_cache_clear = false;
                                    throw $e;
                                }

                            }

                            if(count($ps_product_new) > 0){
                                //dd(count($ps_product_new));
                                $this->warn('New Course '.count($ps_product_new));
                                $this->insertNewProducts($ps_product_new, $this->ps_shops[$partner['partner_name']]);
                                $this->is_rebuild_index = true;
                            }
                        }else{
                            $this->alert('Partner Not found in Shop : ' . $partner['partner_name']);
                        }

                        $after_three_hrs = date('Y-m-d H:i:s', strtotime('+3 hours'));
                        if($partner['shop_group'] == 3){
                            DB::table('pas_affiliate')
                                ->where('id', '=', $partner['id'])
                                ->update([
                                    'sync_at' => $after_three_hrs,
                                    'sync_status' => 0
                                ]);
                        }elseif($partner['shop_group'] == 2){
                            DB::table('pas_partner')
                                ->where('id', '=', $partner['id'])
                                ->update([
                                    'sync_at' => $after_three_hrs,
                                    'sync_status' => 0
                                ]);
                        }

                    }
                }

                if($this->is_cache_clear){
                    Program::cacheClear();
                }

                if($this->is_rebuild_index){
                    Program::rebuildSearch();
                }

            }catch (\Exception $e){
                if(count($this->partner_syncing) > 0){
                    if($this->partner_syncing['shop_group'] == 3){
                        DB::table('pas_affiliate')
                            ->where('id', '=', $this->partner_syncing['id'])
                            ->update(['sync_status' => 2]);
                    }elseif($this->partner_syncing['shop_group'] == 2){
                        DB::table('pas_partner')
                            ->where('id', '=', $this->partner_syncing['id'])
                            ->update(['sync_status' => 2]);
                    }
                }

                //dd($e);
                //dd($e->getMessage());

                $email_req = new EmailRequest();
                $email_req
                    ->setTo([
                        [$_ENV['DEVELOPER_EMAIL_FIRST'], "Xoom Web Development"],
                        ['rajneeshxwds@gmail.com', "Xoom Web Development"],
                    ])
                    ->setSubject($_ENV['APP_ENV'].' PAS ERROR :: '.__CLASS__)
                    ->setBody('Line No. '.$e->getLine().' MSG. '.$e->getMessage())
                    ->setLogSave(false);

                $email_helper = new EmailHelper($email_req);
                $email_helper->sendEmail();

                if($this->is_cache_clear){
                    Program::cacheClear();
                }

                if($this->is_rebuild_index){
                    Program::rebuildSearch();
                }

            }
    }

    private function insertNewProducts($ps_product_new, $id_shop){
        $sql_products_relation['category_product'] = [];
        foreach ($ps_product_new as $sql_index => $sql_product) {
            $id_product = DB::connection('we_shop')->table('ps_product')
                ->insertGetId($sql_product['ps_product']);

            if (!empty($id_product)) {
                $sql_product['ps_product_lang']['id_product'] = $id_product;
                $sql_product['ps_product_shop']['id_product'] = $id_product;
                $sql_products_relation['product_lang'][] = $sql_product['ps_product_lang'];
                $sql_products_relation['product_shop'][] = $sql_product['ps_product_shop'];

                $product_categories = Program::saveCategoryProductWithoutIsExist($sql_product['ps_product'], $id_product, $id_shop, true);
                array_push($sql_products_relation['category_product'], $product_categories);
            }
        }

        //echo '<pre>';print_r($sql_products_relation);die;

        if (count($sql_products_relation) > 0) {
            DB::connection('we_shop')->table('ps_product_lang')->insert($sql_products_relation['product_lang']);
            DB::connection('we_shop')->table('ps_product_shop')->insert($sql_products_relation['product_shop']);

            $category_product_data = [];
            foreach ($sql_products_relation['category_product'] as $product_categories) {
                foreach ($product_categories as $item) {
                    $category_product_data[] = $item;
                }
            }

            DB::connection('we_shop')->table('ps_category_product')->insert($category_product_data);
        }
    }

}
