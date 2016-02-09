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
    
  
    public function getLoginUrl() {
        $fb_params = [
            "app_id" => self::app_id,
            "app_secret" => self::app_secret
        ];
        $fb = new Facebook($fb_params);
        
        $helper = $fb->getRedirectLoginHelper();
        $permissions = ['email', 'user_likes'];
        $loginUrl = $helper->getLoginUrl('http://localhost:8000/app_dev.php/callbackFb', $permissions);
        return $loginUrl;
    }
    

}
