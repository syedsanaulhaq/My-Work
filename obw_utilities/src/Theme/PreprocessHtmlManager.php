<?php
/**
 * Created by PhpStorm.
 * User: leopham
 * Date: 10/12/18
 * Time: 3:03 PM
 */

namespace Drupal\obw_utilities\Theme;

use Drupal;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;

class PreprocessHtmlManager {

  public static function NodeTypeStory(&$variables) {
    $node = Drupal::routeMatch()->getParameter('node');
    $variables['page_tabbar'] = '';
    // $variables['page_tabbar'] = 'has-tabbar';

    global $base_url;
    $html_head = $variables['page']['#attached']['html_head'];
    $current_path = Drupal::service('path.current')->getPath();
    $result = Drupal::service('path_alias.manager')
      ->getAliasByPath($current_path);

    if (isset($node) && $node instanceof Node) {
      // Create variable out of the current node type
      $node_type = $node->getType();

      if ($node_type == 'story') {

        if ($node->get('field_story_subject_profile')->first()) {
          $field_story_subject_profile = $node->get('field_story_subject_profile')
            ->first()->entity;
          if ($field_story_subject_profile->get('field_para_ss_profile_title')
            ->getValue()) {
            $variables['head_title']['title'] = $field_story_subject_profile->get('field_para_ss_profile_title')
                ->getValue()[0]['value'] . ': ' . $variables['head_title']['title'];
          }
        }
      }
    }


    if ($node->get('field_story_thumbnail') && isset($node->get('field_story_thumbnail')[0]->entity->uri->value)) {
      $thumbnail = $node->get('field_story_thumbnail')[0]->entity->uri->value;

      if (!is_null($html_head) && !empty($html_head)) {
        foreach ($html_head as $key => $value) {
          if (in_array('og_title', $value) || in_array('og_description', $value)
            || in_array('og_image_url', $value)
            || in_array('og_image_secure_url', $value)
            || in_array('og_image', $value)) {
            unset($html_head[$key]);
          }
        }

        $fb_meta_description_node = '';
        if ($node->get('field_story_short_description') && isset($node->get('field_story_short_description')[0]->value)) {
          $fb_meta_description_node = [
            '#tag' => 'meta',
            '#attributes' => [
              'property' => 'og:description',
              'content' => $node->get('field_story_short_description')[0]->value,
            ],
          ];
        }

        $node_title_share_fb = '';
        if ($node->title->value) {
          $node_title_share_fb = $node->title->value;
          if (strlen($node_title_share_fb) > 58) {
            $node_title_share_fb = substr($node_title_share_fb, 0, 58);
            $node_title_share_fb .= '...';
          }
        }

        $fb_meta_title_node = [
          '#tag' => 'meta',
          '#attributes' => [
            'property' => 'og:title',
            'content' => $node_title_share_fb,
          ],
        ];

        if (isset($node->field_metatags) && !empty($node->field_metatags->getValue())) {
          $metatags = unserialize($node->field_metatags->getValue()[0]['value']);
          if (!empty($metatags['og_title'])) {
            $fb_meta_title_node = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'og:title',
                'content' => $metatags['og_title'],
              ],
            ];
          }

          if (!empty($metatags['og_description'])) {
            $fb_meta_description_node = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'og:description',
                'content' => $metatags['og_description'],
              ],
            ];
          }
        }

        $style600x316 = ImageStyle::load('thumbnail_600x316');
        $image600x316 = $style600x316->buildUrl($thumbnail);
        $fb_meta_img = [
          '#tag' => 'meta',
          '#attributes' => [
            'property' => 'og:image',
            'content' => file_create_url($image600x316),
          ],
        ];
        $og_meta_img_url = [
          '#tag' => 'meta',
          '#attributes' => [
            'property' => 'og:image:url',
            'content' => file_create_url($image600x316),
          ],
        ];
        $og_meta_img_secure_url = [
          '#tag' => 'meta',
          '#attributes' => [
            'property' => 'og:image:secure_url',
            'content' => file_create_url($image600x316),
          ],
        ];
        $html_head[] = [$og_meta_img_url, 'og_image_url'];
        $html_head[] = [$og_meta_img_secure_url, 'og_image_secure_url'];
        $html_head[] = [$fb_meta_img, 'og_image'];
        $html_head[] = [$fb_meta_description_node, 'og_description'];
        $html_head[] = [$fb_meta_title_node, 'og_title'];
        $variables['page']['#attached']['html_head'] = $html_head;
      }
    }


    // Alter metatag
    $sharing_type = isset($_GET['share']) ? $_GET['share'] : NULL;

    if (isset($sharing_type)) {

      if (!is_null($html_head) && !empty($html_head)) {

        $title_share = t('Make an impact with me!');
        if ($node->field_story_share_message->first()->entity->field_para_banner_title) {
          $title_share = $node->field_story_share_message->first()->entity->field_para_banner_title->getValue()['0']['value'];
        }

        $description_share = t('Come join in making an impact at this event, check out this story to find out more');
        if ($node->field_story_share_message->first()->entity->field_para_banner_intro) {
          $description_share = $node->field_story_share_message->first()->entity->field_para_banner_intro->getValue()['0']['value'];
        }

        switch ($sharing_type) {
          case '':  // todo: case of whatsapp. Because in link not pass params
          case 'whatsapp':
            foreach ($html_head as $key => $value) {
              if (in_array('title', $value) || in_array('description', $value) || in_array('og_image', $value)
                || in_array('og_image_url', $value) || in_array('og_title', $value) || in_array('og_description', $value) || in_array('og_url', $value)) {
                unset($html_head[$key]);
              }
            }
            $meta_title = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'title',
                'content' => $title_share,
              ],
            ];
            $meta_description = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'description',
                'content' => $description_share,
              ],
            ];
            $url = $base_url . $result . '?share=whatsapp';
            $og_meta_url = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'og:url',
                'content' => $url,
              ],
            ];
            $og_meta_title = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'og:title',
                'content' => $title_share,
              ],
            ];
            $thumbnail = $node->get('field_story_thumbnail')[0]->entity->uri->value;
            $og_meta_img = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'og:image',
                'content' => file_create_url($thumbnail),
              ],
            ];
            $og_meta_img_url = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'og:image:url',
                'content' => file_create_url($thumbnail),
              ],
            ];
            $og_meta_description = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'og:description',
                'content' => $description_share,
              ],
            ];
            $html_head[] = [$og_meta_title, 'og_title'];
            $html_head[] = [$og_meta_description, 'og_description'];
            $html_head[] = [$og_meta_img, 'og_image'];
            $html_head[] = [$og_meta_img_url, 'og_image_url'];
            $html_head[] = [$og_meta_url, 'og_url'];
            $html_head[] = [$meta_title, 'title'];
            $html_head[] = [$meta_description, 'description'];
            break;
          case 'twitter':
            foreach ($html_head as $key => $value) {
              if (in_array('twitter_cards_image', $value) || in_array('twitter_cards_page_url', $value) ||
                in_array('twitter_cards_title', $value) || in_array('twitter_cards_description', $value)) {
                unset($html_head[$key]);
              }
            }
            $twitter_meta_title = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'twitter:title',
                'content' => $title_share,
              ],
            ];
            $twitter_meta_description = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'twitter:description',
                'content' => $description_share,
              ],
            ];
            $twitter_meta_url = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'twitter:url',
                'content' => $base_url . $result . '?share=twitter',
              ],
            ];
            $thumbnail = $node->get('field_story_thumbnail')[0]->entity->uri->value;
            $twitter_meta_img = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'twitter:image',
                'content' => file_create_url($thumbnail),
              ],
            ];
            // twitter_cards_image
            $html_head[] = [$twitter_meta_img, 'twitter_cards_image'];
            $html_head[] = [$twitter_meta_title, 'twitter_cards_title'];
            $html_head[] = [$twitter_meta_url, 'twitter_cards_page_url'];
            $html_head[] = [
              $twitter_meta_description,
              'twitter_cards_description',
            ];
            break;
          case 'fb':
            foreach ($html_head as $key => $value) {
              if (in_array('og_title', $value) || in_array('og_description', $value)
                || in_array('og_url', $value) || in_array('og_image_url', $value) || in_array('og_image', $value)) {
                unset($html_head[$key]);
              }
            }

            $fb_url = $base_url . $result . '?share=fb';
            $fb_meta_url = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'og:url',
                'content' => $fb_url,
              ],
            ];
            $fb_meta_title = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'og:title',
                'content' => $title_share,
              ],
            ];
            $fb_meta_description = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'og:description',
                'content' => $description_share,
              ],
            ];
            $thumbnail = $node->get('field_story_thumbnail')[0]->entity->uri->value;
            $style600x316 = ImageStyle::load('thumbnail_600x316');
            $image600x316 = $style600x316->buildUrl($thumbnail);
            $fb_meta_img = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'og:image',
                'content' => file_create_url($image600x316),
              ],
            ];

            $html_head[] = [$fb_meta_title, 'og_title'];
            $html_head[] = [$fb_meta_description, 'og_description'];
            $html_head[] = [$fb_meta_url, 'og_url'];
            $html_head[] = [$fb_meta_img, 'og_image'];
            break;
          case 'pin':
            foreach ($html_head as $key => $value) {
              if (in_array('pinterest_url', $value) || in_array('pinterest_description', $value)) {
                unset($html_head[$key]);
              }
            }
            $pin_url = $base_url . $result . '?share=pin';
            $pin_meta_url = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'pin:url',
                'content' => $pin_url,
              ],
            ];
            $pin_meta_description = [
              '#tag' => 'meta',
              '#attributes' => [
                'property' => 'pin:description',
                'content' => $description_share,
              ],
            ];
            $html_head[] = [$pin_meta_description, 'pinterest_description'];
            $html_head[] = [$pin_meta_url, 'pinterest_url'];
            break;
          case 'facebook':
            break;
          default:
            break;
        }

        $variables['page']['#attached']['html_head'] = $html_head;
      }
    }

  }

  public static function NodeAllType(&$variables) {
    $pattern_cta_volunteer = '/^\/story\/[a-zA-Z0-9\-]*\/volunteer\/[a-zA-Z0-9\-]*/';
    $pattern_cta_support = '/^\/story\/[a-zA-Z0-9\-]*\/support\/[a-zA-Z0-9\-]*/';

    $current_path = Drupal::service('path.current')->getPath();
    $result = Drupal::service('path_alias.manager')
      ->getAliasByPath($current_path);
    $site = \Drupal::config('system.site');
    $node = Drupal::routeMatch()->getParameter('node');

    if (preg_match($pattern_cta_volunteer, $result, $group_matched)) {
      $html_head = $variables['page']['#attached']['html_head'];

      $meta_non_index_robot = [
        '#tag' => 'meta',
        '#attributes' => [
          'name' => 'robots',
          'content' => 'noindex',
        ],
      ];
      $html_head[] = [$meta_non_index_robot, 'robots'];
      $variables['page']['#attached']['html_head'] = $html_head;
    }
    if (preg_match($pattern_cta_support, $result, $group_matched)) {
      $html_head = $variables['page']['#attached']['html_head'];

      $meta_non_index_robot = [
        '#tag' => 'meta',
        '#attributes' => [
          'name' => 'robots',
          'content' => 'noindex',
        ],
      ];
      $html_head[] = [$meta_non_index_robot, 'robots'];
      $variables['page']['#attached']['html_head'] = $html_head;
    }

    if ((isset($node) && $node instanceof Node)) {
      $node_id = $node->id();
      if ($node->getType() == 'gsoty_story') {
        $gsoty_thumbnail = $node->field_gsoty_thumbnail->entity;
        if (!empty($gsoty_thumbnail)) {
          $data_meta['img_path'] = file_create_url($gsoty_thumbnail->uri->value);
        }
        else {
          $story_id = $node->field_gsoty_title->getValue()[0]['target_id'];
          $story = Node::load($story_id);
          $data_meta['img_path'] = file_create_url($story->field_story_thumbnail->entity->uri->value);
        }
        static::customMetaHeader($data_meta, $variables);
      }
      switch ($node_id) {
        case '1098': // Series (refugees)
          $data_meta['description'] = 'The refugee crisis isn\'t going away. Be inspired by stories of people helping refugees thrive.';
          $data_meta['img_path'] = '/themes/custom/obw_theme/images/img-share/series_refugees.jpg';
          $data_meta['title'] = $node->getTitle();
          $data_meta['url'] = $node->toUrl()->toString();;
          static::customMetaHeader($data_meta, $variables);
          break;
        case '1100': // Series (food)
          $data_meta['description'] = 'Good food becomes great when it helps someone. Stories that tickle taste buds — and your altruism.';
          $data_meta['img_path'] = '/themes/custom/obw_theme/images/img-share/series_food.jpg';
          $data_meta['title'] = $node->getTitle();
          $data_meta['url'] = $node->toUrl()->toString();
          static::customMetaHeader($data_meta, $variables);
          break;
        case '1099': // Series (Just Do Lah)
          $data_meta['description'] = 'Stories of Singaporeans doing good at home or abroad. Delve into their “Just Do Lah” grit.';
          $data_meta['img_path'] = '/themes/custom/obw_theme/images/img-share/series_just_do_lah.jpg';
          $data_meta['title'] = $node->getTitle();
          $data_meta['url'] = $node->toUrl()->toString();;
          static::customMetaHeader($data_meta, $variables);
          break;
        case '1237': // Our Story
          $metatags = NULL;
          if (isset($node->field_metatags) && !empty($node->field_metatags->getValue())) {
            $metatags = unserialize($node->field_metatags->getValue()[0]['value']);
          }
          if (isset($metatags) && isset($metatags['description'])) {
            $data_meta['description'] = $metatags['description'];
          }
          else {
            $data_meta['description'] = 'We have told many stories of others. Here’s ours.';
          }
          $data_meta['img_path'] = '/themes/custom/obw_theme/images/img-share/avatar_large_orange.png';
          $data_meta['title'] = $node->getTitle() . " | " . $site->get("name");
          $data_meta['url'] = $node->toUrl()->toString();;
          static::customMetaHeader($data_meta, $variables);
          break;
        case '1278': // Our Impact
          $data_meta['description'] = 'The stories we\'ve told. The actions you\'ve taken. Check out the impact we\'ve created together.';
          $data_meta['img_path'] = '/themes/custom/obw_theme/images/img-share/our_impact.jpg';
          $data_meta['title'] = $node->getTitle() . " | " . $site->get("name");
          $data_meta['url'] = $node->toUrl()->toString();;
          static::customMetaHeader($data_meta, $variables);
          break;
        case '148': // Support Us
          $data_meta['description'] = 'You can help us keep telling stories that inspire action. Find out how.';
          $data_meta['img_path'] = '/themes/custom/obw_theme/images/img-share/support_us.jpg';
          $data_meta['title'] = 'Please ' . $node->getTitle() . ' | ' . $site->get("name");
          $data_meta['url'] = $node->toUrl()->toString();;
          static::customMetaHeader($data_meta, $variables);
          break;
        case '169': // Pitch Stories
          $data_meta['description'] = 'Doing good for a better world, or know someone who does? Pitch us the story.';
          $data_meta['img_path'] = '/themes/custom/obw_theme/images/img-share/pitch_stories.jpg';
          $data_meta['title'] = $node->getTitle();
          $data_meta['url'] = $node->toUrl()->toString();;
          static::customMetaHeader($data_meta, $variables);
          break;
        case '158': // Create Stories
          $data_meta['description'] = 'Your storytelling skills can be a force for good. Create stories with us for a better world.';
          $data_meta['img_path'] = '/themes/custom/obw_theme/images/img-share/create_stories.jpg';
          $data_meta['title'] = $node->getTitle();
          $data_meta['url'] = $node->toUrl()->toString();;
          static::customMetaHeader($data_meta, $variables);
          break;
        case '160': // Partner Us
          $data_meta['description'] = 'Believe that storytelling can be a force for good? Let\'s explore how we can work together.';
          $data_meta['img_path'] = '/themes/custom/obw_theme/images/img-share/partner_us.png';
          $data_meta['title'] = $node->getTitle();
          $data_meta['url'] = $node->toUrl()->toString();;
          static::customMetaHeader($data_meta, $variables);
          break;
        case '185': // Choose Your Action
          $data_meta['description'] = 'Want to take action and wondering where to start? We can help in three easy steps.';
          $data_meta['img_path'] = '/themes/custom/obw_theme/images/img-share/choose_your_action.png';
          $data_meta['title'] = $node->getTitle();
          $data_meta['url'] = $node->toUrl()->toString();;
          static::customMetaHeader($data_meta, $variables);
          break;
        case '784': // Our Team
          $data_meta['description'] = $node->field_story_short_description->value;
          $data_meta['img_path'] = '/themes/custom/obw_theme/images/img-share/obw_makan_and_stories.jpg';
          $data_meta['title'] = $node->getTitle() . " | " . $site->get("name");
          $data_meta['url'] = $node->toUrl()->toString();;
          static::customMetaHeader($data_meta, $variables);
          break;
        case '1365':
        case '1368':
          $data_meta['description'] = '2018 was a pivotal year for Our Better World. Here\'s the good we\'ve made together.';
          $data_meta['img_path'] = '/themes/custom/obw_theme/images/img-share/impactreport2018-spotlight.png';
          $data_meta['title'] = $node->getTitle();
          $data_meta['url'] = $node->toUrl()->toString();;
          static::customMetaHeader($data_meta, $variables);
          break;
        case '97': // Contact Us
          $html_head = $variables['page']['#attached']['html_head'];

          $data_meta['description'] = 'Here are ways to contact us. We\'d love to hear from you!                ';
          $meta_description = [
            '#tag' => 'meta',
            '#attributes' => [
              'name' => 'description',
              'content' => $data_meta['description'],
            ],
          ];
          $html_head[] = [$meta_description, 'description'];
          $variables['page']['#attached']['html_head'] = $html_head;
          break;
        case '1408':
          $html_head = $variables['page']['#attached']['html_head'];

          $data_meta['description'] = $node->getTitle();
          $data_meta['title'] = $node->getTitle();
          $data_meta['url'] = $node->toUrl()->toString();;

          $meta_description = [
            '#tag' => 'meta',
            '#attributes' => [
              'name' => 'description',
              'content' => $data_meta['description'],
            ],
          ];
          $html_head[] = [$meta_description, 'description'];
          $variables['page']['#attached']['html_head'] = $html_head;
          break;
        default:
          break;
      }
    }
  }

  public static function entityMetaHeader(&$variables) {

    $current_path = Drupal::routeMatch()->getRouteName();
    switch ($current_path) {
      case 'user.register':
        $data_meta['title'] = 'Sign up for an account | Our Better World';
        $data_meta['description'] = 'Join the Our Better World community to do more with our stories.';
        $data_meta['img_path'] = '/themes/custom/obw_theme/images/img-share/obw_makan_and_stories.jpg';
        $data_meta['url'] = '/user/register';
        static::customMetaHeader($data_meta, $variables);
        break;
      case 'user.login':
        $data_meta['title'] = 'Log In | Our Better World';
        $data_meta['description'] = 'Log in with your registered account to do more with our stories';
        $data_meta['img_path'] = '/themes/custom/obw_theme/images/img-share/obw_makan_and_stories.jpg';
        $data_meta['url'] = '/user/login';
        static::customMetaHeader($data_meta, $variables);
        break;
      default:
        break;
    }
  }

  public static function customMetaHeader($data_meta, &$variables) {

    global $base_url;
    $html_head = $variables['page']['#attached']['html_head'];
    $node = Drupal::routeMatch()->getParameter('node');

    $node_type = (isset($node) && $node instanceof Node) ? $node->getType() : '';

    if ($node_type == 'gsoty_story') {
      $img_url = $data_meta['img_path'];
    }
    else {
      $img_url = $base_url . $data_meta['img_path'];
    }

    // Facebook & Whatsapp
    if (!is_null($html_head) && !empty($html_head)) {

      foreach ($html_head as $key => $value) {
        if ($node_type != 'gsoty_story') {
          if ($data_meta['title'] && in_array('title', $value)
            || $data_meta['url'] && in_array('og_url', $value)) {
            unset($html_head[$key]);
          }
        }

        if ($node_type != 'gsoty_story') {
          if (in_array('og_description', $value)
            || in_array('og_image_secure_url', $value)
            || in_array('og_image_url', $value)
            || in_array('og_image', $value)
            || in_array('description', $value)) {
            unset($html_head[$key]);
          }
        }
        else {
          if (in_array('og_image_secure_url_0', $value)
            || in_array('og_image_url_0', $value)
            || in_array('og_image_0', $value)) {
            unset($html_head[$key]);
          }
        }
      }

      if ($node_type != 'gsoty_story') {
        // Add meta tile
        if ($data_meta['title']) {
          $meta_title = [
            '#tag' => 'meta',
            '#attributes' => [
              'name' => 'title',
              'content' => $data_meta['title'],
            ],
          ];
          $html_head[] = [$meta_title, 'title'];
        }
        // Add meta url
        if ($data_meta['url']) {
          $fb_meta_url = [
            '#tag' => 'meta',
            '#attributes' => [
              'property' => 'og:url',
              'content' => $base_url . $data_meta['url'],
            ],
          ];
          $html_head[] = [$fb_meta_url, 'og_url'];
        }
      }

      $fb_meta_img = [
        '#tag' => 'meta',
        '#attributes' => [
          'property' => 'og:image',
          'content' => $img_url,
        ],
      ];
      $og_meta_img_url = [
        '#tag' => 'meta',
        '#attributes' => [
          'property' => 'og:image:url',
          'content' => $img_url,
        ],
      ];
      $og_meta_img_secure_url = [
        '#tag' => 'meta',
        '#attributes' => [
          'property' => 'og:image:secure_url',
          'content' => $img_url,
        ],
      ];

      if ($node_type != 'gsoty_story') {
        $html_head[] = [$og_meta_img_url, 'og_image_url'];
        $html_head[] = [$og_meta_img_secure_url, 'og_image_secure_url'];
        $html_head[] = [$fb_meta_img, 'og_image'];
      }
      else {
        $html_head[] = [$og_meta_img_url, 'og_image_url_0'];
        $html_head[] = [$og_meta_img_secure_url, 'og_image_secure_url_0'];
        $html_head[] = [$fb_meta_img, 'og_image_0'];
      }


      if ($node_type != 'gsoty_story') {
        if ($data_meta['description']) {
          $fb_meta_description = [
            '#tag' => 'meta',
            '#attributes' => [
              'property' => 'og:description',
              'content' => $data_meta['description'],
            ],
          ];
          $meta_description = [
            '#tag' => 'meta',
            '#attributes' => [
              'name' => 'description',
              'content' => $data_meta['description'],
            ],
          ];
          $html_head[] = [$fb_meta_description, 'og_description'];
          $html_head[] = [$meta_description, 'description'];
        }
      }


      // Twitter
      foreach ($html_head as $key => $value) {
        if ($node_type != 'gsoty_story' && $data_meta['description'] && in_array('twitter_cards_description', $value)) {
          unset($html_head[$key]);
        }
        if (in_array('twitter_cards_image', $value)) {
          unset($html_head[$key]);
        }
      }
      $twitter_meta_img = [
        '#tag' => 'meta',
        '#attributes' => [
          'property' => 'twitter:image',
          'content' => $img_url,
        ],
      ];
      $html_head[] = [$twitter_meta_img, 'twitter_cards_image'];

      if ($node_type != 'gsoty_story' && $data_meta['description']) {
        $twitter_meta_description = [
          '#tag' => 'meta',
          '#attributes' => [
            'property' => 'twitter:description',
            'content' => $data_meta['description'],
          ],
        ];
        $html_head[] = [$twitter_meta_description, 'twitter_cards_description'];
      }


      // Pinterest
      if ($node_type != 'gsoty_story') {
        foreach ($html_head as $key => $value) {
          if ($data_meta['url'] && in_array('pinterest_url', $value)
            || $data_meta['description'] && in_array('pinterest_description', $value)) {
            unset($html_head[$key]);
          }
        }

        if ($data_meta['url']) {
          $pin_meta_url = [
            '#tag' => 'meta',
            '#attributes' => [
              'property' => 'pin:url',
              'content' => $data_meta['url'],
            ],
          ];
          $html_head[] = [$pin_meta_url, 'pinterest_url'];
        }
        if ($data_meta['description']) {
          $pin_meta_description = [
            '#tag' => 'meta',
            '#attributes' => [
              'property' => 'pin:description',
              'content' => $data_meta['description'],
            ],
          ];
          $html_head[] = [$pin_meta_description, 'pinterest_description'];
        }
      }

      $variables['page']['#attached']['html_head'] = $html_head;
    }
  }

}
