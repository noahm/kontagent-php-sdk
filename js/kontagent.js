function setCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function getCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function deleteCookie(name) {
	setCookie(name,"",-1);
}

/*
 * Generate a random uuid.
 *
 * USAGE: Math.uuid(length, radix)
 *   length - the desired number of characters
 *   radix  - the number of allowable values for each character.
 *
 * EXAMPLES:
 *   // No arguments  - returns RFC4122, version 4 ID
 *   >>> Math.uuid()
 *   "92329D39-6F5C-4520-ABFC-AAB64544E172"
 *
 *   // One argument - returns ID of the specified length
 *   >>> Math.uuid(15)     // 15 character ID (default base=62)
 *   "VcydxgltxrVZSTV"
 *
 *   // Two arguments - returns ID of the specified length, and radix. (Radix must be <= 62)
 *   >>> Math.uuid(8, 2)  // 8 character ID (base=2)
 *   "01001010"
 *   >>> Math.uuid(8, 10) // 8 character ID (base=10)
 *   "47473046"
 *   >>> Math.uuid(8, 16) // 8 character ID (base=16)
 *   "098F4D35"
 */
Math.uuid = (function() {
  // Private array of chars to use
  var CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('');

  return function (len, radix) {
    var chars = CHARS, uuid = [], rnd = Math.random;
    radix = radix || chars.length;

    if (len) {
      // Compact form
      for (var i = 0; i < len; i++) uuid[i] = chars[0 | rnd()*radix];
    } else {
      // rfc4122, version 4 form
      var r;

      // rfc4122 requires these characters
      uuid[8] = uuid[13] = uuid[18] = uuid[23] = '';
      uuid[14] = '4';

      // Fill in random data.  At i==19 set the high bits of clock sequence as
      // per rfc4122, sec. 4.1.5
      for (var i = 0; i < 36; i++) {
        if (!uuid[i]) {
          r = 0 | rnd()*16;
          uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r & 0xf];
        }
      }
    }

    var ret = uuid.join('');
    return ret.substring(0, 32);
  };
})();

function urlencode( str ) {
    // http://kevin.vanzonneveld.net
    // +   original by: Philip Peterson
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: AJ
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: travc
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Lars Fischer
    // +      input by: Ratheous
    // %          note 1: info on what encoding functions to use from: http://xkr.us/articles/javascript/encode-compare/
    // *     example 1: urlencode('Kevin van Zonneveld!');
    // *     returns 1: 'Kevin+van+Zonneveld%21'
    // *     example 2: urlencode('http://kevin.vanzonneveld.net/');
    // *     returns 2: 'http%3A%2F%2Fkevin.vanzonneveld.net%2F'
    // *     example 3: urlencode('http://www.google.nl/search?q=php.js&ie=utf-8&oe=utf-8&aq=t&rls=com.ubuntu:en-US:unofficial&client=firefox-a');
    // *     returns 3: 'http%3A%2F%2Fwww.google.nl%2Fsearch%3Fq%3Dphp.js%26ie%3Dutf-8%26oe%3Dutf-8%26aq%3Dt%26rls%3Dcom.ubuntu%3Aen-US%3Aunofficial%26client%3Dfirefox-a'

    var hash_map = {}, unicodeStr='', hexEscStr='';
    var ret = (str+'').toString();

    var replacer = function(search, replace, str) {
        var tmp_arr = [];
        tmp_arr = str.split(search);
        return tmp_arr.join(replace);
    };

    // The hash_map is identical to the one in urldecode.
    hash_map["'"]   = '%27';
    hash_map['(']   = '%28';
    hash_map[')']   = '%29';
    hash_map['*']   = '%2A';
    hash_map['~']   = '%7E';
    hash_map['!']   = '%21';
    hash_map['%20'] = '+';
    hash_map['\u00DC'] = '%DC';
    hash_map['\u00FC'] = '%FC';
    hash_map['\u00C4'] = '%D4';
    hash_map['\u00E4'] = '%E4';
    hash_map['\u00D6'] = '%D6';
    hash_map['\u00F6'] = '%F6';
    hash_map['\u00DF'] = '%DF';
    hash_map['\u20AC'] = '%80';
    hash_map['\u0081'] = '%81';
    hash_map['\u201A'] = '%82';
    hash_map['\u0192'] = '%83';
    hash_map['\u201E'] = '%84';
    hash_map['\u2026'] = '%85';
    hash_map['\u2020'] = '%86';
    hash_map['\u2021'] = '%87';
    hash_map['\u02C6'] = '%88';
    hash_map['\u2030'] = '%89';
    hash_map['\u0160'] = '%8A';
    hash_map['\u2039'] = '%8B';
    hash_map['\u0152'] = '%8C';
    hash_map['\u008D'] = '%8D';
    hash_map['\u017D'] = '%8E';
    hash_map['\u008F'] = '%8F';
    hash_map['\u0090'] = '%90';
    hash_map['\u2018'] = '%91';
    hash_map['\u2019'] = '%92';
    hash_map['\u201C'] = '%93';
    hash_map['\u201D'] = '%94';
    hash_map['\u2022'] = '%95';
    hash_map['\u2013'] = '%96';
    hash_map['\u2014'] = '%97';
    hash_map['\u02DC'] = '%98';
    hash_map['\u2122'] = '%99';
    hash_map['\u0161'] = '%9A';
    hash_map['\u203A'] = '%9B';
    hash_map['\u0153'] = '%9C';
    hash_map['\u009D'] = '%9D';
    hash_map['\u017E'] = '%9E';
    hash_map['\u0178'] = '%9F';

    // Begin with encodeURIComponent, which most resembles PHP's encoding functions
    ret = encodeURIComponent(ret);

    for (unicodeStr in hash_map) {
        hexEscStr = hash_map[unicodeStr];
        ret = replacer(unicodeStr, hexEscStr, ret); // Custom replace. No regexing
    }

    // Uppercase for full PHP compatibility
    return ret.replace(/(\%([a-z0-9]{2}))/g, function(full, m1, m2) {
        return "%"+m2.toUpperCase();
    });
}

function parse_str(query_str){
  var r = {};
  if(query_str == undefined || query_str == "")
    return r;

  var key_val_pair_list = query_str.split('&');
  var key_val_pair_list_len = key_val_pair_list.length;
  for(var i = 0 ; i < key_val_pair_list_len; i++)
  {
    var key_val_pair = key_val_pair_list[i];
    var item = key_val_pair.split("=");
    if(item[1] == undefined || item[1] =="")
    {
      r[item[0]] = null;
    }
    else
    {
      r[item[0]] = decodeURIComponent(item[1].replace(/\+/g, '%20'));
    }
  }
  return r;
}

function http_build_query( formdata, numeric_prefix, arg_separator ) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Legaev Andrey
    // +   improved by: Michael White (http://getsprink.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    revised by: stag019
    // -    depends on: urlencode
    // *     example 1: http_build_query({foo: 'bar', php: 'hypertext processor', baz: 'boom', cow: 'milk'}, '', '&amp;');
    // *     returns 1: 'foo=bar&amp;php=hypertext+processor&amp;baz=boom&amp;cow=milk'
    // *     example 2: http_build_query({'php': 'hypertext processor', 0: 'foo', 1: 'bar', 2: 'baz', 3: 'boom', 'cow': 'milk'}, 'myvar_');
    // *     returns 2: 'php=hypertext+processor&myvar_0=foo&myvar_1=bar&myvar_2=baz&myvar_3=boom&cow=milk'

    var value, key, tmp = [];

    var _http_build_query_helper = function (key, val, arg_separator) {
        var k, tmp = [];
        if (val === true) {
            val = "1";
        } else if (val === false) {
            val = "0";
        }
        if (typeof(val) == "array" || typeof(val) == "object") {
            for (k in val) {
                if(val[k] !== null) {
                    tmp.push(_http_build_query_helper(key + "[" + k + "]", val[k], arg_separator));
                }
            }
            return tmp.join(arg_separator);
        } else if(typeof(val) != "function") {
	  if(val != undefined)
	    return urlencode(key) + "=" + urlencode(val);
	  else
	    return undefined;
        }
    };

    if (!arg_separator) {
        arg_separator = "&";
    }
    for (key in formdata) {
        value = formdata[key];
        if (numeric_prefix && !isNaN(key)) {
            key = String(numeric_prefix) + key;
        }
	var key_val_str = _http_build_query_helper(key, value, arg_separator);
	if(key_val_str != undefined)
	  tmp.push(key_val_str);
    }

    return tmp.join(arg_separator);
}

///////////////// Kontagent class /////////////////

function Kontagent(kt_host, kt_api_key){
  this.kt_api_key = kt_api_key;
  this.kt_host = kt_host;
  this.version = 'v1';
};

Kontagent.prototype = {
  run : function()
  {
    // capture User Info
    if(window.SESSION){
      var user_info_cookie_key = this.gen_kt_capture_user_info_key(FB_ID, SESSION['uid']);
      if(!getCookie(user_info_cookie_key)){
	setCookie(user_info_cookie_key, 1, 14);
	this.track_user_info();
      }
    }

    if(window.kt_landing_str){
      this.kt_send_msg_via_img_tag(kt_landing_str);
    }

    // do we need to redirect?
    if(window.kt_redirect){
      top.location.href = kt_redirect;
    }
  },

  gen_kt_capture_user_info_key : function(app_id, uid)
  {
    return 'kt_capture_user_info_'+app_id+"_"+uid;
  },

  track_user_info : function()
  {
    if( !window.SESSION ) return;
    FB.init({ appId   : FB_ID,
	      xfbml   : true ,
	      session : SESSION
	    });
    var me_json = null;
    var me_friends_json = null;
    var this_obj = this;
    FB.api("/me",
	   function(response){
	     me_json = response;
	     if( me_json != null && me_friends_json != null){
	       this_obj.track_user_info_impl(me_json, me_friends_json);
	     }
	   }
	  );
    FB.api("/me/friends",
	   function(response){
	     me_friends_json = response;
	     if( me_json != null && me_friends_json != null){
	       this_obj.track_user_info_impl(me_json, me_friends_json);
	     }
	   });
  },
  track_user_info_impl : function(user_info, user_friends_info)
  {
    var params = { s : user_info['id']};
    if( user_info['gender'] != undefined){
      params['g'] = urlencode(user_info['gender'].toUpperCase());
    }
    if( user_info['birthday'] != undefined){
      var birthday_components = user_info['birthday'].split('/');
      if(birthday_components.length == 3)
	params['b'] = urlencode(birthday_components[2]);
    }
    if( user_friends_info['data'] != undefined ){
      params['f'] = user_friends_info['data'].length;
    }
    this.kt_outbound_msg('cpu', params);
  },

  track_revenue : function(amount_in_cents)
  {
    var uid = this.get_session_uid();
    if(uid)
    {
      var params = { s : uid,
      v : amount_in_cents };
      this.kt_outbound_msg('mtu', params);
    }
  },

  track_event : function(event_name, value, level,
			 st1, st2, st3)
  {
    var uid = this.get_session_uid();
    if(uid)
    {
      var params = { s : uid,
		     n : event_name };
      if(value != null) params['v'] = value;
      if(level != null) params['l'] = level;
      if(st1 != null) params['st1'] = st1;
      if(st2 != null) params['st2'] = st2;
      if(st3 != null) params['st3'] = st3;
      this.kt_outbound_msg('evt', params);
    }
  },

  // uid can be a number or a string or an array of uids.
  track_goal_count : function(uid, goal_id, inc)
  {
    var args = {
    };
    args[goal_id] = inc;
    this.track_multi_goal_counts(uid, args);
  },

  // uid can be a number or a string or an array of uids.
  // goal_counts : Example: {1=>10. 2=>20};
  track_multi_goal_counts : function(uid, goal_counts)
  {
    var params = {};
    if ((typeof uid) == 'string' || (typeof uid) == 'number') {
      params['s'] = uid;
    }else if((typeof uid) == 'object'){
      params['s'] = uid.join(',');
    }

    for(var key in goal_counts){
      params['gc'+key]  = goal_counts[key];
    }
    this.kt_outbound_msg('gci', params);
  },

  kt_outbound_msg : function(channel, params)
  {
    var timestamp = Date.parse((new Date()).toUTCString().slice(0, -4))/1000;
    params['ts'] = timestamp;
    var url_path = "http://"+this.kt_host + "/api/" + this.version + "/" + this.kt_api_key + "/" + channel + "/?" + http_build_query(params);
    this.kt_send_msg_via_img_tag(url_path);
  },

  kt_send_msg_via_img_tag : function(url_path)
  {
    var img = document.createElement('img');
    img.src = url_path;
  },

  append_kt_query_str : function(original_url, query_str)
  {
    var position = original_url.indexOf('?');
    if(position == -1)
    {
      return original_url + "?" + query_str;
    }
    else
    {
      return original_url + "&" + query_str;
    }
  },

  get_session_uid : function()
  {
    var parsed_qs = parse_str(window.location.search.substring(1,window.location.search.length));
    var uid = null;
    if(parsed_qs['session'] != undefined)
      uid = JSON.parse(parsed_qs['session'])['uid'];
    return uid;
  },

  gen_stream_link : function(link, uuid, st1, st2, st3)
  {
    var param_array = {kt_type : 'stream',
		       kt_ut   : String(uuid),
		       kt_st1  : st1,
		       kt_st2  : st2,
		       kt_st3  : st3};

    var query_str = http_build_query(param_array);
    var mod_link = this.append_kt_query_str(link, query_str);
    return mod_link;
  }

};

if(window.SEND_MSG_VIA_JS){
  var kt = new Kontagent(KT_API_SERVER , KT_API_KEY);
  kt.run();
}
