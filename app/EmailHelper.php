<?php
/**
 * Created by PhpStorm.
 * User: rajneeshgautam
 * Date: 29/04/21
 * Time: 2:45 PM
 */

namespace App;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use PHPMailer\PHPMailer\PHPMailer;

class EmailHelper
{

    private $requestData;
    /**
     * EmailHelper constructor.
     */
    public function __construct(EmailRequest $email_req){
        $this->getData($email_req);
        $validate = $this->isValid($this->requestData);
        if($validate){
            if(!empty($email_req->getTemplate())){
                $this->setTemplateData($email_req);
            }else{
                if($email_req->getFromName() == ''){
                    $email_req->setFromName($_ENV['FROM_NAME']);
                }

                if($email_req->getFromEmail() == ''){
                    $email_req->setFromEmail($_ENV['FROM_EMAIL']);
                }
            }
        }
        $this->getData($email_req);
    }

    /**
     * @return bool
     */
    public function sendEmail(){
        $mail = new PHPMailer(true);
        if($_ENV['ASES_EMAIL_IS_ENABLE'] == 'true'){
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls';
            $mail->Port = $_ENV['SMTP_PORT'];
            $mail->Username = $_ENV['SMTP_USERNAME'];
            $mail->Password = $_ENV['SMTP_PASSWORD'];
            $mail->addCustomHeader('X-PM-Message-Stream', 'outbound');
            $mail->Host = $_ENV['SMTP_HOST'];
        }

        if(!empty($this->requestData['SMTPDebug'])){
            $mail->SMTPDebug = $this->requestData['SMTPDebug'];
        }
        $mail->setFrom($this->requestData['from'][0], $this->requestData['from'][1]);
        $mail->Subject = $this->requestData['subject'];
        $mail->MsgHTML($this->requestData['body']);

        if(count($this->requestData['attachments']) > 0){
            foreach ($this->requestData['attachments'] as $attachment) {
                $mail->addAttachment($attachment);
            }
        }

        $mail->ContentType = $this->requestData['contentType'];

        if(is_array($this->requestData['replyTo']) && count($this->requestData['replyTo']) > 0){
            $mail->addReplyTo($this->requestData['replyTo'][0], isset($this->requestData['replyTo'][1]) ? $this->requestData['replyTo'][1]:'');
        }


        foreach ($this->requestData['to'] as $name_and_email) {
            $mail->addAddress($name_and_email[0], isset($name_and_email[1]) ? $name_and_email[1]:'');
        }

        foreach ($this->requestData['cc'] as $name_and_email) {
            $mail->addCC($name_and_email[0], isset($name_and_email[1]) ? $name_and_email[1]:'');
        }

        if($this->requestData['logSave']){
            foreach ($this->requestData['to'] as $requestEmail) {
                $email_log['from_email'] = $this->requestData['from'][0];
                $email_log['to_email'] = $requestEmail[0];
                $email_log['cc_email'] = json_encode($this->requestData['cc']);
                $email_log['subject'] = $this->requestData['subject'];
                $email_log['message'] = $this->requestData['body'];
                $email_log['attachments'] = json_encode($this->requestData['attachments']);
                $email_log['added_date'] = date('Y-m-d H:i:s');
                $email_log['added_by'] = !Auth::guest() ? Auth::user()->id: null;

                DB::table('pas_email_logs')->insert($email_log);
            }
        }

        Session::flash('email_data', $this->requestData['body']);
        Session::flash('alert-class', 'alert-danger');

        return $mail->send();
    }


    public function rules(){
        return [
            'template' => 'nullable|required_without_all:body|integer',
            'to' => 'required|array|array_email',
            'from' => 'required|array|min:1',
            'replyTo' => 'nullable|array',
            'body' => 'required_without_all:template',
            'subject' => 'required_without_all:template',
            'cc' => 'nullable|array',
            'bcc' => 'nullable|array',
        ];
    }

    public function getData(EmailRequest $email_req){
        $data['to'] = $email_req->getTo();
        $data['template'] = $email_req->getTemplate();
        $data['from'] = [$email_req->getFromEmail(), $email_req->getFromName()];
        $data['replyTo'] = $email_req->getReplyTo();
        $data['cc'] = $email_req->getCc();
        $data['bcc'] = $email_req->getBcc();
        $data['subject'] = $email_req->getSubject();
        $data['body'] = $email_req->getBody();
        $data['attachments'] = $email_req->getAttachments();
        $data['contentType'] = $email_req->getContentType();
        $data['logSave'] = $email_req->getLogSave();
        $data['replyTo'] = $email_req->getReplyTo();
        $data['SMTPDebug'] = $email_req->getSMTPDebug();

        $this->requestData = $data;
    }

    /**
     * @param $data
     * @return bool|\Illuminate\Support\MessageBag
     */
    public function isValid($data){
        Validator::extend('integer_keys', function($attribute, $value) {
            return is_array($value) && count(array_filter(array_keys($value), 'is_string')) === 0;
        });

        Validator::extend('array_email', function($attribute, $value) {
            if(is_array($value)){
                foreach ($value as $name_and_email) {
                    if(count($name_and_email) == 0 || count($name_and_email) > 2 || !filter_var($name_and_email[0], FILTER_VALIDATE_EMAIL)){
                        return false;
                    }
                }
                return true;
            }
            return false;
        });

        $validator = Validator::make($data, $this->rules(), $this->attributeNames());

        if($validator->fails()){
            return $validator->errors();
        }
        return true;
    }

    private function attributeNames(){
        return [
            'tos' => 'To',
            'from' => 'From',
        ];
    }

    /**
     * @param EmailRequest $email_req
     */
    private function setTemplateData(EmailRequest $email_req): void
    {
        $email_template = DB::table('pas_email_templates')
            ->where([["id", '=', $email_req->getTemplate()]])
            ->first();

        $message = $email_template->message;
        foreach ($email_req->getPlaceholder() as $placeholder_key => $placeholder_val) {
            $message = str_replace("%" . $placeholder_key . "%", $placeholder_val, $message);
        }
        $email_req->setBody(nl2br($message));

        if ($email_req->getSubject() == '') {
            $email_req->setSubject($email_template->subject);
        }

        if ($email_req->getFromName() == '') {
            $email_req->setFromName($email_template->from_name);
        } else {
            $email_req->setFromName($_ENV['FROM_NAME']);
        }

        if ($email_req->getFromEmail() == '') {
            $email_req->setFromEmail($email_template->from_email);
        } else {
            $email_req->setFromEmail($_ENV['FROM_EMAIL']);
        }
        
    }

}