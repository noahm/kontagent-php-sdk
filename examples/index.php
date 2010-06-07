<?php
require_once '../src/facebook.php';
require_once '../kt/php/kt_config.php';
require_once '../kt/php/kontagent.php';
require_once '../kt/php/kt_facebook.php';
require_once '../kt/php/kt_landing.php';


$canvas_url = "http://apps.facebook.com/lih_test_lowlevelnew/";
$canvas_callback_url = FB_CALLBACK_URL;

// Create our Application instance.
$facebook = new KtFacebook(array('appId'  => FB_ID,
                                 'secret' => FB_SECRET,
                                 'cookie' => true,
                                 )
                           );

// Create a kontagent instance
$kt = new Kontagent(KT_API_SERVER, KT_API_KEY, SEND_MSG_VIA_JS);
$session = $facebook->fbNativeAppRequireLogin(array('req_perms'=>'publish_stream,user_birthday,user_relationships',
                                                    'display'=>'popup')); //lihchen

// We may or may not have this data based on a $_GET or $_COOKIE based session.
//
// If we get a session here, it means we found a correctly signed session using
// the Application Secret only Facebook and the Application know. We dont know
// if it is still valid until we make an API call using the session. A session
// can become invalid if it has already expired (should not be getting the
// session back in this case) or if the user logged out of Facebook.

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
    switch($_POST["clicked_button"])
    {
    case "php stream":
    {
        $attachment = array(
            'name' => 'php old REST',
            'caption' => 'The Facebook REST SDK',
            'href'=> FB_CANVAS_URL,
            'description' => 'Post stream via the old rest API.',
            'media' => array(array('type' => 'image',
                                   'src'  => 'http://icanhascheezburger.files.wordpress.com/2009/03/funny-pictures-kitten-finished-his-milk-and-wants-a-cookie.jpg',
                                   'href' => FB_CANVAS_URL)
                             )
                            );
        $message = "Check out this great app! (php)";
        $action_links = array(array('text'=>'click me', 'href'=>FB_CANVAS_URL));
        $post_id = $facebook->api(array('method'=>'stream.publish',
                                        'message' => $message,
                                        'attachment' => $attachment,
                                        'action_links' => $action_links,
                                        'st1' => 'st111',
                                        'st2' => 'st222',
                                        'st3' => 'st333'
                                        ));
        break;
    }
    case "php revenue":
    {
        $kt->track_revenue($uid, 110);
        break;
    }
    case "php event":
    {
        $kt->track_event($uid, "test php event", 10, 2,
                         'st1Event', 'st2Event', 'st3Event');
        break;
    }
    case "php goal count":
    {
        $kt->track_goal_count($uid, 1, 10);
        break;
    }
    case "php multi goal count":
    {
        $kt->track_multiple_goal_counts($uid, array(1=>10, 2=>20, 3=>30));
        break;
    }
    
    }
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
    <div id="fb-root"></div>
    <script src="http://connect.facebook.net/en_US/all.js"></script>
    <script src="../kt/js/kontagent.js?v=32"></script>
    <script src="../kt/js/kt_facebook.js?v=32"></script>
          
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
         <input name="clicked_button" type="submit" value="php stream"/>
         <input name="clicked_button" type="button" value="js stream" onclick="test_js_stream()" />
         <input name="clicked_button" type="submit" value="php revenue"/>
         <input name="clicked_button" type="button" value="js revenue" onclick="test_js_revenue()"/>
         <input name="clicked_button" type="submit" value="php event"/>
         <input name="clicked_button" type="button" value="js event" onclick="test_js_event()"/>
         <input name="clicked_button" type="submit" value="php goal count"/>
         <input name="clicked_button" type="submit" value="php multi goal count"/>
         <input name="clicked_button" type="button" value="js goal count" onclick="test_js_goal_count()"/>
         <input name="clicked_button" type="button" value="js multi goal count" onclick="test_js_multi_goal_count()"/>
         <input name="clicked_button" type="button" value="js setcookie" onclick="test_js_data_setcookie()"/>
         <input name="clicked_button" type="button" value="js getcookie" onclick="test_js_data_getcookie()"/>
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
  if(window.SESSION)
  {
      FB.init({
            appId  : '117179248303858',
            xfbml  : true,  // parse XFBML
            session : SESSION
             });
  }else{
      FB.init({
            appId  : '117179248303858',
            xfbml  : true,  // parse XFBML
             });
  }


function test_js_data_getcookie(){
    FB.api(
        {
          method: 'data.getCookies',
          name : 'kt_test_cookie',
          uid  : FB.getSession().uid
        },
        function(response){
            console.log(response);//xxxx
        }
           );
}

function test_js_data_setcookie(){

    FB.api(
        {
          method: 'data.setCookie',
          name: 'kt_test_cookie',
          uid : FB.getSession().uid,
          value : 'testing cookie'
        },
        function(response){
            console.log(response); 
        }
           );
}

function test_js_stream(){
    FB.ui(
   {
     st1 : 'stream_st1',
     st2 : 'stream_st2',

     method: 'stream.publish',
     message: 'Check out this great app!!',
     action_links: [{ text: 'click me', href: '<?php echo FB_CANVAS_URL?>'}],
     attachment: {
       name: 'Connect',
       caption: 'The Facebook Connect JavaScript SDK',
       description: (
         'A small JavaScript library that allows you to harness ' +
         'the power of Facebook, bringing the user\'s identity, ' +
         'social graph and distribution power to your site.'
       ),
      href: '<?php echo FB_CANVAS_URL?>',
               media: [ {type : 'image',
                         src:  'http://icanhascheezburger.files.wordpress.com/2009/03/funny-pictures-kitten-finished-his-milk-and-wants-a-cookie.jpg',
                         href: '<?php echo FB_CANVAS_URL?>'},
                        {type:   'mp3',
                         src:    'http://ktsdk.kontagent.com:8080/kt_fb_testapp/examples/jaydiohead.mp3', 
                         title:  'optimism',
                         artist: 'jaydiohead',
                         album:  'jaydiohead' }]
               }
   }
/*    , */
/*    function(resp) */
/*    { */
/*        console.log("og cb");//xxx */
/*    } */
  );
}


function test_js_revenue(){
    kt.track_revenue(220);
}
function test_js_event(){
    kt.track_event("js event", 2, 10, "jsSt1", "jsSt2", "jsSt3");
}

function test_js_goal_count(){
    kt.track_goal_count(kt.get_session_uid(), 1, 4);
}
function test_js_multi_goal_count(){
    kt.track_multi_goal_counts(kt.get_session_uid(), {1:10, 2:20});
}

</script>
      