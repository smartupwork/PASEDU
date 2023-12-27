<?php
/**
 * Created by PhpStorm.
 * User: rajneeshgautam
 * Date: 29/04/21
 * Time: 2:45 PM
 */

namespace App;

class EmailRequest
{
    const EMAIL_PARTNER_REGISTRATION = 1;
    const EMAIL_WE_USER_REGISTRATION = 2;
    const EMAIL_ACCOUNT_DISABLE = 3;
    const EMAIL_ACCOUNT_ENABLE = 4;
    const EMAIL_PROGRESS_REQUEST = 5;
    const EMAIL_PERSONAL_DETAIL_UPDATED = 6;
    const EMAIL_LEADS_PERSONAL_DETAIL = 7;
    const EMAIL_COLLATERAL_REQUEST = 8;

    private
        $to = [],
        $template,
        $fromName,
        $fromEmail,
        $replyTo,
        $cc = [],
        $bcc = [],
        $subject,
        $body,
        $contentType = 'text/html',
        $SMTPDebug = 0,
        $placeholder = [],
        $headers = [],
        $attachments = [],
        $logSave = false;


    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param int $template
     */
    public function setTemplate(int $template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param array $to
     */
    public function setTo(array $to)
    {
        if(isset($to) && !is_array($to)){
            $to = [$to];
        }
        $this->to = $to;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @param $fromName
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
    }


    /**
     * @param $fromEmail
     */
    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @param mixed $replyTo
     */
    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @param mixed $cc
     */
    public function setCc($cc)
    {
        if(isset($cc) && !is_array($cc)){
            $cc = [$cc];
        }
        $this->cc = $cc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * @param mixed $bcc
     */
    public function setBcc($bcc)
    {
        $this->bcc = $bcc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param mixed $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSMTPDebug()
    {
        return $this->SMTPDebug;
    }

    /**
     * @param mixed $SMTPDebug
     */
    public function setSMTPDebug($SMTPDebug)
    {
        $this->SMTPDebug = $SMTPDebug;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * @param $placeholder
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param mixed $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogSave()
    {
        return $this->logSave;
    }

    /**
     * @param mixed $logSave
     */
    public function setLogSave($logSave)
    {
        $this->logSave = $logSave;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param mixed $attachments
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
        return $this;
    }


}