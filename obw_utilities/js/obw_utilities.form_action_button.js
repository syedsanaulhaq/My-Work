(function ($) {

  "use strict";

  /**
   * Attaches the behavior of the media entity browser view.
   */
  Drupal.behaviors.btnDiscard = {
    attach: function (context, settings) {
      $('.btn-discard', context).once().click(function (e) {
        e.preventDefault();
        var confirm_discard = confirm(Drupal.t('All data will be lost and will have to click a button "OK" to proceed or "Cancel" to resume editing'));
        if(!confirm_discard){
          return false;
        }

        window.history.back();
        return false;
      });
    }
  };

  Drupal.behaviors.btnCancel = {
    attach: function (context, settings) {
      $('.btn-cancel', context).once().click(function (e) {
        e.preventDefault();

        window.history.back();
        return false;
      });
    }
  };

}(jQuery, Drupal));

