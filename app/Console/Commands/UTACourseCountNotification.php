<?php

namespace App\Console\Commands;

use App\EmailHelper;
use App\EmailRequest;
use App\ZohoHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PHPUnit\Exception;

class UTACourseCountNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UTACourseCountNotification:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update ZOHO notification webhook.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            $programs = DB::table('pas_price_book_program_map AS pm')
                //->select(['COUNT(pm.program_id)'])
                ->join('pas_price_book AS pb', 'pb.id', '=', 'pm.price_book_id')
                ->join('pas_partner AS pa', 'pa.price_book_id', '=', 'pm.price_book_id')
                ->join('pas_program AS p', 'p.id', '=', 'pm.program_id')
                //->select(['pm.price_book_id', 'pm.program_id', 'p.name', 'p.code', 'pm.program_list_price', 'pb.name AS price_book', 'pa.id AS partner_id'])
                //->where('pm.updated_at', '>', $time_before)

                ->where('p.status', '=', 'Active')
                ->where('p.displayed_on', '=', 'All')
                ->where('pa.partner_name', '=', 'University of Texas at Arlington')->count('pm.program_id');

            $body = '<p>Total Program IN PAS: '.$programs.'</p>';

            $zoho_id_exists = DB::connection('we_shop')
                ->table('ps_product AS p')
                ->select(['p.id_product', 'p.reference'])
                //->where('p.reference', '=', $program->code)
                ->where('p.id_shop_default', '=', 111)
                ->whereNotNull('zoho_id')
                ->count('p.id_product');

            $body .= '<p>ZOHO Program in ps_product: '.$zoho_id_exists.'</p>';

            $all_product_count = DB::connection('we_shop')
                ->table('ps_product AS p')
                ->select(['p.id_product', 'p.reference'])
                //->where('p.reference', '=', $program->code)
                ->where('p.id_shop_default', '=', 111)
                ->count('p.id_product');

            $body .= '<p>All Program in ps_product : '.$all_product_count.'</p>';

            $product_shop_arr = DB::connection('we_shop')
                ->table('ps_product_shop AS ps')
                ->select(['ps.*', 'p.id_product', 'p.reference'])
                ->join('ps_product AS p', 'p.id_product', '=',  'ps.id_product')
                //->where('p.reference', '=', $program->code)
                ->where('ps.id_shop', '=', 111)
                ->count('p.id_product');

            $body .= '<p>Total Program in ps_product_shop : '.$product_shop_arr.'</p>';

            $email_req = new EmailRequest();
            $email_req
                ->setTo([
                    [$_ENV['DEVELOPER_EMAIL_FIRST'], "Xoom Web Development"],
                    ['rajneeshxwds@gmail.com', "Rajneesh Gautam"],
                ])
                ->setSubject('University of Texas at Arlington Course Count')
                ->setBody($body)
                ->setLogSave(false);

            $email_helper = new EmailHelper($email_req);
            $email_helper->sendEmail();

        }catch (Exception $e){
            $email_req = new EmailRequest();
            $email_req
                ->setTo([
                    [$_ENV['DEVELOPER_EMAIL_FIRST'], "Xoom Web Development"],
                    //[$_ENV['DEVELOPER_EMAIL_SECOND'], "Info Xoom Web Development"],
                ])
                ->setSubject('University of Texas at Arlington Course Count:: ERROR')
                ->setBody('Line No. '.$e->getLine().' MSG. '.$e->getMessage())
                ->setLogSave(false);

            $email_helper = new EmailHelper($email_req);
            $email_helper->sendEmail();
        }

    }
}
