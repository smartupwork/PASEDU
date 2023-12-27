<?php

namespace App\Console\Commands;

use App\EmailHelper;
use App\EmailRequest;
use App\Models\Partner;
use App\Models\PriceBook;
use App\UserActivityHelper;
use App\Utility;
use App\ZohoHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PartnerUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'partnerUpdate:hook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Partner Update with ZOHO Notification API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            $partners = DB::table('pas_zoho_notification')->where([
                ['module', '=', 'Accounts'],
                ['is_executed', '=', 0],
            ])
                ->limit(200)
                ->get()
                ->toArray();

            if(count($partners) > 0){
                $partner_action =[];
                //dd($partners);
                $partner_ids = array_column($partners, 'id');
                //dd($partner_ids);
                $partner_action['all'] = [];
                $partner_action['insert'] = [];
                $partner_action['update'] = [];
                $partner_action['delete'] = [];
                foreach ($partners as $partner) {
                    if($partner->operation != 'delete'){
                        $partner_action['all'][] = $partner->ids;
                    }
                    $partner_action[$partner->operation][] = $partner->ids;
                }
                //dump($partner_action);
                /*foreach ($partner_action['update'] as $zoho_id) {
                    if (($key = array_search($zoho_id, $partner_action['insert'])) !== false) {
                        unset($partner_action['update'][$key]);
                    }
                }*/

                $our_db_partners = Partner::all()->toArray();
                $partner_arr = array_column($our_db_partners, 'id', 'zoho_id');

                $our_db_price_books = PriceBook::all()->toArray();
                $our_db_price_books_arr = array_column($our_db_price_books, 'id', 'zoho_id');

                $zoho_partners = ZohoHelper::getInstance()->fetchByIds('Accounts', array_unique($partner_action['all']), ['Account_Name', 'TP_Contact_Name', 'TP_Contact_Title', 'TP_Email', 'TP_Phone', 'Phone', 'Secondary_Email', 'Department', 'WIA', 'MyCAA', 'Hosted_Site', 'Shipping_Street', 'Shipping_City', 'Shipping_State', 'Shipping_Code', 'Account_Type', 'Record_Image', 'Parent_Account', 'Price_Book', 'Mkt_Colors1', 'Mkt_Colors2', 'Mkt_Colors3', 'Mkt_Colors4', 'Mkt_Colors5', 'Mkt_Colors6', 'Mkt_Colors7', 'Mkt_Colors8', 'Mkt_Colors9', 'Mkt_Colors10']);

                $response = ['insert' => 0, 'update' => 0, 'delete' => 0];
                $log_data = ['insert' => [], 'update' => []];

                if(isset($zoho_partners['data']) && count($zoho_partners['data']) > 0){
                    foreach ($zoho_partners['data'] as $zoho_partner){
                        $data = [];
                        $data['partner_name'] = addslashes($zoho_partner['Account_Name']);
                        $data['contact_name'] = addslashes($zoho_partner['TP_Contact_Name']);
                        if($zoho_partner['Parent_Account'] && isset($zoho_partner['Parent_Account']['id'])){
                            $data['parent_partner_name'] = addslashes($zoho_partner['Parent_Account']['name']);
                            $data['parent_partner_zoho_id'] = $zoho_partner['Parent_Account']['id'];
                            $data['parent_partner_id'] = isset($partner_arr[$zoho_partner['Parent_Account']['id']]) ? $partner_arr[$zoho_partner['Parent_Account']['id']]:null;
                        }else{
                            $data['parent_partner_name'] = null;
                            $data['parent_partner_zoho_id'] = null;
                            $data['parent_partner_id'] = null;
                        }

                        if($zoho_partner['Price_Book'] && isset($zoho_partner['Price_Book']['id'])){
                            $data['price_book_id'] = isset($our_db_price_books_arr[$zoho_partner['Price_Book']['id']])? $our_db_price_books_arr[$zoho_partner['Price_Book']['id']]:null;
                            $data['price_book_zoho_id'] = $zoho_partner['Price_Book']['id'];
                        }else{
                            $price_books = ZohoHelper::getInstance()->fetchRelatedRecords('Accounts/'.$zoho_partner['id'], 'Price_Books8');
                            //dd($price_books);

                            if($price_books['status'] == 'success' && isset($price_books['data']['data']) && count($price_books['data']['data']) > 0 && isset($price_books['data']['data'][0]['PriceBooks']['id'])){
                                $price_book_zoho_id = $price_books['data']['data'][0]['PriceBooks']['id'];
                                if(isset($our_db_price_books_arr[$price_book_zoho_id])){
                                    $data['price_book_id'] = isset($our_db_price_books_arr[$price_book_zoho_id])? $our_db_price_books_arr[$price_book_zoho_id]:null;
                                    $data['price_book_zoho_id'] = $price_book_zoho_id;
                                }
                            }else{
                                $data['price_book_id'] = null;
                                $data['price_book_zoho_id'] = null;
                            }

                        }

                        $data['title'] = addslashes($zoho_partner['TP_Contact_Title']);
                        $data['phone'] = $zoho_partner['TP_Phone'];
                        $data['email'] = $zoho_partner['TP_Email'];
                        $data['pi_phone'] = !empty($zoho_partner['Phone']) ? $zoho_partner['Phone']:null;
                        $data['pi_email'] = !empty($zoho_partner['Secondary_Email']) ? $zoho_partner['Secondary_Email']:null;
                        $data['department'] = !empty($zoho_partner['Department']) ? addslashes($zoho_partner['Department']): null;
                        $data['street'] = addslashes($zoho_partner['Shipping_Street']);
                        $data['zip_code'] = $zoho_partner['Shipping_Code'];
                        $data['wia'] = $zoho_partner['WIA'] ? 1:0;
                        $data['mycaa'] = $zoho_partner['MyCAA'] ? 1:0;
                        $data['hosted_site'] = $zoho_partner['Hosted_Site'];
                        $data['record_image'] = $zoho_partner['Record_Image'];
                        if(!empty($zoho_partner['Record_Image'])){
                            $zoho_file_name = ZohoHelper::getInstance()->downloadPhoto('Accounts', $zoho_partner['id'], 'photo', "public/partners/");

                            $ext = pathinfo("public/partners/".$zoho_file_name, PATHINFO_EXTENSION);

                            $s3 = \Storage::disk('s3');
                            $file_name = uniqid() .'.'. $ext;
                            $s3filePath = "/partner/" . $file_name;
                            $s3->put($s3filePath, file_get_contents("public/partners/".$zoho_file_name), 'public');

                            $data['logo'] = $file_name;
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

                        $data['zoho_id'] = $zoho_partner['id'];
                        $data['status'] = 1;
                        $data['partner_type'] = $zoho_partner['Account_Type'];

                        if (count($partner_action['insert']) > 0 && in_array($zoho_partner['id'], $partner_action['insert'])) {
                            $is_exists = DB::table('pas_partner')
                                ->where('zoho_id', '=', $zoho_partner['id'])
                                ->count('id');

                            if($is_exists > 0){
                                ++$response['update'];
                                $log_data['update'][] = $data;
                                $log_data['update_ids'][] = $zoho_partner['id'];
                                DB::table('pas_partner')->where("zoho_id", '=', $zoho_partner['id'])->update($data);
                            }else{
                                ++$response['insert'];
                                $log_data['insert'][] = $data;
                                DB::table('pas_partner')->insert($data);
                                $log_data['insert_ids'][] = $data['zoho_id'];
                            }

                        } else if (count($partner_action['update']) > 0 && in_array($zoho_partner['id'], $partner_action['update'])) {
                            ++$response['update'];
                            $log_data['update'][] = $data;
                            $log_data['update_ids'][] = $zoho_partner['id'];
                            DB::table('pas_partner')->where("zoho_id", '=', $zoho_partner['id'])->update($data);
                        }
                    }
                }else{
                    $this->warn('There are not update or insert partner.');
                }

                if (count($partner_action['delete']) > 0) {
                    $response['delete'] = count($partner_action['delete']);
                    DB::table('pas_partner')->whereIn('zoho_id', $partner_action['delete'])->delete();
                }

                if(count($partner_ids) > 0){
                    DB::table('pas_zoho_notification')->whereIn("id", $partner_ids)->delete();
                }

                $leeds_data['action_via'] = 'cron';
                $leeds_data['url'] = 'cron-partner';
                $leeds_data['ip_address'] = Utility::getClientIp();
                $leeds_data['session_id'] = Session::getId();
                $leeds_data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
                if(isset($log_data['update']) && count($log_data['update']) > 0){
                    $leeds_data['action'] = 'update';
                    //$leeds_data['old_data'] = json_encode($old_data);
                    $leeds_data['new_data'] = json_encode($log_data['update']);
                    $leeds_data['ref_ids'] = implode(',', $log_data['update_ids']);
                    DB::table('pas_user_activity')->insert($leeds_data);
                }
                if(isset($log_data['insert']) && count($log_data['insert']) > 0){
                    $leeds_data['action'] = 'create';
                    //$leeds_data['old_data'] = json_encode($old_data);
                    $leeds_data['new_data'] = json_encode($log_data['insert']);
                    $leeds_data['ref_ids'] = implode(',', $log_data['insert_ids']);
                    //dump($leeds_data);
                    DB::table('pas_user_activity')->insert($leeds_data);
                }

                echo '<pre>';print_r($response);
                $this->info('Partner successfully updated.');
                //$this->info('Total records Inserted('.count($this->data['insert']).') and Updated('.count($this->data['update']).' and Deleted('.count($hook_action['delete']).').');
            }else{
                $this->info('Data not found to update/insert/delete into notification API.');
            }
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
    }
}
