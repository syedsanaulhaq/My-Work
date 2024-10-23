(function ($, Drupal, drupalSettings) {
  var running_count = 1;

  var element_confirmation_display = {
    element_type: "donate_display",
    common_fields: {
      event: "donate",
      eventCategory: "Support OBW",
      eventAction: "Donate Confirm"
    },
    static_fields: {
      ecommerce: {
        checkout: {
          actionField: {
            step: 1
          },
          products: {
            category: "Donation",
            quantity: 1
          }
        }
      }
    },
    confirm_form: {
      id: ".donate-form #edit-support-us-step-2",
      recommened_amount: [5, 10, 20]
    }
  };
  var element_payment_display = {
    element_type: "payment_display",
    common_fields: {
      event: "donate",
      eventCategory: "Support OBW",
      eventAction: "Donate Payment"
    },
    static_fields: {
      ecommerce: {
        checkout: {
          actionField: {
            step: 2,
            option: "credit card"
          },
          products: {
            category: "Donation",
            quantity: 1
          }
        }
      }
    },
    payment_form: {
      id: "#stripe-element-container.show .popup-content",
      recommened_amount: [5, 10, 20]
    }
  };
  var element_donate_success = {
    element_type: "donate_success",
    common_fields: {
      event: "donate",
      eventCategory: "Support OBW",
      eventAction: "Donate Submit"
    },
    static_fields: {
      ecommerce: {
        purchase: {
          actionField: {
          },
          products: {
            category: "Donation",
            quantity: 1
          }
        }
      }
    },
    success_form: {
      id: ".thank-you-page .webform-confirmation"
    }
  };

  function trackingDonate(section) {
    var recommend_amount  = element_confirmation_display.confirm_form.recommened_amount;
    
    var custom_dynamic_fields = {};
    var popupConfig = drupalSettings.stripeButtonSelector[0].popupConfig;
    var amount = popupConfig.amount / 100;

    var amount_type = recommend_amount.indexOf(amount) >= 0 ? "preset amount" : "customize";
    var frequency   = popupConfig.label === "Monthly donation" ? "Monthly" : "One Time";
    // var product_id  = "<sku>";
    var product_id  = drupalSettings.stripeButtonSelector[0].config.key;

    custom_dynamic_fields = section.static_fields;
    custom_dynamic_fields.eventLabel = amount_type + " | " + amount + " | " + frequency;

    var ecommerce_type = "checkout";

    if (section.element_type === "donate_success") {
      ecommerce_type = "purchase";
      custom_dynamic_fields.ecommerce.purchase.actionField.id = "";
      // custom_dynamic_fields.ecommerce.purchase.actionField.id = "<transaction ID>";
      custom_dynamic_fields.ecommerce.purchase.actionField.revenue = amount;
    }

    custom_dynamic_fields.ecommerce[ecommerce_type].products.name = frequency + " Donation";
    custom_dynamic_fields.ecommerce[ecommerce_type].products.id = product_id;
    custom_dynamic_fields.ecommerce[ecommerce_type].products.price = amount;

    Drupal.custom.trackingCommon.handle_layer_item(
        section,
        {},
        custom_dynamic_fields
    );
  }

  Drupal.behaviors.donate = {
    attach: function(context, settings) {
      if (running_count !== 1) {
        return;
      }
      running_count = running_count + 1;
      var _this = $;
      var confirm_form_id   = element_confirmation_display.confirm_form.id;
      var success_form_id   = element_donate_success.success_form.id;
      var recommend_amount  = element_confirmation_display.confirm_form.recommened_amount;

      var payment_form_id   = element_payment_display.payment_form.id;
      var donate_confirmation_step = _this(confirm_form_id);
      var success_form      = _this(success_form_id);

      // console.log("------ DONATE.js ------");
      // console.time("[time] DONATE.js");
      if (donate_confirmation_step.length > 0) {
        trackingDonate(element_confirmation_display);

        _this("input#edit-payment-method-credit").click(function() {
          trackingDonate(element_payment_display);
        });
      }
      if (success_form.length > 0) {
        trackingDonate(element_donate_success);
      }
      // console.timeEnd("[time] DONATE.js");
    }
  };
})(jQuery, Drupal, drupalSettings);