<?php
require_once '../../src/facebook.php'; // replace this with YOUR facebook.php path
require_once 'kt_config.php';
require_once 'kontagent.php';
require_once 'kt_facebook.php';

// http://bugs.developers.facebook.com/show_bug.cgi?id=10567
$facebook = new KtFacebook(array('appId'  => FB_ID,
                                 'secret' => FB_SECRET,
                                 'cookie' => true,
                                 )
                           );
$signed_req = $facebook->getSignedRequest();

$kt = new Kontagent(KT_API_SERVER, KT_API_KEY, SEND_MSG_VIA_JS);
$kt->track_uninstall($signed_req['user_id']); 