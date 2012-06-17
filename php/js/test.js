  VK.Widgets.Like = function(objId, options, page) {
    var pData = VK.Util.getPageData();
    if (!VK._apiId) throw Error('VK not initialized. Please use VK.init');
    options = VK.extend(options || {}, {allowTransparency: true});
    if (options.type == 'button' || options.type == 'vertical' || options.type == 'mini') delete options.width;
    var
      type = (options.type == 'full' || options.type == 'button' || options.type == 'vertical' || options.type == 'mini') ? options.type : 'full',
      width = type == 'full' ? Math.max(200, options.width || 350) : (type == 'button' ? 180 : (type == 'mini' ? 100 : 41)),
      btnHeight = parseInt(options.height) || 22,
      height = type == 'vertical' ? (2 * btnHeight + 7) : (type == 'full' ? btnHeight + 1 : btnHeight),
      params = {
        page: page || 0,
        url: options.pageUrl || pData.url,
        type: type,
        verb: options.verb == 1 ? 1 : 0,
        title: options.pageTitle || pData.title,
        description: options.pageDescription || pData.description,
        image: options.pageImage || pData.image,
        text: (options.text || '').substr(0, 140),
        h: btnHeight
      },
      ttHere = options.ttHere || false,
      isOver = false,
      obj, buttonIfr, buttonRpc, tooltipIfr, tooltipRpc, checkTO, statsBox;

    VK.Widgets._constructor('widget_like.php', objId, options, params, {
      initTooltip: function (counter) {
        tooltipRpc = new fastXDM.Server({
          onInit: counter ? function() {showTooltip(true)} : function () {},
          proxy: function () {
             buttonRpc.callMethod.apply(buttonRpc, arguments);
          },
          showBox: function (url, props) {
            var box = VK.Util.Box((options.base_domain || 'http://vkontakte.ru/') + url, [props.width, props.height], {
              proxy: function () {
                tooltipRpc.callMethod.apply(tooltipRpc, arguments);
              }
            });
            box.show();
          },
          statsBox: function (act) {
            hideTooltip(true);
            statsBox = VK.Util.Box(buttonIfr.src + '&act=a_stats_box', [498, 442]);
            statsBox.show();
          }
        });
        tooltipIfr = tooltipRpc.append(ttHere ? obj : document.body, {
          src: buttonIfr.src + '&act=a_share_tooltip',
          scrolling: 'no',
          allowTransparency: true,
          id: buttonIfr.id + '_tt',
          style: {position: 'absolute', padding: 0, display: 'block', opacity: 0.01, filter: 'alpha(opacity=1)', border: '0', width: '206px', height: '127px', zIndex: 5000, overflow: 'hidden'}
        });
        tooltipIfr.setAttribute('vkhidden', 'yes');

        obj.onmouseover = tooltipIfr.onmouseover = function () {isOver = true;};
        obj.onmouseout = tooltipIfr.onmouseout = function () {
          clearTimeout(checkTO);
          isOver = false;
          checkTO = setTimeout(function () {hideTooltip(); }, 200);
        };
      },
      showTooltip: showTooltip,
      hideTooltip: hideTooltip,
      showBox: function (url, props) {
        var box = VK.Util.Box((options.base_domain || 'http://vkontakte.ru/') + url, [props.width, props.height], {
          proxy: function () {
            buttonRpc.callMethod.apply(buttonRpc, arguments);
          }
        });
        box.show();
      },
      proxy: function () {if (tooltipRpc) tooltipRpc.callMethod.apply(tooltipRpc, arguments);}
    }, {
      startHeight: height + 'px',
      minWidth: width
    }, function (o, i, r) {
      buttonRpc = r;
      VK.Util.ss(obj = o, {height: height + 'px', width: width + 'px', position: 'relative', clear: 'both'});
      VK.Util.ss(buttonIfr = i, {height: height + 'px', width: width + 'px', overflow: 'hidden', zIndex: 150});
    });

    function showTooltip(force) {
      if ((!isOver && !force) || !tooltipRpc) return;
      if (!tooltipIfr || !tooltipRpc || tooltipIfr.style.display != 'none' && tooltipIfr.getAttribute('vkhidden') != 'yes') return;
      var scrollTop = options.getScrollTop ? options.getScrollTop() : (document.body.scrollTop || document.documentElement.scrollTop || 0), objPos = VK.Util.getXY(obj, options.fixed), startY = ttHere ? 0 : objPos[1];
      if (scrollTop > objPos[1] - 120 && options.tooltipPos != 'top' || type == 'vertical' || options.tooltipPos == 'bottom') {
        tooltipIfr.style.top = (startY + height + 2) + 'px';
        tooltipRpc.callMethod('show', false);
      } else {
        tooltipIfr.style.top = (startY - 125) + 'px';
        tooltipRpc.callMethod('show', true);
      }
      VK.Util.ss(tooltipIfr, {left: ((ttHere ? 0 : objPos[0]) - (type == 'vertical' || type == 'mini' ? 36 : 2)) + 'px', display: 'block', opacity: 1, filter: 'none'});
      tooltipIfr.setAttribute('vkhidden', 'no');
      isOver = true;
    };
    function hideTooltip(force) {
      if ((isOver && !force) || !tooltipRpc) return;
      tooltipRpc.callMethod('hide');
      buttonRpc.callMethod('hide');
      setTimeout(function () {
        tooltipIfr.style.display = 'none'
      }, 400);
    };
  }
