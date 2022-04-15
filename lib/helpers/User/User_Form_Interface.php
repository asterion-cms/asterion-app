<?php
/**
 * @class UserFormInterface
 *
 * This class is the main interface for future forms for the User objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class User_Form_Interface extends Form
{

    public function createFormFields($options = [])
    {
        return '
            ' . $this->field('id') . '
            ' . $this->field('image') . '
            ' . $this->field('name') . '
            ' . $this->field('email') . '
            ' . $this->field('active');
    }

    public function formModifyAdministratorActionsTop()
    {
        if ($this->object->id() != '') {
            return '
                <div class="form_admin_modify_actions_top">
                    <div class="form_admin_modify_actions_top_left">
                        <p><strong>' . __('created') . ' : </strong>' . Date::sqlText($this->object->get('created'), true) . '</p>
                        <p><strong>' . __('last_login_date') . ' : </strong>' . Date::sqlText($this->object->get('last_login_date'), true) . '</p>
                    </div>
                    <div class="form_admin_modify_actions_top_right">
                        <a href="' . url(camelToSnake($this->object->className) . '/send_forgot_password/' . $this->object->id(), true) . '" class="button button_small button_default">
                            <i class="fa fa-envelope"></i>
                            <span>' . __('send_forgot_password') . '</span>
                        </a>
                    </div>
                </div>';
        }
    }

    public function login($options = [])
    {
        $userClass = new $this->userClassName;
        return '
            <div class="simple_form">
                <h1>' . __('login') . '</h1>
                ' . (($this->object->hasLoginFacebook() || $this->object->hasLoginGoogle()) ? '
                    <div class="button_login_extras">
                        ' . (($this->object->hasLoginFacebook()) ? '
                                <div class="facebook_info"
                                    data-appid="' . Parameter::code('facebook_app_id') . '"
                                    data-urllogin="' . $this->object->urlLoginFacebook . '"></div>
                                <div class="button_login_extra button_login_extra_facebook">
                                    <div class="button_login_extra_ins">' . __('facebook_login') . '</div>
                                </div>
                            ' : '') . '
                        ' . (($this->object->hasLoginGoogle()) ? '
                                <div class="google_info"
                                    data-clientid="' . Parameter::code('google_client_id') . '"
                                    data-urllogin="' . $this->object->urlLoginGoogle . '"></div>
                                <div class="button_login_extra button_login_extra_google">
                                    <div class="button_login_extra_ins">
                                        <div id="button_login_extra_google"></div>
                                    </div>
                                </div>
                            ' : '') . '
                    </div>
                ' : '') . '
                <p>' . __('login_message') . '</p>
                ' . $this->loginForm($options) . '
                <div class="simple_form_actions">
                    <a href="' . $userClass->urlForgot . '">' . __('password_forgot') . '</a>
                    ' . ((!isset($options['no_register'])) ? '
                    <a href="' . $userClass->urlRegister . '">' . __('register') . '</a>
                    ' : '') . '
                </div>
            </div>';
    }

    public function loginForm($options = [])
    {
        $userClass = new $this->userClassName;
        $defaultOptions = [
            'action' => $userClass->urlLogin,
            'class' => 'form_site form_user',
            'recaptchav3' => true,
            'submit' => __('send'),
        ];
        $options = array_merge($defaultOptions, $options);
        $fields = '
            ' . $this->field('email') . '
            ' . $this->field('password');
        return Form::createForm($fields, $options);
    }

    public function register($options = [])
    {
        return '
            <div class="simple_form">
                <h1>' . __('register') . '</h1>
                <p>' . __('register_message') . '</p>
                ' . $this->registerForm($options) . '
            </div>';
    }

    public function registerForm($options = [])
    {
        $userClass = new $this->userClassName;
        $defaultOptions = [
            'action' => $userClass->urlRegister,
            'class' => 'form_site form_user',
            'recaptchav3' => true,
            'submit' => __('send'),
        ];
        $options = array_merge($defaultOptions, $options);
        $this->errors['password_confirmation'] = isset($this->errors['password_confirmation']) ? $this->errors['password_confirmation'] : '';
        $fields = '
            ' . $this->field('name') . '
            ' . $this->field('email') . '
            ' . $this->field('password') . '
            ' . FormField::show('password', ['label' => __('password_confirmation'), 'name' => 'password_confirmation', 'error' => $this->errors['password_confirmation'], 'value' => '']);
        return Form::createForm($fields, $options);
    }

    public function forgot($options = [])
    {
        $userClass = new $this->userClassName;
        return '
            <div class="simple_form">
                <h1>' . __('password_forgot') . '</h1>
                <p>' . __('password_forgot_message') . '</p>
                ' . $this->forgotForm($options) . '
                <div class="simple_form_actions">
                    <a href="' . $userClass->urlLogin . '">' . __('try_login_again') . '</a>
                </div>
            </div>';
    }

    public function forgotForm($options = [])
    {
        $userClass = new $this->userClassName;
        $defaultOptions = [
            'action' => $userClass->urlForgot,
            'class' => 'form_site form_user',
            'recaptchav3' => true,
            'submit' => __('send'),
        ];
        $options = array_merge($defaultOptions, $options);
        $fields = $this->field('email');
        return Form::createForm($fields, $options);
    }

    public function changeDefaultPassword($options = [])
    {
        return '
            <div class="simple_form">
                <p>' . __('update_default_password_message') . '</p>
                ' . $this->changeDefaultPasswordForm($options) . '
            </div>';
    }

    public function changeDefaultPasswordForm($options = [])
    {
        $userClass = new $this->userClassName;
        $defaultOptions = [
            'action' => $userClass->urlUpdateDefaultPassword,
            'class' => 'form_site form_user',
            'recaptchav3' => true,
            'submit' => __('save'),
        ];
        $options = array_merge($defaultOptions, $options);
        $this->errors['password_new'] = isset($this->errors['password_new']) ? $this->errors['password_new'] : '';
        $this->errors['password_confirmation'] = isset($this->errors['password_confirmation']) ? $this->errors['password_confirmation'] : '';
        $fields = '
            ' . FormField::show('password', ['label' => __('password'), 'name' => 'password_new', 'error' => $this->errors['password_new'], 'value' => '']) . '
            ' . FormField::show('password', ['label' => __('password_confirmation'), 'name' => 'password_confirmation', 'error' => $this->errors['password_confirmation'], 'value' => '']);
        return Form::createForm($fields, $options);
    }

    public function changePassword($options = [])
    {
        return '
            <div class="simple_form">
                <p>' . __('change_password_message') . '</p>
                ' . $this->changePasswordForm($options) . '
            </div>';
    }

    public function changePasswordForm($options = [])
    {
        $userClass = new $this->userClassName;
        $defaultOptions = [
            'action' => $userClass->urlUpdatePassword,
            'class' => 'form_site form_user',
            'recaptchav3' => true,
            'submit' => __('save'),
        ];
        $options = array_merge($defaultOptions, $options);
        $this->errors['old_password'] = isset($this->errors['old_password']) ? $this->errors['old_password'] : '';
        $this->errors['new_password'] = isset($this->errors['new_password']) ? $this->errors['new_password'] : '';
        $this->errors['new_password_confirmation'] = isset($this->errors['new_password_confirmation']) ? $this->errors['new_password_confirmation'] : '';
        $fields = '
            ' . FormField::show('password', ['label' => __('old_password'), 'name' => 'old_password', 'error' => $this->errors['old_password'], 'value' => '', 'required' => true]) . '
            ' . FormField::show('password', ['label' => __('new_password'), 'name' => 'new_password', 'error' => $this->errors['new_password'], 'value' => '', 'required' => true]) . '
            ' . FormField::show('password', ['label' => __('new_password_confirmation'), 'name' => 'new_password_confirmation', 'error' => $this->errors['new_password_confirmation'], 'value' => '', 'required' => true]);
        return Form::createForm($fields, $options);
    }

    public function changeEmail($options = [])
    {
        return '
            <div class="simple_form">
                <p>' . __('change_email_message') . '</p>
                ' . $this->changeEmailForm($options) . '
            </div>';
    }

    public function changeEmailForm($options = [])
    {
        $userClass = new $this->userClassName;
        $defaultOptions = [
            'action' => $userClass->urlUpdateEmail,
            'class' => 'form_site form_user',
            'recaptchav3' => true,
            'submit' => __('save'),
        ];
        $options = array_merge($defaultOptions, $options);
        $this->errors['new_email'] = isset($this->errors['new_email']) ? $this->errors['new_email'] : '';
        $fields = '
            ' . FormField::show('text_email', ['label' => __('new_email'), 'name' => 'new_email', 'error' => $this->errors['new_email'], 'value' => '']);
        return Form::createForm($fields, $options);
    }

    public function confirmChangeEmail($options = [])
    {
        return '
            <div class="simple_form">
                <p>' . __('confirm_change_email_message') . '</p>
                ' . $this->confirmChangeEmailForm($options) . '
            </div>';
    }

    public function confirmChangeEmailForm($options = [])
    {
        $userClass = new $this->userClassName;
        $defaultOptions = [
            'action' => $userClass->urlUpdateEmailConfirm,
            'class' => 'form_site form_user',
            'recaptchav3' => true,
            'submit' => __('save'),
        ];
        $options = array_merge($defaultOptions, $options);
        $this->errors['code'] = isset($this->errors['code']) ? $this->errors['code'] : '';
        $fields = '
            ' . FormField::show('text', ['label' => __('code'), 'name' => 'code', 'error' => $this->errors['code'], 'value' => '', 'required' => 'not_empty']);
        return Form::createForm($fields, $options);
    }

    public function profile($options = [])
    {
        return '
            <div class="simple_form">
                <p>' . __('account_message') . '</p>
                ' . $this->profileForm($options) . '
            </div>';
    }

    public function profileForm($options = [])
    {
        $userClass = new $this->userClassName;
        $defaultOptions = [
            'action' => $userClass->urlProfile,
            'class' => 'form_site form_user',
            'recaptchav3' => true,
            'submit' => __('save'),
        ];
        $options = array_merge($defaultOptions, $options);
        $fields = '
            <div class="form_field form_field_email">
                <div class="form_field_ins">
                    <label>' . __('email') . '</label>
                    <p>' . $this->object->get('email') . '</p>
                </div>
            </div>
            ' . $this->field('image') . '
            ' . $this->field('name');
        return Form::createForm($fields, $options);
    }

}
