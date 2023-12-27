<?php

namespace App\Console\Commands;

use App\ZohoHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportFieldsMeta extends Command
{
    private $data = [
        'insert' => [],
        'update' => [],
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importFieldsMeta:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $fields_module_key = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->fields_module_key = DB::table('zoho_field_meta')->pluck('id', 'module')->toArray();

        $this->importFieldsMeta();
        $this->info('Total records Inserted('.count($this->data['insert']).') and Updated('.count($this->data['update']).').');
    }

    private function importFieldsMeta(){
        $fields = ZohoHelper::getInstance()->fetchFieldMeta('Deals');
        //echo '<pre>';print_r($fields);die;
        foreach ($fields['data']['fields'] as $field) {
            if(count($field['pick_list_values']) > 0){
                if(isset($this->fields_module_key[$field['field_label']])){
                    $this->data['update'][] = [
                        'id' => $this->fields_module_key[$field['field_label']],
                        'module' => $field['display_label'],
                        'field_label' => $field['field_label'],
                        'pick_list_values' => json_encode($field['pick_list_values']),
                        'all_data' => json_encode($field),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                }else{
                    $this->data['insert'][] = [
                        'module' => $field['display_label'],
                        'field_label' => $field['field_label'],
                        'pick_list_values' => json_encode($field['pick_list_values']),
                        'all_data' => json_encode($field),
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                }
            }

        }

        //dd($this->data);
        if(count($this->data['insert']) > 0){
            DB::table('zoho_field_meta')->insert($this->data['insert']);
        }
        if(count($this->data['update']) > 0){
            try{
                foreach ($this->data['update'] as $zoho_fields) {
                    $id = $zoho_fields['id'];
                    unset($zoho_fields['id']);
                    DB::table('zoho_field_meta')->where([["id", '=', $id]])->update($zoho_fields);
                }
            }catch(\Exception $e){
                dd($e->getMessage());
            }
        }

    }
}
