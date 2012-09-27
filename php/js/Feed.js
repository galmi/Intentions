var Feed = {
  page : 1,
  type : 'new',
  server: '',

  init: function(server) {
    var uids = [];
    var html = '';
    var friends = {};
    if (server) {
        this.server = server;
      }
    $.post('/index/feed',{page:Feed.page, type: Feed.type}, function(data){
      if (data.length){
        $.each(data, function(key, val){
          uids.push(val.friend);
        });
        VK.api("getProfiles", {uids:uids.join(','),fields:"uid,photo_rec"}, function (profiles) {
          $.each(profiles.response, function(key,val){
            friends[val.uid] = {photo_rec: val.photo_rec, first_name: val.first_name, last_name: val.last_name};
          });
          $.each(data, function(key, val){
            var rowData = {
              uid: val.friend,
              intention_id: val.intention_id,
              description: val.description,
              avatar: friends[val.friend].photo_rec,
              first_name: friends[val.friend].first_name,
              last_name: friends[val.friend].last_name,
              ts: val.ts,
              action: val.action,
              color: val.color
            };
            if (Feed.type=='my') {
                $.extend(rowData,{nolike:true});
            }
            html += Feed.row(rowData);
          });
          if (html) {
            $("#news").append(html);
            Feed.page++;
          }
          $("#show_more").children('a').removeClass('loading');
        });
      } else {
        $("#show_more").hide();
      }
    }, 'json');

    $("#sub_menu a").click(function(){
      var type = $(this).attr('data-filter');
      if (type!=Feed.type) {
        $("#sub_menu a").removeClass('selected');
        $(this).addClass('selected');
        Feed.type = type;
        Feed.page = 1;
        $("#news").html('');
        Feed.init();
      }
    });
    $("#show_more").click(function(){
      if (!$(this).children('a').hasClass('loading')) {
        $(this).children('a').addClass('loading');
        Feed.init();
      }
    });
  },

  row : function(data) {
    var action = (data.action=='done')?' уже сделал это':' хочет сделать это';
    var date = new Date(data.ts*1000);
    if (data.color) {
        var color = 'style="background-color:'+data.color+'"';
    } else {
        var color = '';
    }
    var row =
    '<div class="news_block gray_block" '+color+'>' +
    '<div style="float:right;"><div id="vk_like_' + data.intention_id + '" class="like" data-id="'+data.intention_id+'"></div></div>' +
    '  <span class="news_header">'+
    '    <a onclick="Common.url(\'/intention/view/' + data.intention_id + '\')">' + data.description.replace("'","") + '</a>' +
    '  </span>' +
    '  <div class="news_user">' +
    '    <table border="0" cellpadding="0" cellspacing="0">' +
    '      <tbody>' +
    '      <tr>' +
    '       <td class="image">' +
    '          <a class="ava" onclick="Common.url(\'/user/view/'+data.uid+'\')">' +
    '            <img width="40" height="40" src="'+data.avatar+'">' +
    '          </a>' +
    '        </td>' +
    '        <td>' +
    '          <p><a onclick="Common.url(\'/user/view/'+data.uid+'\')">'+data.first_name+' '+data.last_name+'</a>' + action + '.</p>' +
    '          <span class="rel_date">'+date.toLocaleDateString()+'</span>' +
    '        </td>' +
    '      </tr>' +
    '      </tbody>' +
    '    </table>' +
    '  </div>' +
    '</div>';
    if (!data.nolike){
        var row = row + '<script type="text/javascript">VK.Widgets.Like("vk_like_'+data.intention_id+'", {type:"mini", pageUrl:"'+Feed.server+'/intention/view/'+data.intention_id+'", text:\'Мне понравилось желание "'+data.description+'"\'});</script>';
    }
    return row;
  }
}