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
class User_Login_Interface
{

    /**
     * Singleton pattern.
     * To instantiate this object we use the getInstance() static function.
     */
    protected static $login = null;
    protected $info;

    private function __construct()
    {
        $this->info = Session::get('info_user_' . $this->userClassName);
        $this->info = ($this->info == '') ? [] : $this->info;
    }

    private function __clone()
    {}

    public static function getInstance($test='')
    {
        $calledClass = get_called_class();
        if (null === $calledClass::$login) {
            $calledClass::$login = new $calledClass();
        }
        return $calledClass::$login;
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
    public function checklogin($options)
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
     * Check if the user is logged and that is not using a temporary password, if not redirect.
     */
    public function checkLoginRedirect()
    {
        $this->checkLoginSimpleRedirect();
        if ($this->user()->get('password_temporary') != '') {
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
