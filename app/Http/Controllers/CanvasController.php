<?php
namespace App\Http\Controllers;
use App\CanvasHelper;
use App\Console\Commands\Canvas\CanvasRequest;
use App\EmailHelper;
use App\EmailRequest;
use App\Models\CanvasUserEnrolement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Config;
use Lang;
require base_path("vendor/autoload.php");
use Cookie;

class CanvasController extends Controller
{
    private $data = ['update' => []];

    public function importLoginActivity(Request $request){
        try{
            $user_id = isset($request->user_id) ? $request->user_id:null;
            $query = DB::table('pas_canvas_user');

            if(!empty($user_id)){
                $query->where('canvas_user_id', '=', $user_id);
            }

            $canvas_users = $query->pluck('canvas_user_id')->toArray();

            foreach ($canvas_users as $user_id){
                $can_req = new CanvasRequest();
                $can_req->user_id = $user_id;

                $user_enrollments = CanvasHelper::getInstance()->userEnrollments($can_req);
                //dd($user_enrollments);
                //$this->info('Enrollment Found : '.count($user_enrollments).' User ID: '.$user_id);

                foreach ($user_enrollments as $user_enrollment) {
                    $last_activity = DB::table('pas_canvas_user_enrollment')
                        ->where('canvas_enrollment_id', '=', $user_enrollment['id'])
                        ->where('user_id', '=', $user_id)
                        ->where('course_id', '=', $user_enrollment['course_id'])
                        ->where('course_section_id', '=', $user_enrollment['course_section_id'])
                        ->orderBy('id', 'DESC')
                        ->limit(1)
                        ->get()->first();

                    $total_activity_sec = $user_enrollment['total_activity_time'];
                    $today_activity_sec = 0;
                    if($last_activity){
                        if($last_activity->total_activity_sec != $total_activity_sec){
                            $today_activity_sec = $user_enrollment['total_activity_time'] - $last_activity->total_activity_sec;
                        }
                    }else{
                        $today_activity_sec = $user_enrollment['total_activity_time'];
                    }


                    //if($today_activity_sec > 0) {
                        //dd(date('H:i:s', strtotime($today_activity_time)));
                        $data['insert'][] = [
                            'canvas_enrollment_id' => $user_enrollment['id'],
                            'user_id' => $user_enrollment['user_id'],
                            'course_id' => $user_enrollment['course_id'],
                            'course_section_id' => $user_enrollment['course_section_id'],
                            'enroll_start_date' => $user_enrollment['start_at'],
                            'enroll_end_date' => $user_enrollment['end_at'],
                            'total_activity_sec' => $total_activity_sec,
                            'today_activity_sec' => $today_activity_sec,
                            //'last_activity_at' => !empty($user_enrollment['last_activity_at']) ? date('Y-m-d H:i:s', strtotime($user_enrollment['last_activity_at'])):null,
                            'last_activity_at' => $user_enrollment['last_activity_at'],
                            'report_at' => date('Y-m-d', strtotime('-1 day')),
                            'created_at' => date('Y-m-d H:i:s'),
                            //'updated_at' => date('Y-m-d H:i:s'),
                        ];
                    //}
                }
            }

            dd($data['insert']);
            if(count($data['insert']) > 0){
                //DB::table('pas_canvas_user_enrollment')->insert($this->data['insert']);
            }
        }
        catch (\Exception $e){
            //dd($e->getMessage());
            $email_req = new EmailRequest();
            $email_req
                ->setTo([
                    [$_ENV['DEVELOPER_EMAIL_FIRST'], "Xoom Web Development"],
                    //[$_ENV['DEVELOPER_EMAIL_SECOND'], "Info Xoom Web Development"],
                ])
                ->setSubject($_ENV['APP_ENV'].' PAS ERROR :: '.__CLASS__)
                ->setBody('Line No. '.$e->getLine().' MSG. '.$e->getMessage())
                ->setLogSave(true);

            $email_helper = new EmailHelper($email_req);
            $email_helper->sendEmail();
        }
    }

    public function updateLoginIP(Request $request)
    {
        if(empty($request->date)){
           die('Please provide date params.');
        }

        //dump(date('Y-m-d', strtotime('-6 day')));
        try{
            $users = CanvasUserEnrolement::select(['id', 'user_id', 'course_id', 'report_at'])
                ->where('report_at', '=', $request->date)
                ->where('today_activity_sec', '>', 0)
                //->whereIn('user_id', [647])
                //->pluck('user_id', 'id')
                ->get()->toArray();

            $group_users = collect($users)->groupBy('user_id')->toArray();
            //echo "<pre>";print_r($group_users);die;

            foreach ($group_users as $user_id => $user_courses){
                $req = new CanvasRequest();
                $req->user_id = $user_id;
                $req->query_params = [
                    /*'start_time' => date('Y-m-d').' 00:01:00',
                    'end_time' => date('Y-m-d').' 23:59:00',*/
                    'start_time' => $user_courses[0]['report_at'] . ' 00:01:00',
                    'end_time' => $user_courses[0]['report_at'] . ' 23:59:00',
                ];
                //echo '<pre>';print_r($req);die;
                $this->getIP($req, $user_courses);
            }

            //echo '<pre>';print_r($this->data['update']);die;

            if(count($this->data['update']) > 0){
                foreach ($this->data['update'] as $user_activities) {
                    foreach ($user_activities as $course_activity) {
                        //dd($course_activity);
                        Db::table('pas_canvas_user_enrollment')
                            ->where('id', '=', $course_activity['id'])
                            ->update([
                                'ip_address' => $course_activity['ip_address'],
                                'login_time' => $course_activity['login_time'],
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                    }
                }
            }

            dd('Total Records Updated(' . count($this->data['update']) . ').');
        }
        catch (\Exception $e){
            dd($e->getMessage());
            $email_req = new EmailRequest();
            $email_req
                ->setTo([
                    [$_ENV['DEVELOPER_EMAIL_FIRST'], "Xoom Web Development"],
                    //[$_ENV['DEVELOPER_EMAIL_SECOND'], "Info Xoom Web Development"],
                ])
                ->setSubject($_ENV['APP_ENV'].' PAS ERROR :: '.__CLASS__)
                ->setBody('Line No. '.$e->getLine().' MSG. '.$e->getMessage())
                ->setLogSave(true);

            $email_helper = new EmailHelper($email_req);
            $email_helper->sendEmail();
        }
    }

    /**
     * @param CanvasRequest $req
     */
    private function getIP(CanvasRequest $req, $user_courses){
        //echo '<pre>';print_r($req);die;

        $canvas_records = CanvasHelper::getInstance()->getUserPageViews($req);
        krsort($canvas_records);

        if (is_array($canvas_records) && count($canvas_records) > 0) {
            $_user_courses = array_column($user_courses, 'course_id');
            foreach ($canvas_records as $canvas_record) {
                if($canvas_record['context_type'] == 'Course' && isset($canvas_record['links']) && count($canvas_record['links']) > 0){

                    //$this->data['update'] = "UPDATE pas_canvas_user_enrollment SET ip_address = '".$canvas_record["remote_ip"]."'";
                    if(in_array($canvas_record['links']['context'], $_user_courses)){
                        $this->data['update'][$canvas_record['links']['user']][$canvas_record['links']['context']] = [
                            'id' => $user_courses[0]['id'],
                            'course_id' => $canvas_record['links']['context'],
                            'ip_address' => $canvas_record['remote_ip'],
                            'login_time' => $canvas_record['created_at'],
                        ];
                    }
                }
            }

        }
    }

}
