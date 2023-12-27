<?php

/*function changeDateFormate($date,$date_format){
    return \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format($date_format);    
}
   
function zohoPartners(){

    $tdata = DB::table('pas_zoho_token')->select('refresh_token')->first();
    $refresh_token = $tdata->refresh_token;
    
    $client_id = $_ENV['ZOHO_CLIENT_ID'];//"1000.R2LXNS09CR9STYVA9UP5YPBA28IYPA";
    $client_secret = $_ENV['ZOHO_CLIENT_SECRET'];
    $url = "https://accounts.zoho.com/oauth/v2/token?refresh_token=$refresh_token&client_id=$client_id&grant_type=refresh_token&client_secret=$client_secret";

    $curl = curl_init($url);
    curl_setopt_array($curl, array(
        CURLOPT_POST => 1,
        CURLOPT_RETURNTRANSFER => true
        ));
    $response = curl_exec($curl);        
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        return array("cURL Error #:" . $err);
    } else {        
        $rarr = json_decode($response);            
        if(isset($rarr->error)){
            return array("error".$rarr->error);
        }else{
            $access_token = $rarr->access_token;
            $curl_pointer = curl_init();
            $curl_options = array();
            $url = "https://www.zohoapis.com/crm/v2/coql";

            $curl_options[CURLOPT_URL] = $url;
            $curl_options[CURLOPT_RETURNTRANSFER] = true;
            $curl_options[CURLOPT_HEADER] = 1;
            $curl_options[CURLOPT_CUSTOMREQUEST] = "POST";
            $requestBody = array();

            $requestBody["select_query"] = "select Account_Name, Contact_Name, Contact_Title from Accounts where Account_Name != ''";
            $curl_options[CURLOPT_POSTFIELDS]= json_encode($requestBody);
            $headersArray = array();
            $headersArray[] = "Authorization". ":" . "Zoho-oauthtoken " .$access_token;
            $curl_options[CURLOPT_HTTPHEADER]=$headersArray;
            
            curl_setopt_array($curl_pointer, $curl_options);
            
            $result = curl_exec($curl_pointer);
            $responseInfo = curl_getinfo($curl_pointer);
            curl_close($curl_pointer);

            list ($headers, $content) = explode("\r\n\r\n", $result, 2);
            if(strpos($headers," 100 Continue")!==false){
                list( $headers, $content) = explode( "\r\n\r\n", $content , 2);
            }
            $headerArray = (explode("\r\n", $headers, 50));
            $headerMap = array();
            foreach ($headerArray as $key) {
                if (strpos($key, ":") != false) {
                    $firstHalf = substr($key, 0, strpos($key, ":"));
                    $secondHalf = substr($key, strpos($key, ":") + 1);
                    $headerMap[$firstHalf] = trim($secondHalf);
                }
            }
            $jsonResponse = json_decode($content, true);
            if ($jsonResponse == null && $responseInfo['http_code'] != 204) {
                list ($headers, $content) = explode("\r\n\r\n", $content, 2);
                $jsonResponse = json_decode($content, true);
            }
            return $jsonResponse['data'];
        }
    }    
}*/

/**
 * @param $string
 * @return string
 */
function pas_encrypt($string) {
    $encrypt_method = "AES-256-CBC";
    $secret_key = env('ENCRYPTION_SECRET_PRIVATE', 'AA74CDCC2BBRT935136HH7B63C27'); // user define private key
    $secret_iv = env('ENCRYPTION_SECRET_KEY', '5fgf5HJ5g27'); // user define secret key
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
    $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
    $output = base64_encode($output);

    return $output;
}

/**
 * @param $string
 * @return string
 */
function pas_decrypt($string) {
    $encrypt_method = "AES-256-CBC";
    $secret_key = env('ENCRYPTION_SECRET_PRIVATE', 'AA74CDCC2BBRT935136HH7B63C27'); // user define private key
    $secret_iv = env('ENCRYPTION_SECRET_KEY', '5fgf5HJ5g27'); // user define secret key
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
    $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    return $output;
}

function getPartners($partner_id = null){
    $query = \App\Models\Partner::select(['id', \Illuminate\Support\Facades\DB::raw('CAST(zoho_id AS CHAR) AS zoho_id'), 'canvas_sub_account_id', 'partner_name', 'contact_name', 'hosted_site', 'title', 'phone', 'email', 'pi_phone', 'pi_email', 'department', 'wia', 'mycaa', 'street','city', 'state', 'zip_code', 'price_book_id', 'price_book_zoho_id', 'logo', 'status'])
        ->where('partner_type', '=', 'Active');
    if($partner_id){
        $query->where('id', '=', $partner_id);
    }
    return $query->orderBy('partner_name', 'ASC')->get()->toArray();
}

function send_php_email($to, $subject, $message){
    // To send HTML mail, the Content-type header must be set
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=iso-8859-1';

    // Additional headers
    //$headers[] = 'To: Mary <mary@example.com>, Kelly <kelly@example.com>';
    $headers[] = $to;
    $headers[] = 'From: WE Education DEV <info@partner-worldeducation.net>';
    //$headers[] = 'Cc: birthdayarchive@example.com';
    //$headers[] = 'Bcc: birthdaycheck@example.com';

    if(mail($to, $subject, $message, implode("\r\n", $headers))){
        dump('Email Sent');
    }else{
        dump('Email Failed');
    }
}