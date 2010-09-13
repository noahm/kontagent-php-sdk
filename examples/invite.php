<?php
require_once '../kt/php/kt_config.php';
require_once '../kt/php/kontagent.php';
$kt = new Kontagent(KT_API_SERVER, KT_API_KEY, SEND_MSG_VIA_JS);
$long_tracking_code = $kt->gen_long_tracking_code();

$st1 = 'st111'; $st2 = 'st222'; $st3 = 'st333'; 
$invite_post_link = $kt->gen_invite_post_link(FB_CALLBACK_URL,
                                              $long_tracking_code,
                                              $uid,
                                              "st111","st222","st333");
$invite_content_link = $kt->gen_invite_content_link(FB_CANVAS_URL,
                                                    $long_tracking_code,
                                                    'st111', 'st222', 'st333');

?>

<fb:request-form
     method='POST'
     action="<?php echo $invite_post_link?>"
     invite='true'
     target='_top'
     type='XFBML'
     content='Would you like to join my Smiley group? 
            <fb:req-choice url="<?php echo $invite_content_link?>" label="Yes" />'
     <fb:multi-friend-selector 
     actiontext="Invite your friends to join your Smiley group.">
</fb:request-form>
