(function ($, Drupal, drupalSettings) {
  var running_count = 1;

  var element_event_loaded = {
    element_type: 'event_load',
    url_pattern: /^\/events\/([a-zA-Z0-9\-]+)/i,
    common_fields: {
      // event: 'form_start',
      // eventCategory: 'Stories',
      // eventAction: 'Registration Start',
      // contentType: 'form',
      // contentName: 'registration form',
      // formStarts: 1
    },
    list_elements: [
    ]
  };
  
  var element_event_submit = {
    element_type: 'event_submit',
    url_pattern: /^\/events\/([a-zA-Z0-9\-]+)/i,
    common_fields: {
      event: 'form_submit',
      eventCategory: 'Stories',
      eventAction: 'Registration Submit',
      contentType: 'form',
      contentName: 'registration form',
      formSubmits: 1
    },
    list_elements: [
    ]
  };

  var element_story_support_submit = {
    element_type: 'story_support',
    url_pattern: /^\/story\/(\S+)\/support\/(\S+)$/i,
    common_fields: {
      event: 'form_submit',
      eventCategory: 'Stories',
      eventAction: 'Support Form Submit',
      contentType: 'form',
      contentName: 'story support form',
      formSubmits: 1
    },
    list_elements: [
    ]
  };
  var element_contact_us_submit = {
    element_type: 'story_support',
    url_pattern: /^\/contact-us(\S*)$/i,
    common_fields: {
      event: 'form_submit',
      eventAction: 'Form Submit',
      contentType: 'form',
      formSubmits: 1
    },
    list_elements: [
    ]
  };

  var config_form_load_regex = {
    event: element_event_loaded.url_pattern
  }

  function handle_form_load(form_type) {
    var current_element = {};
    var item = {};
    var custom_dynamic_fields = {};

    if (form_type === 'event') {
      current_element = element_event_loaded;

      var form_name = drupalSettings.tracking_seo.form_name;
      story_id      = drupalSettings.tracking_seo.story_id;

      if (story_id) {
        custom_dynamic_fields.contentID = story_id;
      }
      custom_dynamic_fields.contentName = form_name;
    }

    Drupal.custom.trackingCommon.handle_layer_item(
      current_element,
      item,
      custom_dynamic_fields
    );
  }

  function handle_form_submit_succcess () {
    var current_element = {};
    var item = {};
    var custom_dynamic_fields = {};

    var story_support_pattern = element_story_support_submit.url_pattern;
    var contact_us_pattern    = element_contact_us_submit.url_pattern;
    var event_pattern         = element_event_submit.url_pattern;

    var story_id = {};
    var current_path = window.location.pathname;

    if (story_support_pattern.test(current_path)) {
      current_element = element_story_support_submit;

      var story_title   = drupalSettings.tracking_seo.story_title;
      var support_title = drupalSettings.tracking_seo.support_title;
      story_id          = drupalSettings.tracking_seo.story_id;

      custom_dynamic_fields.eventLabel = story_title + " | " + support_title;
      custom_dynamic_fields.contentID  = story_id;
    }
    if (contact_us_pattern.test(current_path)) {
      current_element = element_contact_us_submit;

      var form_name = drupalSettings.tracking_seo.form_name;
      story_id      = drupalSettings.tracking_seo.story_id;

      if (story_id) {
        custom_dynamic_fields.contentID = story_id;
      }
      custom_dynamic_fields.eventCategory = "Enquiry";
      custom_dynamic_fields.eventLabel  = form_name;
      custom_dynamic_fields.contentName = form_name;
    }
    if (event_pattern.test(current_path)) {
      current_element = element_event_submit;

      var form_name = drupalSettings.tracking_seo.form_name;
      story_id      = drupalSettings.tracking_seo.story_id;

      if (story_id) {
        custom_dynamic_fields.contentID = story_id;
      }
      custom_dynamic_fields.contentName = form_name;
    }

    Drupal.custom.trackingCommon.handle_layer_item(
        current_element,
        item,
        custom_dynamic_fields
    );
  }

  Drupal.behaviors.form = {
    attach: function(context, settings) {
      if (running_count !== 1) {
        return;
      }
      running_count = running_count + 1;
      var current_path = window.location.pathname;
      var _this = $;

      // console.log("------ FORM.js ------");
      // console.time("[time] FORM.js");

      var form_load_types = Object.keys(config_form_load_regex);

      for (var index = 0; index < form_load_types.length; index++) {
        var form_type   = form_load_types[index];
        var url_pattern = config_form_load_regex[form_type];
        if (url_pattern.test(current_path)) {
          handle_form_load(form_type);
          break;
        }
      }

      // for only Action –Support Submission
      _this("main form input[type='submit']").click(function (event) {
        var $current_form = _this("main form");
        setTimeout(function() {
          if ($current_form.hasClass("success")) {
            handle_form_submit_succcess();
          }
        }, 700);
      });
    }
  };
})(jQuery, Drupal, drupalSettings);