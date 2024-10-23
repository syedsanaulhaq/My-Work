(function($, Drupal, drupalSettings) {
  function openPopup(el) {
    // get the class name in arguments here
    $.magnificPopup.open({
      items: {
        src: "#submission-popup-modal"
      },
      type: "inline"
    });
  }

  $(document).on("click", ".popup-modal-dismiss", function(e) {
    e.preventDefault();
    $.magnificPopup.close();
  });

  var popupModal =
    '<div id="submission-popup-modal" class="white-popup-block mfp-hide">\n' +
    "  <h1>Edit Submission</h1>\n" +
    '  <div id="form-update-submission">\n' +
    '    <input name="sid" id="submission-sid" class="field-hide" type="text">\n' +
    '    <div class="select">\n' +
    "    <label>Status</label>" +
    '    <select name="status" id="submission-status">\n' +
    '      <option value="to_review">To review</option>\n' +
    '      <option value="reject">Reject</option>\n' +
    '      <option value="social_media">Social Media</option>\n' +
    '      <option value="obw_site">OBW site</option>\n' +
    '      <option value="completed">Completed</option>\n' +
    "    </select>\n" +
    "    </div>\n" +
    '    <div class="btn-group clearfix">\n' +
    '      <button class="btn btn-submit" type="button">Submit</button>\n' +
    "    </div>\n" +
    "  </div>\n" +
    "</div>";
  $(".view-content").append(popupModal);

  $("a.show-popup-modal").on("click", function() {
    var sid = $(this).data("sid");
    var status = $(this).data("status");

    $("input#submission-sid").val(sid);
    $("select#submission-status option").each(function() {
      if (status === $(this).text()) {
        $(this).attr("selected", "selected");
      } else {
        $(this).removeAttr("selected");
      }
    });
    openPopup(this.className);
  });

  $("#form-update-submission .btn-submit").on("click", function() {
    var parmas = {
      sid: $("input#submission-sid").val(),
      status: $("select#submission-status").val()
    };
    $.ajax({
      url: "/action/update-submission",
      type: "POST",
      data: JSON.stringify(parmas),
      dataType: "json",
      success: function success(results) {
        console.log(results);
        location.reload(true);
        // alert(results.message);
      },
      error: function error(xmlhttp) {
        console.log(xmlhttp);
      }
    });
  });

  function setTopSticky(element) {
    var toolbarHeight = $("#toolbar-bar").outerHeight();
    setTimeout(() => {
      if (
        $(element)
          .find(".toolbar-tray")
          .hasClass("is-active")
      ) {
        var height =
          $(element)
            .find(".toolbar-tray")
            .outerHeight() +
          toolbarHeight +
          "px";
        $(
          "#views-form-result-of-campaign-humankind-campaign table thead th"
        ).css("top", height);
      } else {
        $(
          "#views-form-result-of-campaign-humankind-campaign table thead th"
        ).css("top", toolbarHeight + "px");
      }
    }, 300);
  }
  $(document).ready(function() {
    if ($("#toolbar-bar").length > 0) {
      $("#toolbar-bar .toolbar-tab").each(function() {
        setTopSticky(this);
      });

      $("#toolbar-bar .toolbar-tab").on("click", function() {
        setTopSticky(this);
      });
    }
  });
})(jQuery, Drupal, drupalSettings);
