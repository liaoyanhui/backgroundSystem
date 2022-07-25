/*
 * @Description: 
 * @Author: 尚夏
 * @Date: 2022-05-30 14:59:26
 * @LastEditTime: 2022-07-13 17:42:39
 * @FilePath: /baiying/public/assets/js/backend-init.js
 */
define(['backend'], function (Backend) {
  $('body').on('click', '[data-image-show]', function () {
    var img = new Image();
    var imgWidth = this.getAttribute('data-width') || '720px';
    img.onload = function () {
      var $content = $(img).appendTo('body').css({ background: '#fff', width: imgWidth, height: 'auto' });
      Layer.open({
        type: 1, area: imgWidth, title: false, closeBtn: 1,
        skin: 'layui-layer-nobg', shadeClose: true, content: $content,
        end: function () {
          $(img).remove();
        },
        success: function () {

        }
      });
    };
    img.onerror = function (e) {

    };
    const _s = window.Config.upload.cdnurl
    img.src = this.getAttribute('data-image-show') || this.src;
  });
});