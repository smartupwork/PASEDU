<?php

namespace App\Console\Commands;

use App\EmailHelper;
use App\EmailRequest;
use App\Models\PriceBook;
use App\Utility;
use App\ZohoHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ImportPriceBook extends Command
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

    private $existing_price_key = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importPriceBook:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Price Book from ZOHO server.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $our_db_prices = PriceBook::all()->toArray();
        $this->existing_price_key = array_column($our_db_prices, 'name', 'zoho_id');

        $this->getPricebooks();
        $this->info('Total records Inserted('.count($this->data['insert']).') and Updated('.count($this->data['update']).').');
    }

    private function getPricebooks(){
        try{
        /*$date = \DateTime::createFromFormat("H:i:s", "07:00:00");
        $last_modified = $date->modify("-1 day")->format("c");*/

        //$before_70_min = Carbon::now('GMT-5')->subMinutes(70)->format('c');
        $zoho_price_book = ZohoHelper::getInstance()->fetch('Price_Books', ['Price_Book_Name', 'Description', 'Owner', 'Active'], $this->page, $this->limit);
        if($zoho_price_book['status'] == 'error'){
            $this->error($zoho_price_book['message']);
            die;
        }
        //dump($zoho_price_book);die;

            if(count($zoho_price_book['data']) > 0){
                foreach ($zoho_price_book['data']['data'] as $zoho_price) {
                    $data = [
                        'name' => addslashes($zoho_price['Price_Book_Name']),
                        'zoho_id' => $zoho_price['id'],
                        'owner' => $zoho_price['Owner']['name'],
                        'owner_id' => $zoho_price['Owner']['id'],
                        'description' => addslashes($zoho_price['Description']),
                        'status' => $zoho_price['Active'] ? 1:0,
                    ];

                    if(isset($this->existing_price_key[$zoho_price['id']])){
                        $data['updated_at'] = Carbon::now()->toDateTimeString();
                        $this->data['update'][] = $data;
                    }else {
                        $data['created_at'] = Carbon::now()->toDateTimeString();
                        $this->data['insert'][] = $data;
                    }
                }

                if ($zoho_price_book['data']['info']['more_records']) {
                    $this->off_set = $this->off_set == 0 ? $this->limit : $this->off_set + $this->limit;
                    $this->page += 1;
                    $this->getPricebooks();
                    ///$this->info($partner['partner_name'].' find more.');
                }else{
                    //dd($this->data);
                    $leeds_data['action_via'] = 'cron';
                    $leeds_data['url'] = 'cron-price-book';
                    $leeds_data['ip_address'] = Utility::getClientIp();
                    $leeds_data['session_id'] = Session::getId();
                    $leeds_data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');

                    if(count($this->data['insert']) > 0){
                        DB::table('pas_price_book')->insert($this->data['insert']);

                        $leeds_data['action'] = 'create';
                        $leeds_data['new_data'] = json_encode($this->data['insert']);
                        DB::table('pas_user_activity')->insert($leeds_data);

                    }
                    if(count($this->data['update']) > 0){
                        $ref_ids = [];
                        foreach ($this->data['update'] as $zoho_pricebook) {
                            DB::table('pas_price_book')->where([["zoho_id", '=', $zoho_pricebook['zoho_id']]])->update($zoho_pricebook);
                            $ref_ids[] = $zoho_pricebook['zoho_id'];
                        }

                        $leeds_data['action'] = 'update';
                        //$leeds_data['old_data'] = json_encode($this->data['update']);
                        $leeds_data['new_data'] = json_encode($this->data['update']);
                        $leeds_data['ref_ids'] = implode(',', $ref_ids);
                        DB::table('pas_user_activity')->insert($leeds_data);

                    }
                }

            }
        }catch (\Exception $e){
            $email_req = new EmailRequest();
            $email_req
                ->setTo([
                    [$_ENV['DEVELOPER_EMAIL_FIRST'], "Xoom Web Development"],
                    //[$_ENV['DEVELOPER_EMAIL_SECOND'], "Info Xoom Web Development"],
                ])
                ->setSubject($_ENV['APP_ENV'].' PAS ERROR :: '.__CLASS__)
                ->setBody('Line No. '.$e->getLine().' MSG. '.$e->getMessage())
                ->setLogSave(false);

            $email_helper = new EmailHelper($email_req);
            $email_helper->sendEmail();
        }
    }
}
