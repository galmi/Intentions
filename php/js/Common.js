var Common = {

	searchInt:0, //Какая то переменная, обратится можно как Common.abc
	profileuser:{},
    like: null,

	getAppFriends:function (uids) {
		VK.api('friends.getAppUsers', function (data) {
				var friendsApp = {};
				friendsApp['friendsApp'] = data['response'];
				$.ajax({url:'/user/appusers', type:'POST', data:$.param(friendsApp)});
		});
	},

	getFriendsAppUserProfile:function () {
		VK.api('friends.getAppUsers', function (data) {
			if(data['response'].length == 0){
				$('#friendcount').html('<span>0 друзей</span>');
			}else{
				var friendsUidsApp = data['response'].join(',');
				VK.api("getProfiles", {uids:friendsUidsApp,fields:"uid,photo_rec"}, function (profiles) {
					$.ajax({url:'/user/wantscount', type:'POST', data:"friendsUidsApp=" + friendsUidsApp, dataType: 'json', success:function (wantscount) {
						var friends = '<div>';
						var friendcount = 0;
						$.each(profiles, function(responce, objFriend){
							var friend = {};
							$.each(objFriend, function(num, friend){
								friends += '<div class="fl_l people_cell">';
								$.each(friend, function(param, val){
									friend[param] = val;
									if(param === "uid"){
						                  try {
						                    friend['wantscount'] = (wantscount[val] == undefined) ? '0' : wantscount[val];
						                  } catch(e) {
						                    friend['wantscount'] = 0;
						                  }
									}
								});
								friends += '<a class="ava" onclick="Common.url(\'/user/view/' + friend['uid'] + '\')"> <img width="50" height="50" src="' + friend['photo_rec'] + '"> <span class="f_wants">' + friend['wantscount'] + '</span> </a>	<div class="name_field"> <a onclick="Common.url(\'/user/view/' + friend['uid'] + '\')">' + friend['first_name'] + '<br><small>' + friend['last_name'] + '</small></a></div>';
								friends += '</div>';
								friendcount = friendcount +1;
							});
						});
						friends += '</div>';
						$('#friendcount').html('<span>' + friendcount + ' друзей</span>');
						$("#ajax_friendsapp").prepend(friends);
					}});
				});
			}
		});

	},

  addIntention: function(){
    if ($.trim($("#s_iwant").val())!='') {
      $("#add_intention").show();
    }
  },

  cancelIntention: function(){
    $("#add_intention .checkbox").removeClass('on');
    $("#s_iwant").val('');
    $('#add_intention').hide();
  },

	saveIntention: function() {
		if (Common.searchInt == 1){
			return;
		}
		Common.searchInt = 1;
		var description = $.trim($('#send').attr('query'));
		if (description!='') {
			$(this).hide();
			$.post('/intention/add',{description:description, done:0}, function(data){
				if (data!=false && data!='false'){
					document.location.href="/intention/view/"+data;
				} else {
					alert('Ошибка сохранения');
				}
			},'json');
		}
	},

	intentionViewUid: function(inner){
		if(inner == 'Author'){
			var uid = [];
			$("[uid]").each(function(){
				uid[uid.length] = $(this).attr('uid');
			});
			uid = uid.join(',');
		}else{
			var uid = $('[uid]').attr('uid');
		}
		VK.api("getProfiles", {uids:uid,fields:"uid,photo_rec,city,online,sex"}, function (profiles) {
			$.each(profiles, function(responce, objFriend){
				$.each(objFriend, function(num, friend){
					$.each(friend, function(param, val){
						friend[param] = val;
					});
					if (inner == 'intentionAuthor'){
						$("[uid]").html('<a class="ava" width="30px" height="30px" onclick="Common.url(\'/user/view/' + friend['uid'] + '\')"> <img src="' + friend['photo_rec'] + '" title="' + friend['first_name'] + ' ' + friend['last_name'] + '"></a>');
					}
					if (inner == 'userFriend' || inner == 'Author'){
						if(friend['online'] == 0){
							var online = 'Не в сети';
						}
						if(friend['online'] == 1){
							var online = 'В сети';
						}
						var url = 'vk.com';
						VK.api("getCities", {cids:friend['city']}, function (city) {
							var friendInfo='<a target="_blank" href="http://'+ url +'/id'+ friend['uid'] +'">\
								<div class="ava fl_l"> <img src="' + friend['photo_rec'] + '"></div>\
								</a>\
									<div class="info">\
									<a target="_blank" href="http://'+ url +'/id'+ friend['uid'] +'">\
										<div class="label">Имя:</div>\
										<div class="labeled">' + friend['first_name'] + ' ' + friend['last_name'] + '</div>\
										</a>';
							if(city.response && city.response.length==1){
								friendInfo +='<div class="label">Город:</div>\
											<div class="labeled">' + city.response[0]['name'] + '</div>';
							}
							friendInfo +='<div class="online">' + online + '</div>\
									</div>'
							if(inner == 'Author'){
								$('[uid = '+ friend['uid'] +']').html(friendInfo);
							}
							if (inner == 'userFriend'){
								$("[uid]").html(friendInfo);
							}
						});
					}
				});
			});
            
		});


	},

	ratingViewUid: function(inner){
        var uid = [];
        $("[uid]").each(function(){
            uid[uid.length] = $(this).attr('uid');
        });
        uid = uid.join(',');
		VK.api("getProfiles", {uids:uid,fields:"uid,photo_rec,city,online,sex"}, function (profiles) {
			$.each(profiles, function(responce, objFriend){
				$.each(objFriend, function(num, friend){
					$.each(friend, function(param, val){
						friend[param] = val;
					});
                    if(friend['online'] == 0){
                        var online = 'Не в сети';
                    }
                    if(friend['online'] == 1){
                        var online = 'В сети';
                    }
                    VK.api("getCities", {cids:friend['city']}, function (city) {
                        var friendInfo='<a target="_blank" onclick="Common.url(\'/user/view/'+ friend['uid'] +'\')">\
                            <div class="ava fl_l"> <img src="' + friend['photo_rec'] + '"></div>\
                            </a>\
                                <div class="info">\
                                <a target="_blank" onclick="Common.url(\'/user/view/'+ friend['uid'] +'\')">\
                                    <div class="label">Имя:</div>\
                                    <div class="labeled">' + friend['first_name'] + ' ' + friend['last_name'] + '</div>\
                                    </a>';
                        if(city.response && city.response.length==1){
                            friendInfo +='<div class="label">Город:</div>\
                                        <div class="labeled">' + city.response[0]['name'] + '</div>';
                        }
                        friendInfo +='<div class="online">' + online + '</div>\
                                </div>';
                        $('[uid = '+ friend['uid'] +']').html(friendInfo);
                    });
				});
			});

		});


	},

	viewUser: function(callback){
		VK.api("getProfiles", {uids:Common.profileuser["uid"],fields:"uid,photo_rec"}, function (profiles) {
			$.each(profiles, function(responce, objFriend){
				$.each(objFriend, function(num, friend){
					$.each(friend, function(param, val){
						Common.profileuser[param] = val;
					});
				});
			});
			callback();
		});
	},

	intentionViewUsers: function(){
		var photo_done = [];
		var photo_wants = [];
		$("#done_li").children().each(function(){
			photo_done[photo_done.length] = $(this).attr("id");
		});
		$("#wants_li").children().each(function(){
			photo_wants[photo_wants.length] = $(this).attr("id");
		});
		var users = photo_wants.concat(photo_done);
		var friendsUidsApp = users.join(',');
		VK.api("getProfiles", {uids:friendsUidsApp,fields:"uid,photo_rec"}, function (profiles) {
				var friendcount = 0;
				var wants_photo = '<div>';
				var done_photo = '<div>';
				$.each(profiles, function(responce, objFriend){
					$.each(objFriend, function(num, friend){
						$.each(friend, function(param, val){
							friend[param] = val;
						});
							$('#' + friend['uid']).html('<a onclick="Common.url(\'/user/view/' + friend['uid'] + '\')"><img src="' + friend['photo_rec'] + '"/></a><div><a onclick="Common.url(\'/user/view/' + friend['uid'] + '\')">' + friend['first_name'] + ' ' + friend['last_name'] + '</a></div>');

						friendcount = friendcount+1;
						if (Common.profileuser["uid"] === friend['uid']) {
							Common.profileuser["photo_rec"] = friend['photo_rec'];
							Common.profileuser["first_name"] = friend['first_name'];
							Common.profileuser["last_name"] = friend['last_name'];
						}
						if (friendcount < 10 && friendcount <= photo_wants.length && photo_wants.length > 0) {
							if (friendcount < 9) {
								if (Common.profileuser["uid"] === friend['uid']) {
									wants_photo += '<a onclick="Common.url(\'/user/view/' + friend['uid'] + '\')"><img uid src="' + friend['photo_rec'] + '" title="' + friend['first_name'] + ' ' + friend['last_name'] + '"/></a>';
								}else{
									wants_photo += '<a onclick="Common.url(\'/user/view/' + friend['uid'] + '\')"><img src="' + friend['photo_rec'] + '" title="' + friend['first_name'] + ' ' + friend['last_name'] + '"/></a>';
								}
							} else{
								wants_photo += '<a style="display: none;" onclick="Common.url(\'/user/view/' + friend['uid'] + '\')"><img src="' + friend['photo_rec'] + '" title="' + friend['first_name'] + ' ' + friend['last_name'] + '"/></a>';
							}

						}
						if ((friendcount - photo_wants.length) < 10 && friendcount > photo_wants.length  && photo_done.length > 0) {
							if((friendcount - photo_wants.length) < 9){
								if (Common.profileuser["uid"] === friend['uid']) {
									done_photo += '<a onclick="Common.url(\'/user/view/' + friend['uid'] + '\')"><img uid src="' + friend['photo_rec'] + '" title="' + friend['first_name'] + ' ' + friend['last_name'] + '"/></a>';
								}else{
									done_photo += '<a onclick="Common.url(\'/user/view/' + friend['uid'] + '\')"><img src="' + friend['photo_rec'] + '" title="' + friend['first_name'] + ' ' + friend['last_name'] + '"/></a>';
								}
							} else {
								done_photo += '<a style="display: none;" onclick="Common.url(\'/user/view/' + friend['uid'] + '\')"><img src="' + friend['photo_rec'] + '" title="' + friend['first_name'] + ' ' + friend['last_name'] + '"/></a>';
							}
						}
					});
					wants_photo += '</div>';
					done_photo += '</div>';
					$('#done_photo').html(done_photo);
					$('#wants_photo').html(wants_photo);
				});
		});
	},

	checkboxes: function(){
		var iDone_checkbox = $('#iDone_checkbox');
		var iWants_checkbox = $('#iWants_checkbox');
		var handler = function() {
			//$(this).children('.checkbox').toggleClass('on');
			iDone_checkbox.children('.checkbox').toggleClass('on');
			iWants_checkbox.children('.checkbox').toggleClass('on');
		};

		var handlerDone = function() {
			var wants_count = 0;
			var done_count = 0;
			var uid = $("div[uid]").attr("uid");
			var elem = $(this).children('.checkbox');
			var id = elem.attr('data-id');
			if (elem.hasClass('on')) {
				var done = 0;
			} else {
				var done = 1;
			}
			$.post('/intention/doneview/'+id, {done:done}, function(data){
				if (data==true){
					if (done===0) {
						elem.removeClass('on');
						iDone_checkbox.removeClass('on');
						$("li#" + Common.profileuser["uid"] + "").remove();
						$("img[uid]").parent().remove();
						done_count = $("#done_li").children("li").length;
						$("#done_count").empty();
						$("#done_count").html(done_count);
						if(done_count == 0){
							$("done_list").css("visibility", "hidden");
						}
						$('#done_photo').children('div').children('a:eq(7)').show();
					} else {
						elem.addClass('on');
						iDone_checkbox.addClass('on');
						iWants_checkbox.parent().removeClass('on');
						iWants_checkbox.children('.checkbox').removeClass('on');
						$("li#" + Common.profileuser["uid"] + "").remove();
						$("img[uid]").parent().remove();
						if(Common.profileuser["photo_rec"] == undefined){
							Common.viewUser(function(){
								$('#done_photo').children('div').prepend('<a onclick="Common.url(\'/user/view/' +  Common.profileuser['uid'] + '\')"><img uid src="' + Common.profileuser["photo_rec"] + '"/></a>');
								$("#done_li").prepend('<li id=' + Common.profileuser["uid"] + '><a onclick="Common.url(\'/user/view/' +  Common.profileuser['uid'] + '\')"><img src="' + Common.profileuser["photo_rec"] + '"/></a><div><a onclick="Common.url(\'/user/view/' +  Common.profileuser['uid'] + '\')">'+Common.profileuser["first_name"] + ' ' + Common.profileuser["last_name"] + '</a></div></li>');
								$("#done_list").css("visibility", "visible");
								wants_count = $("#wants_li").children("li").length;
								$("#wants_count").empty();
								$("#wants_count").html(wants_count);
								done_count = $("#done_li").children("li").length;
								$("#done_count").empty();
								$("#done_count").html(done_count);
								if(wants_count == 0){
									$("#wants_list").css("visibility", "hidden");
								}
								$('#done_photo').children('div').children('a:eq(8)').hide();
								$('#wants_photo').children('div').children('a:eq(7)').show();
							});
						}else{
							$('#done_photo').children('div').prepend('<a onclick="Common.url(\'/user/view/' +  Common.profileuser['uid'] + '\')"><img uid src="' + Common.profileuser["photo_rec"] + '"/></a>');
							$("#done_li").prepend('<li id=' + Common.profileuser["uid"] + '><a onclick="Common.url(\'/user/view/' +  Common.profileuser['uid'] + '\')"><img src="' + Common.profileuser["photo_rec"] + '"/></a><div><a onclick="Common.url(\'/user/view/' +  Common.profileuser['uid'] + '\')">'+Common.profileuser["first_name"] + ' ' + Common.profileuser["last_name"] + '</a></div></li>');
							$("#done_list").css("visibility", "visible");
							wants_count = $("#wants_li").children("li").length;
							$("#wants_count").empty();
							$("#wants_count").html(wants_count);
							done_count = $("#done_li").children("li").length;
							$("#done_count").empty();
							$("#done_count").html(done_count);
							if(wants_count == 0){
								$("#wants_list").css("visibility", "hidden");
							}
							$('#done_photo').children('div').children('a:eq(8)').hide();
							$('#wants_photo').children('div').children('a:eq(7)').show();
						}
					}
				}
			}, 'json');
		};

		var handlerWants = function() {
			var wants_count = 0;
			var done_count = 0;
			var elem = $(this).children('.checkbox');
			var id = elem.attr('data-id');
			if (elem.hasClass('on')) {
				var done = 0;
			} else {
				var done = 1;
			}
			$.post('/intention/wantsview/'+id, {wants:done}, function(data){
				if (data==true){
					if (done===0) {
						elem.removeClass('on');
						iWants_checkbox.removeClass('on');
						$("li#" + Common.profileuser["uid"] + "").remove();
						$("img[uid]").parent().remove();
						wants_count = $("#wants_li").children("li").length;
						$("#wants_count").empty();
						$("#wants_count").html(wants_count);
						if(wants_count == 0){
							$("#wants_list").css("visibility", "hidden");
						}
						$('#wants_photo').children('div').children('a:eq(7)').show();

					} else {
						elem.addClass('on');
						iWants_checkbox.addClass('on');
						iDone_checkbox.removeClass('on');
						iDone_checkbox.children('.checkbox').removeClass('on');
						$("li#" + Common.profileuser["uid"] + "").remove();
						$("img[uid]").parent().remove();
						if(Common.profileuser["photo_rec"] == undefined){
							Common.viewUser(function(){
								$('#wants_photo').children('div').prepend('<a onclick="Common.url(\'/user/view/' +  Common.profileuser['uid'] + '\')"><img uid src="' + Common.profileuser["photo_rec"] + '"/></a>');
								$("#wants_li").prepend('<li id=' + Common.profileuser["uid"] + '><a onclick="Common.url(\'/user/view/' +  Common.profileuser['uid'] + '\')"><img src="' + Common.profileuser["photo_rec"] + '"/></a><div><a onclick="Common.url(\'/user/view/' +  Common.profileuser['uid'] + '\')">'+Common.profileuser["first_name"] + ' ' + Common.profileuser["last_name"] + '</a></div></li>');
								$("#wants_list").css("visibility", "visible");
								wants_count = $("#wants_li").children("li").length;
								$("#wants_count").empty();
								$("#wants_count").html(wants_count);
								done_count = $("#done_li").children("li").length;
								$("#done_count").empty();
								$("#done_count").html(done_count);
								if(done_count == 0){
									$("#done_list").css("visibility", "hidden");
								}
								$('#wants_photo').children('div').children('a:eq(8)').hide();
								$('#done_photo').children('div').children('a:eq(7)').show();
							});
						}else{
							$('#wants_photo').children('div').prepend('<a onclick="Common.url(\'/user/view/' +  Common.profileuser['uid'] + '\')"><img uid src="' + Common.profileuser["photo_rec"] + '"/></a>');
							$("#wants_li").prepend('<li id=' + Common.profileuser["uid"] + '><a onclick="Common.url(\'/user/view/' +  Common.profileuser['uid'] + '\')"><img src="' + Common.profileuser["photo_rec"] + '"/></a><div><a onclick="Common.url(\'/user/view/' +  Common.profileuser['uid'] + '\')">'+Common.profileuser["first_name"] + ' ' + Common.profileuser["last_name"] + '</a></div></li>');
							$("#wants_list").css("visibility", "visible");
							wants_count = $("#wants_li").children("li").length;
							$("#wants_count").empty();
							$("#wants_count").html(wants_count);
							done_count = $("#done_li").children("li").length;
							$("#done_count").empty();
							$("#done_count").html(done_count);
							if(done_count == 0){
								$("#done_list").css("visibility", "hidden");
							}
							$('#wants_photo').children('div').children('a:eq(8)').hide();
							$('#done_photo').children('div').children('a:eq(7)').show();
						}
					}
				}
			}, 'json');
		};

		iDone_checkbox.bind("click", handlerDone);
		iWants_checkbox.bind("click", handlerWants);
	},

	checkboxesFriend: function(){
		var iDone_checkbox = $('[done]');
		var iWants_checkbox = $('[wants]');

		var handlerDone = function() {
			var elem = $(this).children('.checkbox');
			var elemtext = $(this);
			var elemWantstext = elemtext.next().next();
			var elemWants = elemWantstext.children('.checkbox');
//			console.log(elemWantstext);
//			console.log(elemWants);
			var id = elem.attr('data-id');
			if (elem.hasClass('on')) {
				var done = 0;
			} else {
				var done = 1;
			}
			$.post('/intention/doneview/'+id, {done:done}, function(data){
				if (data==true){
					if (done===0) {
						elem.removeClass('on');
						elemtext.removeClass('on');
					} else {
						elem.addClass('on');
						elemtext.addClass('on');
						elemWantstext.removeClass('on');
						elemWants.removeClass('on');
					}
				}
			}, 'json');
		};

		var handlerWants = function() {
			var elem = $(this).children('.checkbox');
			var elemtext = $(this);
			var elemWantstext = elemtext.prev().prev();
			var elemWants = elemWantstext.children('.checkbox');
			var id = elem.attr('data-id');
			if (elem.hasClass('on')) {
				var done = 0;
			} else {
				var done = 1;
			}
			$.post('/intention/wantsview/'+id, {wants:done}, function(data){
				if (data==true){
					if (done===0) {
						elem.removeClass('on');
						elemtext.removeClass('on');
					} else {
						elem.addClass('on');
						elemtext.addClass('on');
						elemWantstext.removeClass('on');
						elemWants.removeClass('on');
					}
				}
			}, 'json');
		};

		iDone_checkbox.bind("click", handlerDone);
		iWants_checkbox.bind("click", handlerWants);
	},

  url : function(url) {
    document.location.href = url;
  },

    //Обработчик события клика "Мне нравится"
  initObserver: function() {
      if (!VK.Observer) {
          VK.Observer = {
            _subscribers: function() {
              if (!this._subscribersMap) {
                this._subscribersMap = {};
              }
              return this._subscribersMap;
            },
            publish: function(eventName) {
              var
                args = Array.prototype.slice.call(arguments),
                eventName = args.shift(),
                subscribers = this._subscribers()[eventName],
                i, j;

              if (!subscribers) return;

              for (i = 0, j = subscribers.length; i < j; i++) {
                if(subscribers[i] != null) {
                  subscribers[i].apply(this, args);
                }
              }
            },
            subscribe: function(eventName, handler) {
              var
                subscribers = this._subscribers();

              if(typeof handler != 'function') return false;

              if(!subscribers[eventName]) {
                subscribers[eventName] = [handler];
              } else {
                subscribers[eventName].push(handler);
              }
            },
            unsubscribe: function(eventName, handler) {
              var
                subscribers = this._subscribers()[eventName],
                i, j;

              if (!subscribers) return false;
              if (typeof handler == 'function') {
                for (i = 0, j = subscribers.length; i < j; i++) {
                  if (subscribers[i] == handler) {
                    subscribers[i] = null;
                  }
                }
              } else {
                delete this._subscribers()[eventName];
              }
            }
          }
        }

      VK.Observer.subscribe('widgets.like.unliked',function(){
          if (Common.like) {
              $.post('/intention/wantsview/'+Common.like, {wants:0});
          }
      });
      VK.Observer.subscribe('widgets.like.liked',function(){
          if (Common.like) {
              $.post('/intention/wantsview/'+Common.like, {wants:1});
          }
      });
      $('.like').live('mouseover', function(){
          Common.like = $(this).attr('data-id');
      });
  },

	searchGo : function(){
		var handlerWants =function(){
			var id = $(this).attr('data-id');
			$.post('/intention/wantsview/'+id, {wants:1}, function(data){
				Common.url('/intention/view/' + id);
			}, 'json');
		}
		$('a[data-id]').bind("click", handlerWants);
	},

    showColor: function(){
        VK.api('getUserBalance', function(response){
            console.log(response);
            if (response.error) {
                alert('Ошибка приложения');
            } else if(response.response>=100) {
                $(".bg").css('width', $('html').width());
                var height = $('body').height();
                if (height<500) {
                    var height = 500;
                }
                $(".bg").css('height', height);
                $(".super").css('display', 'block');
            } else {
                alert('Необходимо пополнить баланс приложения на 1 голос');
            }
        });
    },

    closeColor: function() {
        $(".super").css('display', 'none');
    },

    sendColor: function(){
        var status = $('#a_create').data('hold');
        if (status) {
            return false;
        } else {
            $("#a_create").data('hold',1);
            $("#a_create").css('cursor','wait');
        }
        var color = $('#color').val();
        var id = $("#intention_id").val();
        if (!color) {
            alert('Выберите цвет');
            return;
        }
        $.post('/intention/super/'+id,{color:color}, function(data){
            $("#a_create").data('hold','');
            $("#a_create").css('cursor','pointer');
            if (data.error) {
                alert(data.error);
            }
            document.location.reload();
        },'json');
    },

    changeComment: function(intentionId){
        $.post('/index/newcomment',{intention_id:intentionId});
    },

  animateHighlight: function (elem, highlightColor, duration) {
      var highlightBg = highlightColor || "#FFFF9C";
      var animateMs = duration || 1000;
      var originalBg = elem.css("background-color");

      if (!originalBg || originalBg == highlightBg)
          originalBg = "#FFFFFF"; // default to white

      jQuery(elem)
          .css("backgroundColor", highlightBg)
          .animate({ backgroundColor: originalBg }, animateMs, null, function () {
              jQuery(this).css("backgroundColor", originalBg);
          });
  },

  addStory: function(elText, elButton) {
    var text = $.trim($(elText).val());
    var believe = $('.mystory.checkbox.on').attr('data-value');
    var button = $(elButton);

    if (text.length==0) {
      alert('Напиши историю');
      return;
    }
    if (believe!='believe' && believe!='not_believe') {
      alert('Отметь Правда или Ложь');
      return;
    }

    button.hide();
    button.parent().find('img').show();
    $.post('/story/add', {story:text, value:believe}, function(response){
      if (response.success==true) {
        document.location.reload();
      } else {
        alert(response.error);
      }
      button.parent().find('img').hide();
      button.show();
    },'json');
  },

  vote: function(story_id, el) {
    var value = $(el).attr('data-value');
    var prnt = $(el).parent().parent();
    prnt.html('');
    $.post('/story/vote', {id: story_id, value:value},function(response){
      if (response.success==true) {
        var data = response.data;
        var result = '<div class="fl_l news_header value">' +
          '<span class="value">'+FeedBelieve.values[data.value]+'</span></div>'+
          '<p><span class="news_header">Верят: '+data.believe_count+'</span><br/>'+'<span class="news_header">Не верят: '+data.not_believe_count+'</span>';
        prnt.html(result);
      }
    },'json');
  }

}