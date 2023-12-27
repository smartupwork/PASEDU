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

class ImportEnrollmentUserActivity3 extends Command
{
    /*const OFF_SET = 0;
    const LIMIT = 100;*/

    /*private $off_set = self::OFF_SET;
    private $limit = self::LIMIT;
    private $page = 1;*/

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importEnrollmentUserActivity3:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'User Enrollments and Activity Time with Canvas API';

    private $data = [
        'insert' => [],
        'insert_ids' => [],
    ];

    private $canvas_users = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            $this->canvas_users = DB::table('pas_canvas_user')
                ->offset(200)
                ->limit(100)
                ->pluck('canvas_user_id')->toArray();

            foreach ($this->canvas_users as $user_id){
                    $can_req = new CanvasRequest();
                    $can_req->user_id = $user_id;
                    $user_enrollments = CanvasHelper::getInstance()->userEnrollments($can_req);
                    //dd($user_enrollments);
                    $this->info('Enrollment Found : '.count($user_enrollments).' User ID: '.$user_id);

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

                        if($today_activity_sec > 0) {
                            //dd(date('H:i:s', strtotime($today_activity_time)));
                            $this->data['insert'][] = [
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
                        }
                    }
            }

            if(count($this->data['insert']) > 0){
                DB::table('pas_canvas_user_enrollment')->insert($this->data['insert']);
            }


            $this->info('Total records Inserted(' . count($this->data['insert']) . ')');
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
                ->setLogSave(false);

            $email_helper = new EmailHelper($email_req);
            $email_helper->sendEmail();
        }
    }

}
