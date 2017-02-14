<?php

namespace Drupal\snippet_manager\Form;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Display\VariantManager;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Path\AliasStorageInterface;
use Drupal\Core\Url;
use Drupal\snippet_manager\SnippetVariablePluginManager;
use Drupal\user\PermissionHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Snippet form.
 *
 * @property \Drupal\snippet_manager\SnippetInterface $entity
 */
class SnippetForm extends EntityForm {

  /**
   * The variant manager.
   *
   * @var \Drupal\Core\Display\VariantManager
   */
  protected $variantManager;

  /**
   * The variable manager.
   *
   * @var \Drupal\snippet_manager\SnippetVariablePluginManager
   */
  protected $variableManager;

  /**
   * The path alias storage.
   *
   * @var \Drupal\Core\Path\AliasStorageInterface
   */
  protected $pathAliasStorage;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler to invoke the alter hook.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The permission handler.
   *
   * @var \Drupal\user\PermissionHandlerInterface
   */
  protected $permissionHandler;

  /**
   * Constructs a snippet form object.
   *
   * @param \Drupal\Core\Display\VariantManager $variant_manager
   *   The variant manager.
   * @param \Drupal\snippet_manager\SnippetVariablePluginManager $variable_manager
   *   The variable manager.
   * @param \Drupal\Core\Path\AliasStorageInterface $path_alias_storage
   *   The path alias storage.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\user\PermissionHandlerInterface $permission_handler
   *   The permission handler.
   */
  public function __construct(VariantManager $variant_manager, SnippetVariablePluginManager $variable_manager, AliasStorageInterface $path_alias_storage, ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, PermissionHandlerInterface $permission_handler) {
    $this->variantManager = $variant_manager;
    $this->variableManager = $variable_manager;
    $this->pathAliasStorage = $path_alias_storage;
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->permissionHandler = $permission_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.display_variant'),
      $container->get('plugin.manager.snippet_variable'),
      $container->get('path.alias_storage'),
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('theme_handler'),
      $container->get('user.permissions')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);

    if (!$this->entity->isNew()) {
      $form['#title'] = t('Edit @label', ['@label' => $this->entity->label()]);
    }

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\snippet_manager\Entity\Snippet::load',
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    // -- Code.
    $code = $this->entity->getCode();

    $form['code'] = [
      '#title' => t('Code'),
      '#type' => 'text_format',
      '#default_value' => $code['value'],
      '#rows' => 10,
      '#format' => $code['format'],
      '#editor' => FALSE,
      '#required' => TRUE,
      '#attributes' => ['class' => ['snippet-code-textarea']],
      '#element_validate' => ['::validateTemplate'],
    ];

    // -- Variables.
    $header = [
      t('Name'),
      t('Type'),
      t('Plugin'),
      [
        'data' => t('Operations'),
        'class' => 'sm-snippet-operations',
      ],
    ];

    $form['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => [],
      '#empty' => t('Variables are not configured yet.'),
      '#caption' => t('Variables'),
    ];

    $variables = (array) $this->entity->getVariables();
    foreach ($variables as $variable_name => $variable) {

      $variable_plugin = FALSE;
      try {
        $variable_plugin = $this->variableManager->createInstance($variable['plugin_id'], $variable['configuration']);
      }
      catch (PluginNotFoundException $exception) {
        drupal_set_message(t('The %plugin does not exist.', ['%plugin' => $variable['plugin_id']]), 'warning');
      }

      $route_parameters = [
        'snippet' => $this->entity->id(),
        'variable' => $variable_name,
      ];

      $operation_links = [];
      if ($variable_plugin) {
        $operation_links['edit'] = [
          'title' => t('Edit'),
          'url' => Url::fromRoute('snippet_manager.variable_edit_form', $route_parameters),
        ];
      }
      // Allow deletion of broken variables.
      $operation_links['delete'] = [
        'title' => t('Delete'),
        'url' => Url::fromRoute('snippet_manager.variable_delete_form', $route_parameters),
      ];

      $operation_data = [
        '#type' => 'operations',
        '#links' => $operation_links,
      ];

      $variable_url = Url::fromUserInput(
        '#',
        [
          'fragment' => 'snippet-edit-form',
          'attributes' => [
            'title' => t('Insert to the textarea'),
            'class' => 'snippet-variable',
          ],
        ]
      );

      $form['table']['#rows'][$variable_name] = [
        0 => Link::fromTextAndUrl($variable_name, $variable_url),
        1 => $variable_plugin ? $variable_plugin->getType() : '',
        2 => $variable['plugin_id'] . ($variable_plugin ? '' : ' - ' . t('missing')),
        'operations' => ['data' => $operation_data],
      ];
    }

    $form['additional_settings'] = array(
      '#type' => 'vertical_tabs',
    );

    // -- Page.
    $form['page'] = [
      '#type' => 'details',
      '#title' => t('Page'),
      '#open' => FALSE,
      '#tree' => TRUE,
      '#group' => 'additional_settings',
    ];

    $form['page']['status'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable snippet page'),
      '#default_value' => $this->entity->get('page')['status'],
    ];

    $page_states = [
      'visible' => [
        ':input[name="page[status]"]' => ['checked' => TRUE],
      ],
    ];

    $form['page']['title'] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#description' => t('Leave empty to use snippet label as page title.'),
      '#default_value' => $this->entity->get('page')['title'],
      '#states' => $page_states,
    ];

    $description_args = [
      '%example_1' => '%',
      '%example_2' => 'content/%',
      '%example_3' => 'content/%node',
    ];
    $form['page']['path'] = [
      '#type' => 'textfield',
      '#title' => t('Path'),
      '#description' => t('This view will be displayed by visiting this path on your site. You may use "%example_1" in your URL to represent placeholders. For example, "%example_2". If needed you can even load entities using named route parameters like "%example_3".', $description_args),
      '#default_value' => $this->entity->get('page')['path'],
      '#states' => $page_states,
    ];

    $display_variant_wrapper = 'display-variant-settings';
    $form['page']['display_variant'] = [
      '#type' => 'container',
      '#title' => t('Display variant settings'),
      '#open' => TRUE,
      '#id' => $display_variant_wrapper,
      '#states' => $page_states,
    ];

    $theme_options[''] = t('- Default -');
    foreach ($this->themeHandler->listInfo() as $theme) {
      if ($theme->status && empty($theme->info['hidden'])) {
        $theme_options[$theme->getName()] = $theme->info['name'];
      }
    }

    $form['page']['theme'] = [
      '#type' => 'select',
      '#title' => t('Theme'),
      '#options' => $theme_options,
      '#default_value' => $this->entity->get('page')['theme'],
      '#description' => t('A theme that will be used to render the snippet.'),
      '#states' => $page_states,
    ];

    $variant_definitions = $this->variantManager->getDefinitions();
    $options = ['' => t('- Default -')];
    foreach ($variant_definitions as $id => $definition) {
      $options[$id] = $definition['admin_label'];
    }
    asort($options);

    $display_variant = $this->entity->get('page')['display_variant'];
    $form['page']['display_variant']['id'] = [
      '#type' => 'select',
      '#title' => t('Display variant'),
      '#options' => $options,
      '#default_value' => $display_variant['id'],
      '#ajax' => [
        'wrapper' => $display_variant_wrapper,
        'callback' => '::displayVariantSettings',
        'event' => 'change',
      ],
      '#description' => t('Display variants render the main content in a certain way.'),
    ];

    if ($display_variant && $display_variant['id']) {
      $plugin_configuration = isset($display_variant['configuration']) ?
        $display_variant['configuration'] : [];
      $variant_instance = $this->variantManager->createInstance($display_variant['id'], $plugin_configuration);
      $form['page']['display_variant']['configuration'] = $variant_instance->buildConfigurationForm([], $form_state);
    }

    // -- Block.
    if ($this->moduleHandler->moduleExists('block')) {
      $form['block'] = [
        '#type' => 'details',
        '#title' => t('Block'),
        '#open' => FALSE,
        '#tree' => TRUE,
        '#group' => 'additional_settings',
      ];

      $form['block']['status'] = [
        '#type' => 'checkbox',
        '#title' => t('Enable snippet block'),
        '#default_value' => $this->entity->get('block')['status'],
      ];

      $form['block']['name'] = [
        '#type' => 'textfield',
        '#title' => t('Block admin description'),
        '#description' => t('This will appear as the name of this block in administer » structure » blocks.'),
        '#default_value' => $this->entity->get('block')['name'],
        '#states' => [
          'visible' => [
            ':input[name="block[status]"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

    // -- Access.
    $form['access'] = [
      '#type' => 'details',
      '#title' => t('Access'),
      '#open' => FALSE,
      '#tree' => TRUE,
      '#group' => 'additional_settings',
    ];

    $form['access']['type'] = [
      '#type' => 'radios',
      '#options' => [
        'all' => t('Do not limit'),
        'permission' => t('Permission'),
        'role' => t('Role'),
      ],
      '#default_value' => $this->entity->get('access')['type'],
    ];

    $options = ['' => t('- Select permission -')];
    $permissions = $this->permissionHandler->getPermissions();
    foreach ($permissions as $permission => $permission_info) {
      $provider = $permission_info['provider'];
      $display_name = $this->moduleHandler->getName($provider);
      $options[$display_name][$permission] = strip_tags($permission_info['title']);
    }

    $form['access']['permission'] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => t('Permission'),
      '#description' => t('Only users with the selected permission flag will be able to access this snippet.'),
      '#default_value' => $this->entity->get('access')['permission'],
      '#states' => [
        'visible' => [
          ':input[name="access[type]"]' => ['value' => 'permission'],
        ],
      ],
    ];

    $form['access']['role'] = [
      '#type' => 'checkboxes',
      '#title' => t('Role'),
      '#options' => array_map('\Drupal\Component\Utility\Html::escape', user_role_names()),
      '#description' => t('Only the checked roles will be able to access this snippet.'),
      '#default_value' => $this->entity->get('access')['role'],
      '#states' => [
        'visible' => [
          ':input[name="access[type]"]' => ['value' => 'role'],
        ],
      ],
    ];

    $form['#attached']['drupalSettings']['snippetManager']['snippetId'] = $this->entity->id();
    $form['#attached']['drupalSettings']['snippetManager']['buttonsPath'] = file_create_url(drupal_get_path('module', 'snippet_manager') . '/images/buttons.svg');
    $form['#attached']['drupalSettings']['snippetManager']['codeMirror'] = $this->configFactory->get('snippet_manager.settings')->get('codemirror');
    $form['#attached']['library'][] = 'snippet_manager/snippet_manager';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $page = $form_state->getValue('page');
    if ($page['status']) {
      $errors = $this->validatePath($page['path']);
      foreach ($errors as $error) {
        $form_state->setError($form['page']['path'], $error);
      }
      // Automatically remove '/' and trailing whitespace from path.
      $page['path'] = trim($page['path'], '/');
      $form_state->setValue('page', $page);
    }

    $access = $form_state->getValue('access');

    if ($access['type'] == 'permission' && !$access['permission']) {
      $form_state->setError($form['access']['permission'], t('You must select a permission if access type is "Permission"'));
    }

    $role = array_filter($access['role']);
    if ($access['type'] == 'role' && count($role) == 0) {
      $form_state->setError($form['access']['role'], t('You must select at least one role if access type is "Role"'));
    }
    $form_state->setValue(['access', 'role'], $role);

  }

  /**
   * {@inheritdoc}
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    $element = parent::actionsElement($form, $form_state);

    if (!$this->entity->isNew()) {
      $element['add_variable'] = [
        '#type' => 'link',
        '#title' => t('Add variable'),
        '#url' => Url::fromRoute('snippet_manager.variable_add_form', ['snippet' => $this->entity->id()]),
        '#attributes' => ['class' => 'button'],
        '#weight' => 5,
      ];
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $result = $this->entity->save();

    $message_arguments = ['%label' => $this->entity->label()];
    $message = $result == SAVED_NEW ?
      t('Snippet %label has been created.', $message_arguments) :
      t('Snippet %label has been updated.', $message_arguments);
    drupal_set_message($message);

    $redirect_page = $this->configFactory->get('snippet_manager.settings')->get('redirect_page');
    $form_state->setRedirectUrl($this->entity->toUrl($redirect_page));
  }

  /**
   * Form element validation handler; Validates twig template.
   */
  public static function validateTemplate($element, FormStateInterface $form_state) {

    // Do not validate code format.
    if ($element['#type'] == 'textarea') {
      $code = $form_state->getValue('code');
      try {
        \Drupal::service('twig')->renderInline(check_markup($code['value'], $code['format']));
      }
      catch (\Twig_Error $e) {
        $form_state->setError($element, t('Twig error: %message', ['%message' => $e->getRawMessage()]));
      }
    }

  }

  /**
   * Validates the path of the display.
   *
   * @param string $path
   *   The path to validate.
   *
   * @return array
   *   A list of error strings.
   */
  protected function validatePath($path) {
    $errors = array();
    if (strpos($path, '%') === 0) {
      $errors[] = t('"%" may not be used for the first segment of a path.');
    }

    $parsed_url = UrlHelper::parse($path);
    if (empty($parsed_url['path'])) {
      $errors[] = t('Path is empty.');
    }

    if (!empty($parsed_url['query'])) {
      $errors[] = t('No query allowed.');
    }

    if (!parse_url('internal:/' . $path)) {
      $errors[] = t('Invalid path. Valid characters are alphanumerics as well as "-", ".", "_" and "~".');
    }

    $path_sections = explode('/', $path);
    // Symfony routing does not allow to use numeric placeholders.
    // @see \Symfony\Component\Routing\RouteCompiler
    $numeric_placeholders = array_filter($path_sections, function ($section) {
      return (preg_match('/^%(.*)/', $section, $matches)
        && is_numeric($matches[1]));
    });
    if (!empty($numeric_placeholders)) {
      $errors[] = t('Numeric placeholders may not be used. Please use plain placeholders (%).');
    }
    return $errors;
  }

  /**
   * Ajax callback.
   */
  public function displayVariantSettings(array &$form, FormStateInterface $form_state) {
    return $form['page']['display_variant'];
  }

}
