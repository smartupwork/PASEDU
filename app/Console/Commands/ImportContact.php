<?php

namespace App\Console\Commands;

use App\ZohoHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportContact extends Command
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

    private $existing_contacts = [];
    private $existing_partners = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importContact:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Contact from ZOHO server. Cron should be run every hours.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $our_db_contact = DB::table('pas_contact')->get()->all();
        $this->existing_contacts = array_column($our_db_contact, 'zoho_id');
        //dd($this->existing_contacts);

        $this->existing_partners = DB::table('pas_partner')->pluck('id', 'zoho_id')->toArray();

        $this->getContacts();
        $this->info('Total records Inserted('.count($this->data['insert']).') and Updated('.count($this->data['update']).').');
    }

    private function getContacts(){
        //$before_1_hour = Carbon::now('GMT-5')->subMinutes(70)->format('c');

        $zoho_response = ZohoHelper::getInstance()->fetch('Contacts', ['First_Name', 'Last_Name', 'Contact_Title', 'DOB', 'Email', 'Mobile', 'Phone', 'Contact_Active', 'Lead_Created', 'Mailing_City', 'Mailing_Country', 'Mailing_State', 'Mailing_Street', 'Mailing_Zip', 'Account_Name', 'Secondary_Email', 'Social_Security_Num'], $this->page, $this->limit);
//dd($zoho_response);
        if($zoho_response['status'] == 'error'){
            $this->error($zoho_response['message']);
            die;
        }

        //dd($students_key);
        if(count($zoho_response['data']) > 0){
            foreach ($zoho_response['data']['data'] as $contact) {
                if(!empty($contact['Social_Security_Num'])){
                    $contact['Social_Security_Num'] = sprintf('%09d', $contact['Social_Security_Num']);
                }
                    $zoho_data = [
                        'zoho_id' => $contact['id'],
                        'email' => DB::raw('AES_ENCRYPT("'.$contact['Email'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'),
                        'contact_active' => $contact['Contact_Active'],
                        'contact_title' => addslashes($contact['Contact_Title']),
                        //'contact_role' => $contact['Contact_Role'],
                        'first_name' => !empty(trim($contact['First_Name'])) ? DB::raw('AES_ENCRYPT("'.addslashes(trim($contact['First_Name'])).'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'):null,
                        'last_name' => !empty($contact['Last_Name']) ? DB::raw('AES_ENCRYPT("'.addslashes(trim($contact['Last_Name'])).'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'):null,
                        'mobile' => !empty($contact['Mobile']) ? DB::raw('AES_ENCRYPT("'.$contact['Mobile'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'):null,
                        'phone' => !empty($contact['Phone']) ? DB::raw('AES_ENCRYPT("'.$contact['Phone'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'): null,
                        'date_of_birth' => !empty($contact['DOB']) ? DB::raw('AES_ENCRYPT("'.$contact['DOB'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'):null,
                        'social_security_number' => !empty($contact['Social_Security_Num']) ? DB::raw('AES_ENCRYPT("'.$contact['Social_Security_Num'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'):null,
                        'mailing_street' => $contact['Mailing_Street'],
                        'mailing_country' => $contact['Mailing_Country'],
                        'mailing_state' => $contact['Mailing_State'],
                        'mailing_city' => $contact['Mailing_City'],
                        'mailing_zip' => $contact['Mailing_Zip'],
                        'secondary_email' => $contact['Secondary_Email'],
                        'lead_created' => $contact['Lead_Created'],
                        //'lead_source' => $contact['Lead_Source'],
                        'partner_id' => null,
                        'partner_zoho_id' => null,
                    ];

                    if(isset($contact['Account_Name']['id']) && isset($this->existing_partners[$contact['Account_Name']['id']])){
                        $zoho_data['partner_id'] = $this->existing_partners[$contact['Account_Name']['id']];
                        $zoho_data['partner_zoho_id'] = $contact['Account_Name']['id'];
                    }

                    if(in_array($contact['id'], $this->existing_contacts)){
                        $zoho_data['updated_at'] = date('Y-m-d H:i:s');
                        $this->data['update'][] = $zoho_data;
                    }else {
                        $zoho_data['created_at'] = date('Y-m-d H:i:s');
                        $this->data['insert'][] = $zoho_data;
                    }

            }

            if ($zoho_response['data']['info']['more_records']) {
                $this->off_set = $this->off_set == 0 ? $this->limit : $this->off_set + $this->limit;
                $this->page += 1;
                $this->getContacts();
                ///$this->info($partner['partner_name'].' find more.');
            }else{
                if(count($this->data['insert']) > 0){
                    //echo '<pre>';print_r($this->data['insert']);die;
                    foreach (array_chunk($this->data['insert'],1000) as $t) {
                       Db::table('pas_contact')->insert($t);
                    }
                }
                if(count($this->data['update']) > 0){
                    foreach (array_chunk($this->data['update'],1000) as $contacts) {
                        foreach ($contacts as $contact) {
                            Db::table('pas_contact')->where('zoho_id', '=', $contact['zoho_id'])->update($contact);
                        }
                    }
                }
            }
        }
    }
}
