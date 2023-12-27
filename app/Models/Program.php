<?php

namespace App\Models;

use App\Utility;
use App\ZohoHelper;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Exception;
use Protechstudio\PrestashopWebService\PrestaShopWebserviceException;
use Prestashop;

class Program extends Model
{
    use HasFactory;

    protected $table = 'pas_program';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'zoho_id',
        'category',
        'code',
        'owner',
        'unite_price',
        'quantity_in_stock',
        'vendor_name',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    //public $timestamps = false;

    public function priceBookProgram() {
        return $this->hasOne('App\Models\PriceBookProgramMap', 'program_zoho_id', 'zoho_id');
    }

    /*public function priceBooks() {
        return $this->hasMany('App\Models\PriceBook', 'zoho_id', 'price_book_zoho_id');
    }*/

    public function search($data){
        $query = DB::table('pas_program')
            ->select(['id', 'zoho_id', 'name', 'code', 'unite_price', 'hours', 'retail_wholesale', 'program_type', 'status', 'description', 'certification_included']);

        $query->where([
            ['id', '=', pas_decrypt($data['id'])],
        ]);

        return $query->get()->first();
    }

    public function savePrestaShopProductByShop($partner, $shop, $all_shops){
        try{
            //print_r($partner->priceBook->programs[0]->program->name);die;
            $all_programs = $partner->priceBook->programs;
            //echo '<pre>';print_r($all_programs);die;

            if(count($all_programs) > 0){
                foreach ($all_programs as $program) {
                    if($program->program->is_copy == 0 && !empty($program->program->code)){
                        dump($program->program->code);
                        $opt = [];
                        $opt['resource'] = 'products';
                        $opt['display'] = 'full';
                        $opt['output_format'] = 'JSON';
                        $opt['filter[reference]'] = $program->program->code;
                        //$opt['filter[id_lang]'] = 1;
                        $ps_product = Prestashop::get($opt);
                        //echo '<pre>';print_r($ps_product);die;

                        if($program->program_list_price > 0){
                            $data = [
                                'name'=> $program->program->name,
                                'description'=> json_encode($program->program, JSON_UNESCAPED_SLASHES),
//                'description'=> '',
                                'description_short'=> $program->program->tag_line,
                                //'link_rewrite'=>'pas-product-2',
                                'reference' => $program->program->code,
                                //'id_group_shop' => (int) $shop->id_shop_group,
                                'id_shop_default' => (int) $shop->id,
                                'id_category_default' => 2,
                                'id_tax_rules_group' => 1,
                                'minimal_quantity' => 1,
                                'available_for_order' => 1,
                                'is_virtual' => 1,
                                'show_price' => 1,
                                'state' => true,
                                'price' => $shop['id_shop_group'] == 3 ? $program->unite_price: $program->program_list_price,
                                'wholesale_price' => $shop['id_shop_group'] == 3 ? $program->unite_price: $program->program_list_price,
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

                            if($ps_product->products && $ps_product->products->product){
                                //echo '<pre>';print_r($ps_product->products->product->id);
                            }else{
                                $xmlSchema = Prestashop::getSchema('products');
                                /*$xmlSchema->associations->categories->category->id = 17;
                                $xmlSchema->associations->product_features->product_feature->id = 17;*/
                                //dd($data);
                                $postXml = Prestashop::fillSchema($xmlSchema, $data);
                                //dd($postXml);

                                $opt = [];
                                $opt['resource'] = 'products';
                                $opt['postXml'] = $postXml->asXml();

                                $xml = Prestashop::add($opt);
                                //print_r($xml);die;
                                if($xml){
                                    /*echo '('.$program->program->name.') product added successfully <br>';
                                    $product_add_arr = json_encode($xml);
                                    $product_add_arr = json_decode($product_add_arr, true);
                                    //dd($product_add_arr);

                                    $program_id = $product_add_arr['product']['id'];

                                    $product_shop_data = [];

                                    foreach ($all_shops as $shop) {

                                        //$partner_name = $shop['id_shop_group'] == 3 ? Utility::getConfig('pricebook-for-affiliate'): $shop['name'];

                                        $price = 0;
                                        $wholesale_price = 0;
                                        if($shop['id_shop_group'] == 3){
                                            $price = $zoho_product['unite_price'];
                                            $wholesale_price = $zoho_product['unite_price'];
                                        }else{
                                            $partner_name = $shop['name'];
                                            $partner_program = DB::table('pas_partner')
                                                ->select(['pas_partner.partner_name', 'pas_program.id', 'pas_program.name', 'pas_program.zoho_id', 'program_list_price'])
                                                ->join('pas_price_book_program_map', 'pas_price_book_program_map.price_book_zoho_id', '=', 'pas_partner.price_book_zoho_id')
                                                ->join('pas_program', 'pas_program.zoho_id', '=', 'pas_price_book_program_map.program_zoho_id')
                                                ->where('pas_partner.partner_name', '=', $partner_name)
                                                ->where('pas_program.name', '=', $data['name'])
                                                ->get()->first();
                                            if($partner_program){
                                                $price = $partner_program->program_list_price;
                                                $wholesale_price = $partner_program->program_list_price;
                                            }
                                        }


                                        $is_exists = DB::connection('we_shop')->table('ps_product_shop')
                                            ->where('id_product', '=', $product_add_arr['product']['id'])
                                            ->where('id_shop', '=', $shop['id'])
                                            ->count('id_product');

                                        if($is_exists == 0){
                                            $product_shop_data[] = [
                                                'id_product' => $product_add_arr['product']['id'],
                                                'id_shop' => $shop['id'],
                                                'price' => $price,
                                                'wholesale_price' => $wholesale_price,
                                                'redirect_type' => 404,
                                                'id_category_default' => 2,
                                                'active' => 1,
                                                'is_best_selling' => $zoho_product['is_best_seller'],
                                            ];
                                            //dump($product_shop_data);
                                        }

                                    }

                                    //dump($product_shop_data);
                                    if(count($product_shop_data) > 0){
                                        DB::connection('we_shop')->table('ps_product_shop')
                                            ->insert($product_shop_data);
                                    }

                                    $program_type[$shop['id']] = $this->saveCategoryProduct($shop, $zoho_product, $product_add_arr['product']);*/
                                }
                            }
                        }

                    }
                }
            }


        }catch (PrestaShopWebserviceException $e){
            echo '<pre>';print_r($e);die;
        }

    }

    public function savePrestaShopProduct($pas_program, $ps_shops){
        try{
            $ps_products = DB::connection('we_shop')
                //->select('ps_product.*, ')
                ->table('ps_product')
                ->join('ps_shop', 'ps_shop.id_shop','=','ps_product.id_shop_default')
                ->where('reference', '=', $pas_program['code'])
                ->orderBy('id_shop_default')
                ->get()->all();
            //print_r($ps_products);die;

            dump('Total ('.count($ps_products).') Product Found in Prestashop');
            // Product need to update
            if(count($ps_products) > 0){
                dump('UPDATE ('.$pas_program['zoho_id'].' '.$pas_program['code'].') product need to be updated');

                DB::connection('we_shop')->table('ps_product')
                    ->where('reference', '=', $pas_program['code'])
                    ->update([
                        'is_featured' => $pas_program['is_featured'],
                        'is_best_seller' => $pas_program['is_best_seller'],
                        'displayed_on' => $pas_program['displayed_on'],
                        'status' => $pas_program['status'],
                        'zoho_id' => $pas_program['zoho_id'],
                        'indexed' => 0,
                    ]);

                DB::connection('we_shop')->table('ps_product_lang')
                    ->whereIn('id_product', array_column($ps_products, 'id_product'))
                    ->update([
                        'name' => $pas_program['name'],
                        'description' => json_encode(Program::loadPrestaShopJsonData($pas_program)),
                        'description_short'=> !empty($pas_program['tag_line']) ? $pas_program['tag_line']:'',
                    ]);



                foreach ($ps_products as $ps_product){

                    //dump('<<< ('.$pas_program['name'].') "'.$product_id.'" product updated successfully');

                    $product_shop_data = $this->loadPrestaShopProductPrice($pas_program, $ps_product->name, $ps_product->id_product, $ps_product->id_shop, $ps_product->id_shop_group);

                    if($product_shop_data){
                        $product_shop_data['date_upd'] = date('Y-m-d H:i:s');
                        //dump($product_shop_data);

                        DB::connection('we_shop')->table('ps_product_shop')
                            ->where('id_product', '=', $ps_product->id_product)
                            ->where('id_shop', '=', $ps_product->id_shop_default)
                            ->update([
                                'price' => $product_shop_data['price'],
                                'wholesale_price' => $product_shop_data['wholesale_price'],
                                'date_upd' => date('Y-m-d H:i:s'),
                                'indexed' => 0,
                            ]);

                        //$this->saveCategoryProduct($pas_program, $ps_product->id_product);

                        //$this->weProductSave($pas_program, $ps_product->id_product);
                    }
                }
            }
            else{ // Product need to add
                dump('INSERT '.$pas_program['zoho_id'].' '.$pas_program['code'].')  product need to be added');
                $product_data = $this->loadPrestaShopProduct($pas_program);

                $xmlSchema = Prestashop::getSchema('products');
                $postXml = Prestashop::fillSchema($xmlSchema, $product_data);

                $opt_add['resource'] = 'products';
                $opt_add['postXml'] = $postXml->asXml();

                $xml = Prestashop::add($opt_add);
                if($xml){
                    $product_add_arr = json_encode($xml);
                    $product_add_arr = json_decode($product_add_arr, true);

                    if(isset($product_add_arr['product']['id'])){
                        $product_id = $product_add_arr['product']['id'];
                        //dump('<<< ('.$pas_program['name'].') "'.$product_id.'" product added successfully');

                        $product_shop_data = [];
                        if(count($ps_shops) > 0) {
                            foreach ($ps_shops as $shop) {
                                $product_price = $this->loadPrestaShopProductPrice($pas_program, $shop->name, $product_id, $shop->id_shop, $shop->id_shop_group);
                                if($product_price){
                                    $product_price['date_add'] = date('Y-m-d H:i:s');
                                    $product_shop_data[] = $product_price;
                                }
                            }
                        }

                        //dump($product_shop_data);
                        if(count($product_shop_data) > 0){
                            DB::connection('we_shop')->table('ps_product_shop')
                                ->insert($product_shop_data);
                        }

                        $this->saveCategoryProduct($pas_program, $product_id);
                        //$this->weProductSave($pas_program, $product_id);
                    }


                }
            }
            self::cacheClear();
        }catch (PrestaShopWebserviceException $e){
            self::cacheClear();
            echo '<pre>';print_r($e);die;
        }

    }

    public function weProductSave($zoho_product, $product_id){
        try{
            $we_program_exists = DB::connection('we_shop')->table('we_product_lang')
                ->where('zoho_id', '=', $zoho_product['zoho_id'])
                ->count('zoho_id');

            $zoho_product['id_product'] = $product_id;

            if($we_program_exists){
                unset($zoho_product['created_at']);
                $zoho_product['updated_at'] = date('Y-m-d H:i:s');
                DB::connection('we_shop')->table('we_product_lang')
                    ->where('zoho_id', '=', $zoho_product['zoho_id'])
                    ->update($zoho_product);
            }else{
                unset($zoho_product['updated_at']);
                $zoho_product['created_at'] = date('Y-m-d H:i:s');
                DB::connection('we_shop')->table('we_product_lang')
                    ->insert($zoho_product);
            }
            return true;
        }catch (Exception $e){
            dump($e->getMessage());
            return false;
        }

    }

    /**
     * @param $zoho_product
     * @param $product_id
     */
    public function saveCategoryProduct($zoho_product, $product_id){
        $category_product_data = [];
        DB::connection('we_shop')->table('ps_category_product')
            //->where('id_category', '=', [2, 16])
            ->where('id_product', '=', $product_id)
            ->delete();

        /*$count = DB::connection('we_shop')->table('ps_category_product')
            ->where('id_category', '=', 2)
            ->where('id_product', '=', $product_id)
            ->count();*/

        //if($count == 0){
            $category_product_data[] = [
                'id_category' => 2,
                'id_product' => $product_id,
                'position' => 0,
            ];
        //}

        if(!empty($zoho_product['program_type'])){
            $category_id = $this->getCategory($zoho_product['program_type'], 1);
            $count = DB::connection('we_shop')->table('ps_category_product')
                ->where('id_category', '=', $category_id)
                ->where('id_product', '=', $product_id)
                ->count();
            if($count == 0){
                $category_product_data[] = [
                    'id_category' => $category_id,
                    'id_product' => $product_id,
                    'position' => 1,
                ];
            }
        }

        if(!empty($zoho_product['category'])){
            $category_id = $this->getCategory($zoho_product['category'], $zoho_product['program_type']);
            $count = DB::connection('we_shop')->table('ps_category_product')
                ->where('id_category', '=', $category_id)
                ->where('id_product', '=', $product_id)
                ->count();
            if($count == 0){
                $category_product_data[] = [
                    'id_category' => $category_id,
                    'id_product' => $product_id,
                    'position' => 2,
                ];
            }
        }


        /*if ($zoho_product['is_featured']) {
            $category_product_data[] = [
                'id_category' => 16,
                'id_product' => $product_id,
                'position' => 3,
            ];
        }*/

        if(count($category_product_data) > 0){
            DB::connection('we_shop')->table('ps_category_product')
                ->insert($category_product_data);
        }
    }

    /**
     * @param $zoho_product
     * @param $product_id
     */
    public static function saveCategoryProductWithoutIsExist($zoho_product, $product_id, $id_shop, $return_data = false){
        $category_product_data = [];

        $category_product_data[] = [
            'id_category' => 2,
            'id_product' => $product_id,
            'position' => 0,
        ];

        if(!empty($zoho_product['program_type'])){
            $program_type_id = Program::getProgramType($zoho_product['program_type'], $id_shop);

            //$program_type_id = $this->getCategoryNew($zoho_product['program_type']);

            if(!empty($program_type_id)){
                $category_product_data[] = [
                    'id_category' => $program_type_id,
                    'id_product' => $product_id,
                    'position' => 1,
                ];
            }

            if(!empty($program_type_id) && !empty($zoho_product['category'])){
                $category_id = Program::getCategoryNew($zoho_product['category'], $program_type_id, $id_shop);

                //$category_id = $this->getCategoryNew($zoho_product['category'], $zoho_product['program_type']);

                if(!empty($category_id)){
                    $category_product_data[] = [
                        'id_category' => $category_id,
                        'id_product' => $product_id,
                        'position' => 2,
                    ];
                }
            }

        }

        if($return_data){
           return $category_product_data;
        }

        if(count($category_product_data) > 0){
            DB::connection('we_shop')->table('ps_category_product')
                ->insert($category_product_data);
        }
    }

    public static function getProgramType($category_name, $id_shop){
        $id_category = DB::connection('we_shop')->table('ps_category_lang')
            ->leftJoin('ps_category', 'ps_category.id_category', '=', 'ps_category_lang.id_category')
            ->where('ps_category_lang.name', '=', $category_name)
            ->where('ps_category.id_parent', '=', 2)
            //->where('ps_category.shop_id', '=', $shop_id)
            //->where('ps_category.level_depth', '=', 2)
            ->value('ps_category_lang.id_category');

        if(!empty($id_category)){
            return $id_category;
        }

        DB::connection('we_shop')->table('ps_category')
            ->insert([
                'id_parent' => 2,
                'id_shop_default' => 1,
                'level_depth' => 2,
                'active' => 1,
                'position' => 2,
                'is_root_category' => 0,
                'date_add' => date('Y-m-d H:i:s'),
        ]);
        $id_category = DB::connection('we_shop')->getPdo()->lastInsertId();

        if(empty($id_category)){
            return null;
        }

        DB::connection('we_shop')->table('ps_category_lang')
            ->insert([
                'id_category'=> $id_category,
                'id_shop'=> $id_shop,
                'id_lang'=> 1,
                'name'=> $category_name,
                'description'=> $category_name,
                'link_rewrite' => Str::slug($category_name),
            ]);
        $id_category_lang = DB::connection('we_shop')->getPdo()->lastInsertId();

        if(empty($id_category_lang)){
            return null;
        }
        self::categoryShopMap($id_category, $id_shop);
        return $id_category;
    }

    public static function getCategoryNew($category_name, $product_type, $id_shop){
        $id_category = DB::connection('we_shop')->table('ps_category_lang')
            ->leftJoin('ps_category', 'ps_category.id_category', '=', 'ps_category_lang.id_category')
            ->where('ps_category_lang.name', '=', $category_name)
            ->where('ps_category.id_parent', '=', $product_type)
            ->value('ps_category_lang.id_category');

        if(!empty($id_category)){
            return $id_category;
        }

        DB::connection('we_shop')->table('ps_category')
            ->insert([
                'id_parent' => $product_type,
                'id_shop_default' => 1,
                'level_depth' => 3,
                'active' => 1,
                'position' => 3,
                'is_root_category' => 0,
                'date_add' => date('Y-m-d H:i:s'),
            ]);
        $id_category = DB::connection('we_shop')->getPdo()->lastInsertId();

        if(empty($id_category)){
            return null;
        }

        DB::connection('we_shop')->table('ps_category_lang')
            ->insert([
                'id_category'=> $id_category,
                'id_shop'=> $id_shop,
                'id_lang'=> 1,
                'name'=> $category_name,
                'description'=> $category_name,
                'link_rewrite' => Str::slug($category_name),
            ]);
        $id_category_lang = DB::connection('we_shop')->getPdo()->lastInsertId();

        if(empty($id_category_lang)){
            return null;
        }
        self::categoryShopMap($id_category, $id_shop);
        return $id_category;
    }

    public static function categoryShopMap($id_category, $id_shop) {
        $isExists = DB::connection('we_shop')->table('ps_category_shop')
            ->where('id_category', '=', $id_category)
            ->where('id_shop', '=', $id_shop)
            ->count('id_category');

        if($isExists == 0){
            DB::connection('we_shop')->table('ps_category_shop')
                ->insert([
                    'id_category'=> $id_category,
                    'id_shop'=> $id_shop,
                    'position'=> 10,
                ]);
        }

    }

    public static function categoryProductMap($id_category, $id_product, $position) {
        $isExists = DB::connection('we_shop')->table('ps_category_product')
            ->where('id_category', '=', $id_category)
            ->where('id_product', '=', $id_product)
            ->count('id_product');

        if($isExists == 0){
            DB::connection('we_shop')->table('ps_category_product')
                ->insert([
                    'id_category'=> $id_category,
                    'id_product'=> $id_product,
                    'position'=> $position,
                ]);
        }

    }

    private function getCategory($category_name, $product_type = null){
        $id_category = 0;
        $id_parent = 0;
        if(!empty($product_type)){
            $id_parent = DB::connection('we_shop')->table('ps_category_lang')
                ->leftJoin('ps_category', 'ps_category.id_category', '=', 'ps_category_lang.id_category')
                ->where('ps_category_lang.name', '=', $product_type)
                ->where('ps_category.id_parent', '=', 2)
                ->value('ps_category_lang.id_category');

            //dump(['product_type', $product_type, $id_parent]);

            if(!empty($id_parent)){
                $id_category = DB::connection('we_shop')->table('ps_category_lang')
                    ->leftJoin('ps_category', 'ps_category.id_category', '=', 'ps_category_lang.id_category')
                    ->where('ps_category_lang.name', '=', $category_name)
                    //->where('ps_category_lang.id_shop', '=', $shop['id'])
                    ->where('ps_category.id_parent', '=', $id_parent)
                    ->value('ps_category_lang.id_category');

                //dump(['category', $category_name, $id_category]);
            }

        }

        if(!empty($id_category)){
            return $id_category;
        }else{
            $data = [
                'name'=> $category_name,
                'active' => true,
                'id_parent' => $id_parent,
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


    /**
     * @param $zoho_product
     * @return array
     */
    private function loadPrestaShopProduct($zoho_product)
    {
        $data = [
            'name' => $zoho_product['name'],
            'description' => json_encode($zoho_product, JSON_UNESCAPED_SLASHES),
            'description_short' => !empty($zoho_product['tag_line']) ? $zoho_product['tag_line'] : '',
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
            'displayed_on' => $zoho_product['displayed_on'],
            'status' => $zoho_product['status'],
            /*'associations' => [
                'categories' => [
                    [ 'category' => ['id' => 2] ],
                    [ 'category' => ['id' => 16] ],
                    [ 'category' => ['id' => 17] ],
                    [ 'category' => ['id' => 20] ],
                ],
            ]*/
        ];
        return $data;
    }

    /**
     * @param $zoho_product
     * @param $partner_name
     * @param $ps_product
     * @return array|bool
     */
    public function loadPrestaShopProductPrice($zoho_product, $partner_name, $id_product, $id_shop, $id_shop_group = 2)
    {
        $price = 0;
        $wholesale_price = 0;
        if ($id_shop_group == 3) {
            $price = $zoho_product['unite_price'];
            $wholesale_price = $zoho_product['unite_price'];
        } else {
            $partner_program = DB::table('pas_partner')
                ->select(['pas_partner.partner_name', 'pas_program.id', 'pas_program.name', 'pas_program.zoho_id', 'program_list_price'])
                ->join('pas_price_book_program_map', 'pas_price_book_program_map.price_book_zoho_id', '=', 'pas_partner.price_book_zoho_id')
                ->join('pas_program', 'pas_program.zoho_id', '=', 'pas_price_book_program_map.program_zoho_id')
                ->where('pas_partner.partner_name', '=', $partner_name)
                ->where('pas_program.name', '=', $zoho_product['name'])
                ->get()->first();
            if ($partner_program) {
                $price = $partner_program->program_list_price;
                $wholesale_price = $partner_program->program_list_price;
            }
        }

        if($price <= 0){
            return false;
        }

        return [
            'id_product' => $id_product,
            'id_shop' => $id_shop,
            'price' => $price,
            'wholesale_price' => $wholesale_price,
            'redirect_type' => 404,
            'id_category_default' => 2,
            'active' => 1,
            'is_best_selling' => $zoho_product['is_best_seller'],
        ];
    }

    public static function loadPasProgramData($data){

        $zoho_programs = ZohoHelper::getInstance()->fetchByIds('Products', [$data['zoho_id']], ['Product_Name', 'Program_Sub_Title', 'Product_Category', 'Product_Code', 'Hours', 'Duration', 'Level', 'Retail_Wholesale', 'Owner', 'Program_Type', 'Unit_Price', 'Vendor_Name', 'Program_Status', 'Displayed_On', 'Service_Item_not_program', 'Description', 'Certification_Included', 'Layout', 'Featured_Course', 'Best_Seller', 'Tag_Line', 'Prerequisites', 'Outline', 'Externship_Included', 'Approved_Offering', 'Language', 'CE_Units', 'Level', 'Occupation', 'Feature_Tag_Line', 'Career_Description', 'Median_Salary', 'Job_Growth', 'Right_Career', 'Website_Short_Description', 'Learning_Objectives', 'Support_Description', 'Average_Completion', 'Avg_Completion_Time', 'Required_Materials', 'Technical_Requirements', 'Accreditation', 'Certification_Benefits', 'General_Features_and_Benefits', 'Demo_URL', 'Audience', 'Delivery_Methods_Available', 'Certification', 'Prepares_for_Certification', 'MyCAA_Description']);

        if (isset($zoho_programs['data'][0])) {
            $zoho_program = $zoho_programs['data'][0];

            $inclusion = null;
            $program_inclusion = ZohoHelper::getInstance()->fetchSubForm('Products', $zoho_program['id']);
            if($program_inclusion['status'] == 'success' && isset($program_inclusion['data']['data'][0]['Certifications_Regulatory'][0])){
                $inclusion = json_encode($program_inclusion['data']['data'][0]['Certifications_Regulatory']);
            }

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
                'zoho_id' => $zoho_program['id'],
                'name' => addslashes($zoho_program['Product_Name']),
                'sub_title' => addslashes($zoho_program['Program_Sub_Title']),
                'category' => addslashes($zoho_program['Product_Category']),
                'program_type' => addslashes($zoho_program['Program_Type']),
                'code' => addslashes($zoho_program['Product_Code']),
                'hours' => $zoho_program['Hours'],
                'duration_type' => $duration_type,
                'duration_value' => $duration_value,
                'level' => addslashes($zoho_program['Level']),
                'occupation' => addslashes($zoho_program['Occupation']),
                'feature_tag_line' => addslashes($zoho_program['Feature_Tag_Line']),
                'career_description' => addslashes($zoho_program['Career_Description']),
                'median_salary' => addslashes($zoho_program['Median_Salary']),
                'job_growth' => addslashes($zoho_program['Job_Growth']),
                'right_career' => addslashes($zoho_program['Right_Career']),
                'website_short_description' => addslashes($zoho_program['Website_Short_Description']),
                'learning_objectives' => addslashes($zoho_program['Learning_Objectives']),
                'support_description' => addslashes($zoho_program['Support_Description']),
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
                'certification_inclusion' => $inclusion,
                'vendor_name' => isset($zoho_program['Vendor_Name']['name']) ? addslashes($zoho_program['Vendor_Name']['name']):null,
                'vendor_id' => isset($zoho_program['Vendor_Name']['id']) ? addslashes($zoho_program['Vendor_Name']['id']):null,
                'average_completion' => addslashes($zoho_program['Average_Completion']),
                'avg_completion_time' => addslashes($zoho_program['Avg_Completion_Time']),
                'required_materials' => addslashes($zoho_program['Required_Materials']),
                'technical_requirements' => addslashes($zoho_program['Technical_Requirements']),
                'accreditation' => addslashes($zoho_program['Accreditation']),
                'certification_benefits' => addslashes($zoho_program['Certification_Benefits']),
                'general_features_and_benefits' => addslashes($zoho_program['General_Features_and_Benefits']),
                'demo_url' => addslashes($zoho_program['Demo_URL']),
                //'audience' => addslashes($zoho_program['Audience']),
                'delivery_methods_available' => json_encode($zoho_program['Delivery_Methods_Available']),
                'certification' => addslashes($zoho_program['Certification']),
                'prepares_for_certification' => addslashes($zoho_program['Prepares_for_Certification']),
                'mycaa_description' => addslashes($zoho_program['MyCAA_Description']),
            ];
            return $zoho_data;
        }
    }

    /**
     * @param $program
     * @return array
     */
    public static function loadPrestaShopJsonData($program){
        return [
            'name' => $program['name'],
            'crmProductId' => $program['zoho_id'],
            'subTitle' => $program['sub_title'],
            'tagLine' => $program['tag_line'],
            'occupation' => $program['occupation'],
            'hours' => $program['hours'],
            'duration' => $program['duration_value'].' '.$program['duration_type'],
            'language' => $program['language'],
            'level' => $program['level'],
            'bestSeller' => (bool) $program['is_best_seller'],
            'websiteShortDescription' => $program['website_short_description'],
            'myCaaDescription' => $program['mycaa_description'],
            'includedCoursesNames' => '',
            'outline' => $program['outline'],
            'prerequisites' => $program['prerequisites'],
            'requiredMaterials' => $program['required_materials'],
            'certification' => $program['certification'],
            'eligibleFunding' => $program['approved_offering'],
            'preparesForCertification' => $program['prepares_for_certification'],
            'optionalExternshipIncluded' => $program['prepares_for_certification'],
            'features' => $program['general_features_and_benefits'],
            'careerServices' => $program['career_description'],
            'materials' => (!empty($program['certification_inclusion']) ? $program['certification_inclusion']: null),
            'oldDescription' => $program['description'],
            'duration_type' => $program['duration_type'],
            'duration_value' => $program['duration_value'],
            'category' => $program['category'],
            'program_type' => $program['program_type'],
            'code' => $program['code'],
            'feature_tag_line' => $program['feature_tag_line'],
            'median_salary' => $program['median_salary'],
            'job_growth' => $program['job_growth'],
            'right_career' => $program['right_career'],
            'learning_objectives' => $program['learning_objectives'],
            'support_description' => $program['support_description'],
            'retail_wholesale' => $program['retail_wholesale'],
            'unite_price' => $program['unite_price'],
            'service_item_not_program' => $program['service_item_not_program'],
            'certification_included' => $program['certification_included'],
            'externship_included' => $program['externship_included'],
            'ce_units' => $program['ce_units'],
            'delivery_methods_available' => $program['delivery_methods_available'],
            'avg_completion_time' => $program['avg_completion_time'],
            'technical_requirements' => $program['technical_requirements'],
            'average_completion' => $program['average_completion'],
            'accreditation' => $program['accreditation'],
            'layout' => $program['layout'],
        ];

    }

    public static function importShopCourse($id_shop, $debug = 0){
        $shop_price_table = 'ps_product_shop';
        $shop = DB::connection('we_shop')
            ->table('ps_shop AS s')
            //->join('ps_shop_url AS su', 'su.id_shop', '=', 's.id_shop')
            ->where('s.id_shop', '=', $id_shop)
            //->where('s.id_shop_group', '=', 3)
            ->where('s.active', '=', 1)
            ->get()->first();
        //dd($shop);
        if ($shop) {
            $parent_type_table = 'pas_partner';
            $partner_name = $shop->name;
            if($shop->id_shop_group == 1){
                $partner_name = 'World Education';
                echo 'Main Shop: '.$shop->name .' ('. $partner_name.')';
            }elseif($shop->id_shop_group == 2){
                echo 'Partner Shop: ' . $partner_name;
            }elseif($shop->id_shop_group == 3){
                $parent_type_table = 'pas_affiliate';
                echo 'Affiliate Shop: ' . $partner_name;
            }

            $price_book_programs_query = DB::table($parent_type_table.' AS a')
                ->select([DB::raw('DISTINCT p.id'),'p.id', 'p.zoho_id', 'p.code', 'p.name', 'p.sub_title', 'p.occupation', 'p.hours', 'p.duration_value', 'p.duration_type', 'p.language', 'p.level', 'p.website_short_description', 'p.mycaa_description', 'p.outline', 'p.prerequisites', 'p.required_materials', 'p.certification', 'p.approved_offering', 'p.prepares_for_certification', 'p.general_features_and_benefits', 'p.career_description', 'p.description', 'p.category', 'p.program_type', 'p.feature_tag_line', 'p.median_salary', 'p.job_growth', 'p.right_career', 'p.learning_objectives', 'p.support_description', 'p.retail_wholesale', 'p.unite_price', 'p.service_item_not_program', 'p.certification_included', 'p.externship_included', 'p.ce_units', 'p.delivery_methods_available', 'p.avg_completion_time', 'p.technical_requirements', 'p.average_completion', 'p.accreditation', 'p.layout', 'p.tag_line', 'p.is_featured', 'p.is_best_seller', 'p.displayed_on', 'p.status', 'pbm.program_list_price'])
                ->join('pas_price_book As pb', 'pb.zoho_id', '=', 'a.price_book_zoho_id')
                ->join('pas_price_book_program_map AS pbm', 'pbm.price_book_zoho_id', '=', 'a.price_book_zoho_id')
                ->join('pas_program AS p', 'p.zoho_id', '=', 'pbm.program_zoho_id')
                ->where('p.status', '=', 'Active')
                ->where('p.displayed_on', '=', 'All');

            if($shop->id_shop_group == 3){
                $price_book_programs_query->where('a.affiliate_name', '=', $partner_name);
            }else{
                $price_book_programs_query->where('a.partner_name', '=', $partner_name);
            }

            $ps_products_shop_query = DB::connection('we_shop')
                ->table($shop_price_table)
                ->where('id_shop', '=', $id_shop);
            //->where('p.active', '=', 1)

            //dd([$price_book_programs_query->count('p.id'), $ps_products_shop_query->count('ps.id_product')]);

            //if($price_book_programs_query->count('p.id') != $ps_products_shop_query->count('id_product')){
                //$ps_products_shop_query->delete();

                $pas_products_to_be_added = $price_book_programs_query->get()->all();
                //echo '<pre>';print_r([count($pas_products_to_be_added), $pas_products_to_be_added]);die;

                $ps_products_to_be_added = DB::connection('we_shop')->table('ps_product')
                    ->select(['id_product', 'zoho_id'])
                    ->where('id_shop_default', '=', $id_shop)
                    ->where('status', '=', 'Active')
                    ->where('displayed_on', '=', 'All')
                    ->pluck('id_product', 'zoho_id')->toArray();
                //echo '<pre>';print_r($ps_products_to_be_added);die;

                $sql_value = [];
                $sql_products = [];
                $sql_products_relation = [];

                foreach ($pas_products_to_be_added as $index => $pas_product) {
                    //dd($index);
                    $product_shop = [
                        'id_product' => null,
                        'id_shop' => $id_shop,
                        'id_category_default' => 2,
                        'id_tax_rules_group' => 1,
                        'active' => 1,
                        'price' => $pas_product->program_list_price,
                        'wholesale_price' => $pas_product->program_list_price,
                        'date_add' => date('Y-m-d H:i:s')
                    ];
                    //dump($pas_product->zoho_id);
                    if(isset($ps_products_to_be_added[$pas_product->zoho_id])){
                        $product_shop['id_product'] = $ps_products_to_be_added[$pas_product->zoho_id];
                        $sql_value['product_shop'][] = $product_shop;
                        $sql_value['zoho_product'][] = [
                            'id_product' => $ps_products_to_be_added[$pas_product->zoho_id],
                            'program_type' => $pas_product->program_type,
                            'category' => $pas_product->category
                        ];
                    }else{
                        $sql_products[$index]['zoho_product'] = [
                            'program_type' => $pas_product->program_type,
                            'category' => $pas_product->category
                        ];

                        $sql_products[$index]['product'] = [
                            'id_supplier' => 0,
                            'id_manufacturer' => 0,
                            'id_category_default' => 17,
                            'id_shop_default' => $id_shop,
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


                        $sql_products[$index]['product_lang'] = [
                            'id_product' => null,
                            'id_shop' => $id_shop,
                            'id_lang' => 1,
                            'name' => $pas_product->name,
                            'link_rewrite' => Utility::slugify($pas_product->name).'-'.Utility::slugify($pas_product->code, '-', false),
                            'description' => json_encode(Program::loadPrestaShopJsonData((array) $pas_product)),
                            'description_short' => !empty($pas_product->tag_line) ? $pas_product->tag_line : '',
                        ];

                        $product_shop['id_product'] = null;
                        $sql_products[$index]['product_shop'] = $product_shop;
                    }
                }

                if(!empty($debug)){
                    echo '<pre>';
                    print_r([
                        'total_product_into_crm' => count($pas_products_to_be_added),
                        'product_shop_existing' => $sql_value,
                        'product_not_existing' => $sql_products
                    ]);die;
                }

                if(DB::connection('we_shop')->transactionLevel() == 0){
                    DB::connection('we_shop')->beginTransaction();
                }


                try{
                    if(isset($sql_value['product_shop']) && count($sql_value['product_shop']) > 0){
                        $ps_products_shop_query->delete();
                        DB::connection('we_shop')->table($shop_price_table)->insert($sql_value['product_shop']);

                        $category_product_data = [];
                        $ids = [];
                        foreach ($sql_value['zoho_product'] as $zoho_product) {
                            //dd($product_categories);
                            $ids[] = $zoho_product['id_product'];
                            $product_categories = Program::saveCategoryProductWithoutIsExist($zoho_product, $zoho_product['id_product'], $id_shop, true);

                            foreach ($product_categories as $item) {
                                $category_product_data[] = $item;
                            }
                        }
                        //dd($category_product_data);

                        if(count($category_product_data) > 0){
                            DB::connection('we_shop')->table('ps_category_product')
                                ->whereIn('id_product', $ids)->delete();
                            DB::connection('we_shop')->table('ps_category_product')->insert($category_product_data);
                        }
                    }

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

                                $product_categories = Program::saveCategoryProductWithoutIsExist($sql_product['zoho_product'], $id_product, $id_shop, true);
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
                    //echo '<pre>';print_r($category_product_data);die;

                    if(DB::connection('we_shop')->transactionLevel() > 0){
                        DB::connection('we_shop')->commit();
                    }

                    //self::cacheClear();
                    //self::rebuildSearch();

                    echo '<br><br> DATA IMPORTED SUCCESSFULLY';
                }catch (Exception $e){
                    if(DB::connection('we_shop')->transactionLevel() > 0){
                        DB::connection('we_shop')->rollBack();
                    }
                    dump($e);die;
                }

                echo '<pre>';
                print_r([
                    'product_shop_existing' => isset($sql_value['product_shop']) ? count($sql_value['product_shop']):0,
                    'product_not_existing' => count($sql_products)
                ]);
            //}
        }
    }

    /**
     * Prestashop Cache clear
     */
    public static function cacheClear(){
        $client = new Client();
        $response = $client->get($_ENV['PRESTASHOP_BASE_URL'].'/modules/pasapi/cache-clear.php', [
            'headers' => [
                'Accept'        => 'application/json',
            ],
            'query_params' => [],
        ]);
        $response->getBody();
    }

    /**
     * Prestashop Rebuild Search Index
     */
    public static function rebuildSearch(){
        /*$client = new Client();
        $response = $client->get($_ENV['PRESTASHOP_SEARCH_REINDEX'], [
            'headers' => [
                'Accept'        => 'application/json',
            ],
            'query_params' => [],
        ]);
        $response->getBody();*/
    }



}
