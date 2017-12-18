<?php 
class WebUser extends CWebUser {
    private $_model = null;
 
    function getRole() {
        if($user = $this->getModel()){
            return $user->role;
        }
    }
 
    private function getModel(){
        if (!$this->isGuest && $this->_model === null){
            $this->_model = Users::model()->findByPk($this->id, array('select' => 'role,passwd_token'));
        }
        return $this->_model;
    }

    public function login($identity, $duration = 0)
    {
        if($duration != 0){
            $identity->refreshLoginToken();
        }
        return parent::login($identity, $duration);
    }

    protected function beforeLogin($id,$states,$fromCookie)
    {
        //If the login is not cookie-based then there is no point to check
        if(!$fromCookie) {
            return true;
        }

        //The cookie isn't here, we refuse the login
        if(!isset($states[UserIdentity::LOGIN_TOKEN])){
            return false;
        }

        $user = Users::model()->findByPk($id, array('select' => 'login_token'));
        if(isset($user) && $states[UserIdentity::LOGIN_TOKEN] == $user->login_token) {
            return true;
        }
        return false;
    }

    protected function afterLogin($fromCookie)
    {
        parent::afterLogin($fromCookie);
        Users::model()->updateByPk($this->id, array('lastlogin_date' => date('Y-m-d H:i:s')));
        return true;
    }

    protected function updateAuthStatus()
    {
        parent::updateAuthStatus();
        if(!$this->getIsGuest()){
            if($this->getModel()->passwd_token !== $this->getState(UserIdentity::PASSWD_TOKEN)){
                $this->logout();
                Yii::app()->controller->redirect('/');
            }
        }
    }

    public function cookieUpdateStates()
    {
        if($this->allowAutoLogin) {
            $app = Yii::app();
            $request = $app->getRequest();
            $cookies = $request->getCookies();
            $cookie = $cookies->itemAt($this->getStateKeyPrefix());
            if ($cookie && !empty($cookie->value) && ($data = $app->getSecurityManager()->validateData($cookie->value)) !== false) {
                $data = @unserialize($data);
                if (is_array($data) && isset($data[0], $data[1], $data[2], $data[3])) {
                    $data[3] = $this->saveIdentityStates();
                    $cookie->value = $app->getSecurityManager()->hashData(serialize($data));
                    $cookies->add($cookie->name, $cookie);
                }
            }
        }
    }
}