<?php

App::uses('Component', 'Controller');
# App::uses('Curl', 'Component');

// #Path to CakePHP Vendor Base
$CAKE_VENDOR_BASE = str_replace('/CakePHP/Component', '', __DIR__) . DS;
// # Path to Google_Client
require_once $CAKE_VENDOR_BASE . 'google' . DS . 'google-api-php-client' . DS . 'src' . DS . "Google_Client.php";
// # Path to Google_Oauth2Service
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
            $this->setTokens($token);
            $this->setUserId($this->user['id']);
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
        try {
          return $this->oauth->userinfo->get();
        } catch (Google_ServiceException $e) {
            if ($e->getCode() == 401) {
                  // This user may have disabled the Glassware on MyGlass.
                  // Clean up the mess and attempt to re-auth.
                  $this->cleanUser();
                  if($api_mode):
                      return array('success'=>false, 'message'=>$e->getMessage());
                  else:
                       $controller->redirect($this->getAuthUrl());
                  endif;
                  // $controller->redirect($this->config['redirect_url']);
                  // echo $this->getAuthUrl();
                  // exit;
            } else {
                  // Let it go...
                  // throw $e;
                  if($api_mode):
                      return array('success'=>false, 'message'=>$e->getMessage());
                  else:
                      throw $e;
                  endif;
            }
        }
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
    
    private function setUserId($id) {
        $_SESSION[self::OAUTH_USER_INDEX]['id'] = $id;
    }
    
    function getUser() {
        if (isset($_SESSION[self::OAUTH_USER_INDEX])) {
          return $_SESSION[self::OAUTH_USER_INDEX];
        }
    }
    
    private function getUserId() {
        if(empty($_SESSION[self::OAUTH_USER_INDEX])):
            throw new Exception("No user ID found in session.");
        endif;
        
        return $_SESSION[self::OAUTH_USER_INDEX]['id'];
    }
    
    private function setTokens($token) {
        $_SESSION[self::OAUTH_TOKEN_INDEX] = $token;
    }
    
    public function cleanUser() {
        unset($_SESSION[self::OAUTH_USER_INDEX]);
        unset($_SESSION[self::OAUTH_TOKEN_INDEX]);
    }
    
    public function getTokens() {
        if(!empty($_SESSION[self::OAUTH_TOKEN_INDEX])):
            return $_SESSION[self::OAUTH_TOKEN_INDEX];
        endif;
    }
    
    
}
