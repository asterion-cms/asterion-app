<?php
/**
 * @class UserInterface
 *
 * This class defines the users.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class User_Interface extends Db_Object
{

    public function __construct($values = [])
    {
        parent::__construct($values);
    }

    public function checkBeforeModify()
    {
        unset($this->values['password']);
        unset($this->values['password_salt']);
        unset($this->values['password_temporary']);
    }

    public function urlDeleteImagePublic($valueFile = '')
    {
        return $this->urlDeleteImage;
    }

    public function urlUploadTempImagePublic()
    {
        return $this->urlUploadTempImage;
    }

    /**
     * Function to register a user.
     */
    public function register($values)
    {
        $status = StatusCode::NOK;
        $content = '';
        if (count($values) > 0) {
            $values['active'] = '0';
            $user = new $this->userClassName($values);
            if (count($user->validateRegister()) > 0) {
                Session::flashError(__('errors_form'));
                $form = new $this->userFormClassName($user->values, $user->errors);
                $content = $form->register();
            } else {
                $salt = Text::generateSalt();
                $user->set('password_salt', $salt);
                $user->set('password', $salt . $values['password']);
                $persist = $user->persist();
                if ($persist['status'] == StatusCode::OK) {
                    $user->persistSimple('verify_code', Text::generateRandomString());
                    $user->sendEmailCreation();
                    $status = StatusCode::OK;
                } else {
                    Session::flashError(__('errors_form'));
                    $form = new $this->userFormClassName($persist['values'], $persist['errors']);
                    $content = $form->register();
                }
            }
        } else {
            $form = new $this->userFormClassName();
            $content = $form->register();
        }
        return ['status' => $status, 'content' => $content];
    }

    /**
     * Activate an user if the hash is correct.
     */
    public function activate($hash)
    {
        $user = (new $this->userClassName)->readFirst(['where' => 'verify_code=:hash'], ['hash' => $hash]);
        $response = ['status' => StatusCode::NOK];
        if ($user->id() != '') {
            $user->persistSimple('verify_code', '');
            $user->persistSimple('active', true);
            $user->sendEmailActivation();
            $userLoginClassName = $this->userLoginClassName;
            $login = $userLoginClassName::getInstance();
            $login->autoLogin($user);
            $response = ['status' => StatusCode::OK];
        }
        return $response;
    }

    /**
     * Send the email with a temporary password to the user.
     */
    public function forgot($values)
    {
        $status = StatusCode::NOK;
        $content = '';
        $message_error = '';
        $form = new $this->userFormClassName();
        if (isset($values['email'])) {
            $user = (new $this->userClassName)->readFirst(['where' => 'email="' . $values['email'] . '" AND active="1"']);
            if ($user->id() != '') {
                $temporaryPassword = Text::generateSmallPassword();
                $user->persistSimple('password_temporary', $temporaryPassword);
                $user->sendEmailPasswordForgot();
                $status = StatusCode::OK;
            } else {
                $message_error = __('mail_doesnt_exist');
                $content = $form->forgot();
            }
        } else {
            $form = new $this->userFormClassName();
            $content = $form->forgot();
        }
        return ['status' => $status, 'content' => $content, 'message_error' => $message_error];
    }

    /**
     * Update the temporary or default password.
     */
    public function updateDefaultPassword($values)
    {
        $status = StatusCode::NOK;
        $content = '';
        if (count($values) > 0) {
            $this->addValues($values);
            if (count($this->validateChangePasswordDefault()) > 0) {
                $form = new $this->userFormClassName($this->values, $this->errors);
                $content = $form->changeDefaultPassword();
            } else {
                $salt = Text::generateSalt();
                $this->set('password_salt', $salt);
                $this->set('password', $salt . $values['password_new']);
                $this->set('password_temporary', '');
                $persist = $this->persist();
                if ($persist['status'] == StatusCode::OK) {
                    $status = StatusCode::OK;
                } else {
                    $form = new $this->userFormClassName($persist['values'], $persist['errors']);
                    $content = $form->changeDefaultPassword();
                }
            }
        } else {
            $form = new $this->userFormClassName();
            $content = $form->changeDefaultPassword();
        }
        return ['status' => $status, 'content' => $content];
    }

    /**
     * Update the password.
     */
    public function updatePassword($values)
    {
        $status = StatusCode::NOK;
        $content = '';
        if (count($values) > 0) {
            $this->addValues($values);
            if (count($this->validateChangePassword()) > 0) {
                $form = new $this->userFormClassName($this->values, $this->errors);
                $content = $form->changePassword();
            } else {
                $salt = Text::generateSalt();
                $this->set('password_salt', $salt);
                $this->set('password', $salt . $values['new_password']);
                $this->set('password_temporary', '');
                $persist = $this->persist();
                if ($persist['status'] == StatusCode::OK) {
                    $status = StatusCode::OK;
                } else {
                    $form = new $this->userFormClassName($persist['values'], $persist['errors']);
                    $content = $form->changePassword();
                }
            }
        } else {
            $form = new $this->userFormClassName();
            $content = $form->changePassword();
        }
        return ['status' => $status, 'content' => $content];
    }

    /**
     * Update the email.
     */
    public function updateEmail($values)
    {
        $status = StatusCode::NOK;
        $content = '';
        if (count($values) > 0) {
            $this->addValues($values);
            if (count($this->validateChangeEmail()) > 0) {
                $form = new $this->userFormClassName($this->values, $this->errors);
                $content = $form->changeEmail();
            } else {
                $this->persistSimple('new_email', $values['new_email']);
                $this->persistSimple('new_email_code', Text::generateSmallPassword());
                $this->sendEmailUpdateEmail();
                $status = StatusCode::OK;
            }
        } else {
            $form = new $this->userFormClassName();
            $content = $form->changeEmail();
        }
        return ['status' => $status, 'content' => $content];
    }

    /**
     * Confirm the new email.
     */
    public function updateEmailConfirm($values)
    {
        $status = StatusCode::NOK;
        $content = '';
        if (count($values) > 0) {
            unset($values['new_email']);
            unset($values['new_email_code']);
            $this->addValues($values);
            if (count($this->validateUpdateEmailConfirm()) > 0) {
                $form = new $this->userFormClassName($this->values, $this->errors);
                $content = $form->confirmChangeEmail();
            } else {
                $this->persistSimple('email', $this->get('new_email'));
                $this->persistSimple('new_email', '');
                $this->persistSimple('new_email_code', '');
                $status = StatusCode::OK;
            }
        } else {
            $form = new $this->userFormClassName();
            $content = $form->confirmChangeEmail();
        }
        return ['status' => $status, 'content' => $content];
    }

    /**
     * Update the profile.
     */
    public function updateProfile($values)
    {
        $status = StatusCode::NOK;
        $content = '';
        if (count($values) > 0) {
            $this->addValues($values);
            if (count($this->validateProfile()) > 0) {
                $form = new $this->userFormClassName($this->values, $this->errors);
                $content = $form->profile();
            } else {
                unset($this->values['password']);
                unset($this->values['password_salt']);
                unset($this->values['password_temporary']);
                $persist = $this->persist();
                if ($persist['status'] == StatusCode::OK) {
                    $status = StatusCode::OK;
                } else {
                    $form = new $this->userFormClassName($persist['values'], $persist['errors']);
                    $content = $form->profile();
                }
            }
        } else {
            $form = new $this->userFormClassName();
            $form = $form->fromObject($this);
            $content = $form->profile();
        }
        return ['status' => $status, 'content' => $content];
    }

    /**
     * Delete an account.
     */
    public function deleteAccount($values)
    {
        $status = StatusCode::NOK;
        $content = '';
        $form = new $this->userFormClassName();
        $form = $form->fromObject($this);
        $content = $form->deleteAccount();
        if (count($values) > 0) {
            $code = (isset($values['code'])) ? $values['code'] : '';
            if ($code == $this->get('delete_code')) {
                $delete = $this->delete();
                if ($delete['status'] == StatusCode::OK) {
                    $status = StatusCode::OK;
                    $userLoginClassName = $this->userLoginClassName;
                    $login = $userLoginClassName::getInstance();
                    $login->logout();
                }
            } else {
                Session::flashError(__('delete_code_error_message'));
            }
        } else {
            $this->persistSimple('delete_code', rand(111111, 999999));
            $this->persistSimple('delete_code_date', date('Y-m-d H:i:s'));
            $this->sendEmailDeleteAccount();
        }
        return ['status' => $status, 'content' => $content];
    }

    /**
     * Upload the profile picture. Used for an ajax call.
     */
    public function uploadProfilePicture($filename, $file)
    {
        return File::uploadTempImage($filename, $file);
    }

    /**
     * Delete the profile picture. Used for an ajax call.
     */
    public function deleteProfilePicture()
    {
        $status = StatusCode::NOK;
        try {
            Image_File::deleteImage($this->className, $this->get('image'));
            $this->persistSimple('image', '');
            $status = StatusCode::OK;
        } catch (Exception $e) {};
        return ['status' => $status];
    }

    /**
     * Send an email when the user is registered.
     */
    public function sendEmailCreation()
    {
        $userClass = new $this->userClassName;
        $activationLink = $userClass->urlActivate . '?code=' . $this->get('verify_code');
        $sendEmailAdministrator = (Parameter::code('user_send_email_administrator') == 'true') ? true : false;
        HtmlMail::send($this->get('email'), 'user_registration', ['USER_NAME' => $this->get('name'), 'EMAIL' => $this->get('email'), 'ACTIVATION_LINK' => $activationLink]);
        if ($sendEmailAdministrator) {
            HtmlMail::send(Parameter::code('user_email_admin'), 'user_registration_admin', ['USER_NAME' => $this->get('name'), 'EMAIL' => $this->get('email'), 'ACTIVATION_LINK' => $activationLink]);
        }
    }

    /**
     * Send an email when the user is activated.
     */
    public function sendEmailActivation()
    {
        $sendEmailActivation = (Parameter::code('user_send_email_activation') == 'true') ? true : false;
        if ($sendEmailActivation) {
            HtmlMail::send($this->get('email'), 'user_activation', ['USER_NAME' => $this->get('name'), 'EMAIL' => $this->get('email')]);
            $sendEmailAdministrator = (Parameter::code('user_send_email_administrator') == 'true') ? true : false;
            if ($sendEmailAdministrator) {
                HtmlMail::send(Parameter::code('user_email_admin'), 'user_activation_admin', ['USER_NAME' => $this->get('name'), 'EMAIL' => $this->get('email')]);
            }
        }
    }

    /**
     * Send an email when the user wants to change an email.
     */
    public function sendEmailUpdateEmail()
    {
        HtmlMail::send($this->get('new_email'), 'user_change_email', ['USER_NAME' => $this->get('name'), 'USER_EMAIL' => $this->get('email'), 'CODE' => $this->get('new_email_code')]);
        if ($sendEmailAdministrator) {
            HtmlMail::send(Parameter::code('user_email_admin'), 'user_change_email_admin', ['USER_NAME' => $this->get('name'), 'USER_EMAIL' => $this->get('email'), 'CODE' => $this->get('new_email_code')]);
        }
    }

    /**
     * Send an email with a temporary password to the user.
     */
    public function sendEmailPasswordForgot()
    {
        HtmlMail::send($this->get('email'), 'user_password_forgot', ['USER_NAME' => $this->get('name'), 'TEMPORARY_PASSWORD' => $this->get('password_temporary'), 'UPDATE_PASSWORD_LINK' => $this->urlLogin]);
    }

    /**
     * Send an email when the user is created automatically.
     */
    public function sendEmailCreationAutomatically()
    {
        $userClass = new $this->userClassName;
        HtmlMail::send($this->get('email'), 'user_automatic_creation', ['USER_NAME' => $this->get('name'), 'USER_EMAIL' => $this->get('email'), 'TEMPORARY_PASSWORD' => $this->get('password_temporary'), 'LOGIN_LINK' => $userClass->urlLogin]);
    }

    /**
     * Send an email when the user wants to delete the account.
     */
    public function sendEmailDeleteAccount()
    {
        $userClass = new $this->userClassName;
        HtmlMail::send($this->get('email'), 'user_delete_account', ['USER_NAME' => $this->get('name'), 'USER_EMAIL' => $this->get('email'), 'DELETE_CODE' => $this->get('delete_code')]);
    }

    /**
     * Check if everything is correct before using the object.
     * In this case we need some emails to exists in the database, if not we register them with default values.
     */
    public static function init()
    {
        $emailCodes = [
            'user_registration' => [
                'title' => __('mail_user_registration_title'),
                'subject' => __('mail_user_registration_subject'),
                'description' => __('mail_user_registration_description'),
                'mail' => __('mail_user_registration_content'),
            ],
            'user_registration_admin' => [
                'title' => __('mail_user_registration_admin_title'),
                'subject' => __('mail_user_registration_admin_subject'),
                'description' => __('mail_user_registration_admin_description'),
                'mail' => __('mail_user_registration_admin_content'),
            ],
            'user_activation' => [
                'title' => __('mail_user_activation_title'),
                'subject' => __('mail_user_activation_subject'),
                'description' => __('mail_user_activation_description'),
                'mail' => __('mail_user_activation_content'),
            ],
            'user_activation_admin' => [
                'title' => __('mail_user_activation_admin_title'),
                'subject' => __('mail_user_activation_admin_subject'),
                'description' => __('mail_user_activation_admin_description'),
                'mail' => __('mail_user_activation_admin_content'),
            ],
            'user_change_email' => [
                'title' => __('mail_user_change_email_title'),
                'subject' => __('mail_user_change_email_subject'),
                'description' => __('mail_user_change_email_description'),
                'mail' => __('mail_user_change_email_content'),
            ],
            'user_change_email_admin' => [
                'title' => __('mail_user_change_email_admin_title'),
                'subject' => __('mail_user_change_email_admin_subject'),
                'description' => __('mail_user_change_email_admin_description'),
                'mail' => __('mail_user_change_email_admin_content'),
            ],
            'user_password_forgot' => [
                'title' => __('mail_user_password_forgot_title'),
                'subject' => __('mail_user_password_forgot_subject'),
                'description' => __('mail_user_password_forgot_description'),
                'mail' => __('mail_user_password_forgot_content'),
            ],
            'user_automatic_creation' => [
                'title' => __('mail_user_automatic_creation_title'),
                'subject' => __('mail_user_automatic_creation_subject'),
                'description' => __('mail_user_automatic_creation_description'),
                'mail' => __('mail_user_automatic_creation_content'),
            ],
            'user_delete_account' => [
                'title' => __('mail_user_delete_account_title'),
                'subject' => __('mail_user_delete_account_subject'),
                'description' => __('mail_user_delete_account_description'),
                'mail' => __('mail_user_delete_account_content'),
            ],
        ];
        foreach ($emailCodes as $emailCode => $emailValues) {
            $htmlMail = HtmlMail::code($emailCode);
            if ($htmlMail->id() == '') {
                $values = [];
                $values['code'] = $emailCode;
                foreach (Language::languages() as $languageCode => $language) {
                    $values['title_' . $languageCode] = $emailValues['title'];
                    $values['subject_' . $languageCode] = $emailValues['subject'];
                    $values['description_' . $languageCode] = $emailValues['description'];
                    $values['email_' . $languageCode] = $emailValues['mail'];
                }
                $htmlMail->setValues($values);
                $htmlMail->persist();
            }
        }
    }

    public function validateRegister()
    {
        $this->validateReCaptchaV3();
        $this->validatePasswordConfirmation();
        $this->validate();
        if ((new $this->userClassName)->readFirst(['where' => 'active="1" AND email="' . $this->values['email'] . '"'])->id() != '') {
            $this->errors['email'] = __('email_already_used');
        }
        return $this->errors;
    }

    public function validatePasswordConfirmation()
    {
        if (!isset($this->values['password_confirmation']) || trim($this->values['password_confirmation']) == '') {
            $this->errors['password_confirmation'] = __('not_empty');
        } else {
            if ($this->values['password'] != $this->values['password_confirmation']) {
                $this->errors['password_confirmation'] = __('password_confirmation_error');
            }
        }
        return $this->errors;
    }

    public function validateChangePassword()
    {
        if (!isset($this->values['old_password']) || trim($this->values['old_password']) == '') {
            $this->errors['old_password'] = __('not_empty');
        }
        if (!isset($this->values['new_password']) || trim($this->values['new_password']) == '') {
            $this->errors['new_password'] = __('not_empty');
        }
        if (!isset($this->values['new_password_confirmation']) || trim($this->values['new_password_confirmation']) == '') {
            $this->errors['new_password_confirmation'] = __('not_empty');
        }
        if (count($this->errors) == 0) {
            $errorNewPassword = Db_Validation::validatePassword($this->values['new_password']);
            if ($errorNewPassword != '') {
                $this->errors['new_password'] = $errorNewPassword;
            } else if ($this->get('password_temporary') != '') {
                if ($this->values['old_password'] != $this->get('password_temporary')) {
                    $this->errors['old_password'] = __('old_password_error');
                }
            } else if (hash('sha256', $this->get('password_salt') . $this->values['old_password']) != $this->get('password')) {
                $this->errors['old_password'] = __('old_password_error');
            }
        }
        return $this->errors;
    }

    public function validateChangePasswordDefault()
    {
        if (!isset($this->values['password_new']) || trim($this->values['password_new']) == '') {
            $this->errors['password_new'] = __('not_empty');
        } else if (!isset($this->values['password_confirmation']) || trim($this->values['password_confirmation']) == '') {
            $this->errors['password_confirmation'] = __('not_empty');
        } else {
            $errorPassword = Db_Validation::validatePassword($this->values['password_new']);
            if ($errorPassword != '') {
                $this->errors['password_new'] = $errorPassword;
            }
            if ($this->values['password_new'] != $this->values['password_confirmation']) {
                $this->errors['password_confirmation'] = __('password_confirmation_error');
            }
        }
        return $this->errors;
    }

    public function validateChangeEmail()
    {
        if (!isset($this->values['new_email']) || trim($this->values['new_email']) == '') {
            $this->errors['new_email'] = __('not_empty');
        } else if (!filter_var($this->values['new_email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['new_email'] = __('error_mail');
        }
        $idCheck = ($this->id() != '') ? 'AND id!="' . $this->id() . '"' : '';
        $existingUser = (new $this->userClassName)->readFirst(['where' => 'active="1" ' . $idCheck . ' AND email="' . $this->values['new_email'] . '"']);
        if ($existingUser->id() != '') {
            $this->errors['new_email'] = __('email_already_used');
        }
        return $this->errors;
    }

    public function validateUpdateEmailConfirm()
    {
        if (!isset($this->values['code']) || trim($this->values['code']) == '') {
            $this->errors['code'] = __('not_empty');
        } else if ($this->values['code'] != $this->get('new_email_code')) {
            $this->errors['code'] = __('error_code');
        }
        return $this->errors;
    }

    public function validateProfile()
    {
        return $this->validateAttributes(['name']);
    }

    public function hasLoginFacebook()
    {
        return (isset($this->loginFacebook) && $this->loginFacebook);
    }

    public function hasLoginGoogle()
    {
        return (isset($this->loginGoogle) && $this->loginGoogle);
    }

    public function head()
    {
        return '
            ' . (((isset($this->loginFacebook)) && $this->loginFacebook) ? '
                <script>
                    (function(d, s, id){
                     var js, fjs = d.getElementsByTagName(s)[0];
                     if (d.getElementById(id)) {return;}
                     js = d.createElement(s); js.id = id;
                     js.src = "https://connect.facebook.net/en_US/sdk.js";
                     fjs.parentNode.insertBefore(js, fjs);
                    }(document, \'script\', \'facebook-jssdk\'));
                </script>
            ' : '') . '
            ' . (((isset($this->loginGoogle)) && $this->loginGoogle) ? '
                <script src="https://accounts.google.com/gsi/client" async defer></script>
            ' : '');
    }

}
