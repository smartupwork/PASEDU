<?php

namespace App\Console\Commands;

use App\Models\Program;
use App\ZohoHelper;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Prestashop;
use Protechstudio\PrestashopWebService\PrestaShopWebserviceException;

class ImportProgram extends Command
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

    private $existing_programs = [];
    private $existing_categories = [];
    private $ps_shop = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importProgram:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Partner from ZOHO server.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->existing_programs = DB::table('pas_program')->pluck('name', 'zoho_id')->toArray();

        $this->getPrograms();
        $this->info('Total records Inserted('.count($this->data['insert']).') and Updated('.count($this->data['update']).').');
    }

    private function getPrograms(){
        //$before_70_min = Carbon::now('GMT-5')->subMinutes(70)->format('c');

        $zoho_programs = ZohoHelper::getInstance()->fetch('Products', ['Product_Name', 'Program_Sub_Title', 'Product_Category', 'Product_Code', 'Hours', 'Duration', 'Level', 'Retail_Wholesale', 'Owner', 'Program_Type', 'Unit_Price', 'Vendor_Name', 'Program_Status', 'Displayed_On', 'Service_Item_not_program', 'Description', 'Certification_Included', 'Layout', 'Featured_Course', 'Best_Seller', 'Tag_Line', 'Prerequisites', 'Outline', 'Externship_Included', 'Approved_Offering', 'Language', 'CE_Units', 'Level', 'Occupation', 'Feature_Tag_Line', 'Career_Description', 'Median_Salary', 'Job_Growth', 'Right_Career', 'Website_Short_Description', 'Learning_Objectives', 'Support_Description', 'Average_Completion', 'Avg_Completion_Time', 'Required_Materials', 'Technical_Requirements', 'Accreditation', 'Certification_Benefits', 'General_Features_and_Benefits', 'Demo_URL', 'Audience', 'Delivery_Methods_Available', 'Certification', 'Prepares_for_Certification', 'MyCAA_Description'], $this->page, $this->limit);

        if($zoho_programs['status'] == 'error'){
            $this->error($zoho_programs['message']);
            die;
        }

        dump("Total In CRM: ".count($zoho_programs['data']['data']));

        /*$zoho_program = array_column($zoho_program, 'id', 'id');
        $our_db_program = array_column($our_db_program, 'zoho_id', 'id');
        $to_be_added = array_diff($zoho_program, $our_db_program);
        $to_be_deleted = array_diff($our_db_program, $zoho_program);
        dump([$to_be_added, $to_be_deleted]);die;*/

        //dump($zoho_programs['data']);die;

        if(count($zoho_programs['data']) > 0){
            foreach ($zoho_programs['data']['data'] as $zoho_program) {
                $inclusion = null;
                $program_inclusion = ZohoHelper::getInstance()->fetchSubForm('Products', $zoho_program['id']);
                if($program_inclusion['status'] == 'success' && isset($program_inclusion['data']['data'][0]['Certifications_Regulatory'][0])){
                    $inclusion = json_encode($program_inclusion['data']['data'][0]['Certifications_Regulatory']);
                }

                $duration_type = null;
                $duration_value = null;
                if(!empty($zoho_program['Duration'])){
                    $arr_duration = explode(' ', $zoho_program['Duration']);
                    if(is_array($arr_duration)){
                        if(count($arr_duration) == 2){
                            $duration_value = trim($arr_duration[0]);
                            $duration_type = strtolower(trim($arr_duration[1]));
                        }else if(count($arr_duration) == 3){
                            $duration_value = trim($arr_duration[0]);
                            $duration_type = strtolower(trim($arr_duration[2]));
                        }

                    }
                }

                $zoho_data = [
                    'zoho_id' => $zoho_program['id'],
                    'name' => addslashes($zoho_program['Product_Name']),
                    'sub_title' => addslashes($zoho_program['Program_Sub_Title']),
                    'category' => addslashes($zoho_program['Product_Category']),
                    'program_type' => addslashes($zoho_program['Program_Type']),
                    'code' => addslashes($zoho_program['Product_Code']),
                    'hours' => $zoho_program['Hours'],
                    'duration_type' => $duration_type,
                    'duration_value' => $duration_value,
                    'level' => addslashes($zoho_program['Level']),
                    'occupation' => addslashes($zoho_program['Occupation']),
                    'feature_tag_line' => isset($zoho_program['Feature_Tag_Line']) ? addslashes($zoho_program['Feature_Tag_Line']):null,
                    'career_description' => addslashes($zoho_program['Career_Description']),
                    'median_salary' => addslashes($zoho_program['Median_Salary']),
                    'job_growth' => addslashes($zoho_program['Job_Growth']),
                    'right_career' => addslashes($zoho_program['Right_Career']),
                    'website_short_description' => addslashes($zoho_program['Website_Short_Description']),
                    'learning_objectives' => addslashes($zoho_program['Learning_Objectives']),
                    'support_description' => addslashes($zoho_program['Support_Description']),
                    'retail_wholesale' => addslashes($zoho_program['Retail_Wholesale']),
                    'description' => addslashes($zoho_program['Description']),
                    'service_item_not_program' => $zoho_program['Service_Item_not_program'] ? 1:0,
                    'displayed_on' => addslashes($zoho_program['Displayed_On']),
                    'unite_price' => $zoho_program['Unit_Price'],
                    'certification_included' => addslashes($zoho_program['Certification_Included']),
                    'status' => addslashes($zoho_program['Program_Status']),
                    'layout' => isset($zoho_program['Layout']['name']) ? $zoho_program['Layout']['name']:null,
                    'is_featured' => $zoho_program['Featured_Course'] ? 1:0,
                    'is_best_seller' => $zoho_program['Best_Seller'] ? 1:0,
                    'tag_line' => addslashes($zoho_program['Tag_Line']),
                    'prerequisites' => addslashes($zoho_program['Prerequisites']),
                    'outline' => addslashes($zoho_program['Outline']),
                    'externship_included' => addslashes($zoho_program['Externship_Included']),
                    'approved_offering' => json_encode($zoho_program['Approved_Offering']),
                    'language' => $zoho_program['Language'],
                    'ce_units' => $zoho_program['CE_Units'],
                    'certification_inclusion' => $inclusion,
                    'vendor_name' => isset($zoho_program['Vendor_Name']['name']) ? addslashes($zoho_program['Vendor_Name']['name']):null,
                    'vendor_id' => isset($zoho_program['Vendor_Name']['id']) ? addslashes($zoho_program['Vendor_Name']['id']):null,
                    'average_completion' => addslashes($zoho_program['Average_Completion']),
                    'avg_completion_time' => isset($zoho_program['Avg_Completion_Time']) ? addslashes($zoho_program['Avg_Completion_Time']):null,
                    'required_materials' => addslashes($zoho_program['Required_Materials']),
                    'technical_requirements' => addslashes($zoho_program['Technical_Requirements']),
                    'accreditation' => addslashes($zoho_program['Accreditation']),
                    'certification_benefits' => addslashes($zoho_program['Certification_Benefits']),
                    'general_features_and_benefits' => addslashes($zoho_program['General_Features_and_Benefits']),
                    'demo_url' => isset($zoho_program['Demo_URL']) ? addslashes($zoho_program['Demo_URL']):null,
                    'audience' => addslashes($zoho_program['Audience']),
                    'delivery_methods_available' => json_encode($zoho_program['Delivery_Methods_Available']),
                    'certification' => addslashes($zoho_program['Certification']),
                    'prepares_for_certification' => addslashes($zoho_program['Prepares_for_Certification']),
                    'mycaa_description' => addslashes($zoho_program['MyCAA_Description']),
                ];

                if(isset($this->existing_programs[$zoho_program['id']])){
                    $zoho_data['updated_at'] = date('Y-m-d H:i:s');
                    $this->data['update'][] = $zoho_data;
                }else {
                    $zoho_data['created_at'] = date('Y-m-d H:i:s');
                    $this->data['insert'][] = $zoho_data;
                }

            }

            if (false && $zoho_programs['data']['info']['more_records']) {
                dump('Page No: '.$this->page);
                $this->off_set = $this->off_set == 0 ? $this->limit : $this->off_set + $this->limit;
                $this->page += 1;
                $this->getPrograms();
                ///$this->info($partner['partner_name'].' find more.');
            }else{
                //dump($this->data);
                if(count($this->data['insert']) > 0){
                    foreach ($this->data['insert'] as $zoho_program) {
                        DB::table('pas_program')->insert($zoho_program);
                    }
                }
                if(count($this->data['update']) > 0){
                    foreach ($this->data['update'] as $zoho_program) {
                        DB::table('pas_program')->where([["zoho_id", '=', $zoho_program['zoho_id']]])->update($zoho_program);
                    }
                }

            }

        }
    }

}
