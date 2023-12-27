<?php

namespace App\Console\Commands\Canvas;

use App\CanvasHelper;
use App\EmailHelper;
use App\EmailRequest;
use App\Models\CanvasUserEnrolement;
use App\Utility;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ImportLoginIp extends Command
{
    const OFF_SET = 0;
    const LIMIT = 100;

    /*private $off_set = self::OFF_SET;
    private $limit = self::LIMIT;
    private $page = 1;*/

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importLoginIp:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Course (Program) Add/Update with Canvas API';


    private $data = [
        'insert' => [],
        'insert_ids' => [],
        'update' => [],
        'update_ids' => [],
    ];

    private $users = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        dump(date('Y-m-d', strtotime('-1 day')));
        try{
            $this->users = CanvasUserEnrolement::select(['id', 'user_id', 'course_id', 'report_at'])
                ->where('report_at', '=', date('Y-m-d', strtotime('-1 day')))
                ->where('today_activity_sec', '>', 0)
                //->whereIn('user_id', [875, 892])
                //->pluck('user_id', 'id')
                ->get()->toArray();

            $group_users = collect($this->users)->groupBy('user_id')->toArray();
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

            $this->info('Total Records Updated(' . count($this->data['update']) . ').');
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

    /**
     * @param CanvasRequest $req
     */
    private function getIP(CanvasRequest $req, $user_courses){
        //echo '<pre>';print_r($req);die;

        $canvas_records = CanvasHelper::getInstance()->getUserPageViews($req);
        krsort($canvas_records);

        if (is_array($canvas_records) && count($canvas_records) > 0) {
            $this->info('There are '.count($canvas_records).' page views found for '.$req->user_id);
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

            /*if(isset($data)){
                $this->data['update'][$req->user_id] = $data;
            }*/
        } /*else {
            $this->warn('There are not login time found for '.$req->user_id);
        }*/

        /*$activity['action_via'] = 'cron';
        $activity['url'] = 'canvas-account-cron';
        $activity['ip_address'] = Utility::getClientIp();
        $activity['session_id'] = Session::getId();
        $activity['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
        if(isset($this->data['update']) && count($this->data['update']) > 0){
            $activity['action'] = 'update';
            $activity['new_data'] = json_encode($this->data['update']);
            $activity['ref_ids'] = implode(',', $this->data['update_ids']);
            DB::table('pas_user_activity')->insert($activity);
        }
        if(isset($this->log_data['insert']) && count($this->data['insert']) > 0){
            $activity['action'] = 'create';
            $activity['new_data'] = json_encode($this->data['insert']);
            $activity['ref_ids'] = implode(',', $this->data['insert_ids']);
            DB::table('pas_user_activity')->insert($activity);
        }*/
    }

}
