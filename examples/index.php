<?php
require_once '../src/facebook.php';
require_once '../kt/php/kt_config.php';
require_once '../kt/php/kontagent.php';
require_once '../kt/php/kt_facebook.php';
require_once '../kt/php/kt_landing.php';

$canvas_url = FB_CANVAS_URL;
$canvas_callback_url = FB_CALLBACK_URL;

// Create our Application instance.
$facebook = new KtFacebook(array('appId'  => FB_ID,
                                 'secret' => FB_SECRET,
                                 'cookie' => true,
                                 )
                           );

// Create a kontagent instance
$kt = new Kontagent(KT_API_SERVER, KT_API_KEY, SEND_MSG_VIA_JS);

//$session = $facebook->fbNativeAppRequireLogin(array('req_perms'=>'publish_stream,user_birthday,user_relationships',
//                                                    'display'=>'popup')); //lihchen

$session = $facebook->fbNativeAppRequireLogin(array('canvas'=>1, 'fbconnect'=>0,
                                                    'req_perms'=>'publish_stream,user_birthday,user_relationships')); //lihchen


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
        $kt->track_revenue($uid, 11110, "advertisement", "st111php", "st222php", "st3333php");
        break;
    }
    case "php advertisement revenue":
    {
        $kt->track_advertisement_revenue($uid, 1110, "st1", "st22", "st333");
        break;
    }
    case "php credits revenue":
    {
        $kt->track_credits_revenue($uid, 1110, "st1", "st22", "st333");
        break;
    }
    case "php direct revenue":
    {
        $kt->track_direct_revenue($uid, 1120, "st1", "st22", "st333");
        break;
    }
    case "php indirect revenue":
    {
        $kt->track_indirect_revenue($uid, 1130, "st1", "st22", "st333");
        break;
    }
    case "php other revenue":
    {
        $kt->track_other_revenue($uid, 1130, "st1", "st22", "st333");
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
    <script src="http://connect.facebook.net/en_US/all.js?v=44"></script>

    <script>CONTROL_KT_RUN = true;</script>
    <script src="../kt/js/kontagent.js?v=44"></script>
    <script src="../kt/js/kt_facebook.js?v=44"></script>
    <script>
          kt.post_invite_click_cb = function(data){
          }  
          kt.run();
    </script>
          
          
    <fb:like ref="helloworld" font="arial"></fb:like>

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
         <h2>FB Dialog</h2> 
         <input name="clicked_button" type="button" value="FB Feed Dialog" onclick="test_fb_feed_dialog()"/>
         <input name="clicked_button" type="button" value="FB Request Dialog" onclick="test_fb_request_dialog_many()"/>          
         <input name="clicked_button" type="button" value="FB Request Content" onclick="test_fb_request_content()"/>
         <input name="clicked_button" type="button" value="Delete FB Request Content" onclick="test_fb_delete_request_content()"/>
         <input name="clicked_button" type="button" value="FB Oauth" onclick="test_fb_oauth()"/>
          
         <h2>Other Stuff</h2> 
         <input name="clicked_button" type="submit" value="dashboard.addNews" />
         <input name="clicked_button" type="submit" value="php stream"/>
         <input name="clicked_button" type="button" value="js stream(FB.ui)" onclick="test_js_ui_stream()" id="test_btn"/>
         <input name="clicked_button" type="button" value="js stream(FB.api)" onclick="test_js_api_stream()" id="test_btn"/>
         <input name="clicked_button" type="button" value="js invite" onclick="test_js_invite()" />
         <input name="clicked_button" type="submit" value="php revenue"/>
         <input name="clicked_button" type="submit" value="php advertisement revenue"/>
         <input name="clicked_button" type="submit" value="php credits revenue"/>
         <input name="clicked_button" type="submit" value="php direct revenue"/>
         <input name="clicked_button" type="submit" value="php indirect revenue"/>
         <input name="clicked_button" type="submit" value="php other revenue"/>
          
         <input name="clicked_button" type="button" value="js revenue" onclick="test_js_revenue()"/>
         <input name="clicked_button" type="button" value="js advertisement revenue" onclick="test_js_advertisement_revenue()"/>
         <input name="clicked_button" type="button" value="js credits revenue" onclick="test_js_credits_revenue()"/>
         <input name="clicked_button" type="button" value="js direct revenue" onclick="test_js_direct_revenue()"/>
         <input name="clicked_button" type="button" value="js indirect revenue" onclick="test_js_indirect_revenue()"/>
         <input name="clicked_button" type="button" value="js other revenue" onclick="test_js_other_revenue()"/>
          
         <input name="clicked_button" type="submit" value="php event"/>
         <input name="clicked_button" type="button" value="js event" onclick="test_js_event()"/>
         <input name="clicked_button" type="submit" value="php goal count"/>
         <input name="clicked_button" type="submit" value="php multi goal count"/>
         <input name="clicked_button" type="button" value="js goal count" onclick="test_js_goal_count()"/>
         <input name="clicked_button" type="button" value="js multi goal count" onclick="test_js_multi_goal_count()"/>
         <input name="clicked_button" type="button" value="js setcookie" onclick="test_js_data_setcookie()"/>
         <input name="clicked_button" type="button" value="js getcookie" onclick="test_js_data_getcookie()"/>
         </form>
    <a href="#" onclick="goto_invite_page()">Invite</a>
          
    <h3>Session</h3>
    <?php if ($me): ?>
    <pre><?php print_r($session); ?></pre>
    <pre><?php print_r($_GET); ?></pre>
    
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

echo "<p>invite_content_link: ".$invite_content_link."</p>";
echo "<p>invite_post_link   : ".$invite_post_link."</p>";
echo print_r($facebook->getSignedRequest(),1);
?>

<fb:serverFbml>
<script type="text/fbml">
<fb:fbml>
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
</fb:fbml>
</script>
</fb:serverFbml>

   </body>
</html>


  
<script>


  function goto_invite_page(){
    window.parent.location.href = "<?php echo $canvas_url;?>invite.php?fb_force_mode=fbml";
  }

  if(window.SESSION)
  {
      FB.init({
            appId  : '143737522328410',
            xfbml  : true,  // parse XFBML
            session : SESSION
             });
  }else{
      FB.init({
            appId  : '143737522328410',
            xfbml  : true,  // parse XFBML
             });
  }


FB.Event.subscribe('edge.create', function(href, widget){
        var qs = href.split('?')[1];
        var key_value_pair = qs.split('&')
        for(var i = 0; i < key_value_pair.length; i++){
            var tmp = key_value_pair[i].split('=');
            var key = tmp[0];
            var val = tmp[1]
            if(key_value_pair[i].split('=')[0] == 'signed_request')
                console.log(val);
        }
    });

function test_js_data_getcookie(){
    FB.api(
        {
          method: 'data.getCookies',
          name : 'kt_test_cookie',
          uid  : FB.getSession().uid
        },
        function(response){
            if (!response || response.error) {
                alert('Error occured');
            } else {
                alert('Post ID: ' + response.id);
            }
        });
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

function test_fb_feed_dialog(){
  // http://developers.facebook.com/docs/reference/javascript/FB.ui/
  FB.ui(
    {
      method: 'feed',
      name: 'KT Facebook Dialogs',
      link: 'http://apps.facebook.com/kontagent_php/',
      picture: 'http://fbrell.com/f8.jpg',
      caption: 'Caption Goes Here',
      description: 'Kontagent integrates with the new facebook dialogs seamlessly!!',
      message: 'Kontagent Facebook Dialogs are easy'
     },
    function(response) {
      if (response && response.post_id) {
	alert('Post was published.');
      } else {
	alert('Post was not published.');
      }
    }
  );
}

function test_fb_request_dialog_many(){
    FB.ui(
        {
          method: 'apprequests', 
                message: 'You should learn more about this awesome game.', 
                data: {'data': 'tracking information for the user'},
                st1: 'st111',
                st2: 'st22',
                st3: 'st333'
                },
        function(response){

        }
          );  
}

function test_fb_request_content(){
  FB.api(  
    '/1879529905761',
    function(response){
            debugger;//xxx
        console.log(response);//xxx
    }
  );
}

function test_fb_delete_request_content(){
  var request_ids=1858020888362; // get rid of the hardcoding
  FB.api(
      '/1858020888362','delete'
         );
}

function test_fb_oauth(){
  FB.ui(
    {
      method: 'oauth',
      /* client_id: '143737522328410', */
      /* scope: 'email, publish_stream, offline_access, user_checkins', */
      /* redirect_uri: 'http://apps.facebook.com/kontagent_php/' */
    },
    function (response) {
      if (response && response.post_id) {
	alert('success');
      } else {
	alert('failure');
      }
    }
  );
}

function test_js_ui_stream(){
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

function test_js_api_stream(){
    FB.api('me/feed',
           'post',
           {message : "hello world",
            link    : 'http://apps.facebook.com/kontagent_php/',
            st1     : "st1_api",
            st2     : "st2_api",
            st3     : "st3_api",
            picture : 'http://icanhascheezburger.files.wordpress.com/2009/03/funny-pictures-kitten-finished-his-milk-and-wants-a-cookie.jpg',
            caption : "yada",
            name    : 'click me!'},
           function(resp){
               alert("got a resp back");//xxx
           });
}

function test_js_invite(){
    var js_invite_content_link = "<?php echo $invite_content_link?>";
    var js_invite_post_link = "<?php echo $invite_post_link?>";
    FB.ui(
        {
          method : 'fbml.dialog',
          fbml   : '<fb:fbml><fb:request-form action="' + js_invite_post_link +'" method="POST" invite="true" type="Vroom" content="You\'re invited to voice chat - check out Vroom. <fb:req-choice url=\'' + js_invite_content_link +'\' label=\'Add Vroom\' /> " > <fb:multi-friend-selector showborder="false" actiontext="Invite your friends to use Vroom."></fb:request-form></fb:fbml>', 
          width  : 800, 
          height : 100 },

        function(response){
            if (response && response.post_id) { 
                alert('Post was published.'); 
            } else { 
                alert('Post was not published.'); 
            } 
        }
          );
    
}


function test_js_revenue(){
    kt.track_revenue(220, "advertisement", "st111", "st222", "st3333");
}
function test_js_advertisement_revenue(){
    kt.track_advertisement_revenue(220, "st111", "st222", "st3333");
}
function test_js_credits_revenue(){
    kt.track_credits_revenue(220, "st111", "st222", "st3333");
}
function test_js_direct_revenue(){
    kt.track_direct_revenue(220, "st111", "st222", "st3333");
}
function test_js_indirect_revenue(){
    kt.track_indirect_revenue(220, "st111", "st222", "st3333");
}
function test_js_other_revenue(){
    kt.track_other_revenue(220, "st111", "st222", "st333");
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
      
