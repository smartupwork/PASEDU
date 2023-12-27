<?php
/**
 * Created by PhpStorm.
 * User: rajneeshgautam
 * Date: 29/04/21
 * Time: 2:45 PM
 */

namespace App;

use App\Console\Commands\Canvas\CanvasRequest;
use GuzzleHttp\Client;

class CanvasHelper
{
    private $access_token = '16759~cyIsGCunhZdshE4DklMoYyrcjLtgBzYqOfSagphkmxXunurpapIM4gk7lnUMwsb4';
    private $base_url = 'https://worldeducation.instructure.com';
    private $end_point;
    private $debug = false;

    private static $obj = null;

    const API_VERSION = '/api/v1/';

    const MAIN_ACCOUNT = 1;
    const SUB_ACCOUNT = 43;

    /**
     * @return CanvasHelper|null
     */
    public static function getInstance(){
        if(self::$obj instanceof CanvasHelper){
            return self::$obj;
        }
        return self::$obj = new CanvasHelper();
    }

    /**
     * CanvasHelper constructor.
     */
    private function __construct(){

    }

    private function __clone() {

    }

    // ::::::::::::::::::::::::::::::::::: ACCOUNT/SUB ACCOUNT API's ::::::::::::::::::::::::::::::::::::::

    /**
     * @param CanvasRequest $req
     * @return array|mixed
     */
    public function getSubAccountsOfAccount(CanvasRequest $req){
        $this->end_point = $this->base_url.self::API_VERSION.'accounts/'.$req->account_id.'/sub_accounts';
        return $this->get($req);
    }

    /**
     * @param $account_id
     * @return array|mixed
     */
    public function getAccountDetail($account_id){
        $this->end_point = $this->base_url.self::API_VERSION.'accounts/'.$account_id;
        return $this->get();
    }

    /**
     * @param CanvasRequest $req
     * @return array|mixed
     */
    public function createSubAccountOfAccount(CanvasRequest $req){
        $this->end_point = $this->base_url.self::API_VERSION.'accounts/'.$req->account_id.'/sub_accounts';
        return $this->post($req);
    }

    /**
     * @param $account_id
     * @return array|mixed
     */
    public function deleteAccount($account_id, $event){
        $this->end_point = $this->base_url.self::API_VERSION.'accounts/'.$account_id;
        return $this->delete($event);
    }

    // ::::::::::::::::::::::::::::::::::: COURSE API's ::::::::::::::::::::::::::::::::::::::

    /**
     * @return array|mixed
     */
    public function getCourses(){
        $this->end_point = $this->base_url.self::API_VERSION.'courses';
        return $this->get();
    }

    /**
     * @param CanvasRequest $req
     * @return array|mixed
     */
    public function getCoursesDetail(CanvasRequest $req){
        $this->end_point = $this->base_url.self::API_VERSION.'courses/'.$req->course_id;
        return $this->get($req);
    }

    /**
     * @param CanvasRequest $req
     * @return array|mixed
     */
    public function getCoursesOfAccount(CanvasRequest $req){
        $this->end_point = $this->base_url.self::API_VERSION.'accounts/'.$req->account_id.'/courses';
        return $this->get($req);
    }

    /**
     * @param CanvasRequest $req
     * @return array|mixed
     */
    public function getUsersOfAccount(CanvasRequest $req){
        $this->end_point = $this->base_url.self::API_VERSION.'accounts/'.$req->account_id.'/users';
        return $this->get($req);
    }

    /**
     * @param CanvasRequest $req
     * @return array|mixed
     */
    public function getUserCourses(CanvasRequest $req){
        $this->end_point = $this->base_url.self::API_VERSION.'users/'.$req->user_id.'/courses';
        return $this->get($req);
    }

    // ::::::::::::::::::::::::::::::::::: COURSE GRADEBOOK HISTORY API's ::::::::::::::::::::::::::::::::::::::

    /**
     * @param CanvasRequest $req
     * @return array|mixed
     */
    public function courseGradeBook(CanvasRequest $req){
        $this->end_point = $this->base_url.self::API_VERSION.'courses/'.$req->course_id.'/gradebook_history';
        return $this->get($req);
    }

    /**
     * @param CanvasRequest $req
     * @return array|mixed
     */
    public function courseGradeBookFeed(CanvasRequest $req){
        $this->end_point = $this->base_url.self::API_VERSION.'courses/'.$req->course_id.'/gradebook_history/feed';
        return $this->get($req);
    }

    // ::::::::::::::::::::::::::::::::::: COURSE MODULES API's ::::::::::::::::::::::::::::::::::::::

    /**
     * @param CanvasRequest $req
     * @return array|mixed
     */
    public function courseModules(CanvasRequest $req){
        $this->end_point = $this->base_url.self::API_VERSION.'courses/'.$req->course_id.'/modules';
        return $this->get($req);
    }

    /**
     * @param CanvasRequest $req
     * @return array|mixed
     */
    public function courseModulesDetail(CanvasRequest $req){
        $this->end_point = $this->base_url.self::API_VERSION.'courses/'.$req->course_id.'/modules/'.$req->module_id;
        return $this->get($req);
    }

    // ::::::::::::::::::::::::::::::::::: USER ENROLLMENT API's ::::::::::::::::::::::::::::::::::::::
    /**
     * @param CanvasRequest $req
     * @return array|mixed
     */
    public function userEnrollments(CanvasRequest $req){
        $this->end_point = $this->base_url.self::API_VERSION.'users/'.$req->user_id.'/enrollments';
        return $this->get($req);
    }

    /**
     * @param CanvasRequest $req
     * @return array|mixed
     */
    public function getUserPageViews(CanvasRequest $req){
        $this->end_point = $this->base_url.self::API_VERSION.'users/'.$req->user_id.'/page_views';
        return $this->get($req);
    }

    /**
     * @param $account_id
     * @param $course_id
     * @return array|mixed
     */
    public function getCourseDetailOfAccount($account_id, $course_id){
        $this->end_point = $this->base_url.self::API_VERSION.'accounts/'.$account_id.'/courses/'.$course_id;
        return $this->get();
    }

    /**
     * @param CanvasRequest $req
     * @return array|mixed
     */
    public function createCoursesOfAccount(CanvasRequest $req){
        $this->end_point = $this->base_url.self::API_VERSION.'accounts/'.$req->account_id.'/courses';
        return $this->post($req);
    }

    /**
     * @param CanvasRequest $req
     * @return array|mixed
     */
    public function coursesEnrolled(CanvasRequest $req){
        $this->end_point = $this->base_url.self::API_VERSION.'courses/'.$req->course_id.'/enrollments';
        return $this->post($req);
    }

    /**
     * @param CanvasRequest $req
     * @return array|mixed
     */
    public function updateCoursesOfAccount(CanvasRequest $req){
        $this->end_point = $this->base_url.self::API_VERSION.'accounts/'.$req->account_id.'/courses';
        return $this->put($req);
    }

    public function copyCoursesContent(CanvasRequest $req){
        $this->end_point = $this->base_url.self::API_VERSION.'courses/'.$req->course_id.'/course_copy';
        return $this->post($req);
    }

    public function migrateCoursesContent(CanvasRequest $req){
        $this->end_point = $this->base_url.self::API_VERSION.'courses/'.$req->course_id.'/content_migrations';
        return $this->post($req);
    }

    /**
     * @param $course_id
     * @param $event delete|conclude
     * @return array|mixed
     */
    public function deleteCourses($course_id, $event){
        $this->end_point = $this->base_url.self::API_VERSION.'courses/'.$course_id;
        return $this->delete($event);
    }

    /**
     * @param CanvasRequest $request_data
     * @return array|mixed
     */
    public function post(CanvasRequest $request_data){
        try {

            $client = new Client();

            $response = $client->post($this->end_point, [
                'headers' => [
                    'Authorization' => 'Bearer '. $this->access_token,
                    'Accept'        => 'application/json',
                ],
                'form_params' => $request_data->form_params,
            ]);

            $body = $response->getBody();
            //echo '<pre>';print_r(json_decode((string) $body));

            if($this->debug){
                dump(json_decode($body, true));
            }

            return [
                'status' => true,
                'data' => json_decode($body, true),
            ];
        }
        catch(\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param CanvasRequest $request_data
     * @return array|mixed
     */
    public function put(CanvasRequest $request_data){
        try {

            $client = new Client();

            $response = $client->put($this->end_point, [
                'headers' => [
                    'Authorization' => 'Bearer '. $this->access_token,
                    'Accept'        => 'application/json',
                ],
                'form_params' => $request_data->form_params,
            ]);

            $body = $response->getBody();
            //echo '<pre>';print_r(json_decode((string) $body));

            if($this->debug){
                dump(json_decode($body, true));
            }

            return [
                'status' => true,
                'data' => json_decode($body, true),
            ];
        }
        catch(\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param CanvasRequest $request
     * @return array|mixed
     */
    public function get(CanvasRequest $request){
        try {
            /*$client = new Client();

            $response = $client->get($this->end_point, [
                'headers' => [
                    'Authorization' => 'Bearer '. $this->access_token,
                    'Accept'        => 'application/json',
                ],
                'form_params' => [
                    'page' => $request->page_number,
                    'per_page' => $request->per_page,
                    'search_term' => $request->search_term,
                    'include' => ['terms', 'teachers'],
                ],
            ]);

            $body = $response->getBody();*/
            //echo '<pre>';print_r(json_decode((string) $body));

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->end_point.'?'.$request->getFormParams(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer '.$this->access_token,
                ),
            ));

            $response = curl_exec($curl);

            if($this->debug){
                dump(json_decode($response, true));
            }

            return json_decode($response, true);
        }
        catch(\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param $event
     * @return array|mixed
     */
    public function delete($event){
        try {
            $client = new Client();

            $response = $client->delete($this->end_point, [
                'headers' => [
                    'Authorization' => 'Bearer '. $this->access_token,
                    'Accept'        => 'application/json',
                ],
                'form_params'        => [
                    'event' => $event
                ],
            ]);

            $body = $response->getBody();
            //echo '<pre>';print_r(json_decode((string) $body));

            if($this->debug){
                dump(json_decode($body, true));
            }

            return (json_decode($body, true));
        }
        catch(\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param $request_data
     * @return array
     */
    public function sendWithCurl($request_data){

        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->end_point,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer '.$this->access_token,
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return $response;
        }
        catch(\Exception $e) {
            /*$data['sender_id'] = $this->from;
            $data['mobile'] = $this->to;
            $data['message'] = $this->message;
            $data['api_response'] = $e->getMessage();
            $data['created_by'] = !Auth::guest() ? Auth::user()->id:null;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['is_sent'] = 0;
            $data['user_id'] = $this->user_id;

            DB::table('sms_logs')->insert($data);*/
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }


}