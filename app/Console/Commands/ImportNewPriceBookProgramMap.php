<?php

namespace App\Console\Commands;

use App\EmailHelper;
use App\EmailRequest;
use App\ZohoHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportNewPriceBookProgramMap extends Command
{
    const OFF_SET = 0;
    const LIMIT = 200;

    private $off_set = self::OFF_SET;
    private $limit = self::LIMIT;
    private $page = 1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importNewPriceBookProgramMap:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Price Book New Program Map from ZOHO server.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pas_programs = DB::table('pas_program AS p')
            ->select(['p.id', 'p.zoho_id', 'p.name', DB::raw("(SELECT COUNT('pm.id') FROM pas_price_book_program_map AS pm WHERE p.zoho_id = pm.program_zoho_id) AS program_map_count")])
            ->having('program_map_count', '=', 0)
            ->where('price_book_counter', '<', 10)
            //->where('id', '=', 4)
            //->orderBy('price_book_counter', 'ASC')
            ->get()->all();
//dd($pas_programs);
        if(count($pas_programs) > 0) {
            try{
                foreach ($pas_programs as $pas_program) {
                    DB::table('pas_program')
                        ->where('id', '=', $pas_program->id)
                        ->increment('price_book_counter', 1);

                    $product_price_book = ZohoHelper::getInstance()->fetchRelatedRecords('Products/'.$pas_program->zoho_id, 'Price_Books', $this->page, $this->limit);

                    if($product_price_book['status'] == 'success' && isset($product_price_book['data']['data'])){
                        if($pas_program->program_map_count != count($product_price_book['data']['data'])){
                            //dd([count($product_price_book['data']['data']), $pas_program]);
                            foreach ($product_price_book['data']['data'] as $price_book) {

                                $program_map_exists = DB::table('pas_price_book_program_map')
                                    ->where('price_book_zoho_id', '=', $price_book['id'])
                                    ->where('program_zoho_id', '=', $pas_program->zoho_id)
                                    ->get()->first();

                                if(!$program_map_exists){
                                    $pas_price_book_id = DB::table('pas_price_book')
                                        ->where('zoho_id', '=', $price_book['id'])
                                        ->value('id');

                                    DB::table('pas_price_book_program_map')->insert([
                                        'price_book_id' => $pas_price_book_id,
                                        'price_book_zoho_id' => $price_book['id'],
                                        'program_id' => $pas_program->id,
                                        'program_zoho_id' => $pas_program->zoho_id,
                                        'program_list_price' => $price_book['list_price'],
                                        'created_at' => date('Y-m-d H:i:s'),
                                        'updated_at' => date('Y-m-d H:i:s'),
                                    ]);

                                    $this->info('Insert Price Book: '.$price_book['id'].' Program: '.$pas_program->name.' Program List Price: '.$price_book['list_price']);
                                }
                            }
                        }
                    }else{
                        $this->info('Products ID Not Found into CRM : '.$pas_program->zoho_id);
                    }
                }
            }catch (\Exception $e){
                $this->alert($e->getMessage());
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
        }else{
            $this->info('Price Book not found for Import Pricebook Program Map.');
        }
    }

}
