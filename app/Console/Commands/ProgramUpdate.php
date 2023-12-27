<?php

namespace App\Console\Commands;

use App\EmailHelper;
use App\EmailRequest;
use App\Utility;
use App\ZohoHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ProgramUpdate extends Command
{
    const ZOHO_ID = 'zoho_id';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'programUpdate:hook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Program Update with ZOHO Notification API';

    private $hook_table = 'pas_zoho_notification';

    private $module_name = 'Products';

    private $table = 'pas_program';

    private $log_data = ['insert' => [], 'update' => [], 'insert_ids' => [], 'update_ids' => []];

    const INSERT = 'insert';
    const UPDATE = 'update';
    const DELETE = 'delete';

    private $module_fields = [
        'Product_Name',
        'Product_Category',
        'Product_Code',
        'Hours',
        'Duration',
        'Retail_Wholesale',
        'Owner',
        'Program_Type',
        'Unit_Price',
        'Vendor_Name',
        'Program_Status',
        'Displayed_On',
        'Service_Item_not_program',
        'Description',
        'Certification_Included',
        'Layout',
        'Featured_Course',
    ];


    private $data = [
        self::INSERT => [],
        self::UPDATE => [],
        self::DELETE => [],
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $hooks = DB::table($this->hook_table)->where([
            ['module', '=', $this->module_name],
            ['is_executed', '=', 0],
        ])
            ->limit(200)
            ->get()
            ->toArray();

        if(count($hooks) > 0) {
            try{
                $hook_action = [];
                $hooks_to_be_delete = array_column($hooks, 'id');
                $hook_action['all'] = [];
                $hook_action[self::INSERT] = [];
                $hook_action[self::UPDATE] = [];
                $hook_action[self::DELETE] = [];
                foreach ($hooks as $hook) {
                    if ($hook->operation != self::DELETE) {
                        $hook_action['all'][] = $hook->ids;
                    }
                    $hook_action[$hook->operation][] = $hook->ids;
                }

                $zoho_records = ZohoHelper::getInstance()->fetchByIds($this->module_name, array_unique($hook_action['all']), $this->module_fields);

                if (isset($zoho_records['data']) && count($zoho_records['data']) > 0) {
                    foreach ($zoho_records['data'] as $zoho_record) {
                        $duration_type = null;
                        $duration_value = null;
                        if(!empty($zoho_record['Duration'])){
                            $arr_duration = explode(' ', $zoho_record['Duration']);
                            if(is_array($arr_duration) && count($arr_duration) > 0){
                                if(count($arr_duration) == 2){
                                    $duration_value = trim($arr_duration[0]);
                                    $duration_type = strtolower(trim($arr_duration[1]));
                                }else if(count($arr_duration) == 3){
                                    $duration_value = trim($arr_duration[0]);
                                    $duration_type = strtolower(trim($arr_duration[2]));
                                }
                            }
                        }

                        $data_to_insert = [
                            'name' => addslashes($zoho_record['Product_Name']),
                            self::ZOHO_ID => $zoho_record['id'],
                            'category' => addslashes($zoho_record['Product_Category']),
                            'program_type' => addslashes($zoho_record['Program_Type']),
                            'code' => $zoho_record['Product_Code'],
                            'hours' => $zoho_record['Hours'],
                            'duration_type' => $duration_type,
                            'duration_value' => $duration_value,
                            'retail_wholesale' => $zoho_record['Retail_Wholesale'],
                            'description' => addslashes($zoho_record['Description']),
                            'service_item_not_program' => $zoho_record['Service_Item_not_program'] ? 1 : 0,
                            'displayed_on' => addslashes($zoho_record['Displayed_On']),
                            'unite_price' => $zoho_record['Unit_Price'],
                            'certification_included' => addslashes($zoho_record['Certification_Included']),
                            'status' => addslashes($zoho_record['Program_Status']),
                            'layout' => isset($zoho_record['Layout']['name']) ? addslashes($zoho_record['Layout']['name']):null,
                            'is_featured' => $zoho_record['Featured_Course'] ? 1:0,
                        ];

                        if (count($hook_action[self::INSERT]) > 0 && in_array($zoho_record['id'], $hook_action[self::INSERT])) {
                            $data_to_insert['created_at'] = date('Y-m-d H:i:s');
                            $this->data[self::INSERT][] = $data_to_insert;
                            $this->log_data['insert'][] = $data_to_insert;
                            $this->log_data['insert_ids'][] = $zoho_record['id'];
                        } else if (count($hook_action[self::UPDATE]) > 0 && in_array($zoho_record['id'], $hook_action[self::UPDATE])) {
                            $data['updated_at'] = date('Y-m-d H:i:s');
                            $this->data[self::UPDATE][] = $data_to_insert;
                            $this->log_data['update'][] = $data_to_insert;
                            $this->log_data['update_ids'][] = $zoho_record['id'];
                        }
                    }
                }

                $this->updateProgram($hook_action, $hooks_to_be_delete);
                $this->info('Total records Inserted(' . count($this->data[self::INSERT]) . ') and Updated(' . count($this->data[self::UPDATE]) . ' and Deleted(' . count($hook_action[self::DELETE]) . ').');
            }catch (\Exception $e){
                //dd($e->getMessage());
                $email_req = new EmailRequest();
                $email_req
                    ->setTo([
                        [$_ENV['DEVELOPER_EMAIL_FIRST'], "Xoom Web Development"],
                        //[$_ENV['DEVELOPER_EMAIL_SECOND'], "Info Xoom Web Development"],
                    ])
                    ->setSubject($_ENV['APP_ENV'].' PAS ERROR :: '.__CLASS__)
                    ->setBody('Line No. '.$e->getLine().' MSG. '.$e->getMessage())
                    //->setCc([[$student->email, $student->student_name]])
                    ->setLogSave(false);

                $email_helper = new EmailHelper($email_req);
                $email_helper->sendEmail();
            }
        }else{
            $this->info('Data not found to update/insert/delete into notification API.');
        }

    }

    private function updateProgram($hook_action, $hooks_to_be_delete){
        if (count($this->data[self::INSERT]) > 0) {
            DB::table($this->table)->insert($this->data[self::INSERT]);
        }

        if (count($this->data[self::UPDATE]) > 0) {
            foreach ($this->data[self::UPDATE] as $hook_update) {
                DB::table($this->table)
                    ->where(self::ZOHO_ID, '=', $hook_update[self::ZOHO_ID])
                    ->update($hook_update);
            }
        }

        if (count($hook_action[self::DELETE]) > 0) {
            DB::table($this->table)->whereIn(self::ZOHO_ID, $hook_action[self::DELETE])->delete();
        }

        if (count($hooks_to_be_delete) > 0) {
            DB::table($this->hook_table)->whereIn("id", $hooks_to_be_delete)->delete();
        }

        $leeds_data['action_via'] = 'cron';
        $leeds_data['url'] = 'cron-program';
        $leeds_data['ip_address'] = Utility::getClientIp();
        $leeds_data['session_id'] = Session::getId();
        $leeds_data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
        if(isset($this->log_data['update']) && count($this->log_data['update']) > 0){
            $leeds_data['action'] = 'update';
            //$leeds_data['old_data'] = json_encode($old_data);
            $leeds_data['new_data'] = json_encode($this->log_data['update']);
            $leeds_data['ref_ids'] = implode(',', $this->log_data['update_ids']);
            DB::table('pas_user_activity')->insert($leeds_data);
        }
        if(isset($this->log_data['insert']) && count($this->log_data['insert']) > 0){
            $leeds_data['action'] = 'create';
            //$leeds_data['old_data'] = json_encode($old_data);
            $leeds_data['new_data'] = json_encode($this->log_data['insert']);
            $leeds_data['ref_ids'] = implode(',', $this->log_data['insert_ids']);
            //dump($leeds_data);
            DB::table('pas_user_activity')->insert($leeds_data);
        }

    }
}
