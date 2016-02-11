<?php

namespace Facebook\MenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Facebook\Facebook;


if(!session_id()){
    session_start();
}

/**
 * Controleur gérant les appels au GraphAPI de facebook
 */
class FacebookMenuController extends Controller {


    const base_url = 'http://localhost:8000';

    const fb_parameters = array(
        "app_id" => "142914352759046",
        "app_secret" => "3f6ceb59023bf6323c44dae75e2a00fe",
        "default_graph_version" => 'v2.5'
    );

    public function getLoginUrl() {
        
        $fb = new Facebook(self::fb_parameters);        
        $helper = $fb->getRedirectLoginHelper();
        $loginUrl = $helper->getLoginUrl( self::base_url . '/app_dev.php/getToken', ['email']);
        return $loginUrl;
    }

    public function getTokenAction() {        

        $fb = new Facebook(self::fb_parameters);

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

        //Save token in a session variable then redirect
        if (isset($accessToken)) {
            $_SESSION['facebook_access_token'] = (string) $accessToken;
            return $this->redirect($this->generateUrl("facebook_menu_show"));
        }else{
            return $this->redirect($this->generateUrl("facebook_menu_homepage"));
        }

    }

    public function getMenuList( $posts , $title_pattern , $msg_separator){
        $latest_menus = array();

        $day = array("dimanche","lundi","mardi","mercredi","jeudi","vendredi","samedi"); 
        $month = array("janvier", "fevrier", "mars", "avril", "mai", "juin", "juillet", "aout", "septembre", "octobre", "novembre", "decembre"); 
        $date = explode('|', date("w|d|n|Y"));
        $timestamp = time () ;
        $date = explode('|', date( "w|d|n|Y", $timestamp ));
        $today = $day[$date[0]] . ' ' . $date[1] . ' ' . $month[$date[2]-1] ;

        foreach( $posts as $key => $post){

            if (stripos($post->message, $title_pattern ) !== false) {
                $exploded_message = explode($msg_separator, $post->message, 2);
                $title = $exploded_message[0];
                $list_menu = nl2br( $exploded_message[1] );
                $list_menu = preg_replace('/<br \/>/', '', $list_menu, 1);

                $date_on_menu = $this->wd_remove_accents( trim( str_replace( $title_pattern , "", $title) ) );
                
                
                $wanted_fav_dish = array("Poulet à la crème", "Rougail saucisse");
                $raw_menu = $this->wd_remove_accents($list_menu);
                $founded_fav_dish = array();
                foreach( $wanted_fav_dish as $fav_dish){ 
                    $raw_fav_dish = $this->wd_remove_accents($fav_dish) ;                 
                    if (stripos($raw_menu, $raw_fav_dish) !== false){
                        array_push($founded_fav_dish, $fav_dish);
                    }
                }
                
                $content_array = [
                    "title" => $title,
                    "list_menu" => $list_menu,
                    "full_picture" => $post->full_picture,
                    "is_today_menu" => ($date_on_menu == $today),
                    "founded_fav_dish" => $founded_fav_dish
                ];
                array_push($latest_menus, $content_array);
            }
            if(count($latest_menus) >= 3){
                break;
            }
        }

        return $latest_menus;
    }

    public function getAllMenuContent(){
        $fb = new Facebook(self::fb_parameters);

        $accessToken = $_SESSION["facebook_access_token"];  
        
        $request1 = $fb->get('/lilotregal/posts?fields=message,full_picture', $accessToken);
        $rawMenu1 = json_decode($request1->getBody()) -> data;

        $request2 = $fb->get('/649823778452363/posts?fields=message,full_picture', $accessToken);
        $rawMenu2 = json_decode($request2->getBody()) -> data;         

        $all_menu=[
            "lilotregal" => $this -> getMenuList( $rawMenu1 , "Menu du" , ":"),
            "regalducircuit" => $this -> getMenuList( $rawMenu2 , "Repas du jour" , "\n")
        ];

        return $all_menu; 
    }

    // Remove accents [www.weirdog.com] 
    function wd_remove_accents($str, $charset = 'utf-8') {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

        return $str;
    }

}
