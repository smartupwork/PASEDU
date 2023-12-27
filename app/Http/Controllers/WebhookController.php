<?php
namespace App\Http\Controllers;
use App\Models\Program;
use App\Utility;
use App\ZohoHelper;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Exception;
use Session;
use Config;
use Lang;
require base_path("vendor/autoload.php");
use Cookie;
use Prestashop;

class WebhookController extends Controller
{
    public function updateEnrollment(Request $request) {
        try{
            $post = $request->all();
            $this->saveResponse('SalesOrders', $post);

            $partners_key = DB::table('pas_partner')->pluck('id', 'zoho_id')->toArray();
            $students_key = DB::table('pas_student')->pluck('id', 'zoho_id')->toArray();
            $contacts_key = DB::table('pas_contact')->pluck('id', 'zoho_id')->toArray();

            if($post){
                $post = Utility::addSlashes($post);// dd($post)


                $post['student_id'] = (isset($post['student_zoho_id']) && isset($students_key[$post['student_zoho_id']])) ? $students_key[$post['student_zoho_id']] : null;
                $post['partner_id'] = isset($partners_key[$post['partner_zoho_id']]) ? $partners_key[$post['partner_zoho_id']] : null;
                $post['contact_id'] = isset($contacts_key[$post['contact_zoho_id']]) ? $contacts_key[$post['contact_zoho_id']]:null;

                $existCount = DB::table('pas_enrollment')->where('zoho_id', '=', $post['zoho_id'])->count('id');
                if($existCount == 1){
                    $post['updated_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_enrollment')
                        ->where('zoho_id', '=', $post['zoho_id'])
                        ->update($post);
                    return response()->json(['status' => 'success', 'message' => 'Enrollment updated successfully.']);
                } else {
                    $post['created_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_enrollment')
                        ->insert($post);
                    return response()->json(['status' => 'success', 'message' => 'Enrollment created successfully.']);
                }
            }
            return response()->json(['status' => 'fail', 'message' => 'Program updated failed.']);
        }catch(\Exception $e){
            $this->saveException('SalesOrders', $e);
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()]);
        }

    }

    public function deleteEnrollment(Request $request) {
        try{
            $post = $request->all();
            $this->saveResponse('SalesOrders', $post, 'delete');
            if($post){
                DB::table('pas_enrollment')->where('zoho_id', '=', $post['zoho_id'])->delete();
                return response()->json(['status' => 'fail', 'message' => 'Enrollment deleted successfully.']);
            }
            return response()->json(['status' => 'fail', 'message' => 'Enrollment deleted failed.']);
        }catch(\Exception $e){
            $this->saveException('SalesOrders', $e);
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()]);
        }

    }

    public function updateProgram(Request $request) {
        try{
            $post_webhook = $request->all();
            //echo '<pre>';print_r($post_webhook);die;
            $this->saveResponse('Products', $post_webhook);
            if($post_webhook){
                $pas_program = Program::loadPasProgramData($post_webhook);
                //echo '<pre>';print_r($pas_program);die;

                $existCount = DB::table('pas_program')
                    ->where('zoho_id', '=', $pas_program['zoho_id'])
                    ->count('id');

                $ps_shops = DB::connection('we_shop')
                    ->table('ps_shop AS s')
                    ->where('s.active', '=', 1)
                    ->get()->all();

                if($existCount == 1){
                    $pas_program['updated_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_program')
                        ->where('zoho_id', '=', $pas_program['zoho_id'])
                        ->update($pas_program);

                    (new Program())->savePrestaShopProduct($pas_program, $ps_shops);
                    Program::rebuildSearch();
                    return response()->json(['status' => 'success', 'message' => 'Program updated successfully.']);
                } elseif($existCount == 0) {
                    $pas_program['created_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_program')
                        ->insert($pas_program);

                    //(new Program())->savePrestaShopProduct($pas_program, $ps_shops);
                    //Program::rebuildSearch();
                    return response()->json(['status' => 'success', 'message' => 'Program created successfully.']);
                }
            }
            return response()->json(['status' => 'fail', 'message' => 'Program updated failed.']);
        }catch(\Exception $e){
            $this->saveException('Products', $e);
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()]);
        }

    }

    public function deleteProgram(Request $request) {
        try{
            $post = $request->all();
            $this->saveResponse('Products', $post, 'delete');
            if($post){
                DB::table('pas_price_book_program_map')->where('program_zoho_id', '=', $post['zoho_id'])->delete();
                DB::table('pas_program')->where('zoho_id', '=', $post['zoho_id'])->delete();

                $ps_product = DB::connection('we_shop')->table('ps_product')
                    ->where('zoho_id', '=', $post['zoho_id'])
                    ->get()->first();

                if($ps_product){
                    DB::connection('we_shop')->table('ps_product_shop')
                        ->where('id_product', '=', $ps_product->id_product)
                        ->delete();
                    Program::cacheClear();
                    Program::rebuildSearch();
                }
                return response()->json(['status' => 'fail', 'message' => 'Program deleted successfully.']);
            }
            return response()->json(['status' => 'fail', 'message' => 'Program deleted failed.']);
        }catch(\Exception $e){
            $this->saveException('Products', $e);
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()]);
        }

    }

    public function updateProgramPrice(Request $request) {
        try{
            $post = $request->all();
            $this->saveResponse('PriceBook', $post);
            if($post){
                $post = Utility::addSlashes($post);
                $post['status'] = $post['status'] ? 1:0;
                DB::table('pas_price_book')->where('zoho_id', '=', $post['zoho_id'])->update($post);
                return response()->json(['status' => 'fail', 'message' => 'Program Price update successfully.']);
            }
            return response()->json(['status' => 'fail', 'message' => 'Program Price update failed.']);
        }catch(\Exception $e){
            $this->saveException('PriceBook', $e);
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()]);
        }

    }

    public function updateProduct(Request $request) {
        try{
            $post = $request->all();
            $this->saveResponse('ProductsNew', $post);
            if($post){
                $post = Utility::addSlashes($post);

                $duration_type = null;
                $duration_value = null;
                if(!empty($post['duration'])){
                    $arr_duration = explode(' ', $post['duration']);
                    if(is_array($arr_duration)){
                        if(count($arr_duration) == 2){
                            $post['duration_value'] = trim($arr_duration[0]);
                            $post['duration_type'] = strtolower(trim($arr_duration[1]));
                        }else if(count($arr_duration) == 3){
                            $post['duration_value'] = trim($arr_duration[0]);
                            $post['duration_type'] = strtolower(trim($arr_duration[2]));
                        }
                    }
                }
                unset($post['duration']);
                $post['is_featured'] = $post['is_featured'] ? 1:0;
                $post['is_best_seller'] = $post['is_best_seller'] ? 1:0;
                $existCountPS = DB::connection('we_shop')->table('ps_product')
                    ->where('reference', '=', $post['code'])
                    ->get()->count();

                if($existCountPS > 0){
                    $data['price'] = $post['srp'];
                    $data['wholesale'] = $post['wholesale'];
                    DB::connection('we_shop')->table('ps_product')
                        ->where('reference', '=', $post['code'])
                        ->update($data);

                    $client = new Client();
                    $response = $client->get($_ENV['PRESTASHOP_BASE_URL'].'/modules/pasapi/cache-clear.php', [
                        'headers' => [
                            'Accept'        => 'application/json',
                        ],
                        'query_params' => [],
                    ]);
                    $response->getBody();
                }

                $existCount = DB::table('pas_product')->where('zoho_id', '=', $post['zoho_id'])->count('id');
                if($existCount == 1){
                    $post['updated_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_product')
                        ->where('zoho_id', '=', $post['zoho_id'])
                        ->update($post);

                    return response()->json(['status' => 'success', 'message' => 'New Product updated successfully.']);
                } elseif($existCount == 0) {
                    $post['created_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_product')
                        ->insert($post);
                    return response()->json(['status' => 'success', 'message' => 'New Product created successfully.']);
                }
            }
            return response()->json(['status' => 'fail', 'message' => 'Product updated failed.']);
        }catch(\Exception $e){
            $this->saveException('ProductsNew', $e);
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()]);
        }

    }

    public function deleteProduct(Request $request) {
        try{
            $post = $request->all();
            $this->saveResponse('ProductsNew', $post, 'delete');
            if($post){
                DB::table('pas_product')->where('zoho_id', '=', $post['zoho_id'])->delete();
                return response()->json(['status' => 'fail', 'message' => 'New Product deleted successfully.']);
            }
            return response()->json(['status' => 'fail', 'message' => 'New Product deleted failed.']);
        }catch(\Exception $e){
            $this->saveException('ProductsNew', $e);
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()]);
        }

    }

    public function updateSchedule(Request $request) {
        try{
            $post = $request->all();
            $this->saveResponse('Deals', $post);
            if($post){
                $post = Utility::addSlashes($post);
                if(!empty($post['deal_name'])){
                    $post['deal_name'] = DB::raw('AES_ENCRYPT("'.$post['deal_name'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                }

                if(!empty($post['email'])){
                    $post['email'] = DB::raw('AES_ENCRYPT("'.$post['email'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                }

                if(!empty($post['phone'])){
                    $post['phone'] = DB::raw('AES_ENCRYPT("'.$post['phone'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                }

                if(!empty($post['program_zoho_id'])){
                    $post['program_id'] = DB::table('pas_program')
                        ->where('zoho_id','=', $post['program_zoho_id'])
                        ->value('id');
                }

                if(!empty($post['contact_zoho_id'])){
                    $post['contact_id'] = DB::table('pas_contact')
                        ->where('zoho_id','=', $post['contact_zoho_id'])
                        ->value('id');
                }

                if(!empty($post['partner_zoho_id'])){
                    $post['partner_id'] = DB::table('pas_partner')
                        ->where('zoho_id','=', $post['partner_zoho_id'])
                        ->value('id');
                }

                $state_name = trim(rtrim(trim($post['state']), '.'));
                if (!empty($state_name)) {
                    if (strlen($state_name) == 2) {
                        $post['state'] = $state_name;
                    } else {
                        $post['state'] = DB::table('pas_state')
                            ->where('state_name', '=', $state_name)
                            ->value('iso2_code');
                    }
                }

                $existCount = DB::table('pas_schedule')->where('zoho_id', '=', $post['zoho_id'])->count('id');
                if($existCount == 1){
                    $post['updated_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_schedule')
                        ->where('zoho_id', '=', $post['zoho_id'])
                        ->update($post);
                    return response()->json(['status' => 'success', 'message' => 'Schedule updated successfully.']);
                } elseif($existCount == 0) {
                    $post['created_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_schedule')
                        ->insert($post);
                    return response()->json(['status' => 'success', 'message' => 'Program created successfully.']);
                }
            }
            return response()->json(['status' => 'fail', 'message' => 'Schedule updated failed.']);
        }catch(\Exception $e){
            $this->saveException('Deals', $e);
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()]);
        }

    }

    public function updateLead(Request $request) {
        try{
            $post = $request->all();
            $this->saveResponse('Leads', $post);
            if($post){
                $post = Utility::addSlashes($post);
                //$post['updated_at'] = date('Y-m-d H:i:s');

                $existCount = DB::table('pas_leads')->where('zoho_id', '=', $post['zoho_id'])->count('id');
                //dump($isExists);
                if($existCount == 1){
                    $countries = DB::table('pas_country')->pluck('id', 'country_name')->toArray();
                    $timezones = DB::table('pas_timezone')->pluck('id', 'timezone')->toArray();
                    $programs = DB::table('pas_program')->pluck('id', 'zoho_id')->toArray();
                    $partners = DB::table('pas_partner')->pluck('id', 'zoho_id')->toArray();

                    if(!empty($post['country']) && isset($countries[$post['country']])){
                        $post['country'] = $countries[$post['country']];
                    }else{
                        $post['country'] = null;
                    }

                    if(!empty($post['time_zone']) && isset($timezones[$post['time_zone']])){
                        $post['time_zone'] = $timezones[$post['time_zone']];
                    }else{
                        $post['time_zone'] = null;
                    }

                    if(!empty($post['interested_program']) && isset($programs[$post['interested_program']])){
                        $post['interested_program'] = $programs[$post['interested_program']];
                    }else{
                        $post['interested_program'] = null;
                    }

                    if(!empty($post['partner_id']) && isset($partners[$post['partner_id']])){
                        $post['partner_id'] = $partners[$post['partner_id']];
                    }else{
                        $post['partner_id'] = null;
                    }

                    DB::table('pas_leads')
                        ->where('zoho_id', '=', $post['zoho_id'])
                        ->update($post);
                    return response()->json(['status' => 'success', 'message' => 'Leads updated successfully.']);
                }else{
                    return response()->json(['status' => 'fail', 'message' => 'Leads Zoho id  '.$post['zoho_id'].' is not exists.']);
                }

            }
            return response()->json(['status' => 'fail', 'message' => 'Leads updated failed.']);
        }catch(\Exception $e){
            $this->saveException('Leads', $e);
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()]);
        }

    }

    public function updateLeadSchedule(Request $request) {
        try{
            $post = $request->all();
            $this->saveResponse('Leads', $post);
            if($post){
                $post = Utility::addSlashes($post);
                $post['updated_at'] = date('Y-m-d H:i:s');

                $existCount = DB::table('pas_student')->where('zoho_id', '=', $post['zoho_id'])->count('id');
                if($existCount == 1){
                    if(!empty($post['first_name'])){
                        $post['first_name'] = DB::raw('AES_ENCRYPT("'.$post['first_name'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                    }
                    if(!empty($post['last_name'])){
                        $post['last_name'] = DB::raw('AES_ENCRYPT("'.$post['last_name'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                    }
                    if(!empty($post['email'])){
                        $post['email'] = DB::raw('AES_ENCRYPT("'.$post['email'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                    }else{
                        $post['email'] = null;
                    }

                    if(!empty($post['phone'])){
                        $post['phone'] = DB::raw('AES_ENCRYPT("'.$post['phone'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                    }else{
                        $post['phone'] = null;
                    }

                    DB::table('pas_student')
                        ->where('zoho_id', '=', $post['zoho_id'])
                        ->update($post);
                    return response()->json(['status' => 'success', 'message' => 'Leads Schedule updated successfully.']);
                }else{
                    return response()->json(['status' => 'fail', 'message' => 'Leads Schedule Zoho id  '.$post['zoho_id'].' is not exists.']);
                }
            }
            return response()->json(['status' => 'fail', 'message' => 'Schedule updated failed.']);
        }catch(\Exception $e){
            $this->saveException('Leads', $e);
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()]);
        }

    }

    public function updateContact(Request $request) {
        try{
            $post = $request->all();
            $this->saveResponse('Contacts', $post);
            if($post){
                $post = Utility::addSlashes($post);
                if(!empty($post['social_security_number'])){
                    $post['social_security_number'] = sprintf('%09d', $post['social_security_number']);
                }

                if(!empty($post['partner_zoho_id'])){
                    $post['partner_id'] = DB::table('pas_partner')->where('zoho_id', '=', $post['partner_zoho_id'])->value('id');
                }

                $post['date_of_birth'] = !empty($post['date_of_birth']) ? DB::raw('AES_ENCRYPT("'.$post['date_of_birth'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'):null;
                $post['social_security_number'] = !empty($post['social_security_number']) ? DB::raw('AES_ENCRYPT("'.$post['social_security_number'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")'):null;


                if(!empty($post['first_name'])){
                    $post['first_name'] = DB::raw('AES_ENCRYPT("'.$post['first_name'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                }

                if(!empty($post['last_name'])){
                    $post['last_name'] = DB::raw('AES_ENCRYPT("'.$post['last_name'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                }

                if(!empty($post['email'])){
                    $post['email'] = DB::raw('AES_ENCRYPT("'.$post['email'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                }

                if(!empty($post['phone'])){
                    $post['phone'] = DB::raw('AES_ENCRYPT("'.$post['phone'].'", "'.$_ENV['AES_ENCRYPT_KEY'].'")');
                }else{
                    $post['phone'] = null;
                }

                $existCount = DB::table('pas_contact')->where('zoho_id', '=', $post['zoho_id'])->count('id');
                if($existCount == 1){
                    $post['updated_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_contact')
                        ->where('zoho_id', '=', $post['zoho_id'])
                        ->update($post);
                    return response()->json(['status' => 'success', 'message' => 'Contacts updated successfully.']);
                } elseif($existCount == 0) {
                    $post['created_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_contact')
                        ->insert($post);
                    return response()->json(['status' => 'success', 'message' => 'Contacts inserted successfully.']);
                }
            }
            return response()->json(['status' => 'fail', 'message' => 'Contacts updated failed.']);
        }catch(\Exception $e){
            $this->saveException('Contacts', $e);
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()]);
        }

    }

    public function updatePartner(Request $request) {
        try{
            $post = $request->all();
            $this->saveResponse('Accounts', $post);
            if($post){
                $post = $this->loadPartnerData($post);
                //$post = Utility::addSlashes($partner_load);

                $existCount = DB::table('pas_partner')->where('zoho_id', '=', $post['zoho_id'])->count('id');
                if($existCount == 1){
                    $post['updated_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_partner')
                        ->where('zoho_id', '=', $post['zoho_id'])
                        ->update($post);
                    return response()->json(['status' => 'success', 'message' => 'Partner updated successfully.']);
                } elseif($existCount == 0) {
                    $post['created_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_partner')
                        ->insert($post);
                    return response()->json(['status' => 'success', 'message' => 'Partner inserted successfully.']);
                }
            }
            return response()->json(['status' => 'fail', 'message' => 'Partner updated failed.']);
        }catch(\Exception $e){
            $this->saveException('Accounts', $e);
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()]);
        }

    }


    public function updateAffiliate(Request $request) {
        try{
            $post = $request->all();
            $this->saveResponse('Affiliate', $post);
            if($post){
                $post = Utility::addSlashes($post);

                if(isset($post['price_book_zoho_id']) && !empty($post['price_book_zoho_id'])){
                    $post['price_book_id'] = DB::table('pas_price_book')
                        ->where('zoho_id', '=', $post['price_book_zoho_id'])
                        ->value('id');
                }

                $existCount = DB::table('pas_affiliate')->where('zoho_id', '=', $post['zoho_id'])->count('id');
                if($existCount == 1){
                    $post['updated_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_affiliate')
                        ->where('zoho_id', '=', $post['zoho_id'])
                        ->update($post);
                    return response()->json(['status' => 'success', 'message' => 'Affiliate updated successfully.']);
                } elseif($existCount == 0) {
                    $post['created_at'] = date('Y-m-d H:i:s');
                    DB::table('pas_affiliate')
                        ->insert($post);
                    return response()->json(['status' => 'success', 'message' => 'Affiliate inserted successfully.']);
                }
            }
            return response()->json(['status' => 'fail', 'message' => 'Affiliate updated failed.']);
        }catch(\Exception $e){
            $this->saveException('Affiliate', $e);
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()]);
        }

    }

    private function loadPartnerData($data){

        $zoho_partner = ZohoHelper::getInstance()->fetchByIds('Accounts', [$data['zoho_id']], ['Account_Name', 'TP_Contact_Name', 'TP_Contact_Title', 'TP_Email', 'TP_Phone', 'Phone', 'Secondary_Email', 'Department', 'WIA', 'MyCAA', 'Hosted_Site', 'Shipping_Street', 'Shipping_City', 'Shipping_State', 'Shipping_Code', 'Account_Type', 'Record_Image', 'Parent_Account', 'Price_Book', 'Mkt_Colors1', 'Mkt_Colors2', 'Mkt_Colors3', 'Mkt_Colors4', 'Mkt_Colors5', 'Mkt_Colors6', 'Mkt_Colors7', 'Mkt_Colors8', 'Mkt_Colors9', 'Mkt_Colors10', 'Contact_Name', 'Contact_Title', 'Campus_Name_if_applicable', 'Billing_Street', 'Billing_Address_2', 'Billing_City', 'Billing_Code', 'Billing_Country', 'Billing_State', 'TP_Website']);

        if (isset($zoho_partner['data'][0])) {
            $zoho_partner = $zoho_partner['data'][0];

            $data['partner_name'] = !empty($zoho_partner['Account_Name']) ? addslashes($zoho_partner['Account_Name']):null;
            $data['tp_contact_name'] = !empty($zoho_partner['TP_Contact_Name']) ? addslashes($zoho_partner['TP_Contact_Name']):null;
            if($zoho_partner['Parent_Account'] && isset($zoho_partner['Parent_Account']['id'])){
                $parent_partner_id = DB::table('pas_partner')->where('zoho_id', '=', $zoho_partner['Parent_Account']['id'])->value('id');

                $data['parent_partner_name'] = addslashes($zoho_partner['Parent_Account']['name']);
                $data['parent_partner_zoho_id'] = $zoho_partner['Parent_Account']['id'];
                $data['parent_partner_id'] = $parent_partner_id ? $parent_partner_id:null;
            }else{
                $data['parent_partner_name'] = null;
                $data['parent_partner_zoho_id'] = null;
                $data['parent_partner_id'] = null;
            }

            if($zoho_partner['Price_Book'] && isset($zoho_partner['Price_Book']['id'])){
                $data['price_book_id'] = DB::table('pas_price_book')->where('zoho_id', '=', $zoho_partner['Price_Book']['id'])->value('id');

                $data['price_book_zoho_id'] = $zoho_partner['Price_Book']['id'];
            }else{
                $price_books = ZohoHelper::getInstance()->fetchRelatedRecords('Accounts/'.$zoho_partner['id'], 'Price_Books8');
                //dd($price_books);

                if($price_books['status'] == 'success' && isset($price_books['data']['data']) && count($price_books['data']['data']) > 0 && isset($price_books['data']['data'][0]['PriceBooks']['id'])){
                    $price_book_zoho_id = $price_books['data']['data'][0]['PriceBooks']['id'];
                    $price_book_id = DB::table('pas_price_book')->where('zoho_id', '=', $price_book_zoho_id)->value('id');
                    if($price_book_id){
                        $data['price_book_id'] = $price_book_id;
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
                $record_image = DB::table('pas_partner')
                    ->where('zoho_id', '=', $data['zoho_id'])
                    ->value('record_image');
                if($record_image == $zoho_partner['Record_Image']){
                    $logo_upload = false;
                }
            }

            if(!empty($zoho_partner['Record_Image']) && $logo_upload){
                try{
                    $zoho_file_name = ZohoHelper::getInstance()->downloadPhoto('Accounts', $zoho_partner['id'], 'photo', 'public');

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

            return $data;
        }
    }

    private function saveResponse($module, $response, $action = 'create/update'){
        try{
            DB::table('zoho_webhook')->insert([
                'action' => $action,
                'module' => $module,
                'status' => 'success',
                'response' => json_encode($response),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }catch (Exception $e){
            return $e->getMessage();
        }

    }

    private function saveException($module, \Exception $e){
        DB::table('zoho_webhook')->insert([
            'module' => $module,
            'status' => 'exception',
            'response' => json_encode([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getOwners(Request $request){
        $authorization = $request->header('Authorization');
        if($authorization != $_ENV['PRESTASHOP_ACCESS_TOKEN']){
            die('Invalid access token');
        }

        $query = DB::table('pas_owner')
            ->where('status', '=', 'active');
        if($request->is_random){
            $owners = $query->inRandomOrder()->get()->first();
        }else{
            $owners = $query->get()->all();
        }
        return response()->json($owners);
    }

    public function testConnection(Request $request){
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        dd(DB::connection('we_shop')->table('ps_product')->get()->first());
    }

    public function getZohoRecord($zoho_id, $module) {
        $zoho_ids = explode(',', $zoho_id);
        $record = ZohoHelper::getInstance()->fetchByIds($module, $zoho_ids);
        echo '<pre>';print_r($record);die;
    }

    public function updateProgramTest($zoho_id, $action) {
        try{
            $program = DB::table('pas_program')->where('zoho_id', '=', $zoho_id)->get()->first();

            if(!$program){
                dump('Program not found');
            }

            $pas_program[] = [
                'id' => $zoho_id,
                'Product_Name' => ($action == 'update' ? $program->name.'.': rtrim($program->name, '.')),
            ];
            dump($pas_program);
            //echo '<pre>';print_r($pas_program);die;

            $response = ZohoHelper::getInstance()->updateRecord($pas_program, 'Products');
            dump($response);

            $program_inclusion = ZohoHelper::getInstance()->fetchSubForm('Products', $zoho_id);
            dd($program_inclusion);

        }catch(\Exception $e){
            dd($e);
        }

    }

    public function prestashopAddMissingShopPrice(Request $request)
    {
        try{
            $group = $request->group;
            $partner_name = $request->partner_name;
            $program_code = $request->program_code;
            $debug = $request->debug;

            //$time_before = Carbon::now('GMT-5')->subMinutes(10)->format('Y-m-d H:i:s');
            //$this->info($time_before);
            $partner = $partner_name;
            if($partner_name == 'World Education'){
                $partner = 'Unbound Library';
            }

            $id_shop = DB::connection('we_shop')->table('ps_shop')
                ->where('name', '=', $partner)
                ->value('id_shop');

            echo '<h3>Partner Name:'.$partner_name.'</h3>';
            echo '<h3>Shop Name:'.$partner.'</h3>';

            if(empty($id_shop)){
                die('Shop Not Found: '.$partner_name);
            }

            $table = 'pas_partner';
            $column = 'pa.partner_name';
            if(!empty($group) && $group == 3){
                $table = 'pas_affiliate';
                $column = 'pa.affiliate_name';
            }
dump($table);
            $query = DB::table('pas_price_book_program_map AS pm')
                ->join('pas_price_book AS pb', 'pb.id', '=', 'pm.price_book_id')
                ->join($table.' AS pa', 'pa.price_book_id', '=', 'pm.price_book_id')
                ->join('pas_program AS p', 'p.id', '=', 'pm.program_id')
                ->select(['pm.price_book_id', 'pm.program_id', 'p.name', 'p.code', 'pm.program_list_price', 'pb.name AS price_book', 'pa.id AS partner_id', $column])
                //->where('pm.updated_at', '>', $time_before)

                ->where('p.status', '=', 'Active')
                ->where('p.displayed_on', '=', 'All');

            if(!empty($group) && $group == 3){
                $query->where('pa.affiliate_name', '=', $partner_name);
            }else{
                $query->where('pa.partner_name', '=', $partner_name);
            }

            if(!empty($program_code)){
                $query->where('p.code', '=', $program_code);
            }

            $programs = $query->get()->all();

            echo '<h3>Total Program IN PAS: '.count($programs).'</h3>';

            $id_product_arr = DB::connection('we_shop')
                ->table('ps_product AS p')
                ->select(['p.id_product', 'p.reference'])
                //->where('p.reference', '=', $program->code)
                ->where('p.id_shop_default', '=', $id_shop)
                ->pluck('p.id_product', 'p.reference')->toArray();

            echo '<h3>Total Program in ps_product : '.count($id_product_arr).'</h3>';

            $product_shop_arr = DB::connection('we_shop')
                ->table('ps_product_shop AS ps')
                ->select(['ps.*', 'p.id_product', 'p.reference'])
                ->join('ps_product AS p', 'p.id_product', '=',  'ps.id_product')
                //->where('p.reference', '=', $program->code)
                ->where('ps.id_shop', '=', $id_shop)
                ->pluck('p.id_product', 'p.reference')->toArray();

            echo '<h3>Total Program in ps_product_shop : '.count($product_shop_arr).'</h3>';


            //dd([$product_shop_arr, $id_product_arr]);

            if(count($programs) > 0){
                $counter = 1;
                foreach ($programs as $program) {
                    /*$product_shop = DB::connection('we_shop')
                        ->table('ps_product_shop AS ps')
                        ->select(['ps.*'])
                        ->join('ps_product AS p', 'p.id_product', '=',  'ps.id_product')
                        ->where('p.reference', '=', $program->code)
                        ->where('ps.id_shop', '=', $id_shop)
                        ->get()->first();*/

                    //dd([$id_shop, $product_shop]);

                    if(!isset($product_shop_arr[$program->code])){

                        /*$id_product = DB::connection('we_shop')
                            ->table('ps_product AS p')
                            ->where('p.reference', '=', $program->code)
                            ->where('p.id_shop_default', '=', $id_shop)
                            ->value('p.id_product');*/

                        if(isset($id_product_arr[$program->code])){
                            //if($debug == 1) {
                                echo "<p>$counter: " . $program->code . '</p>';
                                $counter++;
                            //}
                            $product_shop_data[] = [
                                'id_product' => $id_product_arr[$program->code],
                                'id_shop' => $id_shop,
                                'price' => $program->program_list_price,
                                'wholesale_price' => $program->program_list_price,
                                'id_tax_rules_group' => 1,
                                'id_category_default' => 2,
                                'indexed' => 0,
                                'active' => 1,
                                //'is_best_selling' => $zoho_product['is_best_seller'],
                            ];
                        }

                    }/*else{
                        echo "<p>Program not found into ps_products: " . $program->code . '</p>';
                    }*/

                }

                if(isset($product_shop_data) && count($product_shop_data) > 0){
                    if($debug == 1){
                        //echo '<pre>';print_r($product_shop_data);die;
                        die('Remove or replace with 1 last parameter if you want to import.');
                    }

                    DB::connection('we_shop')->table('ps_product_shop')
                        ->insert($product_shop_data);

                    echo '<h3>Total ('.count($product_shop_data).') ps_shop_product imported.</h3>';
                    Program::cacheClear();
                    Program::rebuildSearch();
                }else{
                    dd('<h3>No program imported</h3>');
                }
            }else{
                dd('<h3>Programs Not Found</h3>');
            }
        }catch (Exception $e){
            dd($e->getMessage());
        }

    }


}
