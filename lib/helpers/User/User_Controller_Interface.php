<?php
/**
 * @class UserController
 *
 * This class is the controller for the User objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class User_Controller_Interface extends Controller
{

    public function getContent()
    {
        $userClass = new $this->userClassName();
        $userLoginClassName = $userClass->userLoginClassName;
        $this->head = Recaptcha::head();
        switch ($this->action) {
            default:
                return parent::getContent();
                break;
            case 'login':
                $login = $userLoginClassName::getInstance();
                $this->title_page = __('login');
                $this->head .= $login->user()->head();
                $urlConnected = (Session::get('urlConnected') != '') ? Session::get('urlConnected') : $userClass->urlConnected;
                if ($login->isConnected()) {
                    Session::delete('urlConnected');
                    header('Location: ' . $urlConnected);
                    exit();
                }
                if (count($this->values) > 0) {
                    $login->checkLogin($this->values);
                    if ($login->isConnected()) {
                        Session::delete('urlConnected');
                        header('Location: ' . $urlConnected);
                        exit();
                    } else {
                        $form = new $userClass->userFormClassName();
                        $this->message_error = __('error_connection');
                        $this->content = $form->login();
                    }
                } else {
                    $form = new $userClass->userFormClassName();
                    $this->content = $form->login();
                }
                return $this->ui->render();
                break;
            case 'login_facebook':
                $login = $userLoginClassName::getInstance();
                $this->title_page = __('login');
                $urlConnected = (Session::get('urlConnected') != '') ? Session::get('urlConnected') : $userClass->urlConnected;
                if ($login->isConnected()) {
                    Session::delete('urlConnected');
                    header('Location: ' . $urlConnected);
                } else {
                    $login = $userLoginClassName::getInstance();
                    $authcode = (isset($this->parameters['authcode'])) ? $this->parameters['authcode'] : '';
                    $login->checkLoginFacebook($authcode);
                    if ($login->isConnected()) {
                        Session::delete('urlConnected');
                        header('Location: ' . $urlConnected);
                    } else {
                        header('Location: ' . $userClass->urlLogin);
                    }
                }
                exit();
                break;
            case 'login_google':
                $login = $userLoginClassName::getInstance();
                $this->title_page = __('login');
                $urlConnected = (Session::get('urlConnected') != '') ? Session::get('urlConnected') : $userClass->urlConnected;
                if ($login->isConnected()) {
                    Session::delete('urlConnected');
                    header('Location: ' . $urlConnected);
                } else {
                    $login = $userLoginClassName::getInstance();
                    $credential = (isset($this->parameters['credential'])) ? $this->parameters['credential'] : '';
                    $login->checkLoginGoogle($credential);
                    if ($login->isConnected()) {
                        Session::delete('urlConnected');
                        header('Location: ' . $urlConnected);
                    } else {
                        header('Location: ' . $userClass->urlLogin);
                    }
                }
                exit();
                break;
            case 'register':
                $login = $userLoginClassName::getInstance();
                $this->title_page = __('register');
                $this->head .= $login->user()->head();
                if ($login->isConnected()) {
                    header('Location: ' . url(''));
                    exit();
                }
                $action = $userClass->register($this->values);
                if ($action['status'] == StatusCode::OK) {
                    Session::flashInfo(__('user_registered_email_sent'));
                    header('Location: ' . $userClass->urlHome);
                    exit();
                } else {
                    $this->content = $action['content'];
                    return $this->ui->render();
                }
                break;
            case 'activate':
                $this->title_page = __('activate_user');
                $code = (isset($this->parameters['code'])) ? $this->parameters['code'] : '';
                $action = $userClass->activate($code);
                if ($action['status'] == StatusCode::OK) {
                    Session::flashInfo(__('user_activation_success'));
                    header('Location: ' . $userClass->urlConnected);
                } else {
                    Session::flashError(__('user_activation_error'));
                    header('Location: ' . $userClass->urlHome);
                }
                exit();
                break;
            case 'logout':
                $login = $userLoginClassName::getInstance();
                $login->logout();
                header('Location: ' . $userClass->urlHome);
                exit();
                break;
            case 'forgot':
                $this->title_page = __('password_forgot');
                $action = $userClass->forgot($this->values);
                if ($action['status'] == StatusCode::OK) {
                    Session::flashInfo(__('password_sent_mail'));
                    header('Location: ' . $userClass->urlLogin);
                    exit();
                } else {
                    $this->content = $action['content'];
                    $this->message_error = $action['message_error'];
                    return $this->ui->render();
                }
                break;
            case 'update_default_password':
                $login = $userLoginClassName::getInstance();
                $login->checkLoginSimpleRedirect();
                $this->title_page = __('update_default_password');
                $action = $login->user()->updateDefaultPassword($this->values);
                if ($action['status'] == StatusCode::OK) {
                    Session::flashInfo(__('default_password_changed_message'));
                    header('Location: ' . $userClass->urlConnected);
                    exit();
                } else {
                    $this->content = $action['content'];
                    return $this->ui->render();
                }
                break;
            case 'profile':
                $login = $userLoginClassName::getInstance();
                $login->checkLoginSimpleRedirect();
                $this->title_page = __('profile');
                $action = $login->user()->updateProfile($this->values);
                if ($action['status'] == StatusCode::OK) {
                    Session::flashInfo(__('updated_profile_success'));
                    header('Location: ' . $userClass->urlProfile);
                    exit();
                } else {
                    $this->content = $action['content'];
                    return $this->ui->render();
                }
                break;
            case 'update_password':
                $login = $userLoginClassName::getInstance();
                $login->checkLoginRedirect();
                $this->title_page = __('update_password');
                $action = $login->user()->updatePassword($this->values);
                if ($action['status'] == StatusCode::OK) {
                    Session::flashInfo(__('change_password_success'));
                    header('Location: ' . $userClass->urlProfile);
                    exit();
                } else {
                    $this->content = $action['content'];
                    return $this->ui->render();
                }
                break;
            case 'update_email':
                $login = $userLoginClassName::getInstance();
                $login->checkLoginRedirect();
                $this->title_page = __('update_email');
                $action = $login->user()->updateEmail($this->values);
                if ($action['status'] == StatusCode::OK) {
                    header('Location: ' . $userClass->urlUpdateEmailConfirm);
                    exit();
                } else {
                    $this->content = $action['content'];
                    return $this->ui->render();
                }
                break;
            case 'update_email_confirm':
                $login = $userLoginClassName::getInstance();
                $login->checkLoginRedirect();
                $this->title_page = __('update_email');
                $action = $login->user()->updateEmailConfirm($this->values);
                if ($action['status'] == StatusCode::OK) {
                    Session::flashInfo(__('change_email_success'));
                    header('Location: ' . $userClass->urlProfile);
                    exit();
                } else {
                    $this->content = $action['content'];
                    return $this->ui->render();
                }
                break;
            case 'send_forgot_password':
                $this->checkLoginAdmin();
                $user = $userClass->read($this->id);
                if ($user->id() != '') {
                    $user->persistSimple('active', 1);
                    $forgot = $user->forgot(['email' => $user->get('email')]);
                    if ($forgot['status'] == StatusCode::OK) {
                        Session::flashInfo(str_replace('#EMAIL', $user->get('email'), __('forgot_email_sent')));
                    } else {
                        Session::flashError(__('forgot_email_error'));
                    }
                    header('Location: ' . url(camelToSnake($user->className) . '/modify_view/' . $this->id, true));
                    exit();
                }
                header('Location: ' . url('', true));
                exit();
                break;
            case 'upload_profile_picture':
                $this->mode = 'json';
                $login = $userLoginClassName::getInstance();
                $login->checkLoginRedirect();
                $response = ['status' => StatusCode::NOK];
                if (isset($this->values['filename']) && isset($this->values['file'])) {
                    $response = File::uploadTempImage(['filename' => $this->values['filename'], 'file' => $this->values['file']]);
                }
                return json_encode($response);
                break;
            case 'delete_profile_picture':
                $this->mode = 'json';
                $login = $userLoginClassName::getInstance();
                $login->checkLoginRedirect();
                return json_encode($login->user()->deleteProfilePicture());
                break;
            case 'delete_account':
                $login = $userLoginClassName::getInstance();
                $login->checkLoginSimpleRedirect();
                $this->title_page = __('delete_account');
                $action = $login->user()->deleteAccount($this->values);
                if ($action['status'] == StatusCode::OK) {
                    Session::flashInfo(__('deleted_account_success'));
                    header('Location: ' . $userClass->urlHome);
                    exit();
                } else {
                    $this->content = $action['content'];
                    return $this->ui->render();
                }
                exit();
                break;
        }
    }

}
