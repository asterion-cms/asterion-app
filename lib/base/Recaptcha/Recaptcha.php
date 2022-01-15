<?php
/**
 * @class Recaptcha
 *
 * This is a helper class to deal with the Recaptcha.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Recaptcha
{

    /**
     * Get the Recaptcha header.
     */
    public static function head()
    {
        return '
        	<script src="https://www.google.com/recaptcha/api.js"></script>
            <script>function onSubmitRecaptchaV3(token) {
                for (var i=0; i < document.getElementsByClassName("recaptchav3_form").length; i++) {
                    document.getElementsByClassName("recaptchav3_form")[i].submit();
                }
            }</script>';
    }

}
