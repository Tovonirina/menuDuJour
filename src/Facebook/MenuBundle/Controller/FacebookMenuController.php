<?php

namespace Facebook\MenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Facebook\Facebook;

/**
 * Controleur gérant les appels au GraphAPI de facebook
 */
class FacebookMenuController extends Controller {

    const app_id = "142914352759046";
    const app_secret = "3f6ceb59023bf6323c44dae75e2a00fe";
    const base_url = 'http://localhost:8000';
    
  
    public function getLoginUrl() {
        $fb_params = [
            "app_id" => self::app_id,
            "app_secret" => self::app_secret
        ];
        $fb = new Facebook($fb_params);
        
        $helper = $fb->getRedirectLoginHelper();
        $permissions = ['email', 'user_likes'];
        $loginUrl = $helper->getLoginUrl( self::base_url . '/app_dev.php/getToken', $permissions);
        return $loginUrl;
    }

    public function getTokenAction() {
        if(!session_id()){
            session_start();
        }
        $fb = new Facebook([
            "app_id" => self::app_id,
            "app_secret" => self::app_secret,
            'default_graph_version' => 'v2.5',
            //'default_access_token' => '{access-token}', // optional
        ]);

        // Use one of the helper classes to get a Facebook\Authentication\AccessToken entity.
        $helper = $fb->getRedirectLoginHelper();
        //   $helper = $fb->getJavaScriptHelper();
        //   $helper = $fb->getCanvasHelper();
        //   $helper = $fb->getPageTabHelper();

        //Get token
        try {
            $accessToken = $helper->getAccessToken();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
        }

        //Save token in a session variable
        if (isset($accessToken)) {
            $_SESSION['facebook_access_token'] = (string) $accessToken;
            return $this->redirect($this->generateUrl("facebook_menu_show"));
        }else{
            return $this->redirect($this->generateUrl("facebook_menu_homepage"));
        }
        
    }

}
