<?php
require_once '../kt/php/kt_config.php';

if($_POST['fb_sig_added'] == 1)
{
    $ch = curl_init();
    $data = array('type'=>'client_cred',
                  'client_id'=>$_POST['fb_sig_app_id'],
                  'client_secret'=>FB_SECRET,
                  'sessions'=>$_POST['fb_sig_session_key']
                  );
    curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/oauth/access_token');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $server_output = curl_exec($ch);
    curl_close($ch);
    $tmp_arry = split("=", $server_output);
    $access_token = $tmp_arry[1];
    
    //
    // Set a facebook cookie to give the final destination url a
    // hint that an authorization just occurred.
    //
    $ch = curl_init();
    $data = array('access_token'=>$access_token,
                  'name'=>'kt_just_installed',
                  'value' => '1',
                  'uid' => $_POST['fb_sig_user']);
    curl_setopt($ch, CURLOPT_URL, 'https://api.facebook.com/method/data.setCookie');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);    
    $server_output = curl_exec($ch);
    curl_close($ch);
}