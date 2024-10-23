/**
 * @file
 * Defines the behavior of the media entity browser view.
 */

(function($) {
  "use strict";

  /**
   * Attaches the behavior of the media entity browser view.
   */
  Drupal.behaviors.customPreventLoseData = {
    attach: function(context, settings) {
      window.arrayInputChanged = {};
      $(":input, textarea").on("input", function() {
        if (this.value === "" || this.value === $(this).prop("defaultValue")) {
          delete window.arrayInputChanged[$(this).attr("id")];
        } else {
          window.arrayInputChanged[$(this).attr("id")] = true;
        }
      });

      // ask user when user go to another page or reload
      window.onbeforeunload = function(e) {
        $("#" + Object.keys(window.arrayInputChanged)[0])[0].focus();
        if (Object.keys(window.arrayInputChanged).length) {
          e.returnValue = "input changed";
        }
      };

      // ask user when user choose from dropdown
      var prev_val = "";
      $("select#edit-purpose")
        .focus(function() {
          prev_val = $(this).val();
        })
        .once("select2:selecting")
        .on("select2:selecting", function(e) {
          if (Object.keys(window.arrayInputChanged).length) {
            $(this).blur(); // Firefox fix as suggested by AgDude
            var success = confirm(
              "Are you sure you want to change the Dropdown?"
            );
            if (success) {
              // Other changed code would be here...
              window.arrayInputChanged = {};
            } else {
              $(this).val(prev_val);
              e.preventDefault();
              $("#" + Object.keys(window.arrayInputChanged)[0])[0].focus();
              $('#edit-purpose').select2().trigger("select2:close");
            }
          }
        });

      $("input[type='submit']").once("click").on("click", function (e) {
        window.onbeforeunload = null;
      });

      // ask user when user click submit button
      // $("input[type='submit']").not(".use-ajax").not("[value='Subscribe']")
      //   .once("click")
      //   .on("click", function(e) {
      //     if (Object.keys(window.arrayInputChanged).length) {
      //       var success = confirm("Are you sure you want to update data ?");
      //       if (success) {
      //         // Other changed code would be here...
      //         window.onbeforeunload = null;
      //         window.arrayInputChanged = {};
      //         $(this).closest("form")[0].submit();
      //       } else {
      //         e.preventDefault();
      //         $("#" + Object.keys(window.arrayInputChanged)[0])[0].focus();
      //       }
      //     }
      //   }); 
    }
  };
})(jQuery, Drupal);