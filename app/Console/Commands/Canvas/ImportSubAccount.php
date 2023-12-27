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

class ImportSubAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importCanvasSubAccount:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sub Account(Partners) Update with Canvas API';

    private $table = 'pas_canvas_sub_account';

    private $data = [
        'insert' => [],
        'insert_ids' => [],
        'update' => [],
        'update_ids' => [],
        'delete' => [],
    ];

    private $sub_accounts = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            $this->sub_accounts = DB::table($this->table)
                ->pluck('sub_account_id', 'id')
                ->toArray();

            //dd($this->sub_accounts);

            $can_req = new CanvasRequest();
            $can_req->account_id = CanvasHelper::SUB_ACCOUNT;
            $canvas_records = CanvasHelper::getInstance()->getSubAccountsOfAccount($can_req);
            //dd($canvas_records);

            if (is_array($canvas_records) && count($canvas_records) > 0) {
                foreach ($canvas_records as $sub_account) {
                    $data = [
                        'sub_account_id' => $sub_account['id'],
                        'parent_account_id' => $sub_account['parent_account_id'],
                        'root_account_id' => $sub_account['root_account_id'],
                        'name' => $sub_account['name'],
                        'work_status' => $sub_account['workflow_state'],
                        'uuid' => $sub_account['uuid'],
                        'default_time_zone' => $sub_account['default_time_zone'],
                    ];

                    if (in_array($sub_account['id'], $this->sub_accounts)) {
                        $data['updated_at'] = date('Y-m-d H:i:s');
                        $this->data['update'][] = $data;
                        $this->data['update_ids'][] = $sub_account['id'];
                    } else {
                        $data['created_at'] = date('Y-m-d H:i:s');
                        $this->data['insert'][] = $data;
                        $this->data['insert_ids'][] = $sub_account['id'];
                    }
                }
            } else {
                $this->warn('There are not update or insert deals.');
            }

            //dd($this->data);

            if (count($this->data['insert']) > 0) {
                DB::table($this->table)->insert($this->data['insert']);
            }

            if (count($this->data['update']) > 0) {
                foreach ($this->data['update'] as $account_update) {
                    DB::table($this->table)
                        ->where('sub_account_id', '=', $account_update['sub_account_id'])
                        ->update($account_update);
                }
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

            $this->info('Total records Inserted(' . count($this->data['insert']) . ') and Updated(' . count($this->data['update']) . ') and Deleted(' . count($this->data['delete']) . ').');
        }
        catch (\Exception $e){
                dd($e->getMessage());
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
