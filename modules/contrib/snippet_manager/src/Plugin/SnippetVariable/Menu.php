<?php

namespace Drupal\snippet_manager\Plugin\SnippetVariable;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuActiveTrailInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\snippet_manager\SnippetVariableBase;
use Drupal\snippet_manager\SnippetVariableInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides Menu variable type.
 *
 * @SnippetVariable(
 *   id = "menu",
 *   title = @Translation("Menu"),
 *   category = @Translation("Menu"),
 *   deriver = "\Drupal\snippet_manager\Plugin\SnippetVariable\MenuDeriver",
 * )
 */
class Menu extends SnippetVariableBase implements SnippetVariableInterface, ContainerFactoryPluginInterface {

  /**
   * The menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;

  /**
   * The active menu trail service.
   *
   * @var \Drupal\Core\Menu\MenuActiveTrailInterface
   */
  protected $menuActiveTrail;

  /**
   * Constructs a new SystemMenuBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree
   *   The menu tree service.
   * @param \Drupal\Core\Menu\MenuActiveTrailInterface $menu_active_trail
   *   The active menu trail service.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, MenuLinkTreeInterface $menu_tree, MenuActiveTrailInterface $menu_active_trail) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->menuTree = $menu_tree;
    $this->menuActiveTrail = $menu_active_trail;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu.link_tree'),
      $container->get('menu.active_trail')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->configuration;

    $options = range(0, $this->menuTree->maxDepth());
    unset($options[0]);

    $form['level'] = [
      '#type' => 'select',
      '#title' => t('Initial visibility level'),
      '#default_value' => $config['level'],
      '#options' => $options,
      '#description' => t('The menu is only visible if the menu item for the current page is at this level or below it. Use level 1 to always display this menu.'),
      '#required' => TRUE,
    ];

    $options[0] = t('Unlimited');

    $form['depth'] = [
      '#type' => 'select',
      '#title' => t('Number of levels to display'),
      '#default_value' => $config['depth'],
      '#options' => $options,
      '#description' => t('This maximum number includes the initial level.'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['level'] = $form_state->getValue('level');
    $this->configuration['depth'] = $form_state->getValue('depth');
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $menu_name = $this->getDerivativeId();
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);

    // Adjust the menu tree parameters based on the block's configuration.
    $level = $this->configuration['level'];
    $depth = $this->configuration['depth'];
    $parameters->setMinDepth($level);
    // When the depth is configured to zero, there is no depth limit. When depth
    // is non-zero, it indicates the number of levels that must be displayed.
    // Hence this is a relative depth that we must convert to an actual
    // (absolute) depth, that may never exceed the maximum depth.
    if ($depth > 0) {
      $parameters->setMaxDepth(min($level + $depth - 1, $this->menuTree->maxDepth()));
    }

    $tree = $this->menuTree->load($menu_name, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuTree->transform($tree, $manipulators);
    return $this->menuTree->build($tree);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'level' => 1,
      'depth' => 0,
    ];
  }

}
