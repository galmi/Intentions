var FeedBelieve = {
    page : 1,
    type : 'all',
    server: '',
    admin: false,
    votes: {believe:'Верю', not_believe:'Не верю'},
    values: {believe:'Правда', not_believe:'Ложь'},

    init: function(server, admin) {
        var uids = [];
        var html = '';
        var users = {};
        if (server) {
            this.server = server;
        }
        if (admin) {
            this.admin = admin;
        }
        $.post('/story/list',{page:FeedBelieve.page, type: FeedBelieve.type}, function(response){
            if (response.data.length){
                $.each(response.data, function(key, val){
                    uids.push(val.author);
                });
                VK.api("getProfiles", {uids:uids.join(','),fields:"uid,photo_rec"}, function (profiles) {
                    $.each(profiles.response, function(key,val){
                        users[val.uid] = {photo_rec: val.photo_rec, first_name: val.first_name, last_name: val.last_name};
                    });
                    $.each(response.data, function(key, val){
                        var rowData = {
                            author: val.author,
                            story_id: val.id,
                            story: val.story.replace("'",""),
                            avatar: users[val.author].photo_rec,
                            first_name: users[val.author].first_name,
                            last_name: users[val.author].last_name,
                            value: val.value,
                            vote: val.vote,
                            believe_count: val.believe_count,
                            not_believe_count: val.not_believe_count,
                        };
                        if (FeedBelieve.type=='my') {
                            $.extend(rowData,{nolike:true});
                        }
                        html += FeedBelieve.row(rowData);
                        if (FeedBelieve.page==1 && key==0) {
                          html += FeedBelieve.addFotostrana();
                        }
                    });
                    if (html) {
                        $("#news").append(html);
                        FeedBelieve.page++;
                    }
                    $("#show_more").children('a').removeClass('loading');
                });
            } else {
                $("#show_more").hide();
            }
        }, 'json');

        $("#sub_menu a").click(function(){
            var type = $(this).attr('data-filter');
            if (type!=FeedBelieve.type) {
                $("#sub_menu a").removeClass('selected');
                $(this).addClass('selected');
                FeedBelieve.type = type;
                FeedBelieve.page = 1;
                $("#news").html('');
                FeedBelieve.init();
            }
        });
        $("#show_more").click(function(){
            if (!$(this).children('a').hasClass('loading')) {
                $(this).children('a').addClass('loading');
                FeedBelieve.init();
            }
        });
    },

    row : function(data) {
        if (data.color) {
            var color = 'style="background-color:'+data.color+'"';
        } else {
            var color = '';
        }
        var row =
            '<div class="news_block gray_block" '+color+'>';
        if (this.admin) {
            row += '<a onclick="FeedBelieve.del(\''+data.story_id+'\', this)">Удалить</a>';
        }
        row +=
            '<div style="float:right;"><div id="vk_like_' + data.story_id + '" class="like" data-id="'+data.story_id+'"></div></div>' +
                ' <p>' +
                '<a href="http://vk.com/id'+data.author+'" target="_blank"><img width=50 height=50 src="'+data.avatar+'" style="float:left;margin-right:5px;margin-bottom:5px;"/></a>'+
                '<a href="http://vk.com/id'+data.author+'" target="_blank">'+data.first_name+' '+data.last_name+'</a> говорит:</p>' +
                '<span class="news_header">'+ data.story.replace("'","")+'</span>';
        row +=  '<div style="clear:both"></div>'+
                '<div class="result">';
        if (data.vote) {
            row += '<div class="fl_l news_header value">' +
                '<span class="value">'+FeedBelieve.values[data.value]+'</span></div>'+
                '<p><span class="news_header">Верят: '+data.believe_count+'</span><br/>'+'<span class="news_header">Не верят: '+data.not_believe_count+'</span>';
        } else {
            row +=
                '<div class="fl_l button_blue button_wide" style="margin-left:130px;"><button onclick="Common.vote(\''+data.story_id+'\', this)" data-value="believe">Верю</button></div>'+
                '<div class="fl_r button_blue button_wide" style="margin-right:130px;"><button onclick="Common.vote(\''+data.story_id+'\', this)" data-value="not_believe">Не верю</button></div>';
        }
        row +=
                '</div>'+
                '<div style="clear:both"></div>'+
            '</div>';
        if (!data.nolike){
            var row = row + '<script type="text/javascript">VK.Widgets.Like("vk_like_'+data.story_id+'", {type:"mini", pageUrl:"'+FeedBelieve.server+'/intention/view/'+data.story_id+'", text:\'Мне понравилось желание "'+data.story+'"\'});</script>';
        }
        return row;
    },

    del: function(id, el) {
        var el = $(el).parent();
        $.post('/story/delete',{id:id}, function(data){
            if (data.success) {
                el.remove();
            } else {
                alert(data.message);
            }
        },'json');
    },

    addFotostrana: function() {
      var block =
        '<div class="news_block gray_block">' +
        ' <p>' +
        '   <a href="#" target="_blank">' +
        '     <img width="50" height="50" src="http://cs403324.vk.me/v403324161/74dd/_KfrFhNdons.jpg" style="float:left;margin-right:5px;margin-bottom:5px;">' +
        '   </a>' +
        '   <a href="#" target="_blank">Ильдар</a> говорит:' +
        ' </p>' +
        ' <span class="news_header">Здесь вы обязательно найдете вторую половинку или новых друзей!</span>' +
        ' <div style="clear:both"></div>' +
        ' <div class="result">' +
        '   <div class="fl_l button_blue button_wide" style="margin-left:130px;">' +
        '     <a href="http://cl.cpaevent.ru/51308f647355383e1c000004/" target="_blank">Верю</a>' +
        '   </div>' +
        '   <div class="fl_r button_blue button_wide" style="margin-right:130px;">' +
        '     <a href="http://cl.cpaevent.ru/51308f647355383e1c000004/" target="_blank">Не верю</a>' +
        '   </div>' +
        ' </div>' +
        ' <div style="clear:both"></div>' +
        '</div>';
      return block;
//      $(block).insertAfter(".news_block:first-child");
    }
}