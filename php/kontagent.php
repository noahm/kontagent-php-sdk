<?
/*
 * @copyright 2010 Kontagent
 * @link http://www.kontagent.com
 */
require_once 'kt_comm_layer.php';


class KontagentException extends Exception{}

class Kontagent
{
    private $m_kt_api_key;
    private $m_send_msg_from_js;
    private $m_kt_host;
    private $m_kt_comm_layer;
    
    private function add_subtype123(&$params,
                                    $st1=null, $st2=null, $st3=null)
    {
        if(isset($st1))
            $params['kt_st1'] = $st1;
        if(isset($st2))
        {
            if(!isset($st1))
                throw new KontagentException('In order to supply a st2 string , you must also supply a st1 string');
            $params['kt_st2'] = $st2;
        }
        if(isset($st3))
        {
            if(!isset($st1) || !isset($st2))
                throw new KontagentException('In order to supply a st3 string , you must also supply a st1 string and a st2 string');
            $params['kt_st3'] = $st3;
        }
    }

    public function get_send_msg_from_js()
    {
        return $this->m_send_msg_from_js;
    }
    
    public function gen_kt_handled_installed_cookie_key($fb_api_key, $uid)
    {
        return 'kt_handled_installed_'.$fb_api_key."_".$uid;
    }
        
    public function gen_long_tracking_code()
    {
        return substr(uniqid(rand()), -16);
    }
    
    public function gen_short_tracking_code()
    {
        $t=explode(" ",microtime());
        $a = $t[1];
        $b = round($t[0]*mt_rand(0,0xfffff));
        
        $c = mt_rand(0,0xfffffff);
        $tmp_binary = base_convert($c, 10, 2);
        $c = $c << (8 - strlen($tmp_binary));
      
        $r =  dechex($a ^ $b ^ $c);
        if (strlen($r) > 8)
        {
            // handle a 64 bit arch.
            $r = substr($r, -8);
        }

        if (strlen($r) < 8)
        {
            $num_trailing_zeros = 8 - strlen($r);
            for($i = 0; $i < $num_trailing_zeros; $i++)
            {
                $r.= dechex(rand(0, 15));
            }
        }
        return $r;
    }

    public function get_current_url()
    {
        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

    public function stripped_kt_args($url)
    {
        $parsed_url_arry = parse_url($url);
        $params = array();
        foreach($_GET as $arg => $val)
        {
            if(preg_match('/kt_*/',  $arg))
                if($arg != 'kt_ut' && $arg != 'kt_sut') 
                    continue;
            else
            {
                $params[$arg] = $val;
            }
        }

        $r_url = $parsed_url_arry['scheme']."://".$parsed_url_arry['host'];
        if( isset($parsed_url_arry['port']) )
        {
            $r_url .= ":";
            $r_url .= $parsed_url_arry['port'];
        }
        $r_url .= $parsed_url_arry['path'];
        
        if( sizeof($params) )
        {
            $r_url.='?'.http_build_query($params);
        }
        return $r_url; 
    }
    
   //test this?
    public function append_kt_query_str($original_url, $query_str)
    {
        $position = strpos($original_url, '?');
        
        /* There are no query params, just append the new one */
        if ($position === false) {
            return $original_url.'?'.$query_str;
        }
        
        /* Prefix the params with the reference parameter */
        $noParams                   = substr($original_url, 0, $position + 1);
        $params                     = substr($original_url, $position + 1);
        return $noParams.$query_str.'&'.$params;
    }

    public function __construct($kt_host, $kt_api_key, $send_msg_from_js=false)
    {
        $this->m_send_msg_from_js = $send_msg_from_js;
        $this->m_kt_api_key = $kt_api_key;
        $host_port_arry = split(':', $kt_host);
        $this->m_kt_host = $host_port_arry[0];
        if(sizeof($host_port_arry) == 2){
            $this->m_kt_port = $host_port_arry[1];
        }else{
            $this->m_kt_port = '80';
        }
        
        $this->m_kt_comm_layer = new KtCommLayer($this->m_kt_host, $this->m_kt_port, $this->m_kt_api_key);
    }
    
    public function gen_invite_post_link($post_link, $long_tracking_code,
                                         $sender_uid,
                                         $st1=null, $st2=null, $st3=null)
    {
        $params = array();
        $params['kt_type'] = 'ins';
        $params['kt_ut'] = $long_tracking_code;
        $params['kt_uid'] = $sender_uid;
        $this->add_subtype123($params, $st1, $st2, $st3);
        if(isset($_REQUEST['session'])){
            $param['session'] = json_decode(get_magic_quotes_gpc()
                                            ? stripslashes($_REQUEST['session'])
                                            : $_REQUEST['session'],
                                            true);
        }
        if(isset($_REQUEST['fb_sig_session_key'])){
            $params['fb_sig_session_key'] = $_REQUEST['fb_sig_session_key'];
        }

        $mod_url = $this->append_kt_query_str($post_link,
                                              http_build_query($params, '', '&'));
        return $mod_url;
    }


    public function gen_invite_content_link($content_link, $long_tracking_code, 
                                            $st1=null, $st2=null, $st3=null)
    {
        $params = array();
        $params['kt_type'] = 'inr';
        $params['kt_ut'] = $long_tracking_code;
        $this->add_subtype123($params, $st1, $st2, $st3);
        $mod_url = $this->append_kt_query_str($content_link,
                                              http_build_query($params, '', '&'));
        return $mod_url;
    }
    
    public function gen_tracking_install_url($uid)
    {
        $params = array('s' => $uid);
        $curr_url = $this->get_current_url();
        $parsed_items_arry = parse_url($curr_url);
        parse_str($parsed_items_arry['query'], $parsed_qs_arry);

        if( isset($parsed_qs_arry['kt_ut']) )
        {
            $params['u'] = $parsed_qs_arry['kt_ut'];
        }
        else if( isset($parsed_qs_arry['kt_sut']) )
        {
            $params['su'] = $parsed_qs_arry['kt_sut'];
        }
        return $this->m_kt_comm_layer->gen_tracking_url('v1', 'apa', $params);
    }
    public function track_install($uid)
    {
        $tracking_url = $this->gen_tracking_install_url($uid);
        $this->m_kt_comm_layer->api_call_method($tracking_url);
    }

    //
    // When fb forwards back the control back to the callback url after
    // invite sent, fb_sig_user is no where to be found. That's why we need
    // to have kt_uid in the invite post's query_string.
    //
    public function gen_tracking_invite_sent_url()
    {
        $params = array( 'u' => $_GET['kt_ut'] );
        if(isset($_GET['kt_uid'])) $params['s'] = $_GET['kt_uid'];
        if(isset($_GET['kt_st1'])) $params['st1'] = $_GET['kt_st1'];
        if(isset($_GET['kt_st2'])) $params['st2'] = $_GET['kt_st2'];
        if(isset($_GET['kt_st3'])) $params['st3'] = $_GET['kt_st3'];

        if(isset($_REQUEST['ids'])){
            $params['r'] = join(',' , $_REQUEST['ids']);
        }

        return $this->m_kt_comm_layer->gen_tracking_url('v1', 'ins', $params);
    }
    public function track_invite_sent()
    {
        $tracking_url = $this->gen_tracking_invite_sent_url();
        $this->m_kt_comm_layer->api_call_method($tracking_url);
    }

    // if recipient_uid is not available, pass in null.
    public function gen_tracking_invite_click_url($recipient_uid)
    {
        $installed = 0;
        if( isset($_REQUEST['fb_sig_added']) && $_REQUEST['fb_sig_added'] == 1 )
            $installed = 1;

        $params = array( 'u' => $_GET['kt_ut'],
                         'i' => $installed );
        if(isset($_GET['kt_st1'])) $params['st1'] = $_GET['kt_st1'];
        if(isset($_GET['kt_st2'])) $params['st2'] = $_GET['kt_st2'];
        if(isset($_GET['kt_st3'])) $params['st3'] = $_GET['kt_st3'];
        if(isset($recipient_uid))  $params['r']   = $recipient_uid;

        return $this->m_kt_comm_layer->gen_tracking_url('v1', 'inr', $params);
    }
    public function track_invite_received($recipient_uid)
    {
        $tracking_url = $this->gen_tracking_invite_click_url($recipient_uid);
        $this->m_kt_comm_layer->api_call_method($tracking_url);
    }
}