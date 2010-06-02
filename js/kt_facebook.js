if(window.KT_API_SERVER && window.KT_API_KEY)
{
  var KT_FB = {};
  KT_FB.ui = FB.ui;
  KT_FB.kt = new Kontagent(KT_API_SERVER , KT_API_KEY);

  FB.ui = function(params, cb){
    var uid = KT_FB.kt.get_session_uid();
    var uuid = Math.uuid();
    var st1 = params['st1'];
    var st2 = params['st2'];
    var st3 = params['st3'];

    if(params['method']!=undefined && params['method'] == 'stream.publish'){
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
	  //curr_action_link['href'] = KT_FB.kt.gen_stream_link(curr_action_link['href'], uuid, st1, st2, st3);
        }
      }

      if(cb!=undefined){
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
      }
    }

    if(cb == undefined || cb == null)
      KT_FB.ui(params, cb);
    else
      KT_FB.ui(params, cb);
  };
}


