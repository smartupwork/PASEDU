<?php

namespace App\Console\Commands;

use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PrestashopPriceBookUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestashopPriceBookUpdate:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Prestahop Program Price from PAS server.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //$time_before = Carbon::now('GMT-5')->subMinutes(10)->format('Y-m-d H:i:s');
        $time_before = date('Y-m-d H:i:s', strtotime('-10 minute'));
        $this->info($time_before);

        $programs = DB::table('pas_price_book_program_map AS pm')
            ->join('pas_price_book AS pb', 'pb.id', '=', 'pm.price_book_id')
            ->join('pas_partner AS pa', 'pa.price_book_id', '=', 'pm.price_book_id')
            ->join('pas_program AS p', 'p.id', '=', 'pm.program_id')
            ->select(['pm.price_book_id', 'pm.program_id', 'p.name', 'p.code', 'pm.program_list_price', 'pb.name AS price_book', 'pa.partner_name', 'pa.id AS partner_id'])
            ->where('pm.updated_at', '>', $time_before)
            ->where('p.status', '=', 'Active')
            ->where('p.displayed_on', '=', 'All')
            ->get()->all();

        //dump($programs);
        //$this->info('Total Program Found: '.count($programs));
        if(count($programs) > 0){
            foreach ($programs as $program) {
                $partner_name = $program->partner_name;
                if($program->partner_name == 'World Education'){
                    $partner_name = 'Unbound Library';
                }
                $this->info('Partner Name: '.$partner_name);
                $this->info('Course Code: '.$program->code);

                $id_shop = DB::connection('we_shop')->table('ps_shop')
                    ->where('name', '=', $partner_name)
                    ->value('id_shop');

                if($id_shop){
                    $product_shop = DB::connection('we_shop')
                        ->table('ps_product_shop AS ps')
                        ->select(['ps.*'])
                        ->join('ps_product AS p', 'p.id_product', '=',  'ps.id_product')
                        ->where('p.reference', '=', $program->code)
                        ->where('ps.id_shop', '=', $id_shop)
                        ->get()->first();
                    //dd([$id_shop, $product_shop]);

                    if($product_shop){
                        $this->info('Shop ('.$partner_name.') Product ('.$program->code.') Price ('.$program->program_list_price.') updating.');

                        DB::connection('we_shop')->table('ps_product_shop')
                            ->where('id_shop', '=', $product_shop->id_shop)
                            ->where('id_product', '=', $product_shop->id_product)
                            ->update([
                                'price' => $program->program_list_price,
                                'wholesale_price' => $program->program_list_price,
                                'date_upd' => date('Y-m-d H:i:s'),
                            ]);
                    }else{
                        /*$product_shop = DB::connection('we_shop')
                            ->table('ps_product AS p')
                            //->select(['ps.*'])
                            ->where('p.reference', '=', $program->code)
                            ->where('p.id_shop_default', '=', $id_shop)
                            ->get()->first();

                        $product_shop_data[] = [
                            'id_product' => $product_shop->id_product,
                            'id_shop' => $product_shop->id_shop,
                            'price' => $program->program_list_price,
                            'wholesale_price' => $program->program_list_price,
                            'redirect_type' => 404,
                            'id_category_default' => 2,
                            'active' => 1,
                            //'is_best_selling' => $zoho_product['is_best_seller'],
                        ];

                        dd($product_shop_data);
                        DB::connection('we_shop')->table('ps_product_shop')
                            ->where('id_shop', '=', $product_shop->id_shop)
                            ->where('id_product', '=', $product_shop->id_product)
                            ->update([
                                'price' => $program->program_list_price,
                                'wholesale_price' => $program->program_list_price,
                                'date_upd' => date('Y-m-d H:i:s'),
                            ]);*/

                        $this->warn('ps_product_shop entry not found.');
                    }
                }else{
                    $this->warn('Shop Not Found: '.$partner_name);
                }
            }
            Program::cacheClear();
            Program::rebuildSearch();
        }

    }


}
