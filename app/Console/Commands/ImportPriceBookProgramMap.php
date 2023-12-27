<?php

namespace App\Console\Commands;

use App\EmailHelper;
use App\EmailRequest;
use App\Models\PriceBook;
use App\Models\PriceBookProgramMap;
use App\Models\Program;
use App\Utility;
use App\ZohoHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Matrix\Exception;

class ImportPriceBookProgramMap extends Command
{

    const OFF_SET = 0;
    const LIMIT = 200;

    private $off_set = self::OFF_SET;
    private $limit = self::LIMIT;
    private $page = 1;

    private $pas_price_books = null;
    private $our_db_programms_arr = [];

    private $all_zoho_ids = [];

    private $data = [
        'insert' => [],
        'update' => [],
        'delete' => [],
    ];

    private $user_log = [
        'insert' => [],
        'update' => [],
        'delete' => [],
    ];


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importPriceBookProgramMap:cron';

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
        try{
            $this->pas_price_books = DB::table('pas_price_book')->whereNotNull('zoho_id')
                //->where('zoho_id', '=', '4837391000014827094')
                ->get()
                ->all();

            if(count($this->pas_price_books) > 0) {
                $this->our_db_programms_arr = DB::table('pas_program')
                    ->pluck('id', 'zoho_id')->toArray();

                $this->importPriceBookPrograms();

                //dd($this->all_zoho_ids);

                $leeds_data['action_via'] = 'cron';
                $leeds_data['url'] = 'cron-price-book-program-map';
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
        //dd($this->pas_price_books);
        foreach ($this->pas_price_books as $price_book) {
            $price_book_program_arr = DB::table('pas_price_book_program_map')
                ->where('price_book_zoho_id', '=', $price_book->zoho_id)
                ->pluck( 'program_zoho_id')->toArray();

            $this->info('Price Book ID: '.$price_book->zoho_id);
            //$this->info(' -> Page Number: '.$this->page);
            $this->getPriceBookPrograms($price_book, $price_book_program_arr);
        }
    }

    private function getPriceBookPrograms($price_book, $price_book_program_arr){
        $price_pro_map = ZohoHelper::getInstance()->fetchRelatedRecords('Price_Books/'.$price_book->zoho_id, 'Products', $this->page, $this->limit, null, ['id', 'Program_Type', 'Product_Category', 'Product_Name', 'Product_Code', 'list_price']);

        if($price_pro_map['status'] == 'success' && isset($price_pro_map['data']['data'])){
            try{
                //$this->info('Programs Count Into Price Book: '.$price_pro_map['data']['info']['count']);

                foreach ($price_pro_map['data']['data'] as $price_program_map){
                    $this->all_zoho_ids[] = $price_program_map['id'];
                    if(in_array($price_program_map['id'], $price_book_program_arr)){
                        $this->data['update'][] = [
                            'price_book_id' => $price_book->id,
                            'price_book_zoho_id' => $price_book->zoho_id,
                            'program_id' => isset($this->our_db_programms_arr[$price_program_map['id']]) ? $this->our_db_programms_arr[$price_program_map['id']]: null,
                            'program_zoho_id' => $price_program_map['id'],
                            'program_list_price' => $price_program_map['list_price'],
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                    }else{
                        $this->data['insert'][] = [
                            'price_book_id' => $price_book->id,
                            'price_book_zoho_id' => $price_book->zoho_id,
                            'program_id' => isset($this->our_db_programms_arr[$price_program_map['id']]) ? $this->our_db_programms_arr[$price_program_map['id']]: null,
                            'program_zoho_id' => $price_program_map['id'],
                            'program_list_price' => $price_program_map['list_price'],
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                }

                if ($price_pro_map['data']['info']['more_records']) {
                    $this->off_set = $this->off_set == 0 ? $this->limit : $this->off_set + $this->limit;
                    $this->page += 1;
                    //$this->info(' -> Page Number: '.$this->page);
                    $this->getPriceBookPrograms($price_book, $price_book_program_arr);
                    ///$this->info($partner['partner_name'].' find more.');
                }else{
                    if(count($this->all_zoho_ids) > 0){
                        $deleted_count = DB::table('pas_price_book_program_map')
                            ->where('price_book_zoho_id', '=', $price_book->zoho_id)
                            ->whereNotIn('program_zoho_id', $this->all_zoho_ids)
                            ->delete();
                        $this->alert('Deleted Programs: '.$deleted_count);
                    }

                    if(count($this->data['insert']) > 0){
                        DB::table('pas_price_book_program_map')->insert($this->data['insert']);
                    }

                    if(count($this->data['update']) > 0){
                        $update_sql = '';
                        foreach ($this->data['update'] as $data_update) {
                            $update_sql .= "UPDATE `pas_price_book_program_map` SET `price_book_id`='".$data_update['price_book_id']."',`price_book_zoho_id`='".$data_update['price_book_zoho_id']."',`program_id`='".$data_update['program_id']."',`program_zoho_id`='".$data_update['program_zoho_id']."',`program_list_price`='".$data_update['program_list_price']."',`updated_at`='".$data_update['updated_at']."' WHERE price_book_id = ".$data_update['price_book_id']." AND program_id = ".$data_update['program_id'].";";
                        }
                        //dump($update_sql);
                        if(!empty($update_sql)){
                            DB::unprepared($update_sql);
                        }
                    }

                    $this->user_log['delete'][] = $this->data['delete'];
                    $this->user_log['insert'][] = $this->data['insert'];

                    $this->alert('Total records Inserted(' . count($this->data['insert']) . ') and Updated(' . count($this->data['update']) . ').');

                    $this->all_zoho_ids = [];
                    $this->data = [
                        'insert' => [],
                        'update' => [],
                        'delete' => [],
                    ];

                    $this->off_set = self::OFF_SET;
                    $this->limit = self::LIMIT;
                    $this->page = 1;
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


        }else{
            $this->info('Programs not found into Price Book');
            //dump($price_pro_map);
        }
    }
}
