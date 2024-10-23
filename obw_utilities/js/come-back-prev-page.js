(function ($) {
  var changedAttr = false;

  Drupal.behaviors.comeBackPrevPage = {
    attach: function (context) {
      $(document).ready(function () {
        var $login = $('#header .user-group ul li.user-button-group a.login-btn');
        var $signup = $('#header .user-group ul li.user-button-group a.sign-up-btn');
        if(!changedAttr && $login.length > 0 && $signup.length > 0) {
          changedAttr = true;
          $login.attr('href', $login.attr('href') + '&come_back=true');
          $signup.attr('href', $signup.attr('href') + '&come_back=true');

          $('li.user-button-group a.login-btn.login-popup-form').once().click(function (e) {
            drupalSettings.come_back = false;
          });

          $('li.user-button-group a.sign-up-btn.use-ajax').once().click(function () {
            drupalSettings.come_back = false;
          });

          Drupal.ajax.instances.forEach(function(item) {
            if(!item.selector && (item.url === '/user/login?destination=' + window.location.pathname || item.url === '/user/register?destination=' + window.location.pathname)) {
              item.options.url += '&come_back=true';
            }
          });
          console.log('Added attr comeback');
        }
      });
    }
  }

})(jQuery, Drupal, drupalSettings);
