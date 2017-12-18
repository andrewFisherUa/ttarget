<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
    const LOGIN_TOKEN = 't';
    const PASSWD_TOKEN = 'p';
	private $_id;
	private $email;
	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the email and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate($skipPasswordCheck = false)
	{
		$email=strtolower($this->username);
        $user=Users::model()->find('LOWER(email)=?',array($email));
        if($user===null){
            $this->errorCode=self::ERROR_USERNAME_INVALID;
        } else {
            if($skipPasswordCheck){
                $_valid = true;
            }else {
                $_valid = $user->validatePassword($this->password);
            }

        	if(!$_valid){
        		$this->errorCode=self::ERROR_PASSWORD_INVALID;
        	} else {
        		$this->_id=$user->id;
        		$this->email=$user->email;
        		$this->errorCode=self::ERROR_NONE;

                $this->setState(self::PASSWD_TOKEN, $user->passwd_token);
        		$this->setState('lastlogin_date', $user->lastlogin_date);
        	}
        	
        }
        return $this->errorCode==self::ERROR_NONE;
	}
	
	public function getId(){
        return $this->_id;
    }

    public function refreshLoginToken()
    {
        $token  = sha1(uniqid(mt_rand(), true));
        Users::model()->updateByPk($this->_id, array('login_token' => $token));
        $this->setState(self::LOGIN_TOKEN, $token);
    }
}