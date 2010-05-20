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

$kt = new Kontagent('tofoo.dyndns.org:8080', 'aaaa');

$uid = null;
if( isset($_GET['fb_sig_user']) ) $uid = $_GET['fb_sig_user'];


$session = $facebook->getSession();

//
// Track Install
//
if( !isset($_COOKIE[$kt->gen_kt_handled_installed_cookie_key(FB_ID)]) &&
    $uid != null )
{
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
            break;
        }
    }

    // kt_handle_installed is set to prevent further round
    // trip to facebook to get the fb cookies
    if( isset($_GET['fb_sig_added']) && $_GET['fb_sig_added'] == 1 )
        if( !headers_sent() ) 
            setcookie( $kt->gen_kt_handled_installed_cookie_key(FB_ID), 'done' ); 
}

//
// Track other messages
//
if(isset($_GET['kt_type']))
{
    switch($_GET['kt_type'])
    {
    case 'ins':
    {
        $kt->track_invite_sent();
        $no_kt_param_url = $kt->stripped_kt_args($kt->get_current_url());
        $facebook->redirect($no_kt_param_url); 
        break;
    }
    case 'inr':
    {
        $kt->track_invite_received($uid);
        $no_kt_param_url = $kt->stripped_kt_args($_SERVER['HTTP_REFERER']);
        $facebook->redirect($no_kt_param_url);
        break;
    }
    
    }// switch
}