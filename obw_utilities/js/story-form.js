(function($, Drupal, drupalSettings) {

    $("form.role-writer #edit-submit").on('click', function (e) {
        var choice = confirm('Your story will be submitted to the Content Admin. Any further edits will have to be managed by the Content Admin');
        if(!choice){
            e.preventDefault();
            return false;
        }else{
            return true;
        }
    });

    $('form.role-writer #edit-discard').on('click', function (e) {
      e.preventDefault();
      var choice = confirm('All data will be lost and will have to click a button "OK" to proceed or "Cancel" to resume editing');
      if(!choice){
        return false;
      }else{
        redirect('/admin/writer');
      }
    });

  // $("form.role-content-admin #edit-submit").on('click', function (e) {
  //   var choice = confirm('Your story will be submitted to the Content Admin. Any further edits will have to be managed by the Content Admin');
  //   if(!choice){
  //     e.preventDefault();
  //     return false;
  //   }else{
  //     return true;
  //   }
  // });

    $('form.role-content-admin #edit-discard').on('click', function (e) {
      e.preventDefault();
      var choice = confirm('All data will be lost and will have to click a button "OK" to proceed or "Cancel" to resume editing');
      if(!choice){
        return false;
      }else{
        redirect('/admin/content-admin');
      }
    });

    function redirect (url) {
        var ua = navigator.userAgent.toLowerCase()
        var isIE = ua.indexOf('msie') !== -1
        var  version = parseInt(ua.substr(4, 2), 10); // Internet Explorer 8 and lower
        if (isIE && version < 9) {
            var link = document.createElement('a');
            link.href = url;
            document.body.appendChild(link); link.click();
        } // All other browsers can use the standard window.location.href (they don't lose HTTP_REFERER like Internet Explorer 8 & lower does)
        else { window.location.href = url; }
    }
})(jQuery, Drupal, drupalSettings);

// (function ($) {
//     Drupal.behaviors.obw_utilities = {
//         attach: function(context, settings) {
//             $(".role-writer#node-story-form").on('submit', function (e) {
//                 e.preventDefault();
//                 $( "#dialog" ).dialog();
//                 return false;
//             });
//             $(".role-writer#node-story-edit-form").on('submit', function (e) {
//                 e.preventDefault();
//                 $( "#dialog" ).dialog();
//                 return false;
//             });
//         }
//     };
// })(jQuery);