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
            <script>
            function onSubmitRecaptchaV3Main(token) {
                for (var i=0; i < document.getElementsByClassName("recaptchav3_form").length; i++) {
                    var form = document.getElementsByClassName("recaptchav3_form")[i];
                    if (form.reportValidity() !== false) {
                        form.submit();
                    }
                }
            }
            document.addEventListener("DOMContentLoaded", function() {
                var forms = document.getElementsByClassName("recaptchav3_form");
                for (var i = 0; i < forms.length; i++) {
                    var formId = forms[i].id;
                    if (formId) {
                        window["onSubmitRecaptchaV3_" + formId] = (function(id) {
                            return function(token) {
                                var form = document.getElementById(id);
                                if (form && form.reportValidity() !== false) {
                                    form.submit();
                                }
                            };
                        })(formId);
                    }
                }
            });
            </script>';
    }

}
