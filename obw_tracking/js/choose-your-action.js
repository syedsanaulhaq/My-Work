(function ($, Drupal, drupalSettings) {
  var running_count = 1;

  var element_cta_submit = {
    element_type: "story_support",
    common_fields: {
      event: "choose_action",
      eventCategory: "Choose Action"
    },
    static_fields: {
      action_elements: {
        eventAction: "Select Actions"
      },
      causes_elements: {
        eventAction: "Select Causes"
      },
      countries_elements: {
        eventAction: "Select Countries"
      }
    },
    action_elements: [
      {
        id: "#edit-your-actions--wrapper input[checked='checked']",
        element_type: "selected"
      },
      {
        id: "#edit-your-actions--wrapper input:not([checked='checked'])",
        element_type: "un_selected"
      }
    ],
    causes_elements: [
      {
        id: "#edit-causes--wrapper input[checked='checked']",
        element_type: "selected"
      },
      {
        id: "#edit-causes--wrapper input:not([checked='checked'])",
        element_type: "un_selected"
      }
    ],
    countries_elements: [
      {
        id: "#edit-countries--wrapper input[checked='checked']",
        element_type: "selected"
      },
      {
        id: "#edit-countries--wrapper input:not([checked='checked'])",
        element_type: "un_selected"
      }
    ],
    list_elements: [
      {
        id: "a.next-quiz, a.preview-quiz"
      }
    ]
  };
  var element_cta_result = {
    element_type: "cta_result",
    common_fields: {
      event: "choose_action",
      eventCategory: "Choose Action",
      eventAction: "Result Views"
    },
    list_elements: []
  };
  var element_cta_result_click = {
    element_type: "cta_result_click",
    common_fields: {
      event: "choose_action",
      eventCategory: "Story Clickthrough",
      eventAction: "Choose Action"
    },
    list_elements: [
      {
        id: ".story-card .entry-head > a",
        story_title_id: ".entry-head h3 a"
      },
      {
        id: ".story-card .read-more > a",
        story_title_id: ".entry-head h3 a"
      }
    ]
  };
  var element_cta_save_preferences = {
    element_type: "cta_save_preferences",
    common_fields: {
      event: "choose_action",
      eventCategory: "Choose Action",
      eventAction: "Save Preference"
    },
    list_elements: [
      {
        id: "footer a.submit-quiz",
      }
    ]
  };

  var cta_action_field_id     = "#edit-your-actions--wrapper";
  var cta_causes_field_id     = "#edit-causes--wrapper";
  var cta_countries_field_id  = "#edit-countries--wrapper";

  function handle_item_click(element, action_name, checked_item, unchecked_item) {
    element[action_name].forEach(function (item) {
      var jquery_item = $(item.id);
      if (jquery_item.length > 0) {
        // @TODO add selected items
      }

      if (item.element_type === "selected") {
        jquery_item.each(function() {
          var label = $(this).next().text().trim();
          checked_item[label] = true;
          unchecked_item[label] = false;
        });
      }

      jquery_item.click(function () {
        var label_selecter = "label[for='" + this.id + "']";
        var label = $(label_selecter).text().trim();
        var is_input_unchecked = {};
        var is_input_checked = {};

        if (checked_item[label] === undefined) {
          checked_item[label] = true;
        } else {
          checked_item[label] = !checked_item[label];
        }

        if (unchecked_item[label] !== undefined) {
          unchecked_item[label] = !unchecked_item[label];
        }
      });
    });
  }

  function get_keys_used (object) {
    var used_key = [];
    var all_keys = Object.keys(object);
    used_key = all_keys.filter(function(key) {
      return object[key] === true;
    });
    return used_key;
  }

  Drupal.behaviors.chooseYourAction = {
    attach: function() {
      if (running_count !== 1) {
        return;
      }
      running_count = running_count + 1;
      var _this = $;

      var actions_object    = drupalSettings.whoYouCanHelp.your_actions;
      var causes_object     = drupalSettings.whoYouCanHelp.causes;
      var countries_object  = drupalSettings.whoYouCanHelp.countries;

      var actions_array   = Object.values(actions_object);
      var causes_array    = Object.values(causes_object);
      var countries_array = Object.values(countries_object);

      var actions_label   = actions_array.join(",");
      var causes_label    = causes_array.join(",");
      var countries_label = countries_array.join(",");

      // console.log("------ CHOOSE-YOUR-ACTION.js ------");
      // console.time("[time] CHOOSE-YOUR-ACTION.js");

      // cta form click config
      var $cta_action_field     = _this(cta_action_field_id);
      var $cta_causes_field     = _this(cta_causes_field_id);
      var $cta_countries_field  = _this(cta_countries_field_id);

      //  init selected and not select item
      var checked_item    = {};
      var unchecked_items = {};

      if ($cta_action_field.length > 0) {
        handle_item_click(element_cta_submit,
            "action_elements",
            checked_item,
            unchecked_items
        );
      }
      if ($cta_causes_field.length > 0) {
        handle_item_click(element_cta_submit,
            "causes_elements",
            checked_item,
            unchecked_items
        );
      }
      if ($cta_countries_field.length > 0) {
        handle_item_click(element_cta_submit,
            "countries_elements",
            checked_item,
            unchecked_items
        );
      }
      Drupal.custom.trackingCommon.handle_element(
          _this,
          element_cta_submit,
          function(element) {
            var custom_dynamic_fields = {};

            var all_labels_array = [];
            var selected_array = [];
            var unselected_array = [];

            var checked_labels = [];
            var unselected_labels = [];

            var jquery_actions_label    = "#edit-your-actions--wrapper label";
            var jquery_causes_label     = "#edit-causes--wrapper label";
            var jquery_countries_label  = "#edit-countries--wrapper label";

            selected_array = get_keys_used(checked_item);
            unselected_array = get_keys_used(unchecked_items);

            checked_labels = selected_array.join(",");
            unselected_labels = unselected_array.join(",");

            // custom_dynamic_fields.
            custom_dynamic_fields.eventLabel =
                "Select:" + checked_labels + " | " + "Remove:" + unselected_labels;

            var event_action  = '';
            var cta_actions   = sessionStorage.getItem("ChooseYourAction:actions")    ? sessionStorage.getItem("ChooseYourAction:actions")    : "";
            var cta_causes    = sessionStorage.getItem("ChooseYourAction:causes")     ? sessionStorage.getItem("ChooseYourAction:causes")     : "";
            var cta_countries = sessionStorage.getItem("ChooseYourAction:countries")  ? sessionStorage.getItem("ChooseYourAction:countries")  : "";

            if ($cta_action_field.length > 0) {
              event_action = element.static_fields.action_elements.eventAction;
              cta_actions = checked_labels;
              sessionStorage.setItem("ChooseYourAction:actions", checked_labels);
            }
            if ($cta_causes_field.length > 0) {
              event_action = element.static_fields.causes_elements.eventAction;
              cta_causes = checked_labels;
              sessionStorage.setItem("ChooseYourAction:causes", checked_labels);
            }
            if ($cta_countries_field.length > 0) {
              event_action = element.static_fields.countries_elements.eventAction;
              cta_countries = checked_labels;
              sessionStorage.setItem("ChooseYourAction:countries", checked_labels);
            }

            custom_dynamic_fields.eventAction = event_action;
            custom_dynamic_fields.storyPreference =
                "actions="    + cta_actions +
                ";causes="    + cta_causes +
                ";countires=" + cta_countries +
                ";tags=";

            return custom_dynamic_fields;
          }
      );

      // console.time("[time] result view and save preference");
      if (_this(".results-who-you-can-help").length > 0) {
        sessionStorage.removeItem("ChooseYourAction:actions");
        sessionStorage.removeItem("ChooseYourAction:causes");
        sessionStorage.removeItem("ChooseYourAction:countries");

        var custom_dynamic_fields = {};

        custom_dynamic_fields.eventLabel = "actions=" + actions_label +
            ";causes=" + causes_label + ";countires=" + countries_label + ";tags=";
        custom_dynamic_fields.storyPreference = custom_dynamic_fields.eventLabel;

        // result view
        Drupal.custom.trackingCommon.handle_layer_item(
            element_cta_result,
            {},
            custom_dynamic_fields
        );

        // save preference
        Drupal.custom.trackingCommon.handle_element(
            _this,
            element_cta_save_preferences,
            function() {
              var custom_dynamic_fields = {};
              custom_dynamic_fields.eventLabel = "actions=" + actions_label +
                  ";causes=" + causes_label + ";countires=" + countries_label + ";tags=";
              custom_dynamic_fields.storyPreference = custom_dynamic_fields.eventLabel;

              return custom_dynamic_fields;
            }
        );
      }
      // console.timeEnd("[time] result view and save preference");
      //
      // console.time("[time] choose your action result click");
      Drupal.custom.trackingCommon.handle_element(
          _this,
          element_cta_result_click,
          function(element, item, jquery_item) {
            var custom_dynamic_fields = {};
            var story_id = _this(jquery_item).closest(".story-card").
              find(item.story_title_id).text().trim();

            custom_dynamic_fields.eventLabel = story_id;
            custom_dynamic_fields.storyPreference = "actions=" + actions_label +
                ";causes=" + causes_label + ";countires=" + countries_label + ";tags=";

            return custom_dynamic_fields;
          }
      );
      // console.timeEnd("[time] choose your action result click");
      //
      // console.timeEnd("[time] CHOOSE-YOUR-ACTION.js");
    }
  };
})(jQuery, Drupal, drupalSettings);
