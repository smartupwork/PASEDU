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

class ImportEnrollmentUserActivityBk extends Command
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
    protected $signature = 'importEnrollmentUserActivityBk:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Course (Program) Update with Canvas API';

    private $table = 'pas_enrollment';

    private $data = [
        'insert' => [],
        'insert_ids' => [],
    ];

    private $enrolled_users = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            $this->enrolled_users = DB::table($this->table)
                ->select(['pas_enrollment.id', 'pas_enrollment.subject', 'pas_partner.canvas_sub_account_id'])
                ->join('pas_partner', 'pas_partner.id', 'pas_enrollment.partner_id')
                //->pluck('canvas_course_id', 'id')
                ->where('pas_enrollment.partner_id', '=', 15)
                //->where('pas_enrollment.id', '=', 28)
                ->get()->all();


            //dd($this->enrolled_users);
            foreach ($this->enrolled_users as $enrolled_user){
                $can_req = new CanvasRequest();
                $can_req->account_id = $enrolled_user->canvas_sub_account_id;
                //$can_req->search_term = 'Khemraj Maurya';
                $can_req->search_term = $enrolled_user->subject;

                $user_search = CanvasHelper::getInstance()->getUsersOfAccount($can_req);

                if(isset($user_search['errors']) && count($user_search['errors']) > 0){
                    dump($user_search['errors']);
                }

                if(count($user_search) == 1 && !(isset($user_search['errors']) && count($user_search['errors']) > 0)){
                    $can_req = new CanvasRequest();
                    $can_req->user_id = $user_search[0]['id'];
                    $user_enrollments = CanvasHelper::getInstance()->userEnrollments($can_req);
//dd($user_enrollments);
                    foreach ($user_enrollments as $user_enrollment) {
                        $last_activity = DB::table('pas_canvas_user_enrollment')
                            ->where('enrollment_id', '=', $enrolled_user->id)
                            ->where('user_id', '=', $user_enrollment['user_id'])
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


                        //dd(date('H:i:s', strtotime($today_activity_time)));
                        $this->data['insert'][] = [
                            'enrollment_id' => $enrolled_user->id,
                            'user_id' => $user_enrollment['user_id'],
                            'course_id' => $user_enrollment['course_id'],
                            'course_section_id' => $user_enrollment['course_section_id'],
                            'enroll_start_date' => $user_enrollment['start_at'],
                            'enroll_end_date' => $user_enrollment['end_at'],
                            'total_activity_sec' => $total_activity_sec,
                            'today_activity_sec' => $today_activity_sec,
                            'last_activity_at' => $user_enrollment['last_activity_at'],
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
