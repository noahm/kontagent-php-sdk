<?php



class KtFacebook extends Facebook
{
    public function __construct($config) {
        parent::__construct($config);
        $this->kt = null;
        try{
            /* If Kontagent is not included, it will still work*/
            $this->kt = new Kontagent(KT_API_SERVER, KT_API_KEY, SEND_MSG_VIA_JS);
        }catch(Exception $e){
            
        }
    }

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
            return $session;
        }
        
        if (isset($_SERVER['HTTP_REFERER']) && preg_match('/http:\/\/apps.facebook.com*/', $_SERVER['HTTP_REFERER'])) {
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
    protected function _restserver($params){
        $session = $this->getSession();
        if($session){
            if( isset($params['method']) && $params['method'] == 'stream.publish' ){
                $method_type = 'stream.publish';
                $uuid = $this->kt->gen_long_tracking_code();
                if(isset($params['st1']))
                    $st1 = $params['st1'];
                else
                    $st1 = null;
                if(isset($params['st2']))
                    $st2 = $params['st2'];
                else
                    $st2 = null;
                if(isset($params['st3']))
                    $st3 = $params['st3'];
                else
                    $st3 = null;

                // href
                if(isset($params['attachment']) && isset($params['attachment']['href'] )){
                    $params['attachment']['href'] = $this->kt->gen_stream_link($params['attachment']['href'],
                                                                               $uuid, $st1, $st2, $st3);
                    //media
                    if(isset($params['attachment']['media'])){
                        $media_list = &$params['attachment']['media'];
                        $len = sizeof($media_list);
                        for($i = 0; $i < $len; $i++){
                            $curr_media = &$media_list[$i];
                            if($curr_media['type'] == 'image'){
                                $curr_media['href'] = $this->kt->gen_stream_link($curr_media['href'], $uuid, $st1, $st2, $st3);
                            }else if($curr_media['type'] == 'mp3'){
                                $curr_media['mp3'] = $this->kt->gen_stream_link($curr_media['src'], $uuid, $st1, $st2, $st3);
                            }else if($curr_media[''] == 'flash'){
                                $curr_media['flash'] = $this->kt->gen_stream_link($curr_media['src'], $uuid, $st1, $st2, $st3);
                            }
                        }// for
                    }// if(isset($params['attachment']['media']))
                }

                // action_links
                if(isset($params['action_links'])){
                    $action_links_list = &$params['action_links'];
                    $action_links_list_len = sizeof($action_links_list);
                    for($i = 0; $i < $action_links_list_len; $i++){
                        $curr_action_link = &$action_links_list[$i];
                        $curr_action_link['href'] = $this->kt->gen_stream_link($curr_action_link['href'],
                                                                               $uuid, $st1, $st2, $st3);
                    }
                }
            }
        }// if($session){

        $r = parent::_restserver($params);
        
        if(isset($method_type))
        {
            switch($method_type){
            case 'stream.publish':
            {
                if(!$this->kt->get_send_msg_from_js()){
                    $this->kt->track_stream_send($session['uid'], $uuid, $st1, $st2, $st3);
                }else{
                    echo "<script>var kt_landing_str='".
                        $this->kt->gen_tracking_stream_send_url($session['uid'], $uuid, $st1, $st2, $st3).
                        "';</script>";
                }
                break;
            }
            }
        }
        return $r;
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

        if($this->kt){
            $currentUrl = $this->kt->stripped_kt_args($currentUrl);
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