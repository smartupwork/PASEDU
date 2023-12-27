<?php

namespace App\Console\Commands;

use App\ZohoHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportUsers extends Command
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

    private $existing_users_zid = [];
    private $existing_users_id = [];


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importUsers:cron';

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
        $our_db_partners = DB::table('pas_owner')->get()->all();
        $this->existing_users_zid = array_column($our_db_partners, 'full_name', 'zoho_id');
        $this->existing_users_id = array_column($our_db_partners, 'id', 'zoho_id');

        $this->getUsers();
        $this->info('Total records Inserted('.count($this->data['insert']).') and Updated('.count($this->data['update']).').');
    }

    private function getUsers(){
        //$before_70_min = Carbon::now('GMT-5')->subMinutes(70)->format('c');

        $zoho_users = ZohoHelper::getInstance()->fetchUsers();

        if(isset($zoho_users['data']) && count($zoho_users['data']) > 0) {
            foreach ($zoho_users['data']['users'] as $zoho_user) {
                $data['full_name'] = addslashes($zoho_user['first_name']);
                $data['email'] = $zoho_user['email'];
                $data['zoho_id'] = $zoho_user['id'];
                $data['status'] = addslashes($zoho_user['status']);
                $data['role'] = $zoho_user['role']['name'];
                $data['role_zoho_id'] = $zoho_user['role']['id'];

                //dump($zoho_response);die;

                if(isset($this->existing_users_zid[$zoho_user['id']])){
                    $data['updated_at'] = date('Y-m-d H:i:s');
                    $this->data['update'][] = $data;
                }else {
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $this->data['insert'][] = $data;
                }
            }

            if ($zoho_users['data']['info']['more_records']) {
                $this->off_set = $this->off_set == 0 ? $this->limit : $this->off_set + $this->limit;
                $this->page += 1;
                $this->getUsers();
                ///$this->info($partner['partner_name'].' find more.');
            }else{
                //dd($this->data);
                if(count($this->data['insert']) > 0){
                    DB::table('pas_owner')->insert($this->data['insert']);
                }
                if(count($this->data['update']) > 0){
                    try{
                        foreach ($this->data['update'] as $zoho_user) {
                            DB::table('pas_owner')->where([["zoho_id", '=', $zoho_user['zoho_id']]])->update($zoho_user);
                        }
                    }catch(\Exception $e){
                        dd($e->getMessage());
                    }
                }
            }

        }
    }
}
