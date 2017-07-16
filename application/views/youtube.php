<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb" lang="en-gb" dir="ltr">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Youtube V3 api Search youtube video demo</title>
    <link rel="stylesheet" href="maincss.css" type="text/css">
    <link rel="stylesheet" type="text/css" href="lib/pretty-json.css" />
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" />
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css" type="text/css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js" type="text/javascript"></script>
  <script
			  src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"
			  integrity="sha256-0YPKAwZP7Mp3ALMRVB2i8GXeEndvCq3eSl/WsAl1Ryk="
			  crossorigin="anonymous"></script>
    <!-- Underscore, backbone and pretty are used for displaying the json responce in user redable format -->
   
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {

            var ytkey = 'AIzaSyA469erjMOiQZYBUX0lL5k2falo6OihWuM';
            //youtubeApiCall();
            $("#pageTokenNext").on( "click", function( event ) {
                $("#pageToken").val($("#pageTokenNext").val());
                youtubeApiCall();
            });
            $("#pageTokenPrev").on( "click", function( event ) {
                $("#pageToken").val($("#pageTokenPrev").val());
                youtubeApiCall();
            });
            $("#hyv-searchBtn").on( "click", function( event ) {
                youtubeApiCall();
                return false;
            });
            jQuery( "#hyv-search" ).autocomplete({
              source: function( request, response ) {
                //console.log(request.term);
                var sqValue = [];
                jQuery.ajax({
                    type: "POST",
                    url: "http://suggestqueries.google.com/complete/search?hl=en&ds=yt&client=youtube&hjson=t&cp=1",
                    dataType: 'jsonp',
                    data: jQuery.extend({
                        q: request.term
                    }, {  }),
                    success: function(data){
                        console.log(data[1]);
                        obj = data[1];
                        jQuery.each( obj, function( key, value ) {
                            sqValue.push(value[0]);
                        });
                        response( sqValue);
                    }
                });
              },
              select: function( event, ui ) {
                setTimeout( function () { 
                    youtubeApiCall();
                }, 300);
              }
            });
            function getVideoDetails(ids){
                $.ajax({
                    cache: false,
                    data: $.extend({
                        key: ytkey,
                        part: 'snippet,contentDetails,statistics'
                    }, {id: ids}),
                    dataType: 'json',
                    type: 'GET',
                    timeout: 5000,
                    fields: "items(id,contentDetails,statistics,snippet(publishedAt,channelTitle,channelId,title,description,thumbnails(medium)))",
                    url: 'https://www.googleapis.com/youtube/v3/videos'
                })
                .done(function(data) {
                    var items = data.items, videoList = "";
                    $.each(items, function(index,e) {
                        videoList = videoList + '<li class="hyv-video-list-item"><div class="hyv-content-wrapper"><a rel="'+e.id+'" data-toggle="modal" data-target="#videoModal" class="hyv-content-link" title="'+e.snippet.title+'"><span class="title">'+e.snippet.title+'</span><span class="stat attribution">by <span>'+e.snippet.channelTitle+'</span></span></a></div><div class="hyv-thumb-wrapper"><a  rel="'+e.id+'" data-toggle="modal" data-target="#videoModal" class="hyv-thumb-link"><span class="hyv-simple-thumb-wrap"><img alt="'+e.snippet.title+'" src="'+e.snippet.thumbnails.default.url+'" width="120" height="90"></span></a><span class="video-time">'+YTDurationToSeconds(e.contentDetails.duration)+'</span></div></li>';
                    });
                    $("#hyv-watch-related").html(videoList);
                    // JSON Responce to display for user
                    new PrettyJSON.view.Node({ 
                        el:$(".hyv-watch-sidebar-body"), 
                        data:data
                    });
                });
            }

            function YTDurationToSeconds(duration) {
                var match = duration.match(/PT(\d+H)?(\d+M)?(\d+S)?/)

                var hours = ((parseInt(match[1]) || 0) !== 0)?parseInt(match[1])+":":"";
                var minutes = ((parseInt(match[2]) || 0) !== 0)?parseInt(match[2])+":":"";
                var seconds = ((parseInt(match[3]) || 0) !== 0)?parseInt(match[3]):"00";
                var total = hours + minutes + seconds;
                return total;
            }

            function youtubeApiCall(){
                $.ajax({
                    cache: false,
                    data: $.extend({
                        key: ytkey,
                        q: $('#hyv-search').val(),
                        part: 'snippet'
                    }, {maxResults:20,pageToken:$("#pageToken").val()}),
                    dataType: 'json',
                    type: 'GET',
                    timeout: 5000,
                    fields: "pageInfo,items(id(videoId)),nextPageToken,prevPageToken",
                    url: 'https://www.googleapis.com/youtube/v3/search'
                })
                .done(function(data) {
                    $('.btn-group').show();
                    if (typeof data.prevPageToken === "undefined") {$("#pageTokenPrev").hide();}else{$("#pageTokenPrev").show();}
                    if (typeof data.nextPageToken === "undefined") {$("#pageTokenNext").hide();}else{$("#pageTokenNext").show();}
                    var items = data.items, videoids = [];
                    $("#pageTokenNext").val(data.nextPageToken);
                    $("#pageTokenPrev").val(data.prevPageToken);
                    $.each(items, function(index,e) {
                        videoids.push(e.id.videoId);
                    });
                    getVideoDetails(videoids.join());
                });
            } 

           var $midlayer = $('.modal-body');
            $('#videoModal').on('show.bs.modal', function (event) {
                //var $video = $('a.video');
                //var vid = $video.attr('rel');
                var vid = event.relatedTarget.rel;
                var url = "https://youtube.com/embed/"+vid+"?autoplay=0&autohide=1&modestbranding=1&rel=1&hd=1";
                $('#vedio-temp-url').val(vid);
                $("#video_submit_button").attr('data-id',vid);
				var iframe = '<iframe />';
                var width_f = '100%';
                var height_f = 400;
                var frameborder = 0;
                jQuery(iframe, {
                    name: 'videoframe',
                    id: 'videoframe',
                    src: url,
                    width:  width_f,
                    height: height_f,
                    frameborder: 0,
                    class: 'youtube-player',
                    type: 'text/html',
                    allowfullscreen: true
                }).appendTo($midlayer);   
            });
            $('#videoModal').on('hide.bs.modal', function (event) {
                $('div.modal-body').html('');
            });
            $(document).on("click",'#video_submit_button',function(){

               // alert( );
                var video_id = $('#vedio-temp-url').val();
                var video_link = 'https://www.youtube.com/watch?v='+video_id;
                $("#youtube_video_link").val(video_link);
		//alert(video_id);		

            });
            $("#videoModal").on('click','#video_submit_button',function(){
                
                //alert($("#video_submit_button").attr('data-id'));
                var vid=$("#video_submit_button").attr('data-id');
                $.ajax({
                    url:"<?php echo base_url(); ?>" + "get_videoid",
                    type:"POST",
                    data:{"vid":vid},
                    success:function(data){
                        console.log(data);
                        
                    }
                    
                    
                })
            });
        });
 
    </script>
<script>

</script>
    <style type="text/css"> 
        body{
            background-color: #efefef;
        }
        .container-4 input#hyv-search {
            width: 500px;
            height: 30px;
            border: 1px solid #c6c6c6;
            font-size: 10pt;
            float: left;
            padding-left: 15px;
            -webkit-border-top-left-radius: 5px;
            -webkit-border-bottom-left-radius: 5px;
            -moz-border-top-left-radius: 5px;
            -moz-border-bottom-left-radius: 5px;
            border-top-left-radius: 5px;
            border-bottom-left-radius: 5px;
        }
        .container-4 button.icon {
            height: 30px;
            background: #f0f0f0 url('images/searchicon.png') 10px 1px no-repeat;
            background-size: 24px;
            -webkit-border-top-right-radius: 5px;
            -webkit-border-bottom-right-radius: 5px;
            -moz-border-radius-topright: 5px;
            -moz-border-radius-bottomright: 5px;
            border-top-right-radius: 5px;
            border-bottom-right-radius: 5px;
            border: 1px solid #c6c6c6;
            width: 50px;
            margin-left: -44px;
            color: #4f5b66;
            font-size: 10pt;
        }
    </style>
</head>

<body>


    <div class="modal fade" id="videoModal" tabindex="-1" role="dialog" aria-labelledby="videoModal" aria-hidden="true"> 
        <?php echo form_open('Youtube/get_videoid');?>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <input type="hidden" id="vedio-temp-url" />
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <button type="button" id="video_submit_button" data-dismiss="modal" data-id="" class="btn btn-primary">Save Image</button>
                    
                </div>
                <div class="modal-body">
                    
                </div>
            </div>
        </div>
    </div>
    <div class="row-fluid">
        <main id="content" role="main" class="span12">
            <!-- Begin Content -->
            <div id="hyv-page-container" style="clear:both;">
                <div class="hyv-content-alignment">
                    <div id="hyv-page-content" class="" style="overflow:hidden;">
                        <div class="container-4">
                            <form action="" method="post" name="hyv-yt-search" id="hyv-yt-search">
                                <input type="search" name="hyv-search" id="hyv-search" placeholder="Search..." class="ui-autocomplete-input" autocomplete="off">
								<input type="hidden" name="youtube_video_link" id="youtube_video_link">
                                <button class="icon" id="hyv-searchBtn"></button>
                            </form>
                        </div>
                        <div>
                            <input type="hidden" id="pageToken" value="">
                            <div class="btn-group" role="group" aria-label="..." style="display:none;">
                              <button type="button" id="pageTokenPrev" value="" class="btn btn-default">Prev</button>
                              <button type="button" id="pageTokenNext" value="" class="btn btn-default">Next</button>
                            </div>
                        </div>
                        <div id="hyv-watch-content" class="hyv-watch-main-col hyv-card hyv-card-has-padding">
                            <ul id="hyv-watch-related" class="hyv-video-list">
                            </ul>

                        </div>						                        
                    </div>
                </div>
            </div>
        </main>
    </div>
   
</body>

</html>