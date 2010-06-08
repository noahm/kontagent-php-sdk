<?php
require_once '../src/facebook.php';
require_once '../kt/php/kt_config.php';
require_once '../kt/php/kontagent.php';
require_once '../kt/php/kt_facebook.php';
require_once '../kt/php/kt_landing.php';

?>
<div id="fb-root"></div>
<script src="http://connect.facebook.net/en_US/all.js"></script>
<script src="../kt/js/kontagent.js?v=36"></script>
<script src="../kt/js/kt_facebook.js?v=36"></script>

<input type="button" value="fb login" onclick="test_fb_login()"/>
<input type="button" value="track install" onclick="test_track_install()"/>
      
<script>
      FB.init({ appId  : '117179248303858',
          xfbml  : true,  // parse XFBML
            });

function test_fb_login()
{
    FB.login(function(response){
            if(response.session){
                // successfully logged in
                console.log("wtf");//xxx
            }else{
                // cancel login
                console.log("cancelled login");//xxx
            }
        });
}

function test_track_install()
{
    kt.track_install();
}

</script>
