(function ($, Drupal, drupalSettings) {
  var element_story_behavior;
  var running_count = 1;
  // console.log('drupalSettings:', drupalSettings);
  element_story_behavior = {
    element_type: 'content_participate',
    common_fields: {
      event: 'content_participate',
      eventCategory: 'Stories',
      contentParticipates: 1,
    },
    list_elements: [
      {
        id: ".share #shareBtn",
        static_fields: {
          eventAction: 'Shares',
          eventLabel: 'facebook',
          socialMediaPlatform: 'facebook',
          socialShares: 1
        },
        element_type: 'share',
        share_type: 'fb'
      },
      {
        id: '.share .ico-twitter',
        static_fields: {
          eventAction: 'Shares',
          eventLabel: 'twitter',
          socialMediaPlatform: 'twitter',
          socialShares: 1
        },
        element_type: 'share',
        share_type: 'twitter'
      },
      {
        id: '.share .ico-email',
        static_fields: {
          eventAction: 'Shares',
          eventLabel: 'email',
          socialMediaPlatform: 'email',
          socialShares: 1
        },
        element_type: 'share',
        share_type: 'email'
      },
      {
        id: '.share .ico-what',
        static_fields: {
          eventAction: 'Shares',
          eventLabel: 'whatsapp',
          socialMediaPlatform: 'whatsapp',
          socialShares: 1
        },
        element_type: 'share',
        share_type: 'whatsapp'
      },
      {
        id: '.share .ico-pin',
        static_fields: {
          eventAction: 'Shares',
          eventLabel: 'pinterest',
          socialMediaPlatform: 'pinterest',
          socialShares: 1
        },
        element_type: 'share',
        share_type: 'pinterest'
      },
      {
        id: '#like-action',
        static_fields: {
          eventAction: 'Likes',
          eventLabel: ''
        },
        element_type: 'like'
      },
      {
        id: '#like-action .action-wrap:not(.active)',
        static_fields: {
          eventAction: 'Likes',
          eventLabel: ''
        },
        element_type: 'like'
      },
      {
        id: '#follow-action .action-wrap:not(.active)',
        static_fields: {
          eventAction: 'Follow',
          eventLabel: ''
        },
        element_type: 'follow'
      },
      {
        id: '.you-can-help .view-content .item-block',
        static_fields: {
          eventAction: 'Take Actions',
          takeActions: 1
        },
        element_type: 'you-can-help'
      },
      {
        id: '.list-action a',
        static_fields: {
          eventAction: 'Take Actions',
          takeActions: 1
        },
        element_type: 'take-action'
      }
    ]
  };
  var element_story_unlike_follow = {
    element_type: 'un_like_follow',
    common_fields: {
      event: 'generic',
      eventCategory: 'Stories',
      eventLabel: '',
    },
    list_elements: [
      {
        id: '#like-action .action-wrap.active',
        static_fields: {
          eventAction: 'Unlike for Updates',
        },
        element_type: 'Unlike',
      },
      {
        id: '#follow-action .action-wrap.active',
        static_fields: {
          eventAction: 'UnFollow',
        },
        element_type: 'Unfollow',
      }
    ]
  };
  var element_relatied_story = {
    element_type: 'related_stories',
    common_fields: {
      event: 'generic',
      eventCategory: 'Story Clickthrough',
      eventAction: 'Related Stories Clicks',
    },
    list_elements: [
      {
        id: '.related-card .related-desc h3 a',
        element_type: 'stories-title'
      },
      {
        id: '.related-card .related-img a',
        element_type: 'stories-img'
      },
      {
        id: '.related-card .related-desc .entry-tag a',
        element_type: 'stories-entry-tag'
      },
    ]
  };
  var element_contributor_story = {
    element_type: 'contributor_story',
    common_fields: {
      event: 'generic',
      eventCategory: 'Story Clickthrough',
      eventAction: 'Story Profile Page',
    },
    list_elements: [
      {
        id: '.stories-card .stories-desc h3 a',
        element_type: 'stories-title'
      },
      {
        id: '.stories-card .stories-img a',
        element_type: 'stories-img'
      },
      {
        id: '.stories-card .stories-desc .entry-tag',
        element_type: 'stories-entry-tag'
      },
    ]
  };

  Drupal.behaviors.story = {
    attach: function (context, settings) {
      if (running_count !== 1) {
        return;
      }
      running_count++;

      // console.log('------ STORY.js ------');
      // console.time('[time] STORY.js');

      [element_story_behavior,
        element_story_unlike_follow,
        element_relatied_story,
        element_contributor_story
      ].forEach(function (element) {
        Drupal.custom.trackingCommon.handle_element(
            $,
            element,
            function (element, item, jquery_item) {
              var custom_dynamic_fields = {};
              var current_story_title = {};
              var clicked_story_title = {};
              var profile_name = {};

              switch (element.element_type) {
                case 'content_participate':
                  var item_type = item.element_type ? item.element_type : '';
                  if (item_type === 'you-can-help') {
                    var small_letter = $(jquery_item).find("span").text().trim();
                    custom_dynamic_fields.eventLabel = small_letter;
                  }
                  if (item_type === 'take-action') {
                    var jquery_action_item = $(jquery_item).closest('.action-item');
                    var small_letter = jquery_action_item.find(".action-head p").text().trim();
                    custom_dynamic_fields.eventLabel = small_letter;
                  }
                  break;
                case 'related_stories':
                  current_story_title = drupalSettings.tracking_seo.story_title;
                  if (item.element_type === 'stories-title') {
                    clicked_story_title = jquery_item.innerText.trim();
                  }
                  else if (item.element_type === 'stories-img') {
                    clicked_story_title = $(jquery_item).parents('.related-img').siblings('.related-desc').find('a:first-child')[0].innerText.trim();
                  }
                  else if (item.element_type === 'stories-entry-tag') {
                    clicked_story_title = $(jquery_item).parents('.related-desc').find('a:first-child')[0].innerText.trim();
                  }
                  custom_dynamic_fields.eventLabel = current_story_title + ' | ' + clicked_story_title;
                  break;
                case 'contributor_story':
                  profile_name = drupalSettings.tracking_seo.profile_name;
                  if (item.element_type === 'stories-title') {
                    clicked_story_title = jquery_item.innerText.trim();
                  }
                  else if (item.element_type === 'stories-img') {
                    clicked_story_title = $(jquery_item).parents('.stories-img').siblings('.stories-desc').find('a:first-child')[1].innerText.trim();
                  }
                  else if (item.element_type === 'stories-entry-tag') {
                    clicked_story_title = $(jquery_item).parents('.stories-desc').find('a:first-child')[0].innerText.trim();
                  }
                  custom_dynamic_fields.eventLabel = profile_name + ' | ' + clicked_story_title;
                  break;
              }
              return custom_dynamic_fields;
            }
        );
      });
      // console.timeEnd('[time] STORY.js');
    }
  };
})(jQuery, Drupal, drupalSettings);