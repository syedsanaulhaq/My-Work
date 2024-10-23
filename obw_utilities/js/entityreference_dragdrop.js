(function($){
  /**
   * Fix issue cannot search the entity reference drop down field
   * Override filter function in /web/modules/contrib/entityreference_dragdrop/js/entityreference_dragdrop.js
   *
   */
  // Filters items in a widget.
  Drupal.entityreference_dragdrop.filter = function (input) {
    var $input = $(input);
    var val = $input.val().toLowerCase();
    // console.log('aaaaa');

    if (val != '') {
      // console.log($input.parents('.entityreference-dragdrop-container').find('li.ui-sortable-handle'));
      $input.parents('.entityreference-dragdrop-container').find('li').each(function(i, elem) {
        var $elem = $(elem);

        // console.log($elem.data('label').toLowerCase() + ' ------- ' +  $elem.data('label').toLowerCase());

        if ($elem.data('label').toLowerCase().indexOf(val) >= 0) {
          $elem.show();
        }
        else {
          $elem.hide();
        }
      });
    }
    else {
      $input.parents('.entityreference-dragdrop-container').find('li').show();
    }

  };

})(jQuery);
