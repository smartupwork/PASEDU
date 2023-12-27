<?php
/**
 * Created by PhpStorm.
 * User: rajneeshgautam
 * Date: 29/04/21
 * Time: 2:45 PM
 */

namespace App;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class UserActivityHelper
{

    private $include_host = [
        'rajneesh.pas.net',
        'dev.partner-worldeducation.net',
        'qa.partner-worldeducation.net',
        'partner-worldeducation.net',
    ];

    private $include_methods = [
        'POST',
        'PUT',
        'DELETE',
    ];

    private $exclude_methods = [
        'GET',
    ];

    private $include_action = [
        'partner-users-delete',
        'we-users-delete',
        'system-email-logs-delete',
        'my-user-delete',
        'student-delete',
        'leads-delete',
        'importaudit-delete',
    ];

    private $exclude_action = [
        ''
    ];

    private static $obj = null;


    /**
     * @return UserActivityHelper|null
     */
    public static function getInstance(){
        if(self::$obj instanceof UserActivityHelper){
            return self::$obj;
        }
        return self::$obj = new UserActivityHelper();
    }

    private function __construct(){

    }

    private function __clone() {

    }

    /**
     * @param Request $request
     * @param $_data
     * @return bool
     */
    public function save(Request $request, $_data) {
        try{
            $data['user_id'] = !Auth::guest() ? Auth::user()->id: null;
            $data['url'] = $request->route()->getName();
            $data['method'] = $request->method();
            $data['ip_address'] = Utility::getClientIp();
            $data['session_id'] = Session::getId();
            $data['user_agent'] = $request->header('user-agent');
            $data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
            $data['created_by'] = !Auth::guest() ? Auth::user()->id: null;

            if(isset($_data['url']) && !empty($_data['url'])){
                $data['url'] = $_data['url'];
            }
            DB::table('pas_user_activity')->insert(array_merge($_data, $data));
            return true;
        }catch (\Exception $e){
            dd($e->getMessage());
            $email_req = new EmailRequest();
            $email_req
                /*->setFromName($_ENV['FROM_NAME'])
                ->setFromEmail($_ENV['FROM_EMAIL'])*/
                ->setTo([
                    ['xoomwebdevelopment@gmail.com', "Xoom Web Development"],
                    ['info@xoomwebdevelopment.com', "Info Xoom Web Development"],
                ])
                ->setSubject('User Activity Log Save Failed')
                ->setBody($e->getMessage())
                //->setCc([[$student->email, $student->student_name]])
                ->setLogSave(true);

            $email_helper = new EmailHelper($email_req);
            $email_helper->sendEmail();

            return false;
        }
    }

}