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
        "app_id" => "140784586305356",
        "app_secret" => "dcc09371e6752615cbaeea2e01cbfa45",
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

        //Get local date
        $day = array("dimanche","lundi","mardi","mercredi","jeudi","vendredi","samedi"); 
        $month = array("janvier", "fevrier", "mars", "avril", "mai", "juin", "juillet", "aout", "septembre", "octobre", "novembre", "decembre"); 
        $date = explode('|', date("w|d|n|Y"));
        $timestamp = time () ;
        $date = explode('|', date( "w|d|n|Y", $timestamp ));
        $today = $day[$date[0]] . ' ' . $date[1] . ' ' . $month[$date[2]-1] ;

        foreach( $posts as $key => $post){

            if (stripos($post->message, $title_pattern ) !== false) {
                //Get title and list of dish
                $message_array = explode($msg_separator, $post->message, 2);
                $title = $message_array[0];
                $list_menu = nl2br( $message_array[1] );
                $list_menu = preg_replace('/<br \/>/', '', $list_menu, 1);

                //Check if it's a today menu
                $date_on_menu = $this->wd_remove_accents( trim( str_replace( $title_pattern , "", $title) ) );
                $is_today_menu = false;
                if (stripos( $date_on_menu ,  $today ) !== false){
                      $is_today_menu = true;
                }                
                
                //Search fav dish
                $wanted_fav_dish = array("Poulet à la crème", "Rougail saucisse");
                $raw_menu = $this->wd_remove_accents($list_menu);
                $founded_fav_dish = array();
                foreach( $wanted_fav_dish as $fav_dish){ 
                    $raw_fav_dish = $this->wd_remove_accents($fav_dish) ;                 
                    if (stripos($raw_menu, $raw_fav_dish) !== false){
                        array_push($founded_fav_dish, $fav_dish);
                    }
                }
                
                //Add content menu in array             
                $content_array = [
                    "title" => $title,
                    "list_menu" => $list_menu,
                    "full_picture" => $post->full_picture,
                    "is_today_menu" => $is_today_menu,
                    "founded_fav_dish" => $founded_fav_dish
                ];
                array_push($latest_menus, $content_array);

                //Check number of menu stored
                if(count($latest_menus) >= 3){
                    break;
                }
            }
            
        }

        return $latest_menus;
    }

    public function getAllMenuContent(){
        $fb = new Facebook(self::fb_parameters);
        $accessToken = $_SESSION["facebook_access_token"];  
        
        //Get raw  L'lilôt Régal menu
        $request1 = $fb->get('/lilotregal/posts?fields=message,full_picture', $accessToken);
        $rawMenu1 = json_decode($request1->getBody()) -> data;

        //Get raw  Le régal du circuit menu
        $request2 = $fb->get('/649823778452363/posts?fields=message,full_picture', $accessToken);
        $rawMenu2 = json_decode($request2->getBody()) -> data; 

        
        $lilotregal = [
            "resto_name" => " L'lilôt Régal ",
            "content" => $this -> getMenuList( $rawMenu1 , "Menu du" , ":")
        ];

        $regalducircuit = [
            "resto_name" => " Le régal du circuit ",
            "content" => $this -> getMenuList( $rawMenu2 , "Repas du jour" , "\n")
        ];       

        //Return final menu content
        $all_menu=[$lilotregal , $regalducircuit ];
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
