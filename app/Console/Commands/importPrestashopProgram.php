<?php

namespace App\Console\Commands;

use App\EmailHelper;
use App\EmailRequest;
use App\Models\Program;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PHPUnit\Exception;
use Prestashop;

class ImportPrestashopProgram extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importPrestashopProgram:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import prestashop from PAS server basically import programs for once when new shop created.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Program import START');
        $shop_name = '';
        $table = '';
        $id = '';
        $partner = DB::table('pas_partner')
            ->where('sync_ps_product', '=', 1)
            ->get()->first();
        if($partner){
            $table = 'pas_partner';
            $shop_name = $partner->partner_name;
            $id = $partner->id;
        }else{
            $affiliate = DB::table('pas_affiliate')
                ->where('sync_ps_product', '=', 1)
                ->get()->first();
            if($affiliate){
                $table = 'pas_affiliate';
                $shop_name = $affiliate->affiliate_name;
                $id = $affiliate->id;
            }
        }

        if(!empty($shop_name)){
            $id_shop = DB::connection('we_shop')->table('ps_shop')
                ->where('name', '=', $shop_name)
                ->value('id_shop');

            try{
                Program::importShopCourse($id_shop,0);

                DB::table($table)
                    ->where('id', '=', $id)
                    ->update(['sync_ps_product' => 0]);

                $email_req = new EmailRequest();
                $email_req
                    ->setTo([
                        [$_ENV['DEVELOPER_EMAIL_FIRST'], "Xoom Web Development"],
                        //[$_ENV['DEVELOPER_EMAIL_SECOND'], "Info Xoom Web Development"],
                    ])
                    ->setSubject($shop_name.' Product Imported')
                    ->setBody('Prestashop Product Imported')
                    ->setLogSave(false);

                $email_helper = new EmailHelper($email_req);
                $email_helper->sendEmail();

                Program::cacheClear();
                Program::rebuildSearch();
            }catch (Exception $e){
                $email_req = new EmailRequest();
                $email_req
                    ->setTo([
                        [$_ENV['DEVELOPER_EMAIL_FIRST'], "Xoom Web Development"],
                        //[$_ENV['DEVELOPER_EMAIL_SECOND'], "Info Xoom Web Development"],
                    ])
                    ->setSubject('Prestashop Product Import Failed')
                    ->setBody('Line No. '.$e->getLine().' MSG. '.$e->getMessage())
                    ->setLogSave(false);

                $email_helper = new EmailHelper($email_req);
                $email_helper->sendEmail();
            }


        }else{
            $this->info('Shop Not Found: '.$shop_name);
        }

    }

}
