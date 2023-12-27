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

class ActivityReportScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activityReportScheduler:cron';

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
            $scheduled_email = DB::table('email_queue')->select(['email_queue.*', 'pas_partner.partner_name'])
                ->join('pas_partner', 'pas_partner.id', '=', 'email_queue.partner_id')
                ->whereIn('is_sent', [0,2])->limit(10)
                ->get()->all();
            //echo '<pre>';print_r($scheduled_email);die;
            $ids = [];
            foreach ($scheduled_email as $email) {
                $attach_reports = [];
                $email_req = new EmailRequest();
                $email_req->setSubject($email->subject)
                    ->setBody($email->message)
                    ->setLogSave(true);

                if(!empty($email->from_email)){
                    $from_email = json_decode($email->from_email, true);
                    if(is_array($from_email) && count($from_email) > 0){
                        $email_req->setFromEmail($from_email[0]);
                        if(isset($from_email[1]) && !empty($from_email[1])){
                            $email_req->setFromName($from_email[1]);
                        }
                    }
                }

                if(!empty($email->email)){
                    $email_req->setReplyTo([$email->email, $email->practitioner_name]);
                }

                if(!empty($email->to_email)){
                    $to_emails = json_decode($email->to_email, true);
                    if(is_array($to_emails) && count($to_emails) > 0){
                        $email_req->setTo($to_emails);
                    }
                }

                if(!empty($email->cc_email)){
                    $cc_emails = json_decode($email->cc_email, true);
                    if(is_array($cc_emails) && count($cc_emails) > 0){
                        $email_req->setCc($cc_emails);
                    }
                }

                if(!empty($email->attachments)){
                    $attachments = json_decode($email->attachments, true);
                    if(is_array($attachments) && count($attachments) > 0){
                        $attach_reports = $attachments;
                        $email_req->setAttachments($attachments);
                    }
                }

                //$email_req->setUseSMTP(true);

                $email_helper = new EmailHelper($email_req);
                $email_response = $email_helper->sendEmail();
                if($email_response){
                    foreach ($attach_reports as $attach_report) {
                        @unlink($attach_report);
                    }
                    $ids['success'][] = $email->id;
                    //dump($patient_activity_data);
                }else{
                    $ids['fail'][] = $email->id;
                }
            }

            if(isset($ids['success']) && count($ids['success']) > 0){
                DB::table('email_queue')->whereIn('id', $ids['success'])->update([
                    'is_sent' => 1
                ]);
            }

            if(isset($ids['fail']) && count($ids['fail']) > 0){
                DB::table('email_queue')->whereIn('id', $ids['success'])->update([
                    'is_sent' => 2
                ]);
            }

        }catch (Exception $e){
            $email_req = new EmailRequest();
            $email_req
                ->setTo([
                    [$_ENV['DEVELOPER_EMAIL_FIRST'], "Xoom Web Development"],
                    //[$_ENV['DEVELOPER_EMAIL_SECOND'], "Info Xoom Web Development"],
                ])
                ->setSubject('APS Activity Report EMAIL Scheduler Error :: '.__CLASS__)
                ->setBody($e->getMessage())
                ->setLogSave(false);

            $email_helper = new EmailHelper($email_req);
            $email_helper->sendEmail();
        }
    }
}
