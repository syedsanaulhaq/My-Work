<?php

namespace Drupal\obw_utilities\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\form_alter_service\FormAlterBase;

/**
 *  Alter form to add css class and submit action
 *
 * Class CommunityBlogForm.
 *
 * @package Drupal\obw_utilities\Form
 */
class CommunityBlogForm extends FormAlterBase {

  /**
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function alterForm(array &$form, FormStateInterface $form_state) {
    $route_name = \Drupal::routeMatch()->getRouteName();
    $form['#attached']['library'][] = 'obw_utilities/obw-blog-post-css';
    switch ($route_name) {
      case 'obw_utilities.node.add_new_blog_post':
        $form['title']['widget'][0]['#prefix'] = '<div class="blog_post_header">New Blog Post</div>';
        break;
      case 'obw_utilities.node.edit_new_blog_post':
        $form['title']['widget'][0]['#prefix'] = '<div class="blog_post_header">Edit Blog Post</div>';
        break;
    }
    $form['field_story_spotlight']['widget'][0]['subform']['field_para_banner_background']['widget']['current']['#prefix'] = t('Recommended dimensions: 720 x 400px<br/>One file only, no more than 3 MB, optimised JPG');
    $form['field_story_format']['widget']['#default_value'] = ['2', '16'];
    $form['field_story_format']['#access'] = FALSE;
    $form['field_story_thumbnail']['widget'][0]['#title'] = 'Blog hero image';
    $form['field_story_thumbnail']['widget'][0]['#description'] = t('Recommended dimensions: 1440 x 400px<br/>One file only, no more than 3 MB, optimised JPG');
    $form['field_image_story_blur']['widget']['value']['#description'] = 'Apply landscape photo effect (Blurred sides) for images that are not wide enough.<br/>Example: <a href="http://ourbetter.world/PuzGc1">http://ourbetter.world/PuzGc1</a>';
    unset($form['field_story_spotlight']['widget'][0]['subform']['field_para_photo_credit']);
    unset($form['field_story_spotlight']['widget'][0]['subform']['field_para_add_interactive_icon']);
    unset($form['field_story_spotlight']['widget'][0]['subform']['field_para_banner_intro']);
    unset($form['field_story_spotlight']['widget'][0]['subform']['field_para_hide_cause_date']);
    unset($form['#fieldgroups']['group_story_types']);
  }


}
