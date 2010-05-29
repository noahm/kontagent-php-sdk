<?php
require_once 'kt_config.php';

// Grab the fb session object and validate it manually to avoid dependence on facebook.php
function validateSessionObject($session) {
    // make sure some essential fields exist
    if (is_array($session) &&
        isset($session['uid']) &&
        isset($session['session_key']) &&
        isset($session['secret']) &&
        isset($session['access_token']) &&
        isset($session['sig'])) {
        // validate the signature
        $session_without_sig = $session;
        unset($session_without_sig['sig']);
        $expected_sig = generateSignature( $session_without_sig,
                                           FB_SECRET );
        if ($session['sig'] != $expected_sig) {
            // disable error log if we are running in a CLI environment
            // @codeCoverageIgnoreStart
            if (php_sapi_name() != 'cli') {
                error_log('Got invalid session signature in cookie.');
            }
            // @codeCoverageIgnoreEnd
            $session = null;
        }
        // check expiry time
    } else {
        $session = null;
    }
    return $session;
}

function generateSignature($params, $secret) {
    // work with sorted data
    ksort($params);

    // generate the base string
    $base_string = '';
    foreach($params as $key => $value) {
        $base_string .= $key . '=' . $value;
    }
    $base_string .= $secret;

    return md5($base_string);
}


if (isset($_REQUEST['session'])) {
    $session = json_decode(
        get_magic_quotes_gpc()
        ? stripslashes($_REQUEST['session'])
        : $_REQUEST['session'],
        true);
    $session = validateSessionObject($session);
}

if($session)
{
    //
    // Set a facebook cookie to give the final destination url a
    // hint that an authorization just occurred.
    //
    $ch = curl_init();
    $data = array('access_token'=>$session['access_token'],
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