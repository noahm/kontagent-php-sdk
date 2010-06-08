<?php

require_once '../php/kontagent.php';
require_once '../php/kt_comm_layer.php';


class KontagentTest extends PHPUnit_Framework_TestCase
{
    //const KT_HOST = 'http://test-server.kontagent.com';
    const KT_HOST = 'tofoo.dyndns.org:8080';
    const KT_API_KEY = 'abcabc';
    const APP_URL = "http://apps.facebook.com/myapp/"; 
    
    public function testConstruction()
    {
        $kt = new Kontagent(self::KT_HOST, self::KT_API_KEY, false);
        
    }

    public function testAppendKtQueryStr()
    {
        $original_url = self::APP_URL.'?foo=bar';
        $query_str = "kt_ut=1234&kt_type=ins";
        $kt = new Kontagent(self::KT_HOST, self::KT_API_KEY, false);

        $mod_url = $kt->append_kt_query_str($original_url, $query_str);
        $url_items = parse_url($mod_url);
        parse_str($url_items['query'], $query_items);
        $this->assertEquals( $query_items['foo'], 'bar',
                             'missing foo param');
        $this->assertEquals( $query_items['kt_ut'], '1234',
                             'missing kt_ut');
        $this->assertEquals( $query_items['kt_type'], 'ins',
                             'missing kt_type');

        $original_url = self::APP_URL;
        $mod_url = $kt->append_kt_query_str($original_url, $query_str);
        $url_items = parse_url($mod_url);
        parse_str($url_items['query'], $query_items);
        $this->assertEquals( $query_items['kt_ut'], '1234',
                             'missing kt_ut');
        $this->assertEquals( $query_items['kt_type'], 'ins',
                             'missing kt_type');
    }

    public function testGenLongTrackingCode()
    {
        $kt = new Kontagent(self::KT_HOST, self::KT_API_KEY, false);
        $long_tracking_code = $kt->gen_long_tracking_code();
        $this->assertEquals(strlen($long_tracking_code), 16,
                            'A long tracking code must be 16 character long');
    }

    public function testGenShortTrackingCode()
    {
        $kt = new Kontagent(self::KT_HOST, self::KT_API_KEY, false);
        $short_tracking_code = $kt->gen_short_tracking_code();
        global $kt_short_tag;
        $kt_short_tag = null; // So it doesn't screw up other tests
        $this->assertEquals(strlen($short_tracking_code), 8,
                            'A short tracking code must to be 8 character long');
    }
    
    public function testGenInvitePostLink()
    {
        $kt = new Kontagent(self::KT_HOST, self::KT_API_KEY, false);
        $long_tracking_code = $kt->gen_long_tracking_code();
        $uid = '101010';
        $st1_str = 'st1 string';
        $st2_str = 'st2 string';
        $st3_str = 'st3 string';
        
        $invite_post_link = $kt->gen_invite_post_link(self::APP_URL,
                                                      $long_tracking_code,
                                                      $uid,
                                                      $st1_str, $st2_str, $st3_str);

        $url_items = parse_url($invite_post_link);
        parse_str($url_items['query'], $query_items);
        $this->assertEquals($query_items['kt_type'], 'ins',
                             'wrong kt_type for ins');
        $this->assertEquals(strlen($query_items['kt_ut']), 16,
                            'bad kt_ut for ins');
        $this->assertNotNull($query_items['kt_uid'],
                             'kt_uid is not found for ins');
        $this->assertEquals($query_items['kt_st1'], $st1_str,
                            'wrong kt_st1 value for ins');
        $this->assertEquals($query_items['kt_st2'], $st2_str,
                            'wrong kt_st2 value for ins');
        $this->assertEquals($query_items['kt_st3'], $st3_str,
                            'wrong kt_st3 value for ins');
                            
        
        try{
            $invite_post_link = $kt->gen_invite_post_link(self::APP_URL,
                                                          $long_tracking_code,
                                                          $uid,
                                                          null,
                                                          'st2 string',
                                                          'st3 string'
                                                          );
        }
        catch(KontagentException $e)
        {
            $this->assertEquals($e->getMessage(),
                                'In order to supply a st2 string , you must also supply a st1 string',
                                'Unexpected exception msg when a st2 string is supplied without supplying a st1 string');
        }

        try{
            $invite_post_link = $kt->gen_invite_post_link(self::APP_URL,
                                                          $long_tracking_code,
                                                          $uid,
                                                          'st1 string',
                                                          null,
                                                          'st3 string'
                                                          );
        }
        catch(KontagentException $e)
        {
            $this->assertEquals($e->getMessage(),
                                'In order to supply a st3 string , you must also supply a st1 string and a st2 string',
                                'Unexpected exception msg when a st3 string is supplied without supplying a st2 string');
        }
    }

    public function testGenInviteContentLink()
    {
        $kt = new Kontagent(self::KT_HOST, self::KT_API_KEY, false);
        $long_tracking_code = $kt->gen_long_tracking_code();
        $st1_str = 'st1 string';
        $st2_str = 'st2 string';
        $st3_str = 'st3 string';

        $invite_content_link = $kt->gen_invite_content_link(self::APP_URL,
                                                            $long_tracking_code,
                                                            $st1_str, $st2_str, $st3_str);
        $url_items = parse_url($invite_content_link);
        parse_str($url_items['query'], $query_items);
        $this->assertEquals($query_items['kt_type'], 'inr',
                            'wrong kt_type for inr');
        $this->assertEquals(strlen($query_items['kt_ut']), 16,
                            'bad kt_ut for inr');
        $this->assertEquals($query_items['kt_st1'], $st1_str,
                            'wrong kt_st1 value for inr');
        $this->assertEquals($query_items['kt_st2'], $st2_str,
                            'wrong kt_st2 value for inr');
        $this->assertEquals($query_items['kt_st3'], $st3_str,
                            'wrong kt_st3 value for inr');        
        
    }

    public function testStrippedKtArgsWithAnExtraArg()
    { 
        $kt = new Kontagent(self::KT_HOST, self::KT_API_KEY);
        $kt_url = 'http://apps.facebook.com/lih_test_lowlevelnew/?kt_type=ins&kt_ut=6114be4c5ecb69e4&kt_uid=1166673718&kt_st1=st111&kt_st2=st222&kt_st3=st333&foo=bar';

        $r_url = $kt->stripped_kt_args($kt_url);
        $r_items_arry = parse_url($r_url);

        $this->assertEquals(isset($r_items_arry['query']), true,
                            "should have a query string.");
        parse_str($r_items_arry['query'], $r_GET_arry);

        
        $this->assertEquals( isset($r_GET_arry['kt_type']), false,
                             "kt_type shouldn't be in the query str." );
        $this->assertEquals( isset($r_GET_arry['kt_ut']),   true,
                             "kt_ut should be in the query str." );
        $this->assertEquals( isset($r_GET_arry['kt_st1']),  false,
                             "kt_st1 shouldn't be in the query str." );
        $this->assertEquals( isset($r_GET_arry['kt_st2']),  false,
                             "kt_st2 shouldn't be in the query str." );
        $this->assertEquals( isset($r_GET_arry['kt_st3']),  false,
                             "kt_st3 shouldn't be in the query str." );
        $this->assertEquals( isset($r_GET_arry['foo']), true,
                             "foo should still be there" );
    }

    private function setupServerAndGetVar($kt_url)
    {
        //
        // faking hella data: $_SERVER['HTTP_REFERER'] and $_GET
        //
        $items_arry = parse_url($kt_url);
        parse_str($items_arry['query'],$_GET);
        $_SERVER['HTTP_REFERER'] = $kt_url;
    }
    
    public function testStrippedKtArgsWithoutExtraArgs()
    {
        $kt = new Kontagent(self::KT_HOST, self::KT_API_KEY);
        $kt_url = 'http://apps.facebook.com/lih_test_lowlevelnew/?kt_type=ins&kt_ut=6114be4c5ecb69e4&kt_uid=1166673718&kt_st1=st111&kt_st2=st222&kt_st3=st333';

        $r_url = $kt->stripped_kt_args($kt_url);
        $r_items_arry = parse_url($r_url);
        parse_str($r_items_arry['query'], $r_GET_arry);

        $this->assertEquals( isset($r_GET_arry['kt_ut']), true,
                             'kt_ut should be in the query str.');
        $this->assertEquals( sizeof($r_GET_arry) , 1,
                             'kt_ut should be the only parameter in the query str.' );
    }

    public function testStrippedKtArgsWithShortTag()
    {
        $kt = new Kontagent(self::KT_HOST, self::KT_API_KEY);        
        $kt_url = 'http://apps.facebook.com/lih_test_lowlevelnew/?kt_type=partner';
        
        $short_tracking_code = $kt->gen_short_tracking_code();
        global $kt_short_tag;
        
        $r_url = $kt->stripped_kt_args($kt_url);
        
        $r_items_arry = parse_url($r_url);
        parse_str($r_items_arry['query'], $r_GET_arry);

        $this->assertEquals( isset($r_GET_arry['kt_sut']), true,
                             'kt_sut should be in the query str.');
        $this->assertEquals( sizeof($r_GET_arry) , 1,
                             'kt_sut should be the only parameter in the query str.' );
    }

    public function testStrippedKtArgsWithShortTagAndExtraTags()
    {
        $kt = new Kontagent(self::KT_HOST, self::KT_API_KEY);        
        $kt_url = 'http://apps.facebook.com/lih_test_lowlevelnew/?kt_type=partner&foo=bar';
        
        $short_tracking_code = $kt->gen_short_tracking_code();
        global $kt_short_tag;
        
        $r_url = $kt->stripped_kt_args($kt_url);
        
        $r_items_arry = parse_url($r_url);
        parse_str($r_items_arry['query'], $r_GET_arry);
        $this->assertEquals( isset($r_GET_arry['foo']), true,
                             "foo should still be there" );
        $this->assertEquals( isset($r_GET_arry['kt_sut']), true,
                             'kt_sut should be in the query str.');
    }
    
    
    public function testGenTrackingInviteSentUrl()
    {
        $kt = new Kontagent(self::KT_HOST, self::KT_API_KEY);
        $kt_url = 'http://apps.facebook.com/lih_test_lowlevelnew/?kt_type=ins&kt_ut=6114be4c5ecb69e4&kt_uid=1166673718&kt_st1=st111&kt_st2=st222&kt_st3=st333';        

        $this->setupServerAndGetVar($kt_url);
        $_GET['ids'] = array('1111','2222'); 
        $url = $kt->gen_tracking_invite_sent_url();
        
        $items_arry = parse_url($url);
        parse_str($items_arry['query'], $r_GET_arry);
        $this->assertEquals( isset($r_GET_arry['s']), true,
                             "s is required." );
        $this->assertEquals( isset($r_GET_arry['u']), true,
                             "u is required." );
        $this->assertEquals( strlen($r_GET_arry['u']), 16,
                             "u size should be 16." );
        $this->assertEquals( isset($r_GET_arry['st1']), true,
                             'st1 should be there.');
        $this->assertEquals( isset($r_GET_arry['st2']), true,
                             'st2 should be there.');
        $this->assertEquals( isset($r_GET_arry['st3']), true,
                             'st3 should be there.');
        
        $this->setupServerAndGetVar($kt_url);
        $_GET['ids'] = array('111111','2222222'); 
        $kt->track_invite_sent();
    }

    public function testKtCommLayerApiCallMethod()
    {
        $host = 'tofoo.dyndns.org';
        $port = '8080';
        $tracking_url = 'http://tofoo.dyndns.org:8080/api/v1/aaaa/ins/?s=1166673718&u=9984be86314b5d6f&st1=st111&st2=st222&st3=st333&r=&ts=20100510.194843';
        $ktCommLayer = new KtCommLayer($host, $port, 'aaaa');
        $ktCommLayer->api_call_method($tracking_url);
    }

/*     public function testKtCommLayerSelectServer() */
/*     { */
/*         $host = 'api.geo.kontagent.net'; */
/*         $port = '80'; */
/*         $class = new ReflectionClass('KtCommLayer'); */
/*         $priv_method = $class->getMethod('select_server'); */
/*         $priv_method->setAccessible(true); */
/*         $ktCommLayer = new KtCommLayer($host, $port); */
/*         $r = $priv_method->invokeArgs($ktCommLayer); */
/*         echo $r; */
/*     } */
    
    
}
