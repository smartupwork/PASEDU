<?php
namespace App\Http\Controllers\Prestashop;
use App\Http\Controllers\Controller;
use App\ZohoHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Exception;
use Session;
use Config;
use Lang;
require base_path("vendor/autoload.php");
use Cookie;

class OrderController extends Controller
{
    public function orderSendToZoho(Request $request){
        if(!empty($request->order_id)){
            $order = DB::connection('we_shop')->table('ps_orders AS o')
                ->select(['o.id_order', DB::raw('DATE(o.invoice_date) AS invoice_date'), 'pl.name AS program_name', 'p.zoho_id AS product_zoho_id', 'pl.description', 'c.firstname', 'c.lastname', 'c.email', 'c.phone', 'ad.address1 AS ad_street', 'ad.city AS ad_city', 'das.name AS ad_state', 'ad.postcode AS ad_zip', 'dac.iso_code AS ad_iso_code', 'ai.address1 AS ai_street', 'ai.city AS ai_city', 'ias.name AS ai_state', 'ai.postcode AS ai_zip', 'iac.iso_code AS ai_iso_code'])
                ->join('ps_order_detail AS od', 'od.id_order', '=', 'o.id_order')
                ->join('ps_product AS p', 'p.id_product', '=', 'od.product_id')
                ->join('ps_product_lang AS pl', function($join){
                    $join->on('pl.id_product', '=', 'p.id_product')
                        ->where('pl.id_lang', '=', 1);
                })
                ->join('ps_customer AS c', 'c.id_customer', '=', 'o.id_customer')
                ->join('ps_address AS ad', 'ad.id_address', '=', 'o.id_address_delivery')
                ->join('ps_address AS ai', 'ai.id_address', '=', 'o.id_address_invoice')
                ->leftJoin('ps_country AS dac', 'dac.id_country', '=', 'ad.id_country')
                ->leftJoin('ps_country AS iac', 'iac.id_country', '=', 'ai.id_country')
                ->leftJoin('ps_state AS das', 'das.id_state', '=', 'ad.id_state')
                ->leftJoin('ps_state AS ias', 'ias.id_state', '=', 'ai.id_state')
                ->where('o.id_order', '=', $request->order_id)
                ->get()->first();
dump($order);
            if($order){
                $partner = DB::table('pas_partner')->where('partner_name', '=', 'San Francisco State University')->get()->first();

                $zoho_data = json_decode($order->description, true);
                //dump($zoho_data);

                $end_date = null;
                if(count($zoho_data) > 0 && !empty($zoho_data['duration_value']) && !empty($zoho_data['duration_type'])){
                    $end_date = date('Y-m-d', strtotime('+ '.$zoho_data['duration_value'].' '.$zoho_data['duration_type']));
                }

                return view('prestashop.order.verify-order', compact('order','end_date', 'partner', 'zoho_data'));
            }
            die('Order not found');
        }

    }

    public function orderCreateOne(Request $request){
        //dd(date('Y-m-d', strtotime('+6 month')));
            $customer['First_Name'] = "De'vante";
            $customer['Last_Name'] = 'Jones-Walker';
            $customer['Email'] = 'DevanteJWalker@gmail.com';
            $customer['Phone'] = '4156780439';
            $customer['Mailing_Street'] = '368 Alta Street';
            $customer['Mailing_City'] = 'Brentwood';
            $customer['Mailing_State'] = 'CA'; // id 8
            $customer['Mailing_Zip'] = '94513';
            $customer['Mailing_Country'] = 'US'; //id 21

            $customer['Other_Street'] = '368 Alta Street';
            $customer['Other_City'] = 'Brentwood';
            $customer['Other_State'] = 'CA';// id 8
            $customer['Other_Zip'] = '94513';
            $customer['Other_Country'] = 'US';//id 21

            //dump($customer);

            $enrollment['Subject'] = $customer['First_Name'].' '.$customer['Last_Name'];
            $enrollment['Contact_Name'] = '';
            $enrollment['Start_Date'] = '2022-11-21';
            $enrollment['End_Date'] = '2023-05-21';
            $enrollment['Username'] = $customer['Email'];
            $enrollment['Program'] = '1066248000006634072';
            $enrollment['Adjustment'] = 0;
            $enrollment['Sub_Total'] = 3595;
            $enrollment['Grand_Total'] = 3595;
            $enrollment['Status'] = 'Active';
            $enrollment['Account_Name'] = '1066248000018564023';
            $enrollment['Layout'] = '1066248000486617525';
            $enrollment['Ordered_Items'][] = [
                'product' => [
                    'id' => '1066248000006634072'
                ],
                'Product_Name' => [
                    'id' => '1066248000006634072',
                    'Product_Code' => 'CA-CEH-V',
                    'name' => 'Certified Ethical Hacker'
                ],
                'Program_Code' => 'CI-CICT-01',
                'Program_Type' => 'Career Training Program',
                'Hours' => 80,
                'Quantity' => "1",
                'Discount' => 0,
                'List_Price' => 3595,
                'Tax' => null,
                'Total' => 3595,
            ];

            //dump($enrollment);

            $payment['Registration'] = "";
            $payment['Contact'] = '';
            $payment['Name'] = 'De\'vante Jones-Walker';
            $payment['Payment_Type'] = 'Tuition Payment';
            $payment['Paid_To'] = 'World Education';
            $payment['Payment_Amount'] = 3595;
            $payment['Payment_Date'] = '2022-11-21';
            $payment['Payment_Source'] = 'Paypal';
            $payment['Discount_Amount'] = 0;
            $payment['Promo_Code_PO_Number'] = null;
            $payment['Enrollment_Channel'] = 'Direct Pay';
            $payment['Layout'] = '1066248000486591725';
            $payment['Owner'] = '1066248000000068001';

            //dump($payment);

            if($request->debug == 0){
                /* @var $mod_obj ZohoHelper */
                $mod_obj = ZohoHelper::getInstance();

                dump($customer);
                $contact_response = $this->saveZohoContact($mod_obj, $customer);
                dump($contact_response);

                if(!$contact_response['status'] || empty($contact_response['contact_zoho_id'])){
                    return $contact_response;
                }

                $enrollment['Contact_Name'] = $contact_response['contact_zoho_id'];
                dump($enrollment);
                $enrollment_response = $this->saveZohoEnrollment($mod_obj, $enrollment);
                dump($enrollment_response);
                if(!$enrollment_response['status'] || empty($enrollment_response['zoho_enrollment_id'])){
                    return $enrollment_response;
                }

                $payment['Registration'] = $enrollment_response['zoho_enrollment_id'];
                $payment['Contact'] = $contact_response['contact_zoho_id'];
                dump($payment);
                $payment_response = $this->saveZohoPayment($mod_obj, $payment);
                dd($payment_response);
            }else{
                dd([$customer, $enrollment, $payment]);
            }

    }

    public function orderCreateTwo(Request $request){
        //dd(date('Y-m-d', strtotime('+6 month')));
            $customer['First_Name'] = "De'vante";
            $customer['Last_Name'] = 'Jones-Walker';
            $customer['Email'] = 'DevanteJWalker@gmail.com';
            $customer['Phone'] = '4156780439';
            $customer['Mailing_Street'] = '368 Alta Street';
            $customer['Mailing_City'] = 'Brentwood';
            $customer['Mailing_State'] = 'CA'; // id 8
            $customer['Mailing_Zip'] = '94513';
            $customer['Mailing_Country'] = 'US'; //id 21

            $customer['Other_Street'] = '368 Alta Street';
            $customer['Other_City'] = 'Brentwood';
            $customer['Other_State'] = 'CA';// id 8
            $customer['Other_Zip'] = '94513';
            $customer['Other_Country'] = 'US';//id 21

            //dump($customer);

            $enrollment['Subject'] = $customer['First_Name'].' '.$customer['Last_Name'];
            $enrollment['Contact_Name'] = '';
            $enrollment['Start_Date'] = '2022-11-21';
            $enrollment['End_Date'] = '2023-11-21';
            $enrollment['Username'] = $customer['Email'];
            $enrollment['Program'] = '1066248000017501147';
            $enrollment['Adjustment'] = 0;
            $enrollment['Sub_Total'] = 995;
            $enrollment['Grand_Total'] = 995;
            $enrollment['Status'] = 'Active';
            $enrollment['Account_Name'] = '1066248000018564023';
            $enrollment['Layout'] = '1066248000486617525';
            $enrollment['Ordered_Items'][] = [
                'product' => [
                    'id' => '1066248000017501147'
                ],
                'Product_Name' => [
                    'id' => '1066248000017501147',
                    'Product_Code' => 'WE-IT-1501',
                    'name' => 'Ethical Hacker + Practice Lab'
                ],
                'Program_Code' => 'WE-IT-1501',
                'Program_Type' => 'Career Training Program',
                'Hours' => 90,
                'Quantity' => "1",
                'Discount' => 0,
                'List_Price' => 995,
                'Tax' => null,
                'Total' => 995,
            ];

            //dump($enrollment);

            $payment['Registration'] = "";
            $payment['Contact'] = '';
            $payment['Name'] = 'De\'vante Jones-Walker';
            $payment['Payment_Type'] = 'Tuition Payment';
            $payment['Paid_To'] = 'World Education';
            $payment['Payment_Amount'] = 995;
            $payment['Payment_Date'] = '2022-11-21';
            $payment['Payment_Source'] = 'Paypal';
            $payment['Discount_Amount'] = 0;
            $payment['Promo_Code_PO_Number'] = null;
            $payment['Enrollment_Channel'] = 'Direct Pay';
            $payment['Layout'] = '1066248000486591725';
            $payment['Owner'] = '1066248000000068001';

            //dump($payment);

            if($request->debug == 0){
                /* @var $mod_obj ZohoHelper */
                $mod_obj = ZohoHelper::getInstance();

                dump($customer);
                $contact_response = $this->saveZohoContact($mod_obj, $customer);
                dump($contact_response);

                if(!$contact_response['status'] || empty($contact_response['contact_zoho_id'])){
                    return $contact_response;
                }

                $enrollment['Contact_Name'] = $contact_response['contact_zoho_id'];
                dump($enrollment);
                $enrollment_response = $this->saveZohoEnrollment($mod_obj, $enrollment);
                dump($enrollment_response);
                if(!$enrollment_response['status'] || empty($enrollment_response['zoho_enrollment_id'])){
                    return $enrollment_response;
                }

                $payment['Registration'] = $enrollment_response['zoho_enrollment_id'];
                $payment['Contact'] = $contact_response['contact_zoho_id'];
                dump($payment);
                $payment_response = $this->saveZohoPayment($mod_obj, $payment);
                dd($payment_response);
            }else{
                dd([$customer, $enrollment, $payment]);
            }

    }


    public function orderSendToZohoSubmitBk(Request $request){
        if($request->post()){
            dd($request->customer);
            /* @var $mod_obj ZohoHelper */
            $mod_obj = ZohoHelper::getInstance();

            try{
                $contact_response = $this->saveZohoContact($mod_obj, $request->customer);
                //dump([$contact_response, $this->context->cart->getProducts()]);die;
                if(!$contact_response['status'] || empty($contact_response['contact_zoho_id'])){
                    return $contact_response;
                }

                $discount_applied = false;

                $cart_products = $this->context->cart->getProducts();
                if(count($cart_products) > 0) {
                    foreach ($cart_products as $product) {
                        $product_discount_price = 0;
                        $coupon_code = null;

                        if(count($cart_rules) > 0){
                            $coupon_code = $cart_rules[0]['code'];
                            if(!empty($cart_rules[0]['reduction_percent'])){
                                $product_discount_price = ($product['price'] * $cart_rules[0]['reduction_percent']) /100;
                            }else if(!empty($cart_rules[0]['reduction_amount'])){
                                if(!$discount_applied && $cart_rules[0]['reduction_amount'] <= $product['price']){
                                    $discount_applied = true;
                                    $product_discount_price = ($product['price'] - $cart_rules[0]['reduction_amount']);
                                }
                            }
                        }

                        $enrollment_response = $this->saveZohoEnrollment($mod_obj, $customer, $contact_response['contact_zoho_id'], $product, $partner_detail, $cart, $product_discount_price);
                        if(!$enrollment_response['status'] || empty($enrollment_response['zoho_enrollment_id'])){
                            return $enrollment_response;
                        }

                        $this->saveZohoPayment($mod_obj, $enrollment_response['zoho_enrollment_id'],$contact_response['contact_zoho_id'], $product, $cart, $coupon_code, $product_discount_price, $params);
                    }
                }else{
                    return [
                        'status' => false,
                        'message' => 'Cart is empty',
                    ];
                }
            }catch (Exception $e){
                return [
                    'status' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }
        return response()->json(['status' => 'fail', 'message' => 'Something went wrong.']);
    }

    /**
     * @param ZohoHelper $mod_obj
     * @param $contact_data
     * @return array
     */
    private function saveZohoContact(ZohoHelper $mod_obj, $contact_data){
        try{
            $criteria = [
                ['Email', 'equals', $contact_data['Email']],
            ];

            $contact = $mod_obj->fetchCriteria('Contacts', ['Owner'], 1, 1, $criteria);

            if(isset($contact['status']) && $contact['status'] == 'error'){
                return ['status' => false, 'errors' => $contact['message']];
            }

            if(count($contact['data']) > 0 && isset($contact['data'][0]['id'])) {

                $zoho_contact_id = $contact['data'][0]['id'];
                $contact_data['id'] = $contact['data'][0]['id'];
                $contact_res = $mod_obj->updateRecord([$contact_data], 'Contacts');

                if(isset($contact_res['status']) && $contact_res['status'] == 'error'){
                    /*if(!empty($last_id)){
                        DB::getInstance()->executeS("UPDATE zoho_enrollments SET status=  'fail' ,zoho_response = '".json_encode($contact_res)."' WHERE id = ".$last_id);
                    }*/
                    return ['status' => false, 'errors' => $contact_res['message']];
                }
            }else{
                $contact_res = $mod_obj->addRecord([$contact_data], 'Contacts');
                if(isset($contact_res['status']) && $contact_res['status'] == 'error'){
                    return ['status' => false, 'errors' => $contact_res['message']];
                }
                $zoho_contact_id = $contact_res[0]['details']['id'];
            }

            return ['status' => true, 'contact_zoho_id' => $zoho_contact_id];
        }catch (Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }

    }

    /**
     * @param ZohoHelper $mod_obj
     * @param $enroll_data
     * @return array
     */
    private function saveZohoEnrollment(ZohoHelper $mod_obj, $enroll_data){
        try{

            $enroll_res = $mod_obj->addSubForm([$enroll_data], 'Sales_Orders');
            if(isset($enroll_res['status']) && $enroll_res['status'] == 'error'){
                return ['status' => false, 'errors' => $enroll_res['message']];
            }

            return ['status' => true, 'message' => 'Enrollment created successfully', 'zoho_enrollment_id' => $enroll_res[0]['details']['id']];
        }catch (Exception $e){
            return ['status' => false, 'message' => $e->getMessage()];
        }


    }

    /**
     * @param ZohoHelper $mod_obj
     * @param $payment_data
     * @return array
     */
    private function saveZohoPayment(ZohoHelper $mod_obj, $payment_data){
        //$zoho_data = json_decode($product['description'], true);

        $payment_response = $mod_obj->addRecord([$payment_data], 'Payments');

        if(isset($payment_response['status']) && $payment_response['status'] == 'error'){
            $data['success'] = false;
            $data['errors']['email'] = $payment_response['message'];
        }

        return ['status' => true, 'message' => 'Payment created successfully', 'zoho_payment_id' => $payment_response[0]['details']['id']];
    }

    public function sendEmail($customer){
        require_once __DIR__."/../../vendor/phpmailer/phpmailer/src/PHPMailer.php";
        require_once __DIR__."/../../vendor/phpmailer/phpmailer/src/SMTP.php";
        require_once __DIR__."/../../vendor/phpmailer/phpmailer/src/Exception.php";

        $this->context->smarty->assign('customer', $customer);
        $body = $this->display(__FILE__, 'mails/en/order-confirm-email.tpl');

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->ContentType = 'text/html';

        if(Configuration::get('PS_MAIL_METHOD') != 1) {
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = Configuration::get('PS_MAIL_SMTP_ENCRYPTION');
            $mail->Port = Configuration::get('PS_MAIL_SMTP_PORT');
            $mail->Username = Configuration::get('PS_MAIL_USER');
            $mail->Password = Configuration::get('PS_MAIL_PASSWD');
            $mail->Host = Configuration::get('PS_MAIL_SERVER');
            $mail->addCustomHeader('X-PM-Message-Stream', 'outbound');
        }

        //$mail->addAddress('rajneeshxwds@gmail.com', 'Rajneesh XWDS');
        $mail->addAddress('admissions@worldeducation.net', 'Admission');
        /*if($partner_email){
            $mail->addAddress($partner_email, $this->context->shop->name);
        }*/
        $mail->setFrom('admissions@worldeducation.net', 'World Education');
        $mail->addBCC('rajneeshxwds@gmail.com', 'Rajneesh XWDS');
        $mail->addBCC('xoomwebdevelopment@gmail.com', 'Khemraj XWDS');

        $mail->Subject = 'Remind Me About Certification';
        $mail->MsgHTML($body);

        try {
            return $mail->send();
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            dump($mail->ErrorInfo);
            return "Mailer Error: " . $mail->ErrorInfo;
        }
    }

}
