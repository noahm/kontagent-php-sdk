<?php

/*
 * @copyright 2010 Kontagent
 * @link http://www.kontagent.com
 */



class KtCommLayer
{
    private $m_host;
    private $m_port;
    private $m_api_key;
    private $m_server = "";
    private $m_socket = false;

    public function __construct($host, $port, $api_key) {
        $this->m_host = $host;
        $this->m_port = $port;
        $this->m_api_key = $api_key;
    }

    /**
     * Destructor.
     * 
     * Ensures that socket is closed.
     */
    public function __destruct(){
        if ($this->m_socket) {
            fclose($this->m_socket);
        }
    }

        /**
     * Decides on a server to use for subsequent requests.
     */
    private function select_server() {
        // If we are using Kontagent's primary servers then utilize server selection protocol.
        if ($this->m_host == "api.geo.kontagent.net") {
            $this->m_server = $this->select_ip_address($this->m_host, $this->m_port);
        } else {
            $this->m_server = $this->m_host . ":" . $this->m_port;
        }
    }

    private function select_ip_address($host, $port) {
        // First try all servers in geographically-closest datacenter
        $ip_list = gethostbynamel($host);
        $selected_ip = "";
        
        if ($ip_list != false) {
            shuffle($ip_list);
            
            foreach ($ip_list as $ip) {
                $socket = @stream_socket_client($ip.":".$port, $errno, $errstr, 0.5, STREAM_CLIENT_CONNECT);
                if ($socket) {
                    $this->m_socket = $socket;
                    $selected_ip = $ip;
                    break;
                }
            }
        }
        
        // Looks like entire datacenter is down, so try our luck with one of global IPs
        if ($selected_ip == "") {
            $global_ip_list = gethostbynamel("api.global.kontagent.net");
            shuffle($global_ip_list);
            
            foreach($global_ip_list as $global_ip) {
                $socket = @stream_socket_client($global_ip.":".$port, $errno, $errstr, 0.5, STREAM_CLIENT_CONNECT);
                
                if ($socket) {
                    $this->m_socket = $socket;
                    $selected_ip = $global_ip;
                    break;
                }
            }
        }
        return $selected_ip.":".$port;
    }

    public function gen_tracking_url($version, $msg_type, $params)
    {
        $params['ts'] = gmdate("Ymd.His");
        $url = 'http://'.$this->m_host.":".$this->m_port."/api/".$version."/".$this->m_api_key."/".$msg_type."/?".http_build_query($params,'','&');
        return $url;
    }
    
    public function api_call_method($tracking_url) {
        // We delayed server selection until first API call
        if ($this->m_server == "") {
            $this->select_server();
        }
        
        if ($this->m_server != "") {
            if (!$this->m_socket) {
                $this->m_socket = @stream_socket_client($this->m_server, $errno, $errstr, 0.5, STREAM_CLIENT_CONNECT);
            }
            
            if ($this->m_socket) {
                fwrite($this->m_socket, "GET $tracking_url HTTP/1.1\r\n");
                fwrite($this->m_socket, "Host: $this->m_server\r\n");
                fwrite($this->m_socket, "Content-type: application/x-www-form-urlencoded\r\n");
                fwrite($this->m_socket, "Accept: */*\r\n");
                fwrite($this->m_socket, "\r\n");
                fwrite($this->m_socket, "\r\n");
                
                fclose($this->m_socket);
                $this->m_socket = false;
            }
        }
    }
}