<?php
/**
 * Created by PhpStorm.
 * User: rajneeshgautam
 * Date: 29/04/21
 * Time: 2:45 PM
 */

namespace App;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ZohoHelper
{
    private $api_domain;
    private $refresh_token;
    private $access_token;
    private $base_url = 'https://accounts.zoho.com/oauth/v2/';

    const AUTHORIZATION = 'Authorization';
    const ZOHO_OAUTHTOKEN = 'Zoho-oauthtoken';

    private static $obj = null;

    const API_VERSION = '/crm/v2/';
    const API_VERSION_FIELD_META = '/crm/v2.1/';
    const API_VERSION_SUB_FORM = '/crm/v3/';

    /**
     * @return ZohoHelper|null
     */
    public static function getInstance(){
        if(self::$obj instanceof ZohoHelper){
            return self::$obj;
        }
        return self::$obj = new ZohoHelper();
    }

    /**
     * ZohoHelper constructor.
     */
    private function __construct(){
        $zoho_data = DB::table('pas_zoho_token')->select('id', 'access_token', 'api_domain', 'refresh_token', 'expires_at')->orderBy('id', 'DESC')->first();

        if(!$zoho_data){
            die('ZOHO Token is not found');
        }

        $this->access_token = $zoho_data->access_token;
        $this->api_domain = $zoho_data->api_domain;

        $current_time = Carbon::now(Utility::DEFAULT_TIME_ZONE)->format(Utility::DB_DATE_TIME_FORMAT);
        if($current_time > $zoho_data->expires_at){
            $client_id = $_ENV['ZOHO_CLIENT_ID'];
            $client_secret = $_ENV['ZOHO_CLIENT_SECRET'];

            $url = $this->base_url."token?refresh_token=$zoho_data->refresh_token&client_id=$client_id&grant_type=refresh_token&client_secret=$client_secret";

            $curl = curl_init($url);
            curl_setopt_array($curl, array(
                CURLOPT_POST => 1,
                CURLOPT_RETURNTRANSFER => true
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                Log::channel('zoho')->error('Refresh Token Generate Failed', $err);
                return array("cURL Error #:" . $err);
            } else {
                if($response){
                    $refresh_token = json_decode($response);
                    if(isset($refresh_token->error)){
                        die("error ". $refresh_token->error);
                    }

                    DB::table('pas_zoho_token')->insert([
                        'access_token' => $refresh_token->access_token,
                        'refresh_token' => $zoho_data->refresh_token,
                        'api_domain' => $refresh_token->api_domain,
                        'expires_at' => Carbon::now()->addSeconds($refresh_token->expires_in)->format(Utility::DB_DATE_TIME_FORMAT),
                        'created_at' => Carbon::now()->format(Utility::DB_DATE_TIME_FORMAT)
                    ]);

                    $last_id = DB::getPdo()->lastInsertId();
                    if(empty($last_id)){
                        Log::channel('zoho')->error('Token could not saved into database');
                        die('ZOHO API error.');
                    }

                    $this->api_domain = $refresh_token->api_domain;
                    $this->access_token = $refresh_token->access_token;
                }
            }
        }
    }

    private function __clone() {

    }

    /** NOT USED CURRENTLY
     * @return array
     */
    public function fetch($module_name, $fields = [], $page_num = 1, $per_page_records = 200, $last_modify = null){
        $curl_pointer = curl_init();
        $curl_options = array();
        $url = $this->api_domain . self::API_VERSION . $module_name . "?";
        $parameters = array();
        $parameters["page"] = $page_num;
        $parameters["per_page"] = $per_page_records;
        $parameters["include_child"] = "false";
        if(count($fields) > 0){
            $parameters["fields"]= implode(',', $fields);
        }

        foreach ($parameters as $key => $value) {
            $url = $url . $key . "=" . $value . "&";
        }

        $curl_options[CURLOPT_URL] = $url;
        $curl_options[CURLOPT_RETURNTRANSFER] = true;
        $curl_options[CURLOPT_HEADER] = 1;
        $curl_options[CURLOPT_CUSTOMREQUEST] = "GET";
        $headersArray = array();

        $headersArray[] = self::AUTHORIZATION . ":" . self::ZOHO_OAUTHTOKEN . " " . $this->access_token;
        if(!empty($last_modify)){
            $headersArray[] = "If-Modified-Since" . ":" . $last_modify;
        }
        $curl_options[CURLOPT_HTTPHEADER] = $headersArray;
        curl_setopt_array($curl_pointer, $curl_options);

        $result = curl_exec($curl_pointer);
        $responseInfo = curl_getinfo($curl_pointer);
        curl_close($curl_pointer);
        list ($headers, $content) = explode("\r\n\r\n", $result, 2);
        if (strpos($headers, " 100 Continue") !== false) {
            list($headers, $content) = explode("\r\n\r\n", $content, 2);
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
            $jsonResponse = json_decode($content, true);
        }

        if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
            Log::channel('zoho')->error('Error in fetch() function', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['message']
            ];
        }

        if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
            Log::channel('zoho')->error('Error in fetch() function with data error', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
            ];
        }

        return ['status' => 'success', 'message' => 'Operation successful.', 'data' => $jsonResponse ? $jsonResponse:[]];
    }

    public function fetchRelatedRecords($module_name_with_id, $related, $page_num = 1, $per_page_records = 200, $last_modify = null, $fields = []){
        $curl_pointer = curl_init();
        $curl_options = array();
        $url = $this->api_domain. self::API_VERSION . $module_name_with_id . '/' .$related.'?';
        $parameters = array();
        $parameters["page"] = $page_num;
        $parameters["per_page"] = $per_page_records;

        if(count($fields) > 0){
            $parameters["fields"]= implode(',', $fields);
        }

        foreach ($parameters as $key => $value) {
            $url = $url . $key . "=" . $value . "&";
        }

        $curl_options[CURLOPT_URL] = $url;
        $curl_options[CURLOPT_RETURNTRANSFER] = true;
        $curl_options[CURLOPT_HEADER] = 1;
        $curl_options[CURLOPT_CUSTOMREQUEST] = "GET";
        $headersArray = array();

        $headersArray[] = self::AUTHORIZATION . ":" . self::ZOHO_OAUTHTOKEN . " " . $this->access_token;
        if(!empty($last_modify)){
            $headersArray[] = "If-Modified-Since" . ":" . $last_modify;
        }
        $curl_options[CURLOPT_HTTPHEADER] = $headersArray;
        curl_setopt_array($curl_pointer, $curl_options);

        $result = curl_exec($curl_pointer);
        $responseInfo = curl_getinfo($curl_pointer);
        curl_close($curl_pointer);
        list ($headers, $content) = explode("\r\n\r\n", $result, 2);
        if (strpos($headers, " 100 Continue") !== false) {
            list($headers, $content) = explode("\r\n\r\n", $content, 2);
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
            $jsonResponse = json_decode($content, true);
        }

        if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
            Log::channel('zoho')->error('Error in fetchRelatedRecords() function', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['message']
            ];
        }

        if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
            Log::channel('zoho')->error('Error in fetchRelatedRecords() function with data error', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
            ];
        }

        return ['status' => 'success', 'message' => 'Operation successful.', 'data' => $jsonResponse ? $jsonResponse:[]];
    }

    public function fetchByIds($module_name, $ids = [], $fields = []){
        $curl_pointer = curl_init();

        $curl_options = array();
        $url = $this->api_domain. self::API_VERSION . $module_name . "?";

        $parameters = array();

        if(count($fields) > 0){
            $parameters["fields"] = implode(',', $fields);
        }

        if(count($ids) > 0) {
            $parameters["ids"] = implode(',', $ids);
        }

        foreach ($parameters as $key => $value) {
            $url = $url . $key . "=" . $value . "&";
        }

        $curl_options[CURLOPT_URL] = $url;
        $curl_options[CURLOPT_RETURNTRANSFER] = true;
        $curl_options[CURLOPT_HEADER] = 1;
        $curl_options[CURLOPT_CUSTOMREQUEST] = "GET";

        $headersArray = array();

        $headersArray[] = self::AUTHORIZATION . ":" . self::ZOHO_OAUTHTOKEN . " " . $this->access_token;
        $curl_options[CURLOPT_HTTPHEADER] = $headersArray;
        curl_setopt_array($curl_pointer, $curl_options);

        $result = curl_exec($curl_pointer);
        $responseInfo = curl_getinfo($curl_pointer);
        curl_close($curl_pointer);
        list ($headers, $content) = explode("\r\n\r\n", $result, 2);
        if (strpos($headers, " 100 Continue") !== false) {
            list($headers, $content) = explode("\r\n\r\n", $content, 2);
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
            //list ($headers, $content) = explode("\r\n\r\n", $content, 2);
            $jsonResponse = json_decode($content, true);
        }

        if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
            Log::channel('zoho')->error('Error in fetchByIds() function', $jsonResponse);
            /*return [
                'status' => 'error',
                'message' => $jsonResponse['message']
            ];*/
        }

        if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
            Log::channel('zoho')->error('Error in fetchByIds() function with data error', $jsonResponse);
            /*return [
                'status' => 'error',
                'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
            ];*/
        }

        return $jsonResponse;
    }

    /**
     * @param $module_name
     * @param $id
     * @param $entity
     * @param $destination
     * @return bool|mixed|string
     */
    public function downloadPhoto($module_name, $id, $entity, $destination){
            $curl_pointer = curl_init();

            $curl_options = array();
            $url = $this->api_domain. self::API_VERSION . $module_name .'/'. $id. '/' .$entity;

            $curl_options[CURLOPT_URL] = $url;
            $curl_options[CURLOPT_RETURNTRANSFER] = true;
            $curl_options[CURLOPT_HEADER] = 1;
            $curl_options[CURLOPT_CUSTOMREQUEST] = "GET";

            $headersArray = array();

            $headersArray[] = self::AUTHORIZATION . ":" . self::ZOHO_OAUTHTOKEN . " " . $this->access_token;
            $curl_options[CURLOPT_HTTPHEADER] = $headersArray;
            curl_setopt_array($curl_pointer, $curl_options);

            $result = curl_exec($curl_pointer);
            $responseInfo = curl_getinfo($curl_pointer);
            curl_close($curl_pointer);
            list ($headers, $content) = explode("\r\n\r\n", $result, 2);
            if (strpos($headers, " 100 Continue") !== false) {
                list($headers, $content) = explode("\r\n\r\n", $content, 2);
            }
            $headerArray = (explode("\r\n", $headers, 50));
            $headerMap = array();
            foreach ($headerArray as $key) {
                if (strpos($key, ":") !== false) {
                    $firstHalf = substr($key, 0, strpos($key, ":"));
                    $secondHalf = substr($key, strpos($key, ":") + 1);
                    $headerMap[$firstHalf] = trim($secondHalf);
                }
            }

            $jsonResponse = $content;
            if ($jsonResponse == null && $responseInfo['http_code'] != 204) {
                list ($headers, $content) = explode("\r\n\r\n", $content, 2);
                $jsonResponse = json_decode($content, true);
            }

            if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
                Log::channel('zoho')->error('Error in downloadPhoto() function', $jsonResponse);
                /*return [
                    'status' => 'error',
                    'message' => $jsonResponse['message']
                ];*/
            }

            if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
                Log::channel('zoho')->error('Error in downloadPhoto() function with data error', $jsonResponse);
                /*return [
                    'status' => 'error',
                    'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
                ];*/
            }

            $contentDisp = $headerMap['Content-Disposition'];
            $fileName = substr($contentDisp, strrpos($contentDisp, "'") + 1, strlen($contentDisp));

            if (strpos($fileName, "=") !== false) {
                $fileName = substr($fileName, strrpos($fileName, "=") + 1, strlen($fileName));

                $fileName = str_replace(array(
                    '\'',
                    '"'
                ), '', $fileName);
            }
            Storage::disk($destination)->delete($fileName);
            Storage::disk($destination)
                ->put($fileName, $jsonResponse);

            /*$fp = fopen($destination . $fileName, "w");
            $stream = $jsonResponse;
            fputs($fp, $stream);
            fclose($fp);*/
            return $fileName;
    }

    public function uploadPhoto($module_name, $id, $filePath, $fileName){
            $curl_pointer = curl_init();

            $curl_options = array();
            $url = $this->api_domain. self::API_VERSION . $module_name .'/'. $id. '/photo';

            $curl_options[CURLOPT_URL] = $url;
            $curl_options[CURLOPT_RETURNTRANSFER] = true;
            $curl_options[CURLOPT_HEADER] = 1;
            $curl_options[CURLOPT_CUSTOMREQUEST] = "POST";
            $file = fopen($filePath, "rb");
            $fileData = fread($file, filesize($filePath));
            $date = new \DateTime();

            $current_time_long = $date->getTimestamp();

            $lineEnd = "\r\n";

            $hypen = "--";

            $contentDisp = "Content-Disposition: form-data; name=\""."file"."\";filename=\"".$fileName."\"".$lineEnd.$lineEnd;


            $data = utf8_encode($lineEnd);

            $boundaryStart = utf8_encode($hypen.(string)$current_time_long.$lineEnd) ;

            $data = $data.$boundaryStart;

            $data = $data.utf8_encode($contentDisp);

            $data = $data.$fileData.utf8_encode($lineEnd);

            $boundaryend = $hypen.(string)$current_time_long.$hypen.$lineEnd.$lineEnd;

            $data = $data.utf8_encode($boundaryend);

            $curl_options[CURLOPT_POSTFIELDS]= $data;

            $headersArray = ['ENCTYPE: multipart/form-data','Content-Type:multipart/form-data;boundary='.(string)$current_time_long];
            $headersArray[] = "content-type".":"."multipart/form-data";
            $headersArray[] = self::AUTHORIZATION . ":" . self::ZOHO_OAUTHTOKEN . " " . $this->access_token;

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

            if(isset($jsonResponse['status']) ){
                if($jsonResponse['status'] == 'error'){
                    Log::channel('zoho')->error('Error in uploadPhoto() function with data error', $jsonResponse);
                    return [
                        'status' => 'error',
                        'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
                    ];
                }else if($jsonResponse['status'] == 'success'){
                    return [
                        'status' => 'success',
                        'message' => $jsonResponse['message']
                    ];
                }
            }

            Log::channel('zoho')->error('Error in uploadPhoto() function', $jsonResponse);

            return [
                'status' => 'error',
                'message' => $jsonResponse['message']
            ];
    }

    /**
     * @return array
     */
    public function addRecord($data, $module_name) {
        $json_obj['data'] = $data;
        $json_obj['trigger'] = [
            'approval',
            'workflow',
            'blueprint'
        ];
        $ch = curl_init();
        $url = $this->api_domain. self::API_VERSION . $module_name;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json_obj));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Zoho-oauthtoken " .$this->access_token,
            "Content-Type: application/x-www-form-urlencoded",
        ]);

        $response = curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        curl_close($ch);


        $jsonResponse = json_decode($response, true);
        if ($jsonResponse == null && $responseInfo['http_code'] != 204) {
            $jsonResponse = json_decode($response, true);
        }

        if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
            Log::channel('zoho')->error('Error in addRecord() function', $jsonResponse);
            return [
                'status' => 'error',
                'message' => (isset($jsonResponse['details']['api_name']) ? $jsonResponse['details']['api_name']:'').' '.$jsonResponse['message']
            ];
        }

        if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
            Log::channel('zoho')->error('Error in addRecord() function with data error', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
            ];
        }

        return $jsonResponse['data'];
    }

    public function addRecordDebug($data, $module_name) {
        $json_obj['data'] = $data;
        $json_obj['trigger'] = [
            'approval',
            'workflow',
            'blueprint'
        ];
        dump($json_obj);
        $ch = curl_init();
        $url = $this->api_domain. self::API_VERSION . $module_name;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json_obj));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Zoho-oauthtoken " .$this->access_token,
            "Content-Type: application/x-www-form-urlencoded",
        ]);
//dump($ch);
        $response = curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        curl_close($ch);


        $jsonResponse = json_decode($response, true);
        if ($jsonResponse == null && $responseInfo['http_code'] != 204) {
            $jsonResponse = json_decode($response, true);
        }
dump($jsonResponse);
        if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
            Log::channel('zoho')->error('Error in addRecordDebug() function', $jsonResponse);
            return [
                'status' => 'error',
                'message' => (isset($jsonResponse['details']['api_name']) ? $jsonResponse['details']['api_name']:'').' '.$jsonResponse['message']
            ];
        }

        if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
            Log::channel('zoho')->error('Error in addRecordDebug() function with data error', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
            ];
        }

        return $jsonResponse['data'];
    }

    /**
     * @return array
     */
    public function updateRecord($data, $module_name) {
            $json_obj['data'] = $data;
            $json_obj['trigger'] = [
                'approval',
                'workflow',
                'blueprint'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.zohoapis.com/crm/v2/'.$module_name);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json_obj));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Zoho-oauthtoken " .$this->access_token,
                "Content-Type: application/x-www-form-urlencoded",
            ]);

            $response = curl_exec($ch);
            $responseInfo = curl_getinfo($ch);
            curl_close($ch);


            $jsonResponse = json_decode($response, true);

            if ($jsonResponse == null && $responseInfo['http_code'] != 204) {
                $jsonResponse = json_decode($response, true);
            }

            if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
                Log::channel('zoho')->error('Error in updateRecord() function', $jsonResponse);
                return [
                    'status' => 'error',
                    'message' => $jsonResponse['message']
                ];
            }

            if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
                Log::channel('zoho')->error('Error in updateRecord() function with data error', $jsonResponse);
                return [
                    'status' => 'error',
                    'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
                ];
            }

            return ['status' => 'success', 'message' => 'Operation successful.', 'data' => $jsonResponse ? $jsonResponse:[]];
    }

    /**
     * @param $module_name_with_id
     * @param $related
     * @param $related_ids_to_be_deleted
     * @return array
     */
    public function deleteRelatedRecord($module_name_with_id, $related, $related_ids_to_be_deleted) {
        $curl_pointer = curl_init();
        $curl_options = array();
        $url = $this->api_domain. self::API_VERSION . $module_name_with_id . '/' .$related.'?';
        $parameters = array();

        $parameters["ids"] = implode(',', $related_ids_to_be_deleted);
        foreach ($parameters as $key=>$value){
            $url =$url.$key."=".$value."&";
        }

        $curl_options[CURLOPT_URL] = $url;
        $curl_options[CURLOPT_RETURNTRANSFER] = true;
        $curl_options[CURLOPT_HEADER] = 1;
        $curl_options[CURLOPT_CUSTOMREQUEST] = "DELETE";
        $headersArray = array();
        $headersArray[] = self::AUTHORIZATION. ":" . self::ZOHO_OAUTHTOKEN . " " .$this->access_token;
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
            $jsonResponse = json_decode($content, true);
        }

        if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
            Log::channel('zoho')->error('Error in deleteRelatedRecord() function with data error', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['message']
            ];
        }

        if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
            Log::channel('zoho')->error('Error in deleteRelatedRecord() function with data error', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
            ];
        }

        return ['status' => 'success', 'message' => 'Operation successful.', 'data' => $jsonResponse ? $jsonResponse:[]];
    }

    /**
     * @param $module_name
     * @param $record_ids
     * @return array
     */
    public function deleteRecords($module_name, $record_ids) {
        $curl_pointer = curl_init();
        $curl_options = array();
        $url = $this->api_domain. self::API_VERSION . $module_name .'?';
        $parameters = array();

        $parameters["ids"] = implode(',', $record_ids);
        foreach ($parameters as $key=>$value){
            $url =$url.$key."=".$value."&";
        }

        $curl_options[CURLOPT_URL] = $url;
        $curl_options[CURLOPT_RETURNTRANSFER] = true;
        $curl_options[CURLOPT_HEADER] = 1;
        $curl_options[CURLOPT_CUSTOMREQUEST] = "DELETE";
        $headersArray = array();
        $headersArray[] = self::AUTHORIZATION. ":" . self::ZOHO_OAUTHTOKEN . " " .$this->access_token;
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
            $jsonResponse = json_decode($content, true);
        }

        if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
            Log::channel('zoho')->error('Error in deleteRecords() function with data error', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['message']
            ];
        }

        if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
            Log::channel('zoho')->error('Error in deleteRecords() function with data error', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
            ];
        }

        return ['status' => 'success', 'message' => 'Operation successful.', 'data' => $jsonResponse ? $jsonResponse:[]];
    }

    /**
     * @param $module_name_with_id
     * @param $related
     * @param $data_to_be_updated
     * @return array
     */
    public function updateRelatedRecord($module_name_with_id, $related, $data_to_be_updated) {

        $curl_pointer = curl_init();

        $curl_options = array();
        $url = $this->api_domain. self::API_VERSION . $module_name_with_id . '/' .$related.'?';

        $curl_options[CURLOPT_URL] =$url;
        $curl_options[CURLOPT_RETURNTRANSFER] = true;
        $curl_options[CURLOPT_HEADER] = 1;
        $curl_options[CURLOPT_CUSTOMREQUEST] = "PUT";
        $requestBody = array();
        $recordArray = array();

        $recordArray[] = $data_to_be_updated;
        $requestBody["data"] = $recordArray;
        $curl_options[CURLOPT_POSTFIELDS]= json_encode($requestBody);
        $headersArray = array();

        $headersArray[] = self::AUTHORIZATION. ":" . self::ZOHO_OAUTHTOKEN . " " . $this->access_token;

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
            $jsonResponse = json_decode($content, true);
        }

        if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
            Log::channel('zoho')->error('Error in updateRelatedRecord() function', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['message']
            ];
        }

        if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
            Log::channel('zoho')->error('Error in updateRelatedRecord() function with data error', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
            ];
        }

        return ['status' => 'success', 'message' => 'Operation successful.', 'data' => $jsonResponse ? $jsonResponse:[]];
    }

    public function notificationEnable($events, $channel_id, $channel_expiry = null, $extra_parameters = []){
        $curl_pointer = curl_init();

        $curl_options = array();
        $url = $this->api_domain."/crm/v2/actions/watch";

        $curl_options[CURLOPT_URL] =$url;
        $curl_options[CURLOPT_RETURNTRANSFER] = true;
        $curl_options[CURLOPT_HEADER] = 1;
        $curl_options[CURLOPT_CUSTOMREQUEST] = "POST";
        $requestBody = array();
        $recordArray = array();
        $recordObject = array();


        $recordObject["channel_id"] = $channel_id;
        if(!$channel_expiry){
            $date = \DateTime::createFromFormat("H:i:s", "08:00:00");
            $today_midnight = $date->format("c");

            $recordObject["channel_expiry"] = $today_midnight;
        }else{
            $recordObject["channel_expiry"] = $channel_expiry;
        }
        $recordObject["notify_url"] = $_ENV['APP_URL']."/zoho/notification-callback?channel_id=".$channel_id;
        if(count($extra_parameters) > 0) {
            $url_with_params = [];
            foreach ($extra_parameters as $key => $extra_parameter) {
                $url_with_params[] = $key . "=" . $extra_parameter;
            }
            if(count($url_with_params) > 0){
                $recordObject["notify_url"] .= '&'.implode($url_with_params, '&');
            }

        }

        $recordObject["events"] = $events;

        dump($recordObject);

        $recordArray[] = $recordObject;
        $requestBody["watch"] =$recordArray;
        $curl_options[CURLOPT_POSTFIELDS]= json_encode($requestBody);
        $headersArray = array();

        $headersArray[] = self::AUTHORIZATION. ":" . self::ZOHO_OAUTHTOKEN . " " .$this->access_token;

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

        if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
            Log::channel('zoho')->error('Error in notificationEnable() function', $jsonResponse);
            /*return [
                'status' => 'error',
                'message' => $jsonResponse['message']
            ];*/
        }

        if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
            Log::channel('zoho')->error('Error in notificationEnable() function with data error', $jsonResponse);
            /*return [
                'status' => 'error',
                'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
            ];*/
        }

        return $jsonResponse;

    }

    public function notificationDetail($channel_id){
        $curl_pointer = curl_init();

        $curl_options = array();
        $url = $this->api_domain."/crm/v2/actions/watch?";
        $parameters = array();
        $parameters["channel_id"] = $channel_id;

        foreach ($parameters as $key => $value){
            $url =$url.$key."=".$value."&";
        }
        $curl_options[CURLOPT_URL] = $url;
        $curl_options[CURLOPT_RETURNTRANSFER] = true;
        $curl_options[CURLOPT_HEADER] = 1;
        $curl_options[CURLOPT_CUSTOMREQUEST] = "GET";
        $headersArray = array();

        $headersArray[] = self::AUTHORIZATION. ":" . self::ZOHO_OAUTHTOKEN . " " .$this->access_token;
        $curl_options[CURLOPT_HTTPHEADER] = $headersArray;

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

        if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
            Log::channel('zoho')->error('Error in notificationDetail() function', $jsonResponse);
            /*return [
                'status' => 'error',
                'message' => $jsonResponse['message']
            ];*/
        }

        if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
            Log::channel('zoho')->error('Error in notificationDetail() function with data error', $jsonResponse);
            /*return [
                'status' => 'error',
                'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
            ];*/
        }

        return $jsonResponse;
    }

    public function notificationDetailUpdate($events, $channel_id, $channel_expiry = null, $extra_parameters =[], $token = null){
        $curl_pointer = curl_init();

        $curl_options = array();
        $url = $this->api_domain."/crm/v2/actions/watch";

        $curl_options[CURLOPT_URL] =$url;
        $curl_options[CURLOPT_RETURNTRANSFER] = true;
        $curl_options[CURLOPT_HEADER] = 1;
        $curl_options[CURLOPT_CUSTOMREQUEST] = "PUT";
        $requestBody = array();
        $recordArray = array();
        $recordObject = array();

        $recordObject["channel_id"] = $channel_id;
        if(!$channel_expiry){
            $date = \DateTime::createFromFormat("H:i:s", "08:00:00");
            $today_midnight = $date->format("c");
            $recordObject["channel_expiry"] = $today_midnight;
        }else{
            $recordObject["channel_expiry"] = $channel_expiry;
        }
        if($token){
            $recordObject["token"] = $token;
        }

        $recordObject["notify_url"] = $_ENV['APP_URL']."/zoho/notification-callback?channel_id=".$channel_id;
        if(count($extra_parameters) > 0) {
            $url_with_params = [];
            foreach ($extra_parameters as $key => $extra_parameter) {
                $url_with_params[] = $key . "=" . $extra_parameter;
            }
            if(count($url_with_params) > 0){
                $recordObject["notify_url"] .= '&'.implode($url_with_params, '&');
            }

        }
        $recordObject["events"] = $events;

        dump($recordObject);

        $recordArray[] = $recordObject;
        $requestBody["watch"] =$recordArray;
        $curl_options[CURLOPT_POSTFIELDS]= json_encode($requestBody);
        $headersArray = array();

        $headersArray[] = self::AUTHORIZATION. ":" . self::ZOHO_OAUTHTOKEN . " " .$this->access_token;

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

        if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
            Log::channel('zoho')->error('Error in notificationDetailUpdate() function', $jsonResponse);
            /*return [
                'status' => 'error',
                'message' => $jsonResponse['message']
            ];*/
        }

        if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
            Log::channel('zoho')->error('Error in notificationDetailUpdate() function with data error', $jsonResponse);
            /*return [
                'status' => 'error',
                'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
            ];*/
        }

        return $jsonResponse;
    }

    public function fetchCriteria($module_name, $fields = [], $page_num = 1, $per_page_records = 200, $criteria = []){
        $curl_pointer = curl_init();
        $curl_options = array();
        $url = $this->api_domain . self::API_VERSION . $module_name . "/search?";
        $parameters = array();
        $parameters["page"] = $page_num;
        $parameters["per_page"] = $per_page_records;
        $parameters["include_child"] = "false";
        if(count($fields) > 0){
            $parameters["fields"]= implode(',', $fields);
        }
        $criteria_params = [];
        if($criteria && count($criteria) > 0){
            foreach ($criteria as $key => $value) {
                $criteria_params[] = implode(':',$value);
            }
        }
        if(count($criteria_params) > 0){
            $url .= 'criteria=(('.implode(')and(', $criteria_params).'))&';
        }
        foreach ($parameters as $key => $value) {
            $url = $url . $key . "=" . $value . "&";
        }
        //dd($url);
        //$url = 'https://www.zohoapis.com/crm/v2/Sales_Orders/search?criteria=(Deal_Name.id:equals:4838579000002671135)&page=1&per_page=200&include_child=false';
        //$url = 'https://www.zohoapis.com/crm/v2/Sales_Orders/search?criteria=((Deal_Name.id:equals:4838579000002671135)and(Status:equals:Expired))&page=1&per_page=200&include_child=false';
        //dd($url);
        $curl_options[CURLOPT_URL] = $url;
        $curl_options[CURLOPT_RETURNTRANSFER] = true;
        $curl_options[CURLOPT_HEADER] = 1;
        $curl_options[CURLOPT_CUSTOMREQUEST] = "GET";
        $headersArray = array();

        $headersArray[] = self::AUTHORIZATION . ":" . self::ZOHO_OAUTHTOKEN . " " . $this->access_token;
        if(!empty($last_modify)){
            $headersArray[] = "If-Modified-Since" . ":" . $last_modify;
        }
        $curl_options[CURLOPT_HTTPHEADER] = $headersArray;
        curl_setopt_array($curl_pointer, $curl_options);

        $result = curl_exec($curl_pointer);
        $responseInfo = curl_getinfo($curl_pointer);
        curl_close($curl_pointer);
        list ($headers, $content) = explode("\r\n\r\n", $result, 2);
        if (strpos($headers, " 100 Continue") !== false) {
            list($headers, $content) = explode("\r\n\r\n", $content, 2);
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
            $jsonResponse = json_decode($content, true);
        }

        if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
            Log::channel('zoho')->error('Error in fetchCriteria() function', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['message']
            ];
        }

        if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
            Log::channel('zoho')->error('Error in fetchCriteria() function with data error', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
            ];
        }

        return [
                'status' => 'success',
                'message' => 'Operation successful.',
                'data' => $jsonResponse ? $jsonResponse['data']:[],
                'info' => $jsonResponse ? $jsonResponse['info']:[]
            ];
    }

    public function fetchUsers($type = 'AllUsers', $page_num = 1, $per_page_records = 200, $last_modify = null){
        $curl_pointer = curl_init();

        $curl_options = array();
        $url = "https://www.zohoapis.com/crm/v2/users?";
        $parameters = array();
        $parameters["type"] = $type;
        $parameters["page"] = $page_num;
        $parameters["per_page"] = $per_page_records;
        foreach ($parameters as $key=>$value){
            $url =$url.$key."=".$value."&";
        }

        $curl_options[CURLOPT_URL] = $url;
        $curl_options[CURLOPT_RETURNTRANSFER] = true;
        $curl_options[CURLOPT_HEADER] = 1;
        $curl_options[CURLOPT_CUSTOMREQUEST] = "GET";
        $headersArray = array();

        $headersArray[] = self::AUTHORIZATION . ":" . self::ZOHO_OAUTHTOKEN . " " . $this->access_token;
        if(!empty($last_modify)){
            $headersArray[] = "If-Modified-Since" . ":" . $last_modify;
        }
        $curl_options[CURLOPT_HTTPHEADER] = $headersArray;

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
        //dd($jsonResponse);
        if ($jsonResponse == null && $responseInfo['http_code'] != 204) {
            list ($headers, $content) = explode("\r\n\r\n", $content, 2);
            $jsonResponse = json_decode($content, true);
        }

        if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
            Log::channel('zoho')->error('Error in fetchUsers() function with data error', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['message']
            ];
        }

        if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
            Log::channel('zoho')->error('Error in fetchUsers() function with data error', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
            ];
        }

        return ['status' => 'success', 'message' => 'Operation successful.', 'data' => $jsonResponse ? $jsonResponse:[]];
    }

    /** NOT USED CURRENTLY
     * @return array
     */
    public function fetchFieldMeta($module_name, $type = 'all', $page_num = 1, $per_page_records = 200, $last_modify = null){
        $curl_pointer = curl_init();
        $curl_options = array();
        $url = $this->api_domain . self::API_VERSION_FIELD_META . 'settings/fields' . "?module=".$module_name.'&type='.$type;
        /*$parameters = array();
        $parameters["page"] = $page_num;
        $parameters["per_page"] = $per_page_records;
        $parameters["include_child"] = "false";


        foreach ($parameters as $key => $value) {
            $url = $url . $key . "=" . $value . "&";
        }*/

        $curl_options[CURLOPT_URL] = $url;
        $curl_options[CURLOPT_RETURNTRANSFER] = true;
        $curl_options[CURLOPT_HEADER] = 1;
        $curl_options[CURLOPT_CUSTOMREQUEST] = "GET";
        $headersArray = array();

        $headersArray[] = self::AUTHORIZATION . ":" . self::ZOHO_OAUTHTOKEN . " " . $this->access_token;
        if(!empty($last_modify)){
            $headersArray[] = "If-Modified-Since" . ":" . $last_modify;
        }
        $curl_options[CURLOPT_HTTPHEADER] = $headersArray;
        curl_setopt_array($curl_pointer, $curl_options);

        $result = curl_exec($curl_pointer);
        $responseInfo = curl_getinfo($curl_pointer);
        curl_close($curl_pointer);
        list ($headers, $content) = explode("\r\n\r\n", $result, 2);
        if (strpos($headers, " 100 Continue") !== false) {
            list($headers, $content) = explode("\r\n\r\n", $content, 2);
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
            $jsonResponse = json_decode($content, true);
        }

        if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
            Log::channel('zoho')->error('Error in fetch() function', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['message']
            ];
        }

        if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
            Log::channel('zoho')->error('Error in fetch() function with data error', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
            ];
        }

        return ['status' => 'success', 'message' => 'Operation successful.', 'data' => $jsonResponse ? $jsonResponse:[]];
    }

    public function addSubForm($data, $module_name){
        $json_obj['data'] = $data;
        $json_obj['trigger'] = [
            'approval',
            'workflow',
            'blueprint'
        ];

        $ch = curl_init();
        $url = $this->api_domain. self::API_VERSION_SUB_FORM . $module_name;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json_obj));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Zoho-oauthtoken " .$this->access_token,
            "Content-Type: application/x-www-form-urlencoded",
        ]);

        $response = curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        curl_close($ch);


        $jsonResponse = json_decode($response, true);
        if ($jsonResponse == null && $responseInfo['http_code'] != 204) {
            $jsonResponse = json_decode($response, true);
        }

        if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
            Log::channel('zoho')->error('Error in addRecord() function', $jsonResponse);
            return [
                'status' => 'error',
                'message' => (isset($jsonResponse['details']['api_name']) ? $jsonResponse['details']['api_name']:'').' '.$jsonResponse['message']
            ];
        }

        if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
            Log::channel('zoho')->error('Error in addRecord() function with data error', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
            ];
        }

        return $jsonResponse['data'];
    }

    public function fetchSubForm($module_name, $module_id){
        $curl_pointer = curl_init();
        $curl_options = array();
        $url = $this->api_domain . self::API_VERSION_SUB_FORM .$module_name.'/'.$module_id;

        $curl_options[CURLOPT_URL] = $url;
        $curl_options[CURLOPT_RETURNTRANSFER] = true;
        $curl_options[CURLOPT_HEADER] = 1;
        $curl_options[CURLOPT_CUSTOMREQUEST] = "GET";
        $headersArray = array();

        $headersArray[] = self::AUTHORIZATION . ":" . self::ZOHO_OAUTHTOKEN . " " . $this->access_token;
        if(!empty($last_modify)){
            $headersArray[] = "If-Modified-Since" . ":" . $last_modify;
        }
        $curl_options[CURLOPT_HTTPHEADER] = $headersArray;
        curl_setopt_array($curl_pointer, $curl_options);

        $result = curl_exec($curl_pointer);
        $responseInfo = curl_getinfo($curl_pointer);
        curl_close($curl_pointer);
        list ($headers, $content) = explode("\r\n\r\n", $result, 2);
        if (strpos($headers, " 100 Continue") !== false) {
            list($headers, $content) = explode("\r\n\r\n", $content, 2);
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
            $jsonResponse = json_decode($content, true);
        }

        if(isset($jsonResponse['status']) && $jsonResponse['status'] == 'error'){
            Log::channel('zoho')->error('Error in fetch() function', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['message']
            ];
        }

        if(isset($jsonResponse['data'][0]) && isset($jsonResponse['data'][0]['status']) && $jsonResponse['data'][0]['status'] == 'error' && $responseInfo['http_code'] == 202){
            Log::channel('zoho')->error('Error in fetch() function with data error', $jsonResponse);
            return [
                'status' => 'error',
                'message' => $jsonResponse['data'][0]['details']['api_name'].' '.$jsonResponse['data'][0]['message']
            ];
        }

        return ['status' => 'success', 'message' => 'Operation successful.', 'data' => $jsonResponse ? $jsonResponse:[]];
    }

}
