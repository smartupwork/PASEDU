<?php

namespace App\Console\Commands;

use App\Models\Affiliate;
use App\Models\PriceBook;
use App\ZohoHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportAffiliate extends Command
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

    private $existing_partners_zid = [];
    private $existing_partners_id = [];

    private $our_db_price_books = [];
    private $our_db_price_books_arr = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importAffiliate:cron';

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
        $our_db_partners = Affiliate::all()->toArray();
        $this->existing_partners_zid = array_column($our_db_partners, 'affiliate_name', 'zoho_id');
        $this->existing_partners_id = array_column($our_db_partners, 'id', 'zoho_id');

        $this->our_db_price_books = PriceBook::all()->toArray();
        $this->our_db_price_books_arr = array_column($this->our_db_price_books, 'id', 'zoho_id');

        $this->getPartners();
        $this->info('Total records Inserted('.count($this->data['insert']).') and Updated('.count($this->data['update']).').');
    }

    private function getPartners(){
        //$before_70_min = Carbon::now('GMT-5')->subMinutes(70)->format('c');

        $zoho_partners = ZohoHelper::getInstance()->fetch('SMECs', [], $this->page, $this->limit);

        if(count($zoho_partners['data']) > 0) {
            foreach ($zoho_partners['data']['data'] as $zoho_partner) {

                $data['affiliate_name'] = !empty($zoho_partner['Name']) ? addslashes($zoho_partner['Name']):null;

                if($zoho_partner['Pricebook'] && isset($zoho_partner['Pricebook']['id'])){
                    $data['price_book_id'] = isset($this->our_db_price_books_arr[$zoho_partner['Pricebook']['id']])? $this->our_db_price_books_arr[$zoho_partner['Pricebook']['id']]:null;
                    $data['price_book_zoho_id'] = $zoho_partner['Pricebook']['id'];
                }

                $data['zoho_id'] = $zoho_partner['id'];
                $data['phone'] = $zoho_partner['Phone'];
                $data['email'] = $zoho_partner['Email'];
                $data['address_1'] = $zoho_partner['Address_1'];
                $data['address_2'] = $zoho_partner['Address_2'];
                $data['city'] = $zoho_partner['City'];
                $data['state'] = $zoho_partner['State_Province'];
                $data['zip_postal_code'] = $zoho_partner['Zip_Postal_Code'];
                $data['hosted_site'] = $zoho_partner['Hosted_Site'];
                $data['affiliate_site'] = $zoho_partner['Affiliate_Site'];

                //if(isset($zoho_partner['Status'])){
                    $data['status'] = ($zoho_partner['Status'] == 'Active' ? 1:0);
                /*}else{
                    $data['status'] = 1;
                }*/


                //dump($data);die;

                if(isset($this->existing_partners_zid[$zoho_partner['id']])){
                    $data['updated_at'] = date('Y-m-d H:i:s');
                    $this->data['update'][] = $data;
                }else {
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $this->data['insert'][] = $data;
                }
            }

            if ($zoho_partners['data']['info']['more_records']) {
                $this->off_set = $this->off_set == 0 ? $this->limit : $this->off_set + $this->limit;
                $this->page += 1;
                $this->getPartners();
                ///$this->info($partner['affiliate_name'].' find more.');
            }else{
                //dd($this->data);
                if(count($this->data['insert']) > 0){
                    foreach ($this->data['insert'] as $zoho_partner) {
                        DB::table('pas_affiliate')->insert($zoho_partner);
                    }
                }
                if(count($this->data['update']) > 0){
                    try{
                        foreach ($this->data['update'] as $zoho_partner) {
                            DB::table('pas_affiliate')->where([["zoho_id", '=', $zoho_partner['zoho_id']]])->update($zoho_partner);
                        }
                    }catch(\Exception $e){
                        dd($e->getMessage());
                    }
                }
            }

        }
    }
}
