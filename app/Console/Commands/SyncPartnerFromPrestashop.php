<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Prestashop;

class SyncPartnerFromPrestashop extends Command
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

    private $pas_existing_partners = [];
    private $pas_partners_ids = [];
    private $pas_existing_partners_ids = [];
    private $ps_existing_partners_ids = ['update' => [], 'delete' => []];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncPartnerFromPrestashop:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->pas_existing_partners = DB::table('pas_partner')->pluck('id', 'partner_name')->toArray();
        $this->pas_partners_ids = DB::table('pas_partner')->pluck('id')->toArray();

        $this->syncPartners();
        $this->info('Total records Deleted('.count($this->ps_existing_partners_ids['delete']).') and Updated('.count($this->ps_existing_partners_ids['update']).').');
    }

    private function syncPartners(){
        //$before_70_min = Carbon::now('GMT-5')->subMinutes(70)->format('c');

        $opt['resource'] = 'shops';
        $opt['display'] = 'full';
        $opt['filter[id_shop_group]'] = 2;
        $shops = Prestashop::get($opt);

        $shops_arr = json_encode($shops);
        $shops_arr = json_decode($shops_arr, true);

        if(isset($shops_arr['shops']['shop'])){
            foreach ($shops_arr['shops']['shop'] as $shop){
                if(isset($this->pas_existing_partners[$shop['name']])){
                    DB::table('pas_partner')
                        ->where('id', '=', $this->pas_existing_partners[$shop['name']])
                        ->update(['ps_shop_id' => $shop['id']]);

                    $this->pas_existing_partners_ids[] = $this->pas_existing_partners[$shop['name']];
                    $this->ps_existing_partners_ids['update'][] = $shop;
                }else{
                    $this->ps_existing_partners_ids['delete'][] = $shop['id'];
                }
            }
        }

        /*if(count($this->ps_existing_partners_ids['delete']) > 0) {
            foreach ($this->ps_existing_partners_ids['delete'] as $shop_id){
                $xmlSchema = Prestashop::getSchema('shops');

                $data = [
                    'id' => $shop_id,
                    'deleted' => 1,
                    'active' => 0,
                ];

                $postXml = Prestashop::fillSchema($xmlSchema, $data);
                $res = Prestashop::add(['resource'=> 'shops', 'postXml' => $postXml->asXml()]);

                if($res){
                   dd($res);
                }else{
                    die('no');
                }
            }
        }*/
    }

}
