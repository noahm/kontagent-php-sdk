<?php
/*
 * @copyright 2010 Kontagent
 * @link http://www.kontagent.com
 */

  //
  // Assumption: Whereever kt_landing.php is included,
  // facebook.php and kontagent.php have already
  // been included prior to the loading of this file.
  // 

$facebook = new KtFacebook(array('appId'  => FB_ID,
                                 'secret' => FB_SECRET,
                                 'cookie' => true,
                                 )
                           );

$kt = new Kontagent(KT_API_SERVER, KT_API_KEY, SEND_MSG_VIA_JS);


echo "<script>var KT_API_SERVER = '".KT_API_SERVER."';  var KT_API_KEY = '".KT_API_KEY."';</script>";


$uid = null;
$session = $facebook->getSession();
if($session){
    try{
        $uid = $session['uid'];
    } catch (FacebookApiException $e) {
        error_log($e);
    }
}

if(SEND_MSG_VIA_JS){
    echo "<script>var SEND_MSG_VIA_JS = true; var FB_ID='".FB_ID."'</script>";
    if($uid){
        echo "<script>var SESSION = ".json_encode($session).";</script>";
    }
}

if(KT_AUTO_PAGEVIEW_TRACKING){
    if($uid)
        echo "<img src='".$kt->gen_tracking_pageview_link($uid)."' width='0px' height='0px' style='display:none;'/>";
}

if($uid){
    //
    // Track Install
    //
    $browser_install_cookie_key = $kt->gen_kt_handled_installed_cookie_key(FB_ID, $uid);
    if( !isset($_COOKIE[$browser_install_cookie_key]) ){
        $fb_cookie_arry = $facebook->api(array('method' => 'data.getcookies',
                                               'name'=>'kt_just_installed',
                                               'uid' => $uid));
        $arry_size = sizeof($fb_cookie_arry);
        for($i = 0; $i < $arry_size; $i++)
        {
            $cookie = $fb_cookie_arry[$i];
            if( $cookie['name'] == 'kt_just_installed' &&
                $cookie['uid'] == $uid &&
                $cookie['value'] == 1)
            {
                $kt->track_install($uid);
                $server_output = $facebook->api(array('method' => 'data.setcookie',
                                                      'name' => 'kt_just_installed',
                                                      'uid' =>$uid,
                                                      'expires' => time()-345600));
                $kt_install_param_override = '0';
                break;
            }
        }

        // kt_handle_installed is set to prevent further round
        // trip to facebook to get the fb cookies
        if( $session ){
            if( !headers_sent() ) {
                setcookie( $browser_install_cookie_key, 'done' ); 
            }
        }
    }

    //
    //Acquire User Info
    //
    if(!SEND_MSG_VIA_JS){
        $capture_user_info_key = $kt->gen_kt_capture_user_info_key(FB_ID, $uid);
        if(!isset($_COOKIE[$capture_user_info_key]))
        {
            $user_info = $facebook->api('/me');
            $friends_info = $facebook->api('/me/friends');
            $kt->track_user_info($uid, $user_info, $friends_info);
            if( !headers_sent() ){
                setcookie( $capture_user_info_key, 'done', time()+1209600); // 2 weeks
            }
        }
    }
}
if(!isset($kt_install_param_override)) $kt_install_param_override = null;

//
// Track other messages
//

if(isset($_GET['kt_type']))
{
    switch($_GET['kt_type'])
    {
    case 'ins':
    {
        if(!$kt->get_send_msg_from_js()){
            $kt->track_invite_sent();
        }else{
            echo "<script>var kt_landing_str='".
                $kt->gen_tracking_invite_sent_url().
                "';</script>";
        }
        break;
    }
    case 'inr':
    {
        if(!$kt->get_send_msg_from_js()){
            $kt->track_invite_received($uid);
            // If it doesn't get rid of the the forward the kt_* parameters, except
            // for the kt_ut tag, after install, we'll get another inr message.
            $no_kt_param_url = $kt->stripped_kt_args($_SERVER['HTTP_REFERER']);
            $facebook->redirect($no_kt_param_url);
        }else{
            echo "<script>var kt_landing_str='".
                $kt->gen_tracking_invite_click_url($uid).
                "';</script>";
        }
        break;
    }
    case 'stream':
    {
        if(!$kt->get_send_msg_from_js()){
            $kt->track_stream_click($uid);
        }
        else
        {
            echo "<script>var kt_landing_str='".
                $kt->gen_tracking_stream_click_url($uid).
                "';</script>";
        }
        break;
    }
    case 'ad':
    case 'partner':
    {
        $short_tag = $kt->gen_short_tracking_code();
        if(!$kt->get_send_msg_from_js()){
            $kt->track_ucc_click($uid, $short_tag);
        }
        else{
            echo "<script>var kt_landing_str='".
                $kt->gen_tracking_ucc_click_url($uid, $short_tag).
                "';</script>";
        }
        break;
    }
    
    }// switch
}