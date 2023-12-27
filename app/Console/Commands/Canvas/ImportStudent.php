<?php

namespace App\Console\Commands\Canvas;

use App\CanvasHelper;
use App\EmailHelper;
use App\EmailRequest;
use App\Utility;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ImportStudent extends Command
{
    const OFF_SET = 0;
    const LIMIT = 100;

    /*private $off_set = self::OFF_SET;
    private $limit = self::LIMIT;
    private $page = 1;*/

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importCanvasStudent:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Student (User Role) Update with Canvas API';

    private $table = 'pas_canvas_user';

    private $data = [
        'insert' => [],
        'insert_ids' => [],
        'update' => [],
        'update_ids' => [],
        'delete' => [],
    ];

    private $users = [];
    //private $sub_accounts = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            $this->users = DB::table($this->table)
                ->pluck('canvas_user_id', 'id')
                ->toArray();

            /*$this->sub_accounts = DB::table('pas_canvas_sub_account')
                ->pluck('id', 'sub_account_id')
                ->toArray();*/

            //dd($this->sub_accounts);

            $req = new CanvasRequest();
            $req->form_params = [
                'enrollment_type' => 'student'
            ];
            $this->getStudent($req);

            $this->info('Total records Inserted(' . count($this->data['insert']) . ') and Updated(' . count($this->data['update']) . ') and Deleted(' . count($this->data['delete']) . ').');
        }
        catch (\Exception $e){
            //dd($e->getMessage());
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

    /**
     * @param CanvasRequest $req
     */
    private function getStudent(CanvasRequest $req){
        //echo '<pre>';print_r($req);die;
        $canvas_records = CanvasHelper::getInstance()->getUsersOfAccount($req);

        if (is_array($canvas_records) && count($canvas_records) > 0) {
            foreach ($canvas_records as $sub_account) {
                $data = [
                    'canvas_user_id' => $sub_account['id'],
                    'name' => $sub_account['name'],
                    'role' => 'student',
                    'sortable_name' => $sub_account['sortable_name'],
                    'short_name' => $sub_account['short_name'],
                    'sis_user_id' => $sub_account['sis_user_id'],
                    'integration_id' => $sub_account['integration_id'],
                    'sis_import_id' => $sub_account['sis_import_id'],
                    'login_id' => $sub_account['login_id'],
                ];

                if (in_array($sub_account['id'], $this->users)) {
                    $data['updated_at'] = date('Y-m-d H:i:s');
                    $this->data['update'][] = $data;
                    $this->data['update_ids'][] = $sub_account['id'];
                } else {
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $this->data['insert'][] = $data;
                    $this->data['insert_ids'][] = $sub_account['id'];
                }
            }

            if (count($canvas_records) == self::LIMIT) {
                $req->page_number += 1;
                //dump([count($canvas_records), self::LIMIT]);
                $this->info('Data Loaded of Page No: '.$req->page_number.' Found Record: '.count($canvas_records).'.');
                $this->getStudent($req);
            }else{
                //dump('Total: '.count($this->data['insert']));
                if(count($this->data['insert']) > 0){
                    //echo '<pre>';print_r($this->data['insert']);die;
                    foreach (array_chunk($this->data['insert'],1000) as $t) {
                        Db::table($this->table)->insert($t);
                    }
                }
                if(count($this->data['update']) > 0){
                    foreach (array_chunk($this->data['update'],1000) as $contacts) {
                        foreach ($contacts as $contact) {
                            Db::table($this->table)->where('canvas_user_id', '=', $contact['canvas_user_id'])->update($contact);
                        }
                    }
                }
            }

        } else {
            $this->warn('There are not update or insert user.');
        }

        $activity['action_via'] = 'cron';
        $activity['url'] = 'canvas-account-cron';
        $activity['ip_address'] = Utility::getClientIp();
        $activity['session_id'] = Session::getId();
        $activity['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
        if(isset($this->data['update']) && count($this->data['update']) > 0){
            $activity['action'] = 'update';
            $activity['new_data'] = json_encode($this->data['update']);
            $activity['ref_ids'] = implode(',', $this->data['update_ids']);
            DB::table('pas_user_activity')->insert($activity);
        }
        if(isset($this->log_data['insert']) && count($this->data['insert']) > 0){
            $activity['action'] = 'create';
            $activity['new_data'] = json_encode($this->data['insert']);
            $activity['ref_ids'] = implode(',', $this->data['insert_ids']);
            DB::table('pas_user_activity')->insert($activity);
        }
    }
}
