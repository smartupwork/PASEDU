<?php
/**
 * Created by PhpStorm.
 * User: rajneeshgautam
 * Date: 29/04/21
 * Time: 2:45 PM
 */

namespace App;


use App\Models\User;
use Faker\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Utility
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAIL = 'fail';

    const DEFAULT_DATE_FORMAT = 'm/d/Y';
    const DEFAULT_DATE_TIME_FORMAT = 'm/d/Y h:i A';
    const DEFAULT_TIME_FORMAT = 'h:i A';
    const DEFAULT_TIME_FORMAT_INSTITUTION = 'g:i A';

    const DEFAULT_DATE_FORMAT_MYSQL = '%m/%d/%Y';
    const DEFAULT_DATE_TIME_FORMAT_MYSQL = '%M %d, %Y %k:%i';

    const DB_DATE_FORMAT = 'Y-m-d';
    const DB_DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    const DEFAULT_TIME_ZONE = 'GMT-5';

    const STATUS_ACTIVE = 1;
    const STATUS_LOCKED = 2;

    const ERROR_PAGE_TEMPLATE = 'exception-pages.access-denied';

    public static function validatePassword($password, $confirm_password, $user){
        $response = ["status" => self::STATUS_SUCCESS, "message" => "Password is valid."];
        if($password == ''){
            $response = ["status" => self::STATUS_FAIL,"message" => "Please enter password"];
        }else if(stripos($password, $user->firstname) !== false || stripos($password, $user->lastname) !== false || stripos($password, $user->email) !== false){
            $response =["status" => self::STATUS_FAIL,"message" => "Passwords must not contain your first name, last name and email user name."];
        }else  if(strlen($password) < 10){
            $response =["status" => self::STATUS_FAIL,"message" => "Password must be at least 10 chars long."];
        }else  if(strlen($password) > 16){
            $response =["status" => self::STATUS_FAIL,"message" => "Password must be at most 16 chars long."];
        }else  if(!preg_match('/[A-Z]/', $password)){
            $response =["status" => self::STATUS_FAIL,"message" => "Password must include at least one uppercase."];
        }else if(!preg_match('/[a-z]/', $password)){
            $response =["status" => self::STATUS_FAIL,"message" => "Password use at least 1 lowercase."];
        }else if(!preg_match_all('/[!@#$%^&*()\-_=+{};:,<.>]/',$password, $o)){
            $response =["status" => self::STATUS_FAIL,"message" => "Password use at least 1 special chars."];
        }else if($confirm_password == ''){
            $response =["status" => self::STATUS_FAIL,"message" => "Please enter confirm password."];
        }else if($password != $confirm_password){
            $response =["status" => self::STATUS_FAIL,"message" => "New Password and Confirm Password should be same."];
        }else {
            $is_exists = DB::table('password_history')
                ->where('user_id', '=', $user->id)
                ->where('password', '=', md5($password))
                ->count();

            if($is_exists > 0){
                $response =["status" => self::STATUS_FAIL,"message" => "You can't use any previous password."];
            }
        }
        return $response;
    }

    public static function generateStrongPassword($length = 12, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = array();
        if(strpos($available_sets, 'l') !== false){
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        }
        if(strpos($available_sets, 'u') !== false){
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }
        if(strpos($available_sets, 'd') !== false){
            $sets[] = '23456789';
        }
        if(strpos($available_sets, 's') !== false){
            $sets[] = '!@#$%&*?';
        }
        $all = '';
        $strong_p = '';
        foreach($sets as $set)
        {
            $strong_p .= $set[self::tweakArrayRand(str_split($set))];
            $all .= $set;
        }

        $all = str_split($all);
        for($i = 0; $i < $length - count($sets); $i++){
            $strong_p .= $all[self::tweakArrayRand($all)];
        }
        $strong_p = str_shuffle($strong_p);

        if(!$add_dashes){
            return $strong_p;
        }

        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while(strlen($strong_p) >= $dash_len)
        {
            $dash_str .= substr($strong_p, 0, $dash_len) . '-';
        }
        return $dash_str;
    }

    //take a array and get random index, same function of array_rand, only diference is
    // intent use secure random algoritn on fail use mersene twistter, and on fail use defaul array_rand
    public static function tweakArrayRand($array){
        if (function_exists('random_int')) {
            return random_int(0, count($array) - 1);
        } elseif(function_exists('mt_rand')) {
            return mt_rand(0, count($array) - 1);
        } else {
            return array_rand($array);
        }
    }

    public static function getClientIp()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }

    public static function getStatus(){
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_LOCKED => 'Locked',
        ];
    }

    public static function searchKeywordIntoArray($find, $from, $return_key = true){
        $input = preg_quote($find, '~'); // don't forget to quote input string!
        $result = preg_grep('~' . $input . '~', $from);

        if($return_key){
            return array_keys($result);
        }
        return $result;
    }

    public static function addUserWithoutAnyAccess(){
        $user = User::from( 'pas_users as u' )
            ->select('u.*')
            ->where('u.email', '=', 'krmp@xoomwebdevelopment.com')
            ->first();

        if($user){
            return $user;
        }

        return User::factory()->create([
            'user_type' => User::USER_TYPE_MY_USER,
            'email' => 'krmp@xoomwebdevelopment.com',
            'firstname' => 'Test',
            'lastname' => 'Partner',
            'phone' => Factory::create()->phoneNumber,
            'roleid' => 1,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => null,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'password_expired_at' => date('Y-m-d H:i:s', strtotime('+10 year')),
        ]);
    }

    public static function addWeUserWithoutAnyAccess(){
        $user = User::from( 'pas_users as u' )
            ->select('u.*')
            ->where('u.email', '=', 'krmwu@xoomwebdevelopment.com')
            ->first();

        if($user){
            return $user;
        }

        return User::factory()->create([
            'user_type' => User::USER_TYPE_WE_USER,
            'email' => 'krmwu@xoomwebdevelopment.com',
            'firstname' => 'Test',
            'lastname' => 'We User',
            'phone' => Factory::create()->phoneNumber,
            'roleid' => 6,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => null,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'password_expired_at' => date('Y-m-d H:i:s', strtotime('+10 year')),
        ]);
    }

    public static function addAdminUser(){
        $user = User::from( 'pas_users as u' )
            ->select('u.*')
            ->where('u.email', '=', 'krm@xoomwebdevelopment.com')
            ->first();

        if($user){
            return $user;
        }

        return User::factory()->create([
            'user_type' => User::USER_TYPE_ADMIN,
            'email' => 'krm@xoomwebdevelopment.com',
            'firstname' => 'Test',
            'lastname' => 'Admin',
            'phone' => Factory::create()->phoneNumber,
            'roleid' => 1,
            'status' => Utility::STATUS_ACTIVE,
            'partner_type' => null,
            'first_login' => 1,
            'password' => md5('Info@12345'),
            'password_expired_at' => date('Y-m-d H:i:s', strtotime('+10 year')),
            //'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            //'remember_token' => Str::random(10),
        ]);
    }

    public static function getMonthsInTwoDates($date1, $date2){
        if(empty($date1) || empty($date2)){
            return '--';
        }

        $ts1 = strtotime($date1);
        $ts2 = strtotime($date2);

        $year1 = date('Y', $ts1);
        $year2 = date('Y', $ts2);

        $month1 = date('m', $ts1);
        $month2 = date('m', $ts2);

        return (($year2 - $year1) * 12) + ($month2 - $month1).' Month(s)';
    }

    public static function addSlashes($data){
        if(is_array($data) && count($data) > 0){
            array_walk_recursive($data, function(&$item, $key) {
                $item = !is_integer($item) ? addslashes($item):$item;
            });
            return $data;
        }
        return [];
    }

    public static function getConfig($key, $status = 1, $json_decode = false){
        $config_value = DB::table('ps_configuration')
            ->select(['content'])
            ->where('type', '=', $key)
            ->where('is_active', '=', $status)
            ->value('content');
        if($config_value){
            if($json_decode){
                return json_decode($config_value, true);
            }
        }
        return $config_value;
    }

    public static function setConfig(){
        $banner_notification = DB::table('ps_configuration')
            ->select(['content'])
            //->where('partner_id', '=', $partner)
            ->where('type', '=', 'banner-notification')
            ->where('is_active', '=', 1)
            ->value('content');
    }

    public static function slugify($text, $divider = '-', $is_lowercase = true)
    {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, $divider);

        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);

        // lowercase
        if($is_lowercase){
            $text = strtolower($text);
        }

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}