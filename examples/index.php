<?php
require_once '../src/facebook.php';
require_once '../kt/php/kt_config.php';
require_once '../kt/php/kontagent.php';
require_once '../kt/php/kt_facebook.php';
require_once '../kt/php/kt_landing.php';


$canvas_url = "http://apps.facebook.com/lih_test_lowlevelnew/";
$canvas_callback_url = FB_CALLBACK_URL;

// Create our Application instance.
$facebook = new KtFacebook(array('appId'  => '117179248303858',
                                 'secret' => FB_SECRET,
                                 'cookie' => true,
                                 )
                           );

print_r($_REQUEST);//xxx

// Create a kontagent instance
$kt = new Kontagent(KT_API_SERVER, KT_API_KEY, 'ffff');
$session = $facebook->fbNativeAppRequireLogin(); //lihchen

///////////// test ///////////// 
$access_token = $facebook->getAccessTokenFromSessionKey($_REQUEST['fb_sig_session_key']);
error_log("access_token>>>>>>>>>>>>>>".$access_token);//xxx
//$me_json = $facebook->api('/me', array("access_token"=>$access_token));
///////////// end:test ///////////// 

// We may or may not have this data based on a $_GET or $_COOKIE based session.
//
// If we get a session here, it means we found a correctly signed session using
// the Application Secret only Facebook and the Application know. We dont know
// if it is still valid until we make an API call using the session. A session
// can become invalid if it has already expired (should not be getting the
// session back in this case) or if the user logged out of Facebook.
//$session = $facebook->getSession();

$me = null;
// Session based API call.
$uid = 0;
if ($session) {
  try {
    $uid = $facebook->getUser();
    $me = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    error_log($e);
  }
}

// login or logout url will be needed depending on current user state.
if ($me) {
    $logoutUrl = $facebook->getLogoutUrl();
} else {
    $loginUrl = $facebook->getLoginUrl();
}


// This call will always work since we are fetching public data.
$naitik = $facebook->api('/naitik');

if(isset($_POST["clicked_button"])){
    error_log("clicked_button");//xxx
}

?>
<!doctype html>
<html>
  <head>
    <title>php-sdk</title>
    <style>
      body {
        font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
      }
      h1 a {
        text-decoration: none;
        color: #3b5998;
      }
      h1 a:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
    <script src="http://connect.facebook.net/en_US/all.js"></script>
    <script src="../kt/js/kontagent.js?v=4"></script>
          
    <h1><a href="">php-sdk</a></h1>
    <?php if ($me): ?>
    <a href="<?php echo $logoutUrl; ?>">
      <img src="http://static.ak.fbcdn.net/rsrc.php/z2Y31/hash/cxrz4k7j.gif">
    </a>
    <?php else: ?>
    <a href="<?php echo $loginUrl; ?>">
      <img src="http://static.ak.fbcdn.net/rsrc.php/zB6N8/hash/4li2k73z.gif">
    </a>
    <?php endif ?>

    <form method="POST" action="<?php echo $canvas_callback_url;?>">
         <input name="clicked_button" type="submit" value="dashboard.addNews" />
    </form>

    <h3>Session</h3>
    <?php if ($me): ?>
    <pre><?php print_r($session); ?></pre>

    <h3>You</h3>
    <img src="https://graph.facebook.com/<?php echo $uid; ?>/picture">
    <?php echo $me['name']; ?>

    <h3>Your User Object</h3>
    <pre><?php print_r($me); ?></pre>
<?php else: ?>
    <strong><em>You are not Connected.</em></strong>
    <?php endif ?>

<!--    <h3>Naitik</h3>
    <img src="https://graph.facebook.com/naitik/picture">
    <?php echo $naitik['name']; ?>-->

<?php
$long_tracking_code = $kt->gen_long_tracking_code();
$st1 = 'st111'; $st2 = 'st222'; $st3 = 'st333';
$invite_post_link = $kt->gen_invite_post_link($canvas_callback_url,
                                              $long_tracking_code,
                                              $uid,
                                              "st111","st222","st333");
$invite_content_link = $kt->gen_invite_content_link($canvas_url,
                                                    $long_tracking_code,
                                                    'st111', 'st222', 'st333');

?>
    
<fb:serverFbml>
<script type="text/fbml">
<fb:fbml>
    <fb:request-form
        method='POST'
        action='<?php echo $invite_post_link?>'
        invite='true'
        type='join my Smiley group'
        content='Would you like to join my Smiley group? 
            <fb:req-choice url="<?php echo $invite_content_link?>" label="Yes" />'
        <fb:multi-friend-selector 
            actiontext="Invite your friends to join your Smiley group.">
    </fb:request-form>
</fb:fbml>
</script>
</fb:serverFbml>
    
   </body>
</html>


  
<script>
  FB.init({
     appId  : '117179248303858',
     xfbml  : true  // parse XFBML
   });

    FB.Event.subscribe('auth.login', function(response) {
        // Reload the application in the logged-in state
            window.top.location = 'http://apps.facebook.com/lih_test_lowlevelnew/';
        });

/* FB.login(function(response) { */
/*   if (response.session) { */
/*     // user successfully logged in */
/*     alert(response.perms); */
/*   } else { */
/*     // user cancelled login */
/*     alert("not logged in !"); */
/*   } */
/* }); */

/*    FB.ui( */
/*    { */
/*      method: 'stream.publish', */
/*      message: 'Check out this great app! http://apps.facebook.com/{your_app}', */
/*    }, */

/*    function(resp) */
/*    { */
/*        if(resp && resp.post_id) */
/*        { */
/*            alert('Post was publish.'); */
/*        } */
/*        else */
/*        { */
/*            alert('Post was not publish.'); */
/*        } */
/*    } */
/*   ); */
</script>
