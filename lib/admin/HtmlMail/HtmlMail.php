<?php
/**
 * @class HtmlMail
 *
 * This class represents the wrapup for the emails
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Admin
 * @version 4.0.0
 */
class HtmlMail extends Db_Object
{

    /**
     * Load an object using its code
     */
    public static function code($code)
    {
        return (new HtmlMail)->readFirst(['where' => 'code="' . $code . '"']);
    }

    /**
     * Send an email formatted with a template
     */
    public static function send($email, $code, $values = [], $template = 'basic')
    {
        $htmlMail = HtmlMail::code($code);
        Email::send($email, $htmlMail->get('subject'), $htmlMail->showUi('Mail', ['values' => $values, 'template' => $template]), $htmlMail->get('reply_to'));
    }

    /**
     * Send an email formatted with a template
     */
    public static function sendFromFile($email, $subject, $code, $values = [], $templateFile='')
    {
        $file = ASTERION_BASE_FILE . 'data/HtmlMail/' . $code . '.html';
        $content = (file_exists($file)) ? file_get_contents($file) : '';
        foreach ($values as $key => $value) {
            $content = str_replace('#' . $key, $value, $content);
        }
        if ($content != '') {
            if ($templateFile!='') {
                $fileTemplate = ASTERION_BASE_FILE . 'data/HtmlMailTemplate/' . $templateFile . '.html';
                $contentTemplate = (file_exists($fileTemplate)) ? file_get_contents($fileTemplate) : '';
                Email::send($email, $subject, str_replace('#CONTENT', $content, $contentTemplate));
            } else {
                $template = HtmlMailTemplate::code('basic');
                Email::send($email, $subject, $template->showUi('Template', ['values' => ['CONTENT' => $content]]));
            }
        }
    }

}
