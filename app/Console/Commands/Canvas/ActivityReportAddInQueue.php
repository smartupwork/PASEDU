<?php

namespace App\Console\Commands\Canvas;

use App\CanvasHelper;
use App\EmailHelper;
use App\EmailRequest;
use App\Utility;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PHPUnit\Exception;
use Mpdf\Mpdf;

class ActivityReportAddInQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activityReportAddInQueue:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            $students = DB::table('student_activity_progress')
                ->select(['student_activity_progress.id', 'student_activity_progress.activity_type', 'report_type', 'schedule_interval', 'scheduled_at', 'fetch_report_type', 'fetch_start_date', 'fetch_end_date', 'enrollment_id', 'student_activity_progress.partner_id', 'pas_partner.partner_name', 'pas_contact.email', 'pas_enrollment.subject', 'pas_contact.email', 'pas_contact.phone', 'program_name', 'start_date', 'end_date', 'hours', DB::raw('CONCAT(pas_users.firstname, " ", pas_users.lastname) AS requester_name'), DB::raw('pas_users.email AS requester_email')])
                ->join('pas_enrollment', 'pas_enrollment.id', '=', 'student_activity_progress.enrollment_id')
                ->join('pas_partner', 'pas_partner.id', '=', 'pas_enrollment.partner_id')
                ->join('pas_contact', 'pas_contact.id', '=', 'pas_enrollment.contact_id')
                ->join('pas_program', 'pas_program.zoho_id', '=', 'pas_enrollment.program_zoho_id')
                ->join('pas_users', 'pas_users.id', '=', 'student_activity_progress.created_by')
                ->where('report_type', '=', 'schedule-report')
                ->where('scheduled_at', '=', date('Y-m-d'))
                ->whereNotNull('canvas_student_id')
                ->get()->all();
            //echo '<pre>';print_r($students);die;

            $next_schedules = [];
            $data = [];
            foreach ($students as $activity_progress) {
                $tos = [
                    [$activity_progress->requester_email, $activity_progress->requester_name]
                ];

                if($activity_progress->activity_type == 'activity-log'){
                    $report_type = 'Program Progress Report';
                    $attachments = $this->generateProgressReport($activity_progress);
                }else{
                    $report_type = 'Activity Progress Report';
                    $attachments = $this->generateLogReport($activity_progress);
                }
                if($attachments){
                    if($activity_progress->schedule_interval == 'one-time'){
                        $next_schedules[$activity_progress->id]['scheduled_at'] = null;
                    }else if($activity_progress->is_recurring == 1){
                        if($activity_progress->schedule_interval == 'bi-week'){
                            $next_schedules[$activity_progress->id]['scheduled_at'] = date('Y-m-d', strtotime('+2 week'));
                        }else if($activity_progress->schedule_interval == 'one-month'){
                            $next_schedules[$activity_progress->id]['scheduled_at'] = date('Y-m-d', strtotime('+1 month'));
                        }else if($activity_progress->schedule_interval == 'six-month'){
                            $next_schedules[$activity_progress->id]['scheduled_at'] = date('Y-m-d', strtotime('+6 month'));
                        }
                    }

                    if(!empty($activity_progress->requester_email)){
                        $data[] = [
                            'partner_id' => $activity_progress->partner_id,
                            'enrollment_id' => $activity_progress->enrollment_id,
                            'from_email' => json_encode([ $_ENV['FROM_EMAIL'], $activity_progress->partner_name ]),
                            'to_email' => json_encode($tos),
                            'subject' => $report_type. ' as requested',
                            'message' => view('student.enrollment.email-progress-report-cron', compact('activity_progress', 'report_type'))->render(),
                            'attachments' => json_encode($attachments),
                            'created_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                }

            }

            //echo '<pre>';print_r($scheduled_email);die;

            if(count($data) > 0){
                DB::table('email_queue')->insert($data);

                if(count($next_schedules) > 0){
                    foreach ($next_schedules as $id => $next_schedule) {
                        DB::table('student_activity_progress')
                            ->where('id', '=', $id)->update($next_schedule);
                    }
                }
            }
        }catch (Exception $e){
            $email_req = new EmailRequest();
            $email_req
                ->setTo([
                    [$_ENV['DEVELOPER_EMAIL_FIRST'], "Xoom Web Development"],
                    //[$_ENV['DEVELOPER_EMAIL_SECOND'], "Info Xoom Web Development"],
                ])
                ->setSubject('PAS EMAIL Scheduler Error :: '.__CLASS__)
                ->setBody($e->getMessage())
                ->setLogSave(false);

            $email_helper = new EmailHelper($email_req);
            $email_helper->sendEmail();
        }
    }

    private function generateLogReport($enrollment){
        try{
            /*$can_req = new CanvasRequest();
            $can_req->account_id = 1;
            $can_req->search_term = $enrollment->subject;

            $user_search = CanvasHelper::getInstance()->getUsersOfAccount($can_req);

            if(count($user_search) == 0 || count($user_search) > 1){
                dd(count($user_search).' user account found with '.$enrollment->subject.' name');
            }*/

            $user_search = DB::table('pas_canvas_user')->where('name', '=', $enrollment->subject)->get()->first();
            if(!$user_search){
                return [];
            }

            //echo '<pre>';print_r($user_search);die;

            $query = DB::table('pas_canvas_user_enrollment')
                ->select(['pas_canvas_course.name', 'pas_canvas_user_enrollment.enroll_start_date', 'pas_canvas_user_enrollment.enroll_end_date', 'last_activity_at', 'today_activity_sec', 'report_at', 'ip_address'])
                ->join('pas_canvas_course', 'pas_canvas_course.canvas_course_id', '=', 'pas_canvas_user_enrollment.course_id')
                ->where('pas_canvas_user_enrollment.user_id', '=', $user_search->canvas_user_id)
                //->where('pas_canvas_user_enrollment.report_at', '<', date('Y-m-d'))
                ->orderBy('pas_canvas_user_enrollment.course_id', 'ASC')
                ->orderBy('pas_canvas_user_enrollment.report_at', 'ASC');

            if($enrollment->fetch_report_type == 'date-range'){
                $query->where([
                    ['report_at', '>=', $enrollment->fetch_start_date],
                    ['report_at', '<=', $enrollment->fetch_end_date],
                ]);
            }else{
                $query->where('pas_canvas_user_enrollment.report_at', '<', date('Y-m-d'));
            }

            $courses_activities = $query->get()->all();
            //echo '<pre>';print_r($courses_activities);die;
            /*$courses = DB::table('pas_canvas_user_enrollment')
                ->where('user_id', '=', $user_search->canvas_user_id)
                ->where(DB::raw('DATE(created_at)'), '=', date('Y-m-d'))
                ->get()->all();*/


            $mpdf = new mPDF([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 5,
                'margin_right' => 5,
                'margin_top' => 5,
                'margin_bottom' => 5
            ]);
            $mpdf->SetWatermarkImage(public_path('images/bg.png'),0.4,'',array(40,50));
            $mpdf->showWatermarkImage = true;
            $mpdf->WriteHTML(view('student.enrollment.student-activity-log', compact('user_search', 'enrollment', 'courses_activities')));
            //return $mpdf->Output("Catalog_Management.pdf", 'I');

            //$s3 = \Storage::disk('s3');
            $file_name = uniqid() .'-activity-progress-report.pdf';
            //$s3filePath = '/enrollment-activity-report/' . $file_name;
            //$s3->put($s3filePath, file_get_contents($mpdf->Output("Catalog_Management.pdf", 'I')), 'public');
            //$response = $s3->put($s3filePath, $mpdf->Output($file_name, \Mpdf\Output\Destination::STRING_RETURN), 'public');
            ini_set('memory_limit', '512M');
            $mpdf->Output("public/uploads/student-activity-report/".$file_name, 'F');
            return ["public/uploads/student-activity-report/".$file_name];
        }catch (Exception $e){
            return [];
        }

    }

    private function generateProgressReport($enrollment){
        try{
            $user_search = DB::table('pas_canvas_user')->where('name', '=', $enrollment->subject)->get()->first();
            if(!$user_search){
                die('Canvas user not found.');
            }

            $user_courses = DB::table('pas_canvas_user_enrollment')
                ->orderBy('id', 'DESC')
                ->where('user_id', '=', $user_search->canvas_user_id)->get()->first();

            //dd($user_courses);

            $course_module_list = [];
            if($user_courses) {
                $can_req = new CanvasRequest();
                $can_req->course_id = $user_courses->course_id;
//        $can_req->course_id = 453;
                //$can_req->include = ['items', 'content_details'];
                $can_req->query_params = [
                    //'user_id' => 104,
                    'user_id' => $user_search->canvas_user_id,
                ];
                $grade_feeds = CanvasHelper::getInstance()->courseGradeBookFeed($can_req);
                //echo '<pre>';print_r($grade_feeds);die;

                $final_result = [];
                if (count($grade_feeds) > 0) {
                    foreach ($grade_feeds as $grade_feed) {
                        $final_result[$grade_feed['assignment_id']] = $grade_feed['current_grade'];
                    }
                }
                //echo '<pre>';print_r($final_result);die;

                $can_req = new CanvasRequest();
                $can_req->course_id = $user_courses->course_id;
//        $can_req->course_id = 453;
                $can_req->include = ['items', 'content_details'];
                $can_req->query_params = [
                    //'student_id' => 104,
                    'student_id' => $user_search->canvas_user_id,
                ];
                $course_modules = CanvasHelper::getInstance()->courseModules($can_req);
                //echo '<pre>';print_r($course_modules);die;

                foreach ($course_modules as $course_module) {
                    $counter = 0;
                    $course_module_list[$course_module['id']]['id'] = $course_module['id'];
                    $course_module_list[$course_module['id']]['items_count'] = $course_module['items_count'];
                    $course_module_list[$course_module['id']]['name'] = $course_module['name'];
                    $course_module_list[$course_module['id']]['grade'] = '';
                    $course_module_list[$course_module['id']]['completed'] = 0;

                    foreach ($course_module['items'] as $item) {
                        if (isset($item['content_id']) && isset($final_result[$item['content_id']])) {
                            $course_module_list[$course_module['id']]['grade'] = $final_result[$item['content_id']];
                            //$course_module_list[$course_module['id']]['items_count'] = $course_module['items_count'];
                        }
                        if (isset($course_module['state']) && $course_module['state'] = 'completed') {
                            $counter++;
                            $course_module_list[$course_module['id']]['completed'] = $counter;
                        }
                    }
                }
            }

            //echo '<pre>';print_r($course_module_list);die;

            $mpdf = new mPDF([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 5,
                'margin_right' => 5,
                'margin_top' => 5,
                'margin_bottom' => 5
            ]);
            $mpdf->SetWatermarkImage(public_path('images/bg.png'),0.4,'', array(40,50));
            $mpdf->showWatermarkImage = true;
            $mpdf->WriteHTML(view('student.enrollment.student-activity-progress', compact('user_search', 'enrollment', 'course_module_list')));
            //return $mpdf->Output("Catalog_Management.pdf", 'I');

            //$s3 = \Storage::disk('s3');
            $file_name = uniqid() .'-program-progress-report.pdf';
            //$s3filePath = '/enrollment-activity-report/' . $file_name;
            //$s3->put($s3filePath, file_get_contents($mpdf->Output("Catalog_Management.pdf", 'I')), 'public');
            //$response = $s3->put($s3filePath, $mpdf->Output($file_name, \Mpdf\Output\Destination::STRING_RETURN), 'public');
            ini_set('memory_limit', '512M');
            $mpdf->Output("public/uploads/student-activity-report/".$file_name, 'F');
            return ["public/uploads/student-activity-report/".$file_name];
        }catch (Exception $e){
            return [];
        }

    }
}
