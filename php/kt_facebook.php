<?php



class KtFacebook extends Facebook
{

    /**
     * Session is not available. However, we can still get the access_token
     * by using the old session_key, client_key. If it has already converted,
     * don't do the conversion again to save us the round trip time. 
     */
    protected $tokenSessionLoaded = false;

    /**
     *
     *
     */
    public function fbNativeAppRequireLogin($params=array())
    {
        $session = $this->getSession();
        if($session){
            return $session['uid'];
        }

        $http_referer = $_SERVER['HTTP_REFERER'];
        if(preg_match('/http:\/\/apps.facebook.com*/', $http_referer))
        {
            $this->redirect($this->getLoginUrl($params, false));
        }
        else
        {
            $this->redirect($this->getLoginUrl($params));
        }
    }

    //
    // Overridden
    //
    public function getSession()
    {
        $session = parent::getSession();
      
        if(!$session && isset($_REQUEST['fb_sig_session_key']))
        {
            if(!$this->tokenSessionLoaded){
                $oauth_struct = $this->getAccessTokenFromSessionKey($_REQUEST['fb_sig_session_key']);

                if(!isset($_REQUEST['fb_sig_user'])){
                    // After the initial invite is clicked. FB forwards to a page where a user can further invite
                    // more friends via email. When a skip button is clicked. fb_sig_user was not sent back.
                    // TODO: the access token returned by getAccessTokenFromSessionKey is incorrect.
                    $me_json = $this->api('/me', array("access_token"=>$oauth_struct[0]->access_token));
                    $uid = $me_json['id'];
                }else{
                    $uid = $_REQUEST['fb_sig_user'];
                }
                  
                $session = array('access_token' => $oauth_struct[0]->access_token,
                                 'session_key' => $_REQUEST['fb_sig_session_key'],
                                 'expires'=> $oauth_struct[0]->expires,
                                 'uid' => $uid);
                $this->session = $session;
                $this->tokenSessionLoaded = true;
            }else{
                $session = $this->session;
            }
        }
        return $session;
    }

    //
    // Overridden
    //
    public function getLoginUrl($params=array(), $forward_to_current_url=true)
    {
        if($forward_to_current_url)
        {
            $currentUrl = $this->getCurrentUrl();
        }
        else
        {
            $currentUrl = $_SERVER['HTTP_REFERER'];
        }

        try{
            /* If Kontagent is not included, it will still work*/
            $kt = new Kontagent(KT_API_SERVER, KT_API_KEY, SEND_MSG_VIA_JS);
            $currentUrl = $kt->stripped_kt_args($currentUrl);
        }catch(Exception $e){
          
        }
      
        return $this->getUrl(
            'www',
            'login.php',
            array_merge(array(
                            'api_key'         => $this->getAppId(),
                            'cancel_url'      => $currentUrl,
                            'display'         => 'page',
                            'fbconnect'       => 1,
                            'next'            => $currentUrl,
                            'return_session'  => 1,
                            'session_version' => 3,
                            'v'               => '1.0',
                              ), $params)
                             );
    }

    public function redirect($url)
    {
        if(!SEND_MSG_VIA_JS){
            echo "<script type=\"text/javascript\">\ntop.location.href = \"$url\";\n</script>";
            exit;
        }else{
            echo "<script>var kt_redirect='".$url."'</script>";
        }
    }


    public function getAccessTokenFromSessionKey($session_key)
    {
        $access_token = null;
        $ch = curl_init();
        $data = array('type' => 'client_cred',
                      'client_id'=>$this->getAppId(),
                      'client_secret'=>$this->getApiSecret(),
                      'sessions'=>$session_key,
                      );
        curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/oauth/exchange_sessions');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $server_output = curl_exec($ch);
        curl_close($ch);
        $r = json_decode($server_output);
        return $r;
    }
}