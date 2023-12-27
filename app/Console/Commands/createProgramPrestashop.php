<?php

namespace App\Console\Commands;

use App\Models\Program;
use App\Utility;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Exception;
use Prestashop;

class CreateProgramPrestashop extends Command
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

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'createProgramPrestashop:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync prestashop from PAS server.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pas_programs = DB::table('pas_program')
            ->where('status', '=', 'Active')
            ->where('displayed_on', '=', 'All')
            ->where('is_copy', '=', 1)
            ->limit(1)
            ->pluck('zoho_id')->toArray();

        //dump($pas_programs);

        $sql_products = [];
        $program_ids = [];

        if(count($pas_programs) > 0){
            foreach ($pas_programs as $program_zoho_id) {
                $program_partners = DB::table('pas_price_book_program_map AS pm')
                    ->select(['p.*', 'pr.id', 'pr.partner_name', 'pr.price_book_zoho_id', 'pm.program_list_price'])
                    ->join('pas_partner AS pr', 'pr.price_book_zoho_id', '=', 'pm.price_book_zoho_id')
                    ->join('pas_program AS p', 'p.zoho_id', '=', 'pm.program_zoho_id')
                    ->where('program_zoho_id', '=', $program_zoho_id)
                    ->get()->toArray();
                //dd($program_partners);
                foreach ($program_partners as $index => $program_partner) {
                    $program_ids[] = $program_zoho_id;
                    $partner_name = $program_partner->partner_name;
                    if($program_partner->partner_name == 'World Education'){
                        $partner_name = 'Unbound Library';
                    }

                    $shop = DB::connection('we_shop')
                        ->table('ps_shop')
                        ->where('name','=', $partner_name)
                        ->where('active', '=', 1)->get()->first();

                    if($shop){
                        $ps_product = DB::connection('we_shop')
                            ->table('ps_product')
                            ->where('reference','=', $program_partner->code)
                            ->where('id_shop_default', '=', $shop->id_shop)
                            //->where('active', '=', 1)
                            ->get()->first();

                        $product_shop = [
                            'id_product' => null,
                            'id_shop' => $shop->id_shop,
                            'id_category_default' => 2,
                            'id_tax_rules_group' => 1,
                            'active' => 1,
                            'price' => $program_partner->program_list_price,
                            'wholesale_price' => $program_partner->program_list_price,
                            'date_add' => date('Y-m-d H:i:s')
                        ];

                        if($ps_product){
                            $this->info('Product ('.$program_partner->code.') already exist for '.$partner_name);
                        }else{
                            $this->info('Product ('.$program_partner->code.') need to create for '.$partner_name);
                            $sql_products[$index]['id_shop'] = $shop->id_shop;

                            $sql_products[$index]['zoho_product'] = [
                                'program_type' => $program_partner->program_type,
                                'category' => $program_partner->category
                            ];

                            $sql_products[$index]['product'] = [
                                'id_supplier' => 0,
                                'id_manufacturer' => 0,
                                'id_category_default' => 17,
                                'id_shop_default' => $shop->id_shop,
                                'id_tax_rules_group' => 1,
                                'on_sale' => 0,
                                'online_only' => 1,
                                /*'quantity' => 0,
                                'minimal_quantity' => 0,*/
                                'price' => $program_partner->program_list_price,
                                'wholesale_price' => $program_partner->program_list_price,
                                'reference' => $program_partner->code,
                                'redirect_type' => 404,
                                'out_of_stock' => 2,
                                'active' => 1,
                                'date_add' => date('Y-m-d H:i:s'),

                                'is_virtual' => 1,
                                'is_featured' => $program_partner->is_featured,
                                'is_best_seller' => $program_partner->is_best_seller,
                                'displayed_on' => $program_partner->displayed_on,
                                'status' => $program_partner->status,
                                'zoho_id' => $program_partner->zoho_id,
                            ];


                            $sql_products[$index]['product_lang'] = [
                                'id_product' => null,
                                'id_shop' => $shop->id_shop,
                                'id_lang' => 1,
                                'name' => $program_partner->name,
                                'link_rewrite' => Utility::slugify($program_partner->name).'-'.Utility::slugify($program_partner->code, '-', false),
                                'description' => json_encode(Program::loadPrestaShopJsonData((array) $program_partner), JSON_UNESCAPED_SLASHES),
                                'description_short' => !empty($program_partner->tag_line) ? $program_partner->tag_line : '',
                            ];

                            $product_shop['id_product'] = null;
                            $sql_products[$index]['product_shop'] = $product_shop;
                        }
                    }
                }
            }
        }


        //DB::connection('we_shop')->beginTransaction();

        try{
            if(count($sql_products) > 0) {
                $sql_products_relation['category_product'] = [];
                foreach ($sql_products as $sql_index => $sql_product) {
                    $id_product = DB::connection('we_shop')->table('ps_product')
                        ->insertGetId($sql_product['product']);
                    if (!empty($id_product)) {
                        $sql_product['product_lang']['id_product'] = $id_product;
                        $sql_product['product_shop']['id_product'] = $id_product;
                        $sql_products_relation['product_lang'][] = $sql_product['product_lang'];
                        $sql_products_relation['product_shop'][] = $sql_product['product_shop'];

                        $product_categories = Program::saveCategoryProductWithoutIsExist($sql_product['zoho_product'], $id_product, $sql_product['id_shop'], true);
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

                    if(count($category_product_data) > 0){
                        DB::connection('we_shop')->table('ps_category_product')->insert($category_product_data);
                    }else{
                        $this->warn('Product categories not found');
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
                $this->info('DATA IMPORTED SUCCESSFULLY');

            }else{
                $this->warn('NO PROGRAMS FOUND TO IMPORTED');
            }
            //echo '<pre>';print_r($category_product_data);die;

            if(count($program_ids) > 0){
                DB::table('pas_program')->whereIn('zoho_id', $program_ids)
                    ->update(['is_copy' => 2]);
            }

            //DB::connection('we_shop')->commit();
        }catch (Exception $e){
            //DB::connection('we_shop')->rollBack();
            dump($e);die;
        }
    }

}
