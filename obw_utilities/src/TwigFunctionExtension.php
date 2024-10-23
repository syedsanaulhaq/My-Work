<?php
/**
 * Created by PhpStorm.
 * User: Lucas Nguyen
 * Date: 4/9/2020
 * Time: 6:06 PM
 */

namespace Drupal\obw_utilities;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Render\Markup;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\obw_utilities\Theme\PreprocessNodeManager;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\responsive_image\Entity\ResponsiveImageStyle;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;
use Drupal\views\Views;
use Twig\TwigFunction;

const MH2020_LANDING_ID = '8';
const MH2020_LANDING_YEAR2 = '2';

class TwigFunctionExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('drupal_background_image', [
        $this,
        'drupalBackgroundImage',
      ]),
      new TwigFunction('get_story_narrative', [
        $this,
        'getStoryNarrative',
      ]),
      new TwigFunction('get_next_narrative_page', [
        $this,
        'getNextNarrativePage',
      ]),
      new TwigFunction('render_mh2021_explore_narrative', [
        $this,
        'renderMh2021ExploreNarrative',
      ]),
      new TwigFunction('render_mh_2021_resource_list', [
        $this,
        'render2021ResourceList',
      ]),
      //5522
      new TwigFunction('render_women_resource_list', [
        $this,
        'renderWomenResourceList',
      ]),
      new TwigFunction('get_women_resource_filter_option', [
        $this,
        'getFilterOptionsResourceWomen',
      ]),
      //5522
      new TwigFunction('twig_get_story_info', [
        $this,
        'twigGetStoryInfo',
      ]),
      new TwigFunction('get_filter_options_resource_mh2021', [
        $this,
        'getFilterOptionsResourceMh2021',
      ]),
      new TwigFunction('get_active_menu_title', [
        $this,
        'getActiveMenuTitle',
      ]),
      new TwigFunction('get_mh2021_narrative_color', [
        $this,
        'getMh2021NarrativeColor',
      ]),
      new TwigFunction('get_mh2021_landing_banner_files', [
        $this,
        'getMh2021LandingBannerFiles',
      ]),
      new TwigFunction('format_decimal_donation_amount', [
        $this,
        'formatDecimalDonationAmount',
      ]),
    ];
  }

  /**
   * Generates a Drupal style tag array containing CSS media queries to apply a
   * responsive background image to a specific DOM element/node. The return
   * value must be assigned correctly. See the return description below.
   *
   * @param string $css_selector
   *    A CSS selector that points to the HTML tag to which a background image
   *   will be applied. Do not include curly braces/brackets. This selector
   *   should be unique to avoid the same background image being accidentally
   *   applied to multiple elements. For example, if you have a Paragraph type
   *   of Hero, you should add a class containing the entity ID to the
   *   Paragraph template, and then pass in that class as part of the selector
   *   here. This way multiple instances of the Hero Paragraph can appear on
   *   the same page with different background images. For example:
   *   '.paragraph--id--3 .hero__image', where '3' is the entity ID retrieved
   *   from the entity using $paragraph_entity->id().
   * @param object $entity
   *    An instance of Drupal\Core\Entity\ContentEntityBase which contains the
   *   image field. Preferably this method has been called from within a
   *   preprocess hook such as THEME_preprocess_paragraph(&$vars), in which
   *   case the entity can be retrieved using $vars['paragraph'].
   * @param string $field_machine_name
   *    The machine name of the image field. For example:
   *   'field_hero_background_image'.
   * @param string $responsive_image_style
   *    The machine name of the Responsive Image Style to be used.
   * @param string $media_entity_field_machine_name
   *    Optional. If the image field is a Media field, and the Image field on
   *   the Image Media Type is custom and not the default 'field_media_image',
   *   pass in the custom field machine name.
   *
   * @return array
   *    A Drupal style tag array containing CSS media queries to apply
   *   responsive background images to a specific HTML tag. Assuming that this
   *   method has been called from inside a preprocess function such as
   *   THEME_preprocess_paragraph(&$vars), the return value should be assigned
   *   to $vars['#attached']['html_head'][], or else calling this method will
   *   have no effect. Returns false if media queries cannot be generated.
   */
  public static function drupalBackgroundImage(string $css_selector, ContentEntityBase $entity, string $field_machine_name, string $responsive_image_style_machine_name, string $media_entity_field_machine_name = NULL) {
    if ($entity instanceof NodeInterface && $entity->getType() == 'story') {
      if ($entity->hasField('field_story_spotlight') && !empty($entity->field_story_spotlight->referencedEntities())) {
        $para_spotlight = $entity->field_story_spotlight->referencedEntities()[0];
        if ($para_spotlight->hasField('field_para_banner_background') && !empty($para_spotlight->get('field_para_banner_background')
            ->getValue())) {
          $entity = $para_spotlight;
          $field_machine_name = 'field_para_banner_background';
          $media_entity_field_machine_name = 'thumbnail';
        }
      }
    }

    // Get field type.
    $field_type = $entity->get($field_machine_name)
      ->getFieldDefinition()
      ->getType();
    // @TODO Take into account if image field is hidden in display settings.

    // If field type is an entity_reference we'll assume this is a Media Image field.
    if ($field_type == 'entity_reference') {
      $moduleHandler = \Drupal::service('module_handler');
      if ($moduleHandler->moduleExists('media')) {
        if (empty($entity->get($field_machine_name)->getValue())) {
          return;
        }
        $media_entity = Media::load($entity->get($field_machine_name)
          ->getValue()[0]['target_id']);
        $file_entity = File::load($media_entity->get($media_entity_field_machine_name)
          ->getValue()[0]['target_id']);
      }
      elseif ($moduleHandler->moduleExists('media_entity')) {
        $media_entity = Media::load($entity->get($field_machine_name)
          ->getValue()[0]['target_id']);

        $file_entity = File::load($media_entity->get($media_entity_field_machine_name)
          ->getValue()[0]['target_id']);
      }
    }
    else {
      if ($field_type == 'image') {
        $file_entity = File::load($entity->get($field_machine_name)
          ->getValue()[0]['target_id']);

      }
      else {
        \Drupal::logger('responsive_background_image')
          ->error('Responsive Background Image field must be of type Image or Media Image.');
        return FALSE;
      }
    }
    $uri = $file_entity->getFileUri();

    // Load Responsive Image Style and mappings.
    $ResponsiveImageStyle = ResponsiveImageStyle::load($responsive_image_style_machine_name);
    $image_style_mappings = array_reverse($ResponsiveImageStyle->getImageStyleMappings());

    // Load theme breakpoints.
    $breakpoint_group = $ResponsiveImageStyle->getBreakpointGroup();
    $breakpoints = \Drupal::service('breakpoint.manager')
      ->getBreakpointsByGroup($breakpoint_group);

    $media_queries_1x = '';
    $media_queries_2x = '';

    // Loop through image style mappings starting from smallest to largest and build media queries.
    foreach ($image_style_mappings as $key => $image_style_mapping) {
      // If this is the first mapping and if a fallback image style is set,
      // add a default background image to media query.
      if ($key == 0) {
        $fallback_image_style = $ResponsiveImageStyle->getFallbackImageStyle();
        $disallowed_options = [
          '_empty image_',
          '_original image_',
        ];
        if (!in_array($fallback_image_style, $disallowed_options)) {
          $media_queries_1x .= self::createFallbackMediaQuery($css_selector, file_url_transform_relative(ImageStyle::load($ResponsiveImageStyle->getFallbackImageStyle())
            ->buildUrl($uri)));
        }
      }

      // Load media query for breakpoint.
      $media_query = $breakpoints[$image_style_mapping['breakpoint_id']]->getMediaQuery();

      // Get path to image using image style.
      $image_path = file_url_transform_relative(ImageStyle::load($image_style_mapping['image_mapping'])
        ->buildUrl($uri));

      // If multiplier is 1x.
      if ($image_style_mapping['multiplier'] == '1x') {
        $media_queries_1x .= self::createSingleMediaQuery($media_query, $css_selector, $image_path, '1x');
      }
      // If multiplier is greater than 1x.
      // @TODO: Should we actually pass the exact multiplier to the media query?
      else {
        $media_queries_2x .= self::createSingleMediaQuery($media_query, $css_selector, $image_path, '2x');
      }
    }
    //    dump($media_queries_1x);

    $all_media_queries = $media_queries_1x . $media_queries_2x;

    // Create unique id for use in style tag machine name.
    $entity_id = $entity->id();
    $uuid = \Drupal::service('uuid')->generate();

    // Return style tag array.

    $response = [
      [
        '#tag' => 'style',
        // We have to add this as insecure markup, otherwise auto-escaping escapes the & to &amp; and breaks image urls.
        // See https://www.drupal.org/project/responsive_background_image/issues/3067838#comment-13208830
        '#value' => Markup::create($all_media_queries),
        // Should be last element of <head>, currently impossible
        // due to core issue #2391025, so we'll at least set this as
        // high as possible behind the meta tags,
        // but it won't get behind the <title>.
        '#weight' => 99999,
      ],
      'responsive-background-image-' . $entity_id . '-' . $uuid,
    ];

    if (!empty($response)) {
      return [
        '#attached' => [
          'html_head' => [
            $response,
          ],
        ],
      ];
    }
  }


  /**
   * @param string $css_selector
   *    CSS selector for element that will have background image.
   * @param string $fallback_image_path
   *    Path to the fallback image.
   *
   * @return string
   *    A CSS property for a fallback background image.
   */
  private static function createFallbackMediaQuery(string $css_selector, string $fallback_image_path) {
    return '
    ' . $css_selector . ' {
      background-image: url(' . $fallback_image_path . ');
    }';
  }

  /**
   * @param string $media_query
   *    CSS media query from Breakpoint group config.
   * @param string $css_selector
   *    CSS selector for element that will have background image.
   * @param string $image_path
   *    Path to image.
   * @param string $multiplier
   *    Responsive image multiplier/pixel ratio.
   *
   * @return string
   *    A single CSS media query for one one window width and one
   *   multiplier/pixel ratio.
   */
  private static function createSingleMediaQuery(string $media_query, string $css_selector, string $image_path, string $multiplier) {
    switch ($multiplier) {
      case '1x':
        $min_pixel_ratio = '';
        break;

      default:
        $min_pixel_ratio = 'and (min-device-pixel-ratio: 1.5)';
        break;
    }

    return '
    @media ' . $media_query . $min_pixel_ratio . ' {
      ' . $css_selector . ' {
        background-image: url(' . $image_path . ');
      }
    }';
  }

  /**
   * Get story narrative from landing Year 3 Preamble.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public static function getStoryNarrative($landing_nid, $type, $current_type, $narrative_id, $cardClass): string {
    $data = [];
    $render = '';
    $node = Node::load($landing_nid);

    if ($narrative_id == '1' && $type == 'letter') {
      $letter_id = !empty($node->get('field_landing_narrative_ref')
        ->getValue()) ? $node->get('field_landing_narrative_ref')
        ->getValue() : [];
      $letter_id = !empty($letter_id) ? reset($letter_id)['target_id'] : [];
      if ($letter_id && !empty($letter_id)) {
        if (Node::load($letter_id) instanceof NodeInterface) {
          $letter_node = Node::load($letter_id);
          $letter_alias = $letter_node->toUrl()->toString();
          $data[$type]['letter_alias'] = $letter_alias;
          $data[$type]['letter_description'] = Node::load($letter_id)
            ->get('field_story_short_description')
            ->getValue();
          $render = self::renderHTML($data, $type, FALSE, $narrative_id, $cardClass);
        }
      }
    }


    if (($narrative_id == '1' || $narrative_id == '2' || $narrative_id == '3') && $type != 'letter') {
      $data_from_landing = NULL;
      if (!empty($node->get('field_story_sub_stories')
          ->getValue()) && $type == 'story') {
        $sub_stories = $node->get('field_story_sub_stories')->getValue();
        $data_from_landing = TRUE;
      }
      if (!empty($node->get('field_landing_resource_stories')
          ->getValue()) && $type == 'resource') {
        $sub_stories = $node->get('field_landing_resource_stories')->getValue();
        $data_from_landing = TRUE;
      }
      if (!empty($node->get('field_landing_blogs')
          ->getValue()) && $type == 'blog') {
        $sub_stories = $node->get('field_landing_blogs')->getValue();
        $data_from_landing = TRUE;
      }
      if ($data_from_landing && isset($sub_stories)) {
        if (isset($sub_stories[$narrative_id - 1])) {
          $nid = $sub_stories[$narrative_id - 1]['target_id'];
          $node = Node::load($nid);
          if (isset($_COOKIE['narrative_year_3_read_nodes'])) {
            if (in_array($nid, json_decode($_COOKIE['narrative_year_3_read_nodes']))) {
              $data[$type]['already_read'] = TRUE;
            }
          }
          $data[$type]['story_alias'] = $node
            ->toUrl()
            ->toString();
          if (!empty($node->get('field_story_spotlight')->getValue())) {
            if (isset($node->get('field_story_spotlight')
                  ->getValue()[0]) && $node->get('field_story_spotlight')
                ->getValue()[0]['target_id']) {
              $pid = $node->get('field_story_spotlight')
                ->getValue()[0]['target_id'];
              $paragraph = Paragraph::load($pid);
              $img_background = !empty($paragraph->get('field_para_banner_background')
                ->getValue()) ? $paragraph->get('field_para_banner_background')
                ->getValue()[0]['target_id'] : NULL;
              if ($img_background) {
                $media = Media::load($img_background);
                $file = PreprocessNodeManager::buildImageStyle($media->field_media_image->entity->uri->value, 'medium_600x400');
                $data[$type]['spotlight_image'] = $file;
                if (!empty($media->field_media_image->getValue()[0]['alt'])) {
                  $data[$type]['spotlight_alt'] = $media->field_media_image->getValue()[0]['alt'];
                }
              }
              $spotlight_title = $paragraph->get('field_para_banner_title')
                ->getValue();
              if (!empty($spotlight_title)) {
                $data[$type]['spotlight_title'] = $spotlight_title[0]['value'];
              }

              $spotlight_description = $paragraph->get('field_para_banner_intro')
                ->getValue();
              if (!empty($spotlight_description)) {
                $data[$type]['spotlight_description'] = $spotlight_description[0]['value'];
              }
            }
          }
          $reading_time = PreprocessNodeManager::getReadingTimeNode($node, $variables, TRUE);
          $data[$type]['reading_time'] = $reading_time;
          if (!empty($node->get('published_at')->getValue())) {
            $published_date = $node->get('published_at')
              ->getValue()[0]['value'];
            $data[$type]['published_date'] = date("d M Y", $published_date);
          }
          $story_title = $node->getTitle();
          $story_description = $node->get('field_story_short_description')
            ->getValue();
          $img = $node->get('field_story_thumbnail')->getValue();
          $story_img = PreprocessNodeManager::buildImageStyle(File::load($img[0]['target_id'])
            ->getFileUri(), 'medium_600x400');
          $story_alt = $img[0]['alt'];
          $data[$type]['story_title'] = $story_title;
          $data[$type]['story_description'] = $story_description[0]['value'];
          $data[$type]['story_img'] = $story_img;
          $data[$type]['story_alt'] = $story_alt;
          $tag = $node->get('field_mental_health_label')->getValue();
          if (!empty($tag)) {
            foreach ($tag as $tg) {
              $data[$type]['tag'][] = Term::load($tg['target_id'])->label();
            }
          }
          if ($uid = $node->field_story_blog_author->target_id) {
            $user = User::load($uid);
            $profile_type = 'public_profile';
            $entityTypeManager = \Drupal::entityTypeManager();
            /**
             * @var $public_profile \Drupal\profile\Entity\Profile
             */
            $public_profile = $entityTypeManager->getStorage('profile')
              ->loadByUser($user, $profile_type);
            if ($public_profile) {
              if ($public_profile->field_public_profile_name->value && !empty($public_profile->field_public_profile_name->value)) {
                $full_name = $public_profile->field_public_profile_name->value;
              }
            }
            else {
              $full_name = $user->field_account_first_name->value . ' ' . $user->field_account_last_name->value;
            }
            if ($img = $user->user_picture->target_id) {
              /** @var \Drupal\obw_utilities\ObwUtilitiesService $obw_utilities_services */
              $obw_utilities_services = \Drupal::service('obw_utilities.service');

              $img_src = $obw_utilities_services->getImgUrlByImageStyleForFileEntity($img, 'thumbnail');
            }
          }
          $data[$type]['author_img'] = $img_src ?? NULL;
          $data[$type]['author_name'] = $full_name ?? NULL;
          $format_mh2020_type = [
            1 => 'story',
            2 => 'blog',
            3 => 'resource',
            4 => 'letter',
          ];
          $current_type = $format_mh2020_type[$current_type];
          $is_current = $type == $current_type;
          $render = self::renderHTML($data, $type, $is_current, $narrative_id, $cardClass);
        }
      }
    }
    return $render;
  }

  /**
   * Render HTML for twig.
   */
  private static function renderHTML($data, $type, $is_current, $narrative_id, $cardClass): string {
    $response = '';
    $narrative_color = self::getMh2021NarrativeColor($narrative_id);
    $data = $data[$type];
    $alias = $data['story_alias'] ?? '';
    $tag = '';
    $reading_time = $data['reading_time'] ?? '';
    $published_date = $data['published_date'] ?? '';
    //default
    $img_url = $data['story_img'] ?? '';
    $img_alt = $data['story_alt'] ?? '';
    $title = $data['story_title'] ?? '';
    $description = $data['story_description'] ?? '';
    //spotlight
    $img_url = $data['spotlight_image'] ?? $img_url;
    $img_alt = $data['spotlight_alt'] ?? $img_alt;
    $title = $data['spotlight_title'] ?? $title;
    $description = $data['spotlight_description'] ?? $description;
    $description = strlen($description) > 85 ? substr($description, 0, 85) . "..." : $description;
    if ($type == 'story') {
      if (isset($data['tag'])) {
        $label = $data['tag'];
        $tag = '<div class="card--labels">';
        foreach ($label as $lb) {
          $tag .= '<span class="label--item">' . $lb . '</span>';
        }
        $tag .= '</div>';
      }
    }
    $class_is_current = ($is_current == TRUE || isset($data['already_read']) && $data['already_read']) ? ' current-reading-item' : '';
    $nav_tag = ' nav' . $narrative_id . ' ' . $narrative_color;
    switch ($type) {
      case 'story':
      case 'resource':
        $response = '<div class="obw-stories--item ' . $cardClass . ' ' . $class_is_current . '">
                <div class="obw-stories--card card">
                  <a href="' . $alias . '"
                     class="card--link"></a>
                  <div class="card--thumbnail">
                    <img
                      src="' . $img_url . '"
                      alt="' . $img_alt . '">
                    <span class="thumbnail-mobile"
                          style="background-image: url(' . $img_url . ');"></span>';
        if ($is_current) {
          $response .= '<span class="current-reading">
                    <img src="/themes/custom/obw_theme/images/mental-health-2021/glasses.svg" alt="">
                    You’re here now
                  </span>';
        }
        else {
          if (isset($data['already_read']) && $data['already_read']) {
            $response .= '<span class="current-reading">
                    <img src="/themes/custom/obw_theme/images/mental-health-2021/glasses.svg" alt="">
                    Tick! You’ve read this
                  </span>';
          }
          else {
            $response .= '
                    <span class="story-type' . $nav_tag . '">
                  ' . ucfirst($type == 'resource' ? 'Toolkit' : $type) . '
                </span>';
          }

        }
        $response .= '
                  </div>
                  <div class="card--body">
                    <div class="card--meta">
                      <ul>
                        <li>' . $published_date . '</li>
                        <li> ' . $reading_time . '</li>
                      </ul>
                    </div>
                    <div class="card--description">
                      <h3 class="truncate" truncate="2">' . $title . '</h3>
                      <p class="truncate" truncate="2">' . $description . '</p>
                    </div>
                 ' . $tag . '
                 </div>
                </div>
              </div>';
        break;
      case 'blog':
        $user_img = $data['author_img'] ?? '';
        $user_name = $data['author_name'] ?? '';
        $response = '<div class="obw-stories--item obw-stories-blog-post ' . $cardClass . ' ' . $class_is_current . '">
                <div class="obw-stories--card card">
                  <a href="' . $alias . '"
                    class="card--link"></a>
                  <div class="card--thumbnail">
                    <img
                      src="' . $img_url . '"
                      alt="thumbnail">
                    <span class="thumbnail-mobile"
                          style="background-image: url(' . $img_url . ');"></span>';
        if ($is_current) {
          $response .= '<span class="current-reading">
                    <img src="/themes/custom/obw_theme/images/mental-health-2021/glasses.svg" alt="">
                    You’re here now
                  </span>';
        }
        else {
          if (isset($data['already_read']) && $data['already_read']) {
            $response .= '<span class="current-reading">
                    <img src="/themes/custom/obw_theme/images/mental-health-2021/glasses.svg" alt="">
                    Tick! You’ve read this
                  </span>';
          }
          else {
            $response .= '
                    <span class="story-type' . $nav_tag . '">
                  ' . ucfirst($type) . '
                </span>';
          }
        }
        $response .= '</div>
                  <div class="card--body card--body-blogs-post">
                    <div class="card--meta">
                      <ul>
                        <li>' . $published_date . '</li>
                        <li> ' . $reading_time . '</li>
                      </ul>
                    </div>
                    <div class="card--description">
                      <h3 class="truncate" truncate="2">
                        ' . $title . '
                      </h3>
                    </div>
                    <div class="card--author author">
                      <div class="author--thumbnail">
                        <img
                          src="' . $user_img . '"
                          alt="' . $user_name . '">
                      </div>
                      <div class="author--description">
                        ' . $user_name . '
                      </div>
                    </div>
                  </div>

                </div>
              </div>';

        break;
      case 'letter':
        $alias = $data['letter_alias'] ?? '';
        //        if (!empty($alias) && $cardClass == 'col-md-4 shorthand-letter') {
        //          $alias = $alias . '?shorthand=true';
        //        }
        $description = (isset($data['letter_description']) && !empty($data['letter_description'])) ? $data['letter_description'][0]['value'] : '';
        $description = strlen($description) > 85 ? substr($description, 0, 85) . "..." : $description;
        $response = '<div class="obw-stories--item ' . $cardClass . '">
          <div class="obw-stories--card card">
          <a href="' . $alias . '" class="card--link" target="_blank"></a>
          <div class="card--thumbnail not-full-width">
          <img src="/themes/custom/obw_theme/images/mental-health-2021/letter-to-myself.png"
          alt="thumbnail">
          </div>
          <div class="card--body a-letter-to-myself">
          <div class="card--description auto-height">
          <h3 class="truncate" truncate="2">A Love Letter to Myself</h3>
          <p class="truncate" truncate="2">' . $description . '</p>
          </div>
          </div>
          </div>
          </div>';
        break;
    }
    return $response;
  }

  /**
   * Get narrative next page.
   */
  public static function getNextNarrativePage($landing_nid, $type, $narrative_id): array {
    $node = Node::load($landing_nid);
    $data = [];
    $format_mh2020_type = [
      1 => 'story',
      2 => 'blog',
      3 => 'resource',
      4 => 'story',
    ];
    $type = $format_mh2020_type[$type];
    $define_next = [
      'story' => 'field_landing_blogs',
      'blog' => 'field_landing_resource_stories',
    ];
    $define_next_label = [
      'story' => 'Blog',
      'blog' => 'Resource',
    ];

    if ($type != 'resource') {
      if (!empty($node->get($define_next[$type])->getValue())) {
        $next = $node->get($define_next[$type])->getValue();
        if (isset($next[$narrative_id - 1])) {
          $data['nid'] = $next[$narrative_id - 1]['target_id'];
          $data['label'] = $define_next_label[$type];
        }
      }
    }

    else {
      if (!empty($node->field_landing_narrative_section->getValue())) {
        $tag_data = $node->field_landing_narrative_section->getValue();
        $args_preamble = [1, 2, 0];
        if (isset($tag_data[$args_preamble[$narrative_id - 1]]) && $tag_data[$args_preamble[$narrative_id - 1]]) {
          $narrative_tag = Paragraph::load($tag_data[$args_preamble[$narrative_id - 1]]['target_id'])
            ->get('field_narrative_anchor_tag')
            ->getValue();
          $narrative_tag = array_column($narrative_tag, 'value');
          $narrative_tag = reset($narrative_tag);
          $data['narrative_tag'] = $narrative_tag;
        }
      }

      $data['nid'] = $landing_nid;
      $data['label'] = 'Chapter';
    }

    return $data;

  }

  /**
   * Render ExploreBG Section.
   */
  public static function renderMh2021ExploreNarrative($landing_nid, $narrative_id): string {
    $etm = \Drupal::entityTypeManager();
    $html = '';
    $landing_node = $etm->getStorage('node')->load($landing_nid);
    if (!$landing_node || !$narrative_id) {
      return $html;
    }
    $narrative_bg = [
      '1' => '/themes/custom/obw_theme/images/mental-health-2021/explore-bg.png',
      '2' => '/themes/custom/obw_theme/images/mental-health-2021/explore-bg2.jpg',
      '3' => '/themes/custom/obw_theme/images/mental-health-2021/explore-bg3.jpg',
    ];
    if ($landing_node->hasField('field_landing_narrative_section') && !empty($landing_node->field_landing_narrative_section->getValue())) {
      $landing_url = $landing_node->toUrl()->toString();
      $narrative_items = $landing_node->field_landing_narrative_section->referencedEntities();
      unset($narrative_items[$narrative_id - 1]);
      foreach ($narrative_items as $narrative_item) {
        $landing_url_hashtag = $landing_url;
        if (!empty($narrative_item->field_narrative_card_header->value)
          && !empty($narrative_item->field_narrative_card_desc->value)) {
          if (!empty($narrative_item->field_narrative_anchor_tag->value)) {
            $landing_url_hashtag .= $narrative_item->field_narrative_anchor_tag->value;
          }
          $html .= '<div class="obw-facts--item">
                  <a class="obw-facts--link"
                     href="' . $landing_url_hashtag . '"
                     target="_blank">&nbsp;</a>
                  <div class="obw-facts--icon"><img alt="heart"
                                                    src="/themes/custom/obw_theme/images/heart.svg"/></div>
                  <div class="obw-facts--desc">
                    <h3>' . $narrative_item->field_narrative_card_header->value . '</h3>
                    <p>' . $narrative_item->field_narrative_card_desc->value . '</p>
                  </div>
                </div>';
        }
      }
    }
    return '<div class="obw-facts obw-section"
           style="background-image: url(' . $narrative_bg[$narrative_id] . '); background-size: cover; background-repeat: no-repeat;">
        <div class="container">
          <div class="obw-section--heading" role="sub-heading">
            <h3 class="text-burntSienna" role="heading">Explore Other Chapters</h3>
          </div>
          <div class="obw-section--body">
            <div class="obw-facts--items">
              <div class="row">' . $html . '
              </div>
            </div>
          </div>
        </div>
      </div>';
  }

  /**
   * Render next pre button for individual page.
   */
  public static function render2021ResourceList($filter_exposed, $year = NULL) {
    $data = [];
    if (!is_numeric($filter_exposed)) {
      $filter_exposed = (array) json_decode($filter_exposed);
      $legit = TRUE;
      if (is_array($filter_exposed) && !empty($filter_exposed)) {
        foreach ($filter_exposed as $key => $filter) {
          if (!in_array($filter, [1, 2, 3, 4, 5, 6]) || !in_array($key, [
              1,
              2,
              3,
              4,
              5,
              6,
            ])) {
            $legit = FALSE;
          }
        }
        $etm = \Drupal::entityTypeManager();
        $nodes = $etm->getStorage('node')->loadByProperties([
          'type' => 'landing_page',
          'field_landing_format' => MH2020_LANDING_ID,
          'field_using_year_1_2_template' => MH2020_LANDING_YEAR2,
        ]);

        $node = !empty($nodes) ? reset($nodes) : [];

        if ($legit && in_array($year, [1, 2]) && $node) {
          $view = Views::getView('load_sub_stories_of_the_landing_page');
          if (is_object($view)) {
            $view->setArguments([$node->id()]);
            $view->setDisplay('block_mental_health_2020_resouces_for_year_' . $year);
            if ($year == 1) {
              $view->setExposedInput(['field_story_mh2020_resource_tags_target_id_1' => $filter_exposed]);
            }
            else {
              $view->setExposedInput(['field_story_mh2020_resource_tags_target_id' => $filter_exposed]);
            }
            $view->preExecute();
            $view->execute();
            $data = $view->result;
            self::sortDataArgs($data, $filter_exposed, $year, $view);
          }
        }
        else {
          return [];
        }
      }
      else {
        return [];
      }
    }
    else {
      if (!in_array($filter_exposed, [1, 2, 3, 4, 5, 6])) {
        return [];
      }
      $view = Views::getView('story');
      $view->setDisplay('mh2021_resources_help');
      $view->setExposedInput(['field_story_mh2020_resource_tags_target_id' => $filter_exposed]);
      $view->preExecute();
      $view->execute();
      $data = $view->result;
      self::sortDataArgs($data, [99], 2, $view);
    }

    return $data;
  }
  //5522
  public static function renderWomenResourceList($filter_exposed, $year = NULL) {
    $data = [];
    $view = \Drupal\views\Views::getView('story');
    $view->setDisplay('women_resource');
    $view->setExposedInput(['filter' => $filter_exposed]);
    $view->preExecute();
    $view->execute();
    $data = $view->result;
    return $data;
  }
  public static function getFilterOptionsResourceWomen() {
    $view = \Drupal\views\Views::getView('story');
    $options = [];
    $view->setDisplay('women_resource');
    $view->initHandlers();
    if (!empty($view->getDisplay()
      ->getOption('filters')['field_story_mh2020_resource_tags_target_id']['value'])) {
      $filter_options = $view->getDisplay()
        ->getOption('filters')['field_story_mh2020_resource_tags_target_id']['value'];
      foreach ($filter_options as $key => $option) {
        $options[$key] = \Drupal\taxonomy\Entity\Term::load($key)->get('name')->value;
      }
    }
    return $options;
  }  
  //5522
  /**
   * Re render based on filter options.
   */
  private static function sortDataArgs(&$data, $filter, $year, $view) {
    $data_render = $view->render();
    $empty_temp = [];
    if ($year == 2) {
      if (!in_array(1, $filter)) {
        foreach ($data_render['#rows'] as $parent) {
          foreach ($parent['#rows'] as $child) {
            if (isset($child['#rows'])) {
              foreach ($child['#rows'] as $sub_child) {
                $empty_temp[] = ['nid' => $sub_child['#node']->id()];
              }
            }
            else {
              $empty_temp[] = ['nid' => $child['#node']->id()];
            }
          }
        }
        $data = $empty_temp;

      }
    }
    else {
      if (count($filter) == 1) {
        foreach ($data_render['#rows'] as $parent) {
          foreach ($parent['#rows'] as $child) {
            $empty_temp[] = ['nid' => $child['#node']->id()];
          }
        }
        $data = $empty_temp;
      }
    }
  }

  public static function twigGetStoryInfo($node, $image_style = 'medium_730x400') {
    if (is_numeric($node)) {
      $node = Node::load($node);
    }
    $story_title = $node->getTitle();
    $story_thumbnail_url = PreprocessNodeManager::getThumbnailUrlByImageStyleForStory($image_style, $node, 'field_story_feature_media');
    $story_desc = !empty($node->field_story_short_description->value) ? $node->field_story_short_description->value : '';
    if ($node->hasField('field_story_spotlight') && !empty($node->field_story_spotlight->referencedEntities())) {
      $story_spotlight_para = $node->field_story_spotlight->referencedEntities()[0];
      if (isset($story_spotlight_para->field_para_banner_title) && !empty($story_spotlight_para->field_para_banner_title->value)) {
        $story_title = $story_spotlight_para->field_para_banner_title->value;
      }
      if (PreprocessNodeManager::getThumbnailUrlByImageStyleForSpotlight($story_spotlight_para, $image_style)) {
        $story_thumbnail_url = PreprocessNodeManager::getThumbnailUrlByImageStyleForSpotlight($story_spotlight_para, $image_style);
      }

      if (isset($story_spotlight_para->field_para_banner_intro) && !empty($story_spotlight_para->field_para_banner_intro->value)) {
        $story_desc = $story_spotlight_para->field_para_banner_intro->value;
      }
    }
    $reading_time = [];
    return [
      'title' => $story_title,
      'thumbnail' => $story_thumbnail_url,
      'desc' => $story_desc,
      'reading_time' => PreprocessNodeManager::getReadingTimeNode($node, $reading_time, TRUE) ?? '',
      'node' => $node,
    ];
  }

  public static function getFilterOptionsResourceMh2021() {
    $view = Views::getView('story');
    $options = [];
    if (is_object($view)) {
      $view->setDisplay('mh2021_resources_help');
      $view->initHandlers();
      if (!empty($view->getDisplay()
        ->getOption('filters')['field_story_mh2020_resource_tags_target_id']['group_info']['group_items'])) {
        $filter_options = $view->getDisplay()
          ->getOption('filters')['field_story_mh2020_resource_tags_target_id']['group_info']['group_items'];
        foreach ($filter_options as $key => $option) {
          if (!empty($option['title']) && $option['title'] != 'All Resources') {
            $options[$key] = $option['title'];
          }
        }
      }
    }
    return $options;
  }

  public static function getActiveMenuTitle($menu_name) {
    $active_menu_title = '';
    if ($menu_name == NULL) {
      return $active_menu_title;
    }
    $parameters = new MenuTreeParameters();
    /** Optionally limit to enabled items. */
    $parameters->onlyEnabledLinks();
    /** Optionally set active trail. */
    $menu_active_trail = \Drupal::service('menu.active_trail')
      ->getActiveTrailIds($menu_name);
    /** If exists $menu_active_trail */
    if ($menu_active_trail) {
      $parameters->setActiveTrail($menu_active_trail);
      /** @var Drupal\Core\Menu\MenuLinkInterface $tree */
      $tree = \Drupal::menuTree()->load($menu_name, $parameters);
      /** Finally, build a renderable array from the transformed tree. */
      $menu_tree = \Drupal::menuTree();
      $manipulators = [
        ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
      ];
      $tree = $menu_tree->transform($tree, $manipulators);
      $menu_tmp = $menu_tree->build($tree);
      if (!empty($menu_tmp['#items'])) {
        foreach ($menu_tmp['#items'] as $menu_item) {
          if ($menu_item['in_active_trail']) {
            $active_menu_title = $menu_item['title'];
          }
        }
      }
    }
    return $active_menu_title;
  }

  public static function getMh2021NarrativeColor($narrative_id) {
    $narrative_color = '';
    if (!$narrative_id) {
      return $narrative_color;
    }
    $narrative_colors = [
      '1' => 'panda',
      '2' => 'lemon',
      '3' => 'purple',
    ];
    $narrative_color = $narrative_colors[$narrative_id];
    return $narrative_color;
  }

  public static function getMh2021LandingBannerFiles($node) {
    $banner_files = [
      'img' => [],
      'video' => [],
    ];
    if ($node->hasField('field_landing_featured_media')
      && !empty($node->field_landing_featured_media->referencedEntities())) {
      /** @var Media $media */
      foreach ($node->field_landing_featured_media->referencedEntities() as $media) {
        if ($media->hasField('field_media_image') && !empty($media->field_media_image->entity)) {
          $banner_files['img'] = [
            'url' => $media->field_media_image->entity->createFileUrl(),
            'alt' => $media->field_media_image->getValue()[0]['alt'],
          ];
        }
        elseif ($media->hasField('field_media_video_file') && !empty($media->field_media_video_file->entity)) {
          $video_type = $media->field_media_video_file->entity->getMimeType();
          if ($video_type == 'video/quicktime') {
            $video_type = 'video/mp4; codecs="hvc1"';
          }
          $banner_files['video'][] = [
            'url' => $media->field_media_video_file->entity->createFileUrl(),
            'type' => $video_type,
          ];
        }
      }
    }
    return $banner_files;
  }

  public static function formatDecimalDonationAmount($amount) {
    if (empty($amount)) {
      return 0;
    }
    return str_replace('.00', '', number_format($amount, 2, '.', ''));
  }

}


