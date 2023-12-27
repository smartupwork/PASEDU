<?php

namespace App\Console\Commands;

use App\Models\Partner;
use App\Models\PriceBook;
use App\ZohoHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Exception;

class ImportPartner extends Command
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

    private $existing_partners_zid = [];
    private $existing_partners_id = [];

    private $our_db_price_books = [];
    private $our_db_price_books_arr = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importPartner:cron';

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
        $our_db_partners = Partner::all()->toArray();
        $this->existing_partners_zid = array_column($our_db_partners, 'partner_name', 'zoho_id');
        $this->existing_partners_id = array_column($our_db_partners, 'id', 'zoho_id');

        $this->our_db_price_books = PriceBook::all()->toArray();
        $this->our_db_price_books_arr = array_column($this->our_db_price_books, 'id', 'zoho_id');

        $this->getPartners();
        $this->info('Total records Inserted('.count($this->data['insert']).') and Updated('.count($this->data['update']).').');
    }

    private function getPartners(){
        //$before_70_min = Carbon::now('GMT-5')->subMinutes(70)->format('c');

        $zoho_partners = ZohoHelper::getInstance()->fetch('Accounts', ['Account_Name', 'TP_Contact_Name', 'TP_Contact_Title', 'TP_Email', 'TP_Phone', 'Phone', 'Secondary_Email', 'Department', 'WIA', 'MyCAA', 'Hosted_Site', 'Shipping_Street', 'Shipping_City', 'Shipping_State', 'Shipping_Code', 'Account_Type', 'Record_Image', 'Parent_Account', 'Price_Book', 'Mkt_Colors1', 'Mkt_Colors2', 'Mkt_Colors3', 'Mkt_Colors4', 'Mkt_Colors5', 'Mkt_Colors6', 'Mkt_Colors7', 'Mkt_Colors8', 'Mkt_Colors9', 'Mkt_Colors10', 'Contact_Name', 'Contact_Title', 'Campus_Name_if_applicable', 'Billing_Street', 'Billing_Address_2', 'Billing_City', 'Billing_Code', 'Billing_Country', 'Billing_State', 'TP_Website'], $this->page, $this->limit);
//dump($zoho_partners);die;
        if(count($zoho_partners['data']) > 0) {
            foreach ($zoho_partners['data']['data'] as $zoho_partner) {

                /*$contacts = ZohoHelper::getInstance()->fetchRelatedRecords('Accounts/'.$zoho_partner['id'], 'Contacts');

                $contact_arr = [];
                if($contacts['status'] == 'success' && isset($contacts['data']['data']) && count($contacts['data']['data']) > 0){
                    foreach ($contacts['data']['data'] as $contact) {
                        $contact_arr[] = [
                            'id' => $contact['id'],
                            'First_Name' => $contact['First_Name'],
                            'Last_Name' => $contact['Last_Name'],
                            'Email' => $contact['Email'],
                            'Contact_Role' => $contact['Contact_Role'],
                            'Mailing_City' => $contact['Mailing_City'],
                            'Mailing_State' => $contact['Mailing_State'],
                            'Mailing_Country' => $contact['Mailing_Country'],
                            'Mailing_Street' => $contact['Mailing_Street'],
                            'Phone' => $contact['Phone'],
                            'Skype_ID' => $contact['Skype_ID'],
                            'Contact_Active' => $contact['Contact_Active'],
                        ];
                    }
                }
                $data['contacts'] = count($contact_arr) > 0 ? json_encode($contact_arr):null;*/

                $data['partner_name'] = !empty($zoho_partner['Account_Name']) ? addslashes($zoho_partner['Account_Name']):null;
                $data['tp_contact_name'] = !empty($zoho_partner['TP_Contact_Name']) ? addslashes($zoho_partner['TP_Contact_Name']):null;
                if($zoho_partner['Parent_Account'] && isset($zoho_partner['Parent_Account']['id'])){
                    $data['parent_partner_name'] = addslashes($zoho_partner['Parent_Account']['name']);
                    $data['parent_partner_zoho_id'] = $zoho_partner['Parent_Account']['id'];
                    $data['parent_partner_id'] = isset($this->existing_partners_id[$zoho_partner['Parent_Account']['id']]) ? $this->existing_partners_id[$zoho_partner['Parent_Account']['id']]:null;
                }else{
                    $data['parent_partner_name'] = null;
                    $data['parent_partner_zoho_id'] = null;
                    $data['parent_partner_id'] = null;
                }

                if($zoho_partner['Price_Book'] && isset($zoho_partner['Price_Book']['id'])){
                    $data['price_book_id'] = isset($this->our_db_price_books_arr[$zoho_partner['Price_Book']['id']])? $this->our_db_price_books_arr[$zoho_partner['Price_Book']['id']]:null;
                    $data['price_book_zoho_id'] = $zoho_partner['Price_Book']['id'];
                }else{
                    $price_books = ZohoHelper::getInstance()->fetchRelatedRecords('Accounts/'.$zoho_partner['id'], 'Price_Books8');
                    //dd($price_books);

                    if($price_books['status'] == 'success' && isset($price_books['data']['data']) && count($price_books['data']['data']) > 0 && isset($price_books['data']['data'][0]['PriceBooks']['id'])){
                        $price_book_zoho_id = $price_books['data']['data'][0]['PriceBooks']['id'];
                        if(isset($this->our_db_price_books_arr[$price_book_zoho_id])){
                            $data['price_book_id'] = isset($this->our_db_price_books_arr[$price_book_zoho_id])? $this->our_db_price_books_arr[$price_book_zoho_id]:null;
                            $data['price_book_zoho_id'] = $price_book_zoho_id;
                        }
                    }else{
                        $data['price_book_id'] = null;
                        $data['price_book_zoho_id'] = null;
                    }
                }

                $data['title'] = !empty($zoho_partner['TP_Contact_Title']) ? addslashes($zoho_partner['TP_Contact_Title']): null;
                $data['phone'] = !empty($zoho_partner['TP_Phone']) ? $zoho_partner['TP_Phone']:null;
                $data['email'] = !empty($zoho_partner['TP_Email']) ? $zoho_partner['TP_Email']:null;
                $data['pi_phone'] = !empty($zoho_partner['Phone']) ? $zoho_partner['Phone']:null;
                $data['pi_email'] = !empty($zoho_partner['Secondary_Email']) ? $zoho_partner['Secondary_Email']:null;
                $data['department'] = !empty($zoho_partner['Department']) ? addslashes($zoho_partner['Department']): null;
                $data['street'] = !empty($zoho_partner['Shipping_Street']) ? addslashes($zoho_partner['Shipping_Street']):null;
                $data['zip_code'] = !empty($zoho_partner['Shipping_Code']) ? addslashes($zoho_partner['Shipping_Code']):null;
                $data['wia'] = $zoho_partner['WIA'] ? 1:0;
                $data['mycaa'] = $zoho_partner['MyCAA'] ? 1:0;
                $data['hosted_site'] = !empty($zoho_partner['Hosted_Site']) ? $zoho_partner['Hosted_Site']:null;
                $data['record_image'] = !empty($zoho_partner['Record_Image']) ? $zoho_partner['Record_Image']:null;
                $data['zoho_id'] = !empty($zoho_partner['id']) ? $zoho_partner['id']:null;
                $data['status'] = 1;
                $data['partner_type'] = !empty($zoho_partner['Account_Type']) ? addslashes($zoho_partner['Account_Type']): null;

                $logo_upload = true;
                if(!empty($data['zoho_id'])){
                    $partner_detail = DB::table('pas_partner')
                        ->where('zoho_id', '=', $data['zoho_id'])
                        ->get()->first();
                    if($partner_detail && $partner_detail->record_image == $zoho_partner['Record_Image']){
                        $logo_upload = false;
                    }
                }

                if(!empty($zoho_partner['Record_Image']) && $logo_upload){
                    try{
                        $zoho_file_name = ZohoHelper::getInstance()->downloadPhoto('Accounts', $zoho_partner['id'], 'photo', "public");

                        $raw_file = Storage::disk('public')->get($zoho_file_name);

                        $ext = pathinfo(storage_path("public/").$zoho_file_name, PATHINFO_EXTENSION);

                        $s3 = \Storage::disk('s3');
                        $file_name = uniqid() .'.'. $ext;
                        $s3filePath = "/partner/" . $file_name;
                        $s3->put($s3filePath, $raw_file, 'public');
                        $data['logo'] = $file_name;
                        Storage::disk('public')->delete($zoho_file_name);
                    }catch (Exception $e){

                    }
                }elseif(empty($zoho_partner['Record_Image'])){
                    $data['logo'] = null;
                }elseif($partner_detail){
                    $data['logo'] = $partner_detail->logo;
                }else{
                    $data['logo'] = null;
                }

                $data['mkt_colors1'] = $zoho_partner['Mkt_Colors1'];
                $data['mkt_colors2'] = $zoho_partner['Mkt_Colors2'];
                $data['mkt_colors3'] = $zoho_partner['Mkt_Colors3'];
                $data['mkt_colors4'] = $zoho_partner['Mkt_Colors4'];
                $data['mkt_colors5'] = $zoho_partner['Mkt_Colors5'];
                $data['mkt_colors6'] = $zoho_partner['Mkt_Colors6'];
                $data['mkt_colors7'] = $zoho_partner['Mkt_Colors7'];
                $data['mkt_colors8'] = $zoho_partner['Mkt_Colors8'];
                $data['mkt_colors9'] = $zoho_partner['Mkt_Colors9'];
                $data['mkt_colors10'] = $zoho_partner['Mkt_Colors10'];


                $data['contact_name'] = $zoho_partner['Contact_Name'];
                $data['contact_title'] = $zoho_partner['Contact_Title'];
                $data['campus_name_if_applicable'] = $zoho_partner['Campus_Name_if_applicable'];
                $data['billing_street'] = $zoho_partner['Billing_Street'];
                $data['billing_address_2'] = $zoho_partner['Billing_Address_2'];
                $data['billing_city'] = $zoho_partner['Billing_City'];
                $data['billing_code'] = $zoho_partner['Billing_Code'];
                $data['billing_country'] = $zoho_partner['Billing_Country'];
                $data['billing_state'] = $zoho_partner['Billing_State'];
                $data['tp_website'] = $zoho_partner['TP_Website'];
                //dump($zoho_response);die;

                if(isset($this->existing_partners_zid[$zoho_partner['id']])){
                    $data['updated_at'] = date('Y-m-d H:i:s');
                    $this->data['update'][] = $data;
                }else {
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $this->data['insert'][] = $data;
                }
            }

            if ($zoho_partners['data']['info']['more_records']) {
                $this->off_set = $this->off_set == 0 ? $this->limit : $this->off_set + $this->limit;
                $this->page += 1;
                $this->getPartners();
                ///$this->info($partner['partner_name'].' find more.');
            }else{
                //dd($this->data);
                if(count($this->data['insert']) > 0){
                    foreach ($this->data['insert'] as $zoho_partner) {
                        DB::table('pas_partner')->insert($zoho_partner);
                    }
                }
                if(count($this->data['update']) > 0){
                    try{
                        foreach ($this->data['update'] as $zoho_partner) {
                            DB::table('pas_partner')->where([["zoho_id", '=', $zoho_partner['zoho_id']]])->update($zoho_partner);
                        }
                    }catch(\Exception $e){
                        dd($e->getMessage());
                    }
                }
            }

        }
    }
}
