//
// Attempt to fix a bug where if you pass in a large amount of data in FB.ui,
// it will switch to using POST. However, it fails to json encode the parameters
// properly.
//
var origPostTarget = FB.Content.postTarget;
FB.Content.postTarget = function(opts) {
  FB.Array.forEach(opts.params, function(val, key) {
		     if (typeof val == "object" || typeof val == "array") {
		       opts.params[key] = FB.JSON.stringify(val);
		     }
		   });
  origPostTarget(opts);
};

//
// Override the original fb.ui's stream.post and fb.api's feed
//
if(window.KT_API_SERVER && window.KT_API_KEY)
{
  var KT_FB = {};
  KT_FB.ui = FB.ui;
  KT_FB.api = FB.api;
  KT_FB.login = FB.login;
  KT_FB.kt = kt;
  KT_FB.prototype = {
    gen_long_tracking_tag : function()
    {
      return Math.uuid().substring(0,16);
    }
  };

  FB.ui = function(params, cb){
    var session = FB.getSession();
    if(session && session.uid)
    {
      var uid = session.uid;
      var uuid = Math.uuid();
      var st1 = params['st1'];
      var st2 = params['st2'];
      var st3 = params['st3'];

      if(params['method']!=undefined){
	//
	// Original FB Dialog stuff
	//
	if(params['method'] == 'stream.publish'){
	  // href
	  if(params['attachment'] != undefined && params['attachment']['href'] != undefined){
	    params['attachment']['href'] = KT_FB.kt.gen_stream_link(params['attachment']['href'], uuid, st1, st2, st3);
	    // media
	    if(params['attachment']['media'] != undefined){
	      var media_list = params['attachment']['media'];
	      var media_list_len = media_list.length;
	      for(var i = 0; i < media_list_len; i++){
		var curr_media = media_list[i];
		if(curr_media['type'] == 'image'){
		  curr_media['href'] = KT_FB.kt.gen_stream_link(curr_media['href'], uuid, st1, st2, st3);
		}else if(curr_media['type'] == 'mp3'){
		  curr_media['mp3'] = KT_FB.kt.gen_stream_link(curr_media['src'], uuid, st1, st2, st3);
		}else if(curr_media['type'] == 'flash'){
		  curr_media['flash'] = KT_FB.kt.gen_stream_link(curr_media['src'], uuid, st1, st2, st3);
		}
	      }//for
	    }//if(params['attachment']['href']['media'] != undefined)
	  }

	  // action_links
	  if(params['action_links'] !=undefined){
            var action_links_list = params['action_links'];
            var action_links_list_len = action_links_list.length;
            for(var i = 0; i < action_links_list_len; i++){
	      var curr_action_link = action_links_list[i];
	      curr_action_link['href'] = KT_FB.kt.gen_stream_link(curr_action_link['href'], uuid, st1, st2, st3);
            }
	  }

	  if(cb!=undefined && cb != null){
	    var kt_cb = function(resp){
	      if(resp && resp.post_id){
		// send a pst stream msg.
		KT_FB.kt.kt_outbound_msg('pst',
					 {tu : 'stream', u : uuid, s : uid,
					  st1 : st1, st2 : st2, st3 : st3}
					);
	      }
	      cb(resp); //call the original callback function
	    };
	  }else{
	    var kt_cb = function(resp){
	      if(resp && resp.post_id){
		// send a pst stream msg.
		KT_FB.kt.kt_outbound_msg('pst',
					 {tu : 'stream', u : uuid, s : uid,
					  st1 : st1, st2 : st2, st3 : st3}
					);
	      }
	    };
	  }
	  KT_FB.ui(params, kt_cb);
	}// if(params['method'] == 'stream.publish'
	else if(params['method'] == 'apprequests'){
	  //Stick uid, uuid, st1,st2,st3 in data
	  params['data'] = KT_FB.kt.append_kt_tracking_info_to_apprequests(params['data'],uuid, st1, st2, st3);

	  function kt_cb_impl(resp){
	    if(resp){
	      KT_FB.kt.kt_outbound_msg('ins',
				       { u : uuid, s : uid,
					 st1 : st1, st2 : st2, st3 : st3,
					 r : resp.request_ids.join(",")
				       });
	    }
	  }

	  if(cb != undefined && cb!= null){
	    var kt_cb = function(resp){
	      kt_cb_impl(resp);
	      cb(resp);
	    };
	  }else{
	    var kt_cb = function(resp){
	      kt_cb_impl(resp);
	    };
	  };
	  KT_FB.ui(params, kt_cb);
	}// if(params['method'] == 'apprequests'){
	else if(params['method'] == 'feed'){
	  if(params['link'] != undefined && params['link'] != null)
	    params['link'] = KT_FB.kt.gen_stream_link(params['link'], uuid, st1, st2, st3);

	  function kt_cb_impl(resp){
	    if(resp){
	      KT_FB.kt.kt_outbound_msg('pst',
				       { tu : 'stream', u : uuid, s : uid,
				         st1 : st1, st2 : st2, st3 : st3 }
				      );
	    }
	  }

	  if(cb!= undefined && cb !=null){
	    var kt_cb = function(resp){
	      kt_cb_impl(resp);
	      cb(resp);
	    };
	  }else{
	    var kt_cb = function(resp){
	      kt_cb_impl(resp);
	    };
	  }
	  KT_FB.ui(params, kt_cb);
	}// if(params['method'] == ''){
	else{
	  KT_FB.ui(params, cb);
	}

      }else{//if(params['method'] != undefined...
	KT_FB.ui(params, cb);
      }
    }else{  //if(uid)
      KT_FB.ui(params, cb);
    }

  };//FB.ui = function...

  FB.api = function(){
    if(typeof arguments[0] == 'string'){
      if(arguments[0].search('/feed') > 0){
	var session = FB.getSession();

	if(session && session.uid){
	  var uid = session.uid;
	  var params = arguments[2];
	  // make sure that the link is supplied by the user
	  var uuid = Math.uuid();
	  var st1 = params["st1"];
	  var st2 = params["st2"];
	  var st3 = params["st3"];

	  if(params["link"] != undefined){
	    params["link"] = KT_FB.kt.gen_stream_link(params["link"], uuid, st1, st2, st3);
	    arguments[2] = params;
	  }

	  var cb = arguments[arguments.length-1];
	  var kt_cb = function(resp){
	    if (!resp || resp.error) {
	      //error
	    }else{
	      KT_FB.kt.kt_outbound_msg('pst',
				       {tu : 'stream', u : uuid, s : uid,
					st1 : st1, st2 : st2, st3 : st3});
	    }
	    cb(resp); // call the original callback function
	  };
	  arguments[arguments.length-1] = kt_cb;
	}
      }
      FB.ApiServer.graph.apply(FB.ApiServer, arguments);
    }else{
      FB.ApiServer.rest.apply(FB.ApiServer, arguments);
    }
  };//FB.api = function...

  FB.login = function( cb, opts ){
    var kt_cb = function(resp){
      if(resp.session){
	window.SESSION = resp.session;
	KT_FB.kt.track_install();
      }
      if(cb != undefined || cb != null)
	cb(resp);
    };
    KT_FB.login(kt_cb, opts);
  };
}






