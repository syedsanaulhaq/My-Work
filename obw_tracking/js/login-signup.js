(function ($, Drupal, drupalSettings) {
  var running_count = 1;
  var input_pressed = [];
  var loginSignupInfo = '';
  var signup_error = '';

  if ("tracking_seo" in drupalSettings && "login_signup_info" in drupalSettings.tracking_seo) {
    loginSignupInfo = JSON.parse(drupalSettings.tracking_seo.login_signup_info);
  }

  var eventLabel = {
    signup: {
      facebook: 'Sign up with Facebook',
      google: 'Sign up with Google',
      twitter: 'Sign up with Twitter'
    },
    signin: {
      facebook: 'Sign in with Facebook',
      google: 'Sign in with Google',
      twitter: 'Sign in with Twitter'
    }
  };

  var element_signup_overlay = {
    element_type: 'signup_overlay',
    common_fields: {
      event: 'signup',
      eventCategory: 'Account Signup',
      eventAction: 'Start'
    },
    list_element: [
      {
        id: '#user-action-register .social-login a:nth-child(1)',
        element_type: 'social_signup',
        static_fields: {
          eventLabel: eventLabel.signup.facebook,
          socialMediaPlatform: 'facebook'
        },
      },
      {
        id: '#user-action-register .social-login a:nth-child(2)',
        element_type: 'social_signup',
        static_fields: {
          eventLabel: eventLabel.signup.google,
          socialMediaPlatform: 'google'
        }
      },
      {
        id: '#user-action-register .social-login a:nth-child(3)',
        element_type: 'social_signup',
        static_fields: {
          eventLabel: eventLabel.signup.twitter,
          socialMediaPlatform: 'twitter'
        }
      },
      {
        id: '#user-action-register .form-wrapper input[placeholder="First name"]',
        element_type: 'input',
        static_fields: {
          eventLabel: '',
          socialMediaPlatform: ''
        }
      },
      {
        id: '#user-action-register .form-wrapper input[placeholder="Last name"]',
        element_type: 'input',
        static_fields: {
          eventLabel: '',
          socialMediaPlatform: ''
        }
      },
      {
        id: '#user-action-register .form-wrapper input[placeholder="Email"]',
        element_type: 'input',
        static_fields: {
          eventLabel: '',
          socialMediaPlatform: ''
        }
      }
    ]
  };
  var element_signup_button = {
    element_type: 'signup_overlay',
    common_fields: {
      event: 'newsletter',
      eventCategory: 'Newsletter',
      eventAction: 'Subscribes',
      eventLabel: 'Checkbox | Account Signups',
      newsletterSub: 1
    },
    list_element: [
      {
        id: '#user-action-register .form-wrapper input[type="submit"]',
        static_fields: {
        },
        required_elements: [
          {
            id: '#user-action-register .form-wrapper input[name="field_account_news_subscribed[value]"]',
            required_prop: 'checked',
            required_value: true
          }
        ],
      }
    ]
  };
  var element_signup_success = {
    element_type: 'signup_success',
    common_fields: {
      event: 'signup',
      eventCategory: 'Account Signup',
      eventAction: 'Submit',
      userSignup: 1
    },
    submit_button: {
      id: "form.user-register-form input[type=\"submit\"]"
    }
  };

  var element_login_overlay = {
    element_type: 'login_overlay',
    common_fields: {
      event: 'signin',
      eventCategory: 'Account Signin',
      eventAction: 'Start'
    },
    list_element: [
      {
        id: '#user-action-login .social-login a:nth-child(1)',
        element_type: 'social_signin',
        static_fields: {
          eventLabel: eventLabel.signin.facebook,
          socialMediaPlatform: 'facebook'
        },
      },
      {
        id: '#user-action-login .social-login a:nth-child(2)',
        element_type: 'social_signin',
        static_fields: {
          eventLabel: eventLabel.signin.google,
          socialMediaPlatform: 'google'
        }
      },
      {
        id: '#user-action-login .social-login a:nth-child(3)',
        element_type: 'social_signin',
        static_fields: {
          eventLabel: eventLabel.signin.twitter,
          socialMediaPlatform: 'twitter'
        }
      },
      {
        id: '#user-action-login .form-wrapper input[placeholder="Email Address"]',
        element_type: 'input',
        static_fields: {
          eventLabel: '',
          socialMediaPlatform: ''
        }
      },
      {
        id: '#user-action-login .form-wrapper input[placeholder="Password"]',
        element_type: 'input',
        static_fields: {
          eventLabel: '',
          socialMediaPlatform: ''
        }
      }
    ]
  };
  var element_login_success = {
    element_type: 'login_success',
    common_fields: {
      event: 'signin',
      eventCategory: 'Account Signin',
      eventAction: 'Submit',
      userSignin: 1
    },
    submit_button: {
      id: "form.user-login-form input[type=\"submit\"]"
    }
  };

  var submit_successed_times = 0;

  function handle_form_submit() {
    $(document).ajaxSuccess(function(event, request, settings) {
      var custom_dynamic_fields = {};
      var response_results = request.responseJSON ? request.responseJSON : {};
      response_results.forEach(function(data) {
        {
          if (data.name && data.name === "user_id" && data.value !== 0) {
            custom_dynamic_fields.userID = data.value;
          }
        }
      });
      if (settings.url.indexOf('/user/register') >= 0 &&
          $('.register-modal .message.success').length > 0 &&
          submit_successed_times === 0) {
        submit_successed_times++;
        // console.log('sign up');
        Drupal.custom.trackingCommon.handle_layer_item(
            element_signup_success,
            {},
            custom_dynamic_fields
        );
      } else if (settings.url.indexOf('/user/login') >= 0) {
        Drupal.custom.trackingCommon.handle_layer_item(
            element_login_success,
            {},
            custom_dynamic_fields
        );
      }

    });
  }

  function handleSubscribe(item, _this) {
    for (var index = 0; index < item.required_elements.length; index++) {
      var required_element = item.required_elements[index];
      if (_this(required_element.id).prop(required_element.required_prop) !== required_element.required_value) {
        return;
      }
    }
    Drupal.custom.trackingCommon.handle_layer_item(
        element_signup_button,
        item,
        {}
    );
  }

  Drupal.behaviors.loginSignup = {
    attach: function (context, settings) {
      var _this = $;

      if (loginSignupInfo) {
        var custom_dynamic_fields = {};
        var element = {};
        var social = loginSignupInfo.social;

        if (loginSignupInfo.is_login) {
          element = element_login_success;
          if (social && social !== "not social") {
            custom_dynamic_fields.eventLabel = eventLabel.signin[social];
            custom_dynamic_fields.socialMediaPlatform = social;
          }
        } else {
          element = element_signup_success;
          if (social && social !== "not social") {
            custom_dynamic_fields.eventLabel = eventLabel.signup[social];
            custom_dynamic_fields.socialMediaPlatform = social;
          }
        }

        custom_dynamic_fields.userID = loginSignupInfo.user_id;

        Drupal.custom.trackingCommon.handle_layer_item(
            element,
            {},
            custom_dynamic_fields
        );
      }

      // Form submit successed
      var list_form_submit_button_id = [
        element_login_success.submit_button.id
      ];
      var jquery_submit_button = list_form_submit_button_id.join(",");

      // console.log('------ LOGIN - SIGNUP ------');
      //
      // console.time('[time] signup overlay');
      [element_signup_overlay, 
        element_login_overlay
      ].forEach(function (section) {
        section.list_element.forEach(function(item) {
          if (['social_signup', 'social_signin'].indexOf(item.element_type) >= 0) {
            _this(item.id).click(function (event) {
              // event.preventDefault();
              Drupal.custom.trackingCommon.handle_layer_item(
                  section,
                  item,
                  {}
              );
            });
          } else if (item.element_type === 'input') {
            _this(item.id).keydown(function(event) {
              // event.preventDefault();
              if(input_pressed.indexOf(item.id) >= 0) {
                _this(this).off("keydown");
                return;
              }
              input_pressed.push(item.id);
              Drupal.custom.trackingCommon.handle_layer_item(
                  section,
                  item,
                  {}
              );
            });
          }
        });
      });
      // console.timeEnd('[time] signup overlay');

      // User click signup
      element_signup_button.list_element.forEach(function(item) {
        _this(item.id).click(function(event) {
          // event.preventDefault();
          setTimeout(function() {
            var $error = _this('.user-register-form .form-item--error-message');
            var prevent_signup = true;
            if ($error.length > 0) {
              if (signup_error && signup_error === $error[0]["innerText"]) {
                prevent_signup = false;
              } else {
                signup_error = $error[0]["innerText"];
                return;
              }
            }
            if (!prevent_signup) {
              handleSubscribe(item, _this);
            }
          }, 1500);
        });
      });

      _this(jquery_submit_button).click(function() {
        handle_form_submit();
      });
    }
  };
})(jQuery, Drupal, drupalSettings); // jshint ignore:line