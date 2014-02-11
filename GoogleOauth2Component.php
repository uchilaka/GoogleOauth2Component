<?php

App::uses('Component', 'Controller');
App::uses('Curl', 'Component');
// declare unique app namespace APP_NAME

# Alternate Path to components - if you have components outside your /App/Controller/Component directory
$CAKE_VENDOR_BASE = str_replace('/CakePHP/Component', '', __DIR__) . DS;
# Path to Google_Client Class
require_once $CAKE_VENDOR_BASE . 'google' . DS . 'google-api-php-client' . DS . 'src' . DS . "Google_Client.php";
# Path to Google_Oauth2Service class
require_once $CAKE_VENDOR_BASE . 'google' . DS . 'google-api-php-client' . DS . 'src' . DS . "contrib" . DS . "Google_Oauth2Service.php";

class GoogleOauth2Component extends Component {
    //put your code here
    public $oauth;
    public $google;
    public $userinfo;
    const OAUTH_TOKEN_INDEX = "goa_tokens";
    const OAUTH_USER_INDEX = "goa_user";
    
    public function initialize(\Controller $controller) {
        parent::initialize($controller);
    }
    
    public function connect(\Controller $controller, $config, $scopes = array(
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/userinfo.profile'/*,
        'https://www.googleapis.com/auth/drive', 
        'https://www.googleapis.com/auth/drive.file'*/
    ), $api_mode=false) {
        $this->google = new Google_Client();
        $this->config = $config;
        $this->google->setClientId($config['client_id']);
        $this->google->setClientSecret($config['client_secret']);
        $this->google->setRedirectUri($config['redirect_url']);
        // find scope configuration
        $this->google->setScopes($scopes);
        $this->google->setUseObjects(true);
        
        if(!empty($_REQUEST['code'])):
            $this->authenticate($controller, $_REQUEST['code']);
            // if it makes it here
            $this->ready = true;
        else:    
            $this->oauth = new Google_Oauth2Service($this->google);
            if($this->getTokens()):
                $this->google->setAccessToken($this->getTokens());
                $userinfo = $this->verify_credentials($controller, $this->getTokens(), $api_mode);
                // if it makes it here
                if(!empty($userinfo)):
                    $this->ready = true;
                endif;
                return array('success'=>$this->ready, 'user'=>$userinfo);
            else:
                if($api_mode):
                    return array('success'=>false);
                else:
                    $controller->redirect($this->getAuthUrl());
                endif;
            endif;
        endif;
    }
    
    private function authenticate(\Controller $controller, $code) {
        // $this->service = new Google_DriveService($this->google);
        $this->oauth = new Google_Oauth2Service($this->google);
        $token = $this->google->authenticate($code);
        $this->google->setAccessToken($token);
        $this->userinfo = $this->verify_credentials($controller, $token);
        if(!empty($this->userinfo)):
            $this->setUser($this->user['id'], $token);
            $this->ready = true;
            // send to application
            $controller->redirect($this->config['redirect_url']);
        else:
            $controller->redirect($this->getAuthUrl());
        endif;
        
    }
    
    public function verify_credentials(\Controller $controller, $credentials, $api_mode=false) {
        // TODO: Use the oauth2.tokeninfo() method instead once it's
        //       exposed by the PHP client library
        $this->google->setAccessToken($credentials);
        // attempt to refresh 
        $tokens = json_decode($credentials, true);
        $now = time();
        $expired = 0;
        // get a new access token
        if(!empty($tokens['refresh_token'])):
            $now = time();
            $expired =$tokens['created']+$tokens['expires_in'];
            if($now<$expired):
                $this->google->refreshToken($tokens['refresh_token']);
            endif;
        endif;
        
        if($now>$expired):
            if($api_mode):
                return array('success'=>false, 'message'=>'Authentication failed');
            else:
                $controller->redirect($this->getAuthUrl());
            endif;
        endif;
        
        $user = $this->oauth->userinfo->get();
        if(empty($user)):
            if($api_mode):
                return array('success'=>false, 'message'=>'Authentication failed');
            else:
                $controller->redirect($this->getAuthUrl());
            endif;
        else:
            return $user;
        endif;
    }
    
    public function isReady() {
        return $this->ready;
    }
    
    function getAuthUrl() {
        try {
            $authUrl = $this->google->createAuthUrl();
            return $authUrl;
            //return array('success'=>true, 'authUrl'=>$authUrl);
        } catch (Exception $ex) {
            return false;
            // return array('success'=>false, 'message'=>$ex->getMessage());
        }
    }
    
    private function setUser($id, $token) {
        $_SESSION[APP_NAME . self::OAUTH_USER_INDEX] = array(
            'id'=>$id
        );
        $_SESSION[APP_NAME . self::OAUTH_USER_INDEX][APP_NAME . self::OAUTH_TOKEN_INDEX] = json_decode($token, true);
    }
    /*
    private function setUserId($id) {
        $_SESSION[self::OAUTH_USER_INDEX]['id'] = $id;
    }
    */
    function getUser() {
        if (isset($_SESSION[APP_NAME . self::OAUTH_USER_INDEX])) {
          return $_SESSION[APP_NAME . self::OAUTH_USER_INDEX];
        }
    }
    
    private function getUserId() {
        $user = $_SESSION[APP_NAME . self::OAUTH_USER_INDEX];
        if(!empty($user[APP_NAME . self::OAUTH_TOKEN_INDEX])):
            return $user[APP_NAME . self::OAUTH_TOKEN_INDEX];
        endif;
    }
    
    public function cleanUser() {
        unset($_SESSION[APP_NAME . self::OAUTH_USER_INDEX]);
    }
    
    public function getTokens() {
        $user = $this->getUser();
        if(!empty($user[APP_NAME . self::OAUTH_TOKEN_INDEX])):
            return json_encode($user[APP_NAME . self::OAUTH_TOKEN_INDEX]);
        endif;
    }
    
    
}
