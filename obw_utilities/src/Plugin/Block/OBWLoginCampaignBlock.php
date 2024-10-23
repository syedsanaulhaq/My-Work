<?php

namespace Drupal\obw_utilities\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'OBWLoginCampaignBlock' block.
 *
 * @Block(
 *  id = "obw_login_campaign_block",
 *  admin_label = @Translation("Obw Login Campaign Block"),
 * )
 */
class OBWLoginCampaignBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use RedirectDestinationTrait;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      ] + parent::defaultConfiguration();
  }

  /**
   * Constructs a new UserLoginBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $route_name = $this->routeMatch->getRouteName();
    if ($account->isAnonymous() && !in_array($route_name, [
        'user.login',
        'user.logout',
      ])) {
      return AccessResult::allowed()
        ->addCacheContexts(['route.name', 'user.roles:anonymous']);
    }
    return AccessResult::forbidden();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $form = \Drupal::formBuilder()->getForm('Drupal\user\Form\UserLoginForm');
    unset($form['name']['#attributes']['autofocus']);
    // When unsetting field descriptions, also unset aria-describedby attributes
    // to avoid introducing an accessibility bug.
    // @todo Do this automatically in https://www.drupal.org/node/2547063.
    unset($form['name']['#description']);
    unset($form['name']['#attributes']['aria-describedby']);
    unset($form['pass']['#description']);
    unset($form['pass']['#attributes']['aria-describedby']);
    $form['name']['#size'] = 15;
    $form['pass']['#size'] = 15;

    // Instead of setting an actual action URL, we set the placeholder, which
    // will be replaced at the very last moment. This ensures forms with
    // dynamically generated action URLs don't have poor cacheability.
    // Use the proper API to generate the placeholder, when we have one. See
    // https://www.drupal.org/node/2562341. The placholder uses a fixed string
    // that is
    // Crypt::hashBase64('\Drupal\user\Plugin\Block\UserLoginBlock::build');
    // This is based on the implementation in
    // \Drupal\Core\Form\FormBuilder::prepareForm(), but the user login block
    // requires different behavior for the destination query argument.
    $placeholder = 'form_action_p_4r8ITd22yaUvXM6SzwrSe9rnQWe48hz9k1Sxto3pBvE';

    $form['#attached']['placeholders'][$placeholder] = [
      '#lazy_builder' => [
        '\Drupal\obw_utilities\Plugin\Block\OBWLoginCampaignBlock::renderPlaceholderFormAction',
        [],
      ],
    ];
    $form['#action'] = $placeholder;
    $form["#theme"] = ["obw_user_login_campaign_form"];
    $form['#obw_campaign_title_login'] = $this->label();
    $form['#obw_campaign_description_login'] = $this->configuration['description']['value'];
    $current_url = Url::fromRoute('<current>')->toString();
    $form['#current_url'] = $current_url;
    $session_handler = \Drupal::service('obw_social.session_handler');
    $session_handler->set('login_by_campaign_form_' . $current_url, TRUE);
    $session_handler->set('login_by_campaign_form_params_' . $current_url, [
      'label' => $this->label(),
      'des' => $this->configuration['description']['value'],
    ]);
    $form['#attributes']['class'][] = 'user-login-form-campaign';
    //TODO: check the destination after login
    $build = [];
    $build['user_login_form'] = $form;
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['description'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Description'),
      '#default_value' => $this->configuration['description']['value'],
      '#format' => $this->configuration['description']['format'],
      '#weight' => '1',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['description'] = $form_state->getValue('description');
  }

  /**
   * #lazy_builder callback; renders a form action URL including destination.
   *
   * @return array
   *   A renderable array representing the form action.
   *
   * @see \Drupal\Core\Form\FormBuilder::renderPlaceholderFormAction()
   */
  public static function renderPlaceholderFormAction() {
    return [
      '#type' => 'markup',
      '#markup' => Url::fromRoute('user.login', [], [
        'query' => \Drupal::destination()
          ->getAsArray(),
        'external' => FALSE,
      ])->toString(),
      '#cache' => ['contexts' => ['url.path', 'url.query_args']],
    ];
  }

}
