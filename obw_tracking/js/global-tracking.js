(function ($, Drupal) {
  var running_count = 1;

  var second_header_menu  = $(".main-navigation .action-list li:nth-child(2) a");
  var third_header_menu   = $(".main-navigation .action-list li:nth-child(3) a");

  var element_header = {
    element_type: 'header',
    common_fields: {
      event: 'nav_click',
      eventCategory: 'Generic',
      eventAction: 'Header Clicks'
    },
    list_elements: [
      {
        id: '#header .top-header .messages.desktop a',
        static_fields: {
          eventLabel: 'Sign up as we build this new community space for you.',
          linkHref: 'i | /user/register?destination=/node',
          socialMediaPlatform: '',
          socialShares: ''
        },
      },
      {
        id: '#header .top-header .messages.mobile a',
        static_fields: {
          eventLabel: 'Sign up as we build this new community space for you.',
          linkHref: 'i | /user/register?destination=/node',
          socialMediaPlatform: '',
          socialShares: ''
        }
      },
      {
        id: '#header .main-navigation .logo a',
        static_fields: {
          eventLabel: 'logo',
          linkHref: 'i | /',
          socialMediaPlatform: '',
          socialShares: ''
        }
      },
      {
        id: '#header .top-header .top-share span:nth-child(1)',
        static_fields: {
          eventLabel: 'FOLLOW US',
          linkHref: '',
          socialMediaPlatform: '',
          socialShares: ''
        }
      },
      {
        id: '#header .top-header .top-share .share-icon a:nth-child(1)',
        static_fields: {
          eventLabel: 'Facebook icon',
          linkHref: 'e | https://www.facebook.com/ourbetterworld.org',
          socialMediaPlatform: 'Facebook',
          socialShares: 1
        }
      },
      {
        id: '#header .top-header .top-share .share-icon a:nth-child(2)',
        static_fields: {
          eventLabel: 'Twitter icon',
          linkHref: 'e | https://twitter.com/obetterworld',
          socialMediaPlatform: 'Twitter',
          socialShares: 1
        }
      },
      {
        id: '#header .top-header .top-share .share-icon a:nth-child(3)',
        static_fields: {
          eventLabel: 'Instagram icon',
          linkHref: 'e | https://www.instagram.com/challenge/',
          socialMediaPlatform: 'Instagram',
          socialShares: 1
        }
      },
      {
        id: '#header .top-header .top-share .share-icon a:nth-child(4)',
        static_fields: {
          eventLabel: 'Youtube icon',
          linkHref: 'e | https://www.youtube.com/channel/UCEURClhcOnBgpgUhNseA-4w',
          socialMediaPlatform: 'Youtube',
          socialShares: 1
        }
      },
      {
        id: '#header .main-navigation form[action="/search"]',
        static_fields: {
          eventLabel: 'Search form',
          linkHref: '',
          socialMediaPlatform: '',
          socialShares: ''
        }
      },
      {
        id: '#header .main-navigation .login .login-popup-form',
        static_fields: {
          eventLabel: 'Login button',
          linkHref: '',
          socialMediaPlatform: '',
          socialShares: ''
        }
      },
      {
        id: '#header .main-navigation .user-button-group a.sign-up-btn.use-ajax',
        static_fields: {
          eventLabel: 'Signup button',
          linkHref: '',
          socialMediaPlatform: '',
          socialShares: ''
        }
      },
      {
        id: '#header .main-navigation .avatar',
        static_fields: {
          eventLabel: 'Avatar icon',
          linkHref: '',
          socialMediaPlatform: '',
          socialShares: ''
        }
      },
      {
        id: '#header .main-navigation .username',
        static_fields: {
          eventLabel: 'User name',
          linkHref: '',
          socialMediaPlatform: '',
          socialShares: ''
        }
      },
      {
        id: '#header .main-navigation .action-list li:nth-child(1) a',
        static_fields: {
          eventLabel: 'My Profile',
          linkHref: 'i | /',
          socialMediaPlatform: '',
          socialShares: ''
        }
      },
      {
        id: '#header .main-navigation .action-list li:nth-child(2) a',
        static_fields: {
          eventLabel: 'Account Settings',
          linkHref: 'i | ' +
              (second_header_menu.length > 0 ? second_header_menu[0].href : ''),
          socialMediaPlatform: '',
          socialShares: ''
        }
      },
      {
        id: '#header .main-navigation .action-list li:nth-child(3) a',
        static_fields: {
          eventLabel: 'Logout',
          linkHref: 'i | ' +
              (third_header_menu.length > 0 ? third_header_menu[0].href : ''),
          socialMediaPlatform: '',
          socialShares: ''
        }
      }
    ]
  };
  var element_menu = {
    element_type: 'menu',
    common_fields: {
      event: 'nav_click',
      eventCategory: 'Generic',
      eventAction: 'Menu Clicks'
    },
    list_elements: [
      {
        id: '.menu .menu-item--expanded .menu-item a',
        static_fields: {
          socialMediaPlatform: '',
          socialShares: ''
        }
      }
    ]
  };
  var element_footer = {
    element_type: 'footer',
    common_fields: {
      event: 'nav_click',
      eventCategory: 'Generic',
      eventAction: 'Footer Clicks'
    },
    list_elements: [
      {
        id: '#footer .widget-nav .menu .menu-item a',
        element_type: 'query_list',
        static_fields: {
          socialMediaPlatform: '',
          socialShares: ''
        }
      },
      {
        id: '#footer .logo',
        element_type: 'one_element',
        static_fields: {
          eventLabel: 'logo',
          linkHref: 'i | /',
          socialMediaPlatform: '',
          socialShares: ''
        }
      },
      {
        id: '#footer .widget-about > p a',
        element_type: 'one_element',
        static_fields: {
          eventLabel: 'Singapore International Foundation',
          linkHref: 'e | https://www.sif.org.sg/',
          socialMediaPlatform: '',
          socialShares: ''
        }
      },
      {
        id: '#footer .copyright .field--name-body ul li a',
        element_type: 'query_list',
        static_fields: {
          socialMediaPlatform: '',
          socialShares: ''
        }
      },
      {
        id: '#footer .share a:nth-child(1)',
        element_type: 'one_element',
        static_fields: {
          eventLabel: 'Facebook icon',
          linkHref: 'e | https://www.facebook.com/ourbetterworld.org',
          socialMediaPlatform: 'Facebook',
          socialShares: 1
        }
      },
      {
        id: '#footer .share a:nth-child(2)',
        element_type: 'one_element',
        static_fields: {
          eventLabel: 'Twitter icon',
          linkHref: 'e | https://twitter.com/obetterworld',
          socialMediaPlatform: 'Twitter',
          socialShares: 1
        }
      },
      {
        id: '#footer .share a:nth-child(3)',
        element_type: 'one_element',
        static_fields: {
          eventLabel: 'Instagram icon',
          linkHref: 'e | https://www.instagram.com/challenge/',
          socialMediaPlatform: 'Instagram',
          socialShares: 1
        }
      },
      {
        id: '#footer .share a:nth-child(4)',
        element_type: 'one_element',
        static_fields: {
          eventLabel: 'Youtube icon',
          linkHref: 'e | https://www.youtube.com/channel/UCEURClhcOnBgpgUhNseA-4w',
          socialMediaPlatform: 'Youtube',
          socialShares: 1
        }
      },
    ]
  };

  var element_inline_link = {
    element_type: 'internal_link',
    common_fields: {
      event: 'internal_link',
      eventCategory: 'Generic',
    },
    list_elements: [
      {
        id: 'a',
        not_apply: '#toolbar-administration a, #header a, #footer a'

      }
    ],
    config: {
      buttonEventAction: 'Button Clicks',
      textEventAction: 'Text Clicks',
      carouselEventAction: 'Carousel Clicks',
      imgEventAction: 'Img Clicks'
    }
  };
  var element_exit_link = {
    element_type: 'exit_link',
    common_fields: {
      event: 'exit_link',
      eventCategory: 'Outbound Link'
    }
  };
  var element_download_link = {
    element_type: 'download_link',
    common_fields: {
      event: 'download',
      eventCategory: 'Generic',
      eventAction: 'Downloads'
    },
    config: {
      downloads: 1
    }
  };

  var all_link_config = Drupal.custom.config.all_link_config;
  var content_link_config = Drupal.custom.config.content_link_config;

  var element_page_scroll = {
    element_type: 'page_scroll',
    common_fields: {
      event: 'page_scroll',
      eventCategory: 'Page Scroll'
    }
  };
  var element_tracking_error = {
    element_type: 'tracking_error',
    common_fields: {
      event: 'generic',
      eventCatefory: 'Error'
    },
    list_elements: [
      {
        id: "form.user-login-form",
        field_error_id: "form.user-login-form .form-item--error-message .label.red.visible",
        element_type: "login-signup_form",
        error_type: "form submit error",
      },
      {
        id: "form.user-register-form",
        field_error_id: "form.user-register-form .form-item--error-message .label.red.visible",
        element_type: "login-signup_form",
        error_type: "form submit error",
      },
      {
        id: ".webform-node form",
        field_error_id: "form .error .label.red.visible",
        element_type: "contact-us_form",
        error_type: "form submit error",
      }
    ]
  };
  var element_inter_search = {
    element_type: 'internal_search',
    common_fields: {
      event: 'generic',
      eventCategory: 'Internal Search',
      eventAction: 'Start',
      eventLabel: ''
    }
  };
  var element_search_result = {
    element_type: 'search_result_click',
    common_fields: {
      event: 'search_result_click',
      eventCategory: 'Internal Search',
      eventAction: 'Search Result Clicks',
      internalSearchClick: 1
    }
  };

  function isInternalLink(link) {
    if (link.indexOf(window.location.host) >= 0 && link.indexOf(window.location.host) < 12) {
      return true;
    }
    return false;
  }

  function isDownloadLink(item) {
    if ($(item).is('[download]') || item.href.slice(-3) === 'pdf') {
      return true;
    }
    return false;
  }

  function isExitLink(item) {
    if (!isInternalLink(item.href)) {
      return true;
    }
    return false;
  }

  function internal_search_start() {
    // console.log('search loaded');
    Drupal.custom.trackingCommon.handle_layer_item(
        element_inter_search,
        {},
        {}
    );
  }

  function search_result_click(custom_dynamic_fields) {
    Drupal.custom.trackingCommon.handle_layer_item(
        element_search_result,
        {},
        custom_dynamic_fields
    );

  }

  function get_all_error_message(error_message_id) {
    var $list_error_messages = $(error_message_id);
    var list_error_messages = [];
    // console.log(list_error_messages);

    if ($list_error_messages.length > 0) {
      $.map($list_error_messages, function(element) {
        var error_text = $(element).text();

        // on push one message text once time
        if (!(list_error_messages.indexOf(error_text) >= 0)) {
          list_error_messages.push(error_text);
        }

        return error_text;
      });
      return list_error_messages.join(",");
    }
    return "";
  }

  function handle_tracking_error() {
    element_tracking_error.list_elements.forEach(function(item) {
      // console.log("item input: ", $(item.id + " input[type=\"submit\"]"));
      $(item.id + " input[type=\"submit\"]").click(function(event) {
        setTimeout(function() {
          // handle error
          var custom_dynamic_fields = {};
          var error_field_id = item.field_error_id;
          var error_label = get_all_error_message(error_field_id);

          custom_dynamic_fields.eventAction = item.error_type;
          custom_dynamic_fields.eventLabel  = error_label;

          if (!error_label) {
            return;
          }

          Drupal.custom.trackingCommon.handle_layer_item(
              element_tracking_error,
              {},
              custom_dynamic_fields
          );
          // console.log('[error] log in click function');
        }, 1000);
      });
    });
  }

  Drupal.behaviors.globalChecking = {
    attach: function (context, settings) {
      if (running_count !== 1) {
        return;
      }
      running_count++;
      var _this = $;

      // console.log('------ GLOBAL CHECKING ------');
      // console.time('[time] running tracking-script');
      // console.time('[time] header menu footer');
      // Click event
      [element_header,
        element_menu,
        element_footer
      ].forEach(function(section) {
        Drupal.custom.trackingCommon.handle_element(
            _this,
            section,
            function (element, item, jquery_item) {
              var custom_dynamic_fields = {};

              switch (section.element_type) {
                case 'header':
                  // console.log('header');
                  // no static fields
                  break;
                case 'menu':
                  // console.log('menu');
                  // apply dynamic fields
                  custom_dynamic_fields.eventLabel = jquery_item.text.trim();
                  custom_dynamic_fields.linkHref = (isInternalLink(jquery_item.href) ? 'i | ' : 'e | ') + jquery_item.href;
                  break;
                case 'footer':
                  // console.log('footer');
                  if (item.element_type && item.element_type === 'query_list') {
                    // apply dynamic fields
                    custom_dynamic_fields.eventLabel = jquery_item.text.trim();
                    custom_dynamic_fields.linkHref = (isInternalLink(jquery_item.href) ? 'i | ' : 'e | ') + jquery_item.href;
                  } else if (item.element_type && item.element_type === 'one_element') {
                    // no static fields
                  }
                  break;
                default:
                  break;
              }
              return custom_dynamic_fields;
            }
        );
      });
      // console.timeEnd('[time] header menu footer');

      // console.time('[time] inter link, exit link, download link');
      // Handle link internal, outer, download
      var all_link      = _this(all_link_config.id).not(_this(all_link_config.not_apply));
      var content_link  = _this(content_link_config.id).not(_this(content_link_config.not_apply));

      // Page content click
      content_link.each(function() {
        if(isInternalLink(this.href) && !isDownloadLink(this)) {

          _this(this).click(function (event) {
            // event.preventDefault();
            var custom_dynamic_fields = {
              linkHref: 'i | ' + this.href,
            };

            // var event_item = {};
            // event_item.event = element_inline_link.common_fields.event;
            // event_item.eventCategory = element_inline_link.common_fields.eventCategory;
            // event_item.timestamp = Date.now();
            // event_item.linkHref = 'i | ' + this.href;

            if (this.innerHTML.indexOf('<img') >= 0) {
              // console.log('img');
              custom_dynamic_fields.eventAction = element_inline_link.config.imgEventAction;
              custom_dynamic_fields.eventLabel = '';
            } else if (this.className.indexOf('btn') >= 0){
              // console.log('button');
              custom_dynamic_fields.eventAction = element_inline_link.config.buttonEventAction;
              custom_dynamic_fields.eventLabel = this.text.trim();
            } else if (this.text !== '') {
              // console.log('text');
              custom_dynamic_fields.eventAction = element_inline_link.config.textEventAction;
              custom_dynamic_fields.eventLabel = this.text.trim();
            }
            // TODO handle for carouser link click

            Drupal.custom.trackingCommon.handle_layer_item(
                element_inline_link,
                {},
                custom_dynamic_fields
            );
          });
        }
      });

      // Exit link click and download link
      all_link.each(function() {
        _this(this).click(function(event) {
          // event.preventDefault();
          var custom_dynamic_fields = {
            eventLabel: this.href
          };
          if (isExitLink(this) && !_this(this).is('[download]')) {
            // console.log('exit link');

            custom_dynamic_fields.eventAction  = this.text.trim() + ' Clicks';
            custom_dynamic_fields.linkHref     = 'e | ' + this.href;

            Drupal.custom.trackingCommon.handle_layer_item(
                element_exit_link,
                {},
                custom_dynamic_fields
            );
          } else if (isDownloadLink(this)) {
            // console.log('down load link');

            custom_dynamic_fields.linkHref   = 'e | ' + this.href;
            custom_dynamic_fields.downloads  = element_download_link.config.downloads;

            Drupal.custom.trackingCommon.handle_layer_item(
                element_download_link,
                {},
                custom_dynamic_fields
            );
          }
        });
      });
      // console.timeEnd('[time] inter link, exit link, download link');


      // Page scroll
      var percentCheck = {
        '25': false,
        '50': false,
        '75': false,
        '100': false
      };
      // console.time('[time] page scroll');
      $(window).scroll(function() {
        var scrollPercent = 100 * $(window).scrollTop() / ($(document).height() - $(window).height());
        // console.log('scrollPercent:', scrollPercent);

        switch (true) {
          case (scrollPercent > 20 && scrollPercent < 30):
            if (percentCheck['25'] === false) {
              percentCheck['25'] = true;
              // console.log('25%');
              var custom_dynamic_fields = {};

              custom_dynamic_fields.pageFullyViewed = '';
              custom_dynamic_fields.eventAction = '25% Views';
              custom_dynamic_fields.eventLabel = window.location.pathname + ' | ' + $(document).height();

              Drupal.custom.trackingCommon.handle_layer_item(
                  element_page_scroll,
                  {},
                  custom_dynamic_fields
              );
            }
            break;
          case (scrollPercent > 45 && scrollPercent < 55):
            if (percentCheck['50'] === false) {
              percentCheck['50'] = true;
              // console.log('50%');
              var custom_dynamic_fields = {};

              custom_dynamic_fields.pageFullyViewed = '';
              custom_dynamic_fields.eventAction = '50% Views';
              custom_dynamic_fields.eventLabel = window.location.pathname + ' | ' + $(document).height();

              Drupal.custom.trackingCommon.handle_layer_item(
                  element_page_scroll,
                  {},
                  custom_dynamic_fields
              );
            }
            break;
          case (scrollPercent > 70 && scrollPercent < 80):
            if (percentCheck['75'] === false) {
              percentCheck['75'] = true;
              // console.log('75%');

              var custom_dynamic_fields = {};

              custom_dynamic_fields.pageFullyViewed = '';
              custom_dynamic_fields.eventAction = '75% Views';
              custom_dynamic_fields.eventLabel = window.location.pathname + ' | ' + $(document).height();

              Drupal.custom.trackingCommon.handle_layer_item(
                  element_page_scroll,
                  {},
                  custom_dynamic_fields
              );
            }
            break;
          case scrollPercent === 100:
            if (percentCheck['100'] === false) {
              percentCheck['100'] = true;
              // console.log('100%');

              var custom_dynamic_fields = {};

              custom_dynamic_fields.pageFullyViewed = 1;
              custom_dynamic_fields.eventAction = '100% Views';
              custom_dynamic_fields.eventLabel = window.location.pathname + ' | ' + $(document).height();

              Drupal.custom.trackingCommon.handle_layer_item(
                  element_page_scroll,
                  {},
                  custom_dynamic_fields
              );
            }
            break;
        }
      });
      // console.timeEnd('[time] page scroll');

      // console.time('[time] error tracking');
      // Error tracking
      handle_tracking_error();
      _this(".login > a, .signup > a, .login-desktop > a").click(function(item) {
        setTimeout(function() {
          handle_tracking_error();
        }, 1500);
      });
      // console.timeEnd('[time] error tracking');
      //
      // console.time('[time] search loaded');
      // Search start
      _this('.hammer-menu').click(function () {
        setTimeout(function() {
          if(_this('.main-navigation > .container > .navigation').is('[style="display: block;"]')) {
            internal_search_start();
          }
        }, 500);
      });

      if (!Drupal.custom.trackingCommon.isMobile()) {
        if (_this('.search-navigation-desktop form[action="/search"]').length > 0) {
          internal_search_start();
        }
      }
      // console.timeEnd('[time] search loaded');
      //
      // console.time('[time] search result click');
      _this('.search-result-item .hide-desktop a, .search-result-item .hide-mobile a').click(function() {

        search_result_click({
          eventLabel: _this(this).parents('.search-result-item').
            find('h3 a').text().trim()
        });
      });
      _this('.search-result-item h3 a').click(function() {
        search_result_click({
          eventLabel: this.innerText.trim()
        });
      });
      // console.timeEnd('[time] search result click');
      //
      // console.timeEnd('[time] running tracking-script');
    }
  };
})(jQuery, Drupal);
