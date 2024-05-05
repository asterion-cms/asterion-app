<?php
/**
 * @class UserLogin
 *
 * This class manages the login User objects.
 * It is a singleton, so it can only be instantiated one object using a function.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class User_Login_Interface extends Singleton
{

    protected $info;

    protected function initialize()
    {
        $this->info = Session::get('info_user_' . $this->userClassName);
        $this->info = ($this->info == '') ? [] : $this->info;
    }

    /**
     * Get the id of the logged user.
     */
    public function id()
    {
        return $this->get('id');
    }

    /**
     * Universal getter.
     */
    public function get($value)
    {
        return (isset($this->info[$value])) ? $this->info[$value] : '';
    }

    /**
     * Get the user.
     */
    public function user()
    {
        if (isset($this->user)) {
            return $this->user;
        }
        $this->user = (new $this->userClassName)->read($this->id());
        return $this->user;
    }

    /**
     * Update the session array.
     */
    public function sessionAdjust($info = [])
    {
        Session::set('info_user_' . $this->userClassName, $info);
    }

    /**
     * Check if the user is connected.
     */
    public function isConnected()
    {
        return (isset($this->info['id']) && $this->info['id'] != '') ? true : false;
    }

    /**
     * Check the user login using it's email and password.
     * If so, it saves the user values in the session.
     */
    public function checkLogin($options)
    {
        $values = [];
        $values['email'] = (isset($options['email'])) ? $options['email'] : '';
        $user = (new $this->userClassName())->readFirst(['where' => 'email=:email AND active="1"'], $values);
        $values['password'] = (isset($options['password'])) ? $options['password'] : '';
        if ($values['email'] != '' && $values['password'] != '' && $user->id() != '') {
            $values['hashed_password'] = hash('sha256', $user->get('password_salt') . $values['password']);
            // Regular password
            $user = (new $this->userClassName())->readFirst(['where' => 'email=:email AND password=:hashed_password AND active="1"'], ['email' => $values['email'], 'hashed_password' => $values['hashed_password']]);
            if ($user->id() != '') {
                $user->persistSimple('password_temporary', '');
                $user->persistSimple('last_login_date', date('Y-m-d h:i'));
                $this->autoLogin($user);
                return true;
            }
            // Temporary password
            $user = (new $this->userClassName())->readFirst(['where' => 'email=:email AND password_temporary=:password AND active="1"'], ['email' => $values['email'], 'password' => $values['password']]);
            if ($user->id() != '') {
                $user->persistSimple('last_login_date', date('Y-m-d h:i'));
                $this->autoLogin($user);
                return true;
            }
        }
        return false;
    }

    /**
     * Check the user login using it's authorization code from Facebook.
     */
    public function checkLoginFacebook($authcode)
    {
        $ch = curl_init();
        $url = 'https://graph.facebook.com/me?fields=first_name,last_name,id,email';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['access_token' => $authcode]);
        $data = curl_exec($ch);
        $response = json_decode($data, true);
        if (isset($response['id'])) {
            $user = (new User)->readFirst(['where' => '(id_facebook=:id_facebook OR email=:email) AND active="1"'], ['id_facebook' => $response['id'], 'email' => $response['email']]);
            if ($user->id() != '') {
                $user->persistSimple('last_login_date', date('Y-m-d h:i'));
                $this->autoLogin($user);
                return true;
            } else {
                $salt = Text::generateSalt();
                $email = (isset($response['email'])) ? $response['email'] : $response['id'] . '@facebook.com';
                $firstName = (isset($response['first_name'])) ? $response['first_name'] : __('name');
                $lastName = (isset($response['last_name'])) ? $response['last_name'] : __('last_name');
                $user = new User([
                    'id_facebook' => $response['id'],
                    'email' => $email,
                    'name' => $firstName,
                    'last_name' => $lastName,
                    'password_salt' => $salt,
                    'password' => $salt . Text::generateSalt(),
                    'active' => '1',
                ]);
                $persist = $user->persist();
                if ($persist['status'] == StatusCode::OK) {
                    $this->autoLogin($user);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check the user login using it's credential from Google.
     */
    public function checkLoginGoogle($credential)
    {
        $response = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $credential)[1]))), true);
        if (isset($response['azp']) && $response['azp'] == Parameter::code('google_client_id')) {
            $user = (new User)->readFirst(['where' => 'email=:email AND active="1"'], ['email' => $response['email']]);
            if ($user->id() != '') {
                $user->persistSimple('last_login_date', date('Y-m-d h:i'));
                $this->autoLogin($user);
                return true;
            } else {
                $salt = Text::generateSalt();
                $user = new User([
                    'email' => $response['email'],
                    'name' => $response['given_name'],
                    'last_name' => $response['family_name'],
                    'password_salt' => $salt,
                    'password' => $salt . Text::generateSalt(),
                    'active' => '1',
                ]);
                $persist = $user->persist();
                if ($persist['status'] == StatusCode::OK) {
                    $this->autoLogin($user);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Function to check if an email and a password are correct.
     */
    public function checkEmailPassword($email, $password, $salt)
    {
        $hashed = hash('sha256', $salt . $password);
        $user = (new $this->userClassName())->readFirst(['where' => 'email=:email AND password=:hashed_password AND active="1"'], ['email' => $email, 'hashed_password' => $hashed]);
        return ($user->id() != '') ? true : false;
    }

    /**
     * Check if the user is logged and that is not using a temporary password, if not redirect.
     */
    public function checkLoginRedirect($urlConnected = '')
    {
        if ($urlConnected != '') {
            Session::set('urlConnected', $urlConnected);
        }
        $this->checkLoginSimpleRedirect();
        if (!($this->user()->hasLoginFacebook() || $this->user()->hasLoginGoogle()) && $this->user()->get('password_temporary') != '') {
            $userClass = new $this->userClassName();
            header('Location: ' . $userClass->urlUpdateDefaultPassword);
            exit();
        }
    }

    /**
     * Check if the user is logged, if not redirect.
     */
    public function checkLoginSimpleRedirect()
    {
        if (!$this->isConnected()) {
            $userClass = new $this->userClassName;
            header('Location: ' . $userClass->urlLogin);
            exit();
        }
    }

    /**
     * Automatically login a user.
     */
    public function autoLogin($user)
    {
        $this->info['id'] = $user->id();
        $this->info['email'] = $user->get('email');
        $this->info['label'] = $user->getBasicInfo();
        $this->sessionAdjust($this->info);
        if ($user->getAttribute('authorization')) {
            $authorization = md5(rand() * rand());
            $user->persistSimple('authorization', $authorization);
            Cookie::set('authorization', $authorization);
        }
    }

    /**
     * Eliminate session values and logout a user.
     */
    public function logout()
    {
        $this->info = [];
        $this->sessionAdjust();
    }

}
