<?php
/**
 * @class Email
 *
 * This is a helper class to send emails
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Email
{

    /**
    * Format headers to send an email
    */
    static public function send($mailTo, $subject, $htmlMail, $replyToEmail='', $replyToName='') {
        $status = StatusCode::NOK;
        $replyToEmail = ($replyToEmail!='') ? $replyToEmail : Parameter::code('email');
        $replyToName = ($replyToName!='') ? $replyToName : Parameter::code('meta_title_page');
        require_once ASTERION_APP_FILE."helpers/mailer/PHPMailer.php";
        require_once ASTERION_APP_FILE."helpers/mailer/SMTP.php";
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->CharSet = 'utf-8';
            $mail->Host = Parameter::code('mailer_host');
            $mail->SMTPAuth = true;
            $mail->Username = Parameter::code('mailer_username');
            $mail->Password = Parameter::code('mailer_password');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->setFrom(Parameter::code('mailer_email'), Parameter::code('mailer_label'));
            $mail->addReplyTo($replyToEmail, $replyToName);
            $mail->addAddress($mailTo);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlMail;
            $mail->AltBody = strip_tags($htmlMail);
            $mail->send();
            $status = StatusCode::OK;
        } catch (Exception $e) {
            if (ASTERION_DEBUG) {
                dumpExit($e);
            }
        }
        return ['status' => $status];
    }

}
