<?php

namespace Drupal\snippet_manager\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\snippet_manager\SnippetInterface;

/**
 * Defines the snippet entity type.
 *
 * @ConfigEntityType(
 *   id = "snippet",
 *   label = @Translation("Snippet"),
 *   handlers = {
 *     "access" = "Drupal\snippet_manager\SnippetAccessControlHandler",
 *     "view_builder" = "Drupal\snippet_manager\SnippetViewBuilder",
 *     "list_builder" = "Drupal\snippet_manager\SnippetListBuilder",
 *     "form" = {
 *       "add" = "Drupal\snippet_manager\Form\SnippetForm",
 *       "edit" = "Drupal\snippet_manager\Form\SnippetForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *       "duplicate" = "Drupal\snippet_manager\Form\SnippetDuplicateForm",
 *       "variable_add" = "Drupal\snippet_manager\Form\VariableAddForm",
 *       "variable_edit" = "Drupal\snippet_manager\Form\VariableEditForm",
 *       "variable_delete" = "Drupal\snippet_manager\Form\VariableDeleteForm"
 *     }
 *   },
 *   config_prefix = "snippet",
 *   admin_permission = "administer snippets",
 *   links = {
 *     "collection" = "/admin/structure/snippet",
 *     "canonical" = "/admin/structure/snippet/{snippet}",
 *     "source" = "/admin/structure/snippet/{snippet}/source",
 *     "add-form" = "/admin/structure/snippet/add",
 *     "edit-form" = "/admin/structure/snippet/{snippet}/edit",
 *     "delete-form" = "/admin/structure/snippet/{snippet}/delete",
 *     "duplicate-form" = "/admin/structure/snippet/{snippet}/duplicate",
 *     "enable" = "/admin/structure/snippet/{snippet}/enable",
 *     "disable" = "/admin/structure/snippet/{snippet}/disable"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status",
 *     "uuid" = "uuid",
 *   }
 * )
 */
class Snippet extends ConfigEntityBase implements SnippetInterface {

  /**
   * The snippet ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The snippet label.
   *
   * @var string
   */
  protected $label;

  /**
   * The snippet description.
   *
   * @var string
   */
  protected $description;

  /**
   * The snippet code.
   *
   * @var array
   */
  protected $code;

  /**
   * The snippet variables.
   *
   * @var array
   */
  protected $variables = [];

  /**
   * The snippet page settings.
   *
   * @var array
   */
  protected $page = [
    'status' => FALSE,
    'title' => '',
    'path' => '',
    'display_variant' => [
      'id' => NULL,
      'configuration' => [],
    ],
    'theme' => '',
  ];

  /**
   * The snippet block settings.
   *
   * @var array
   */
  protected $block = [
    'status' => FALSE,
    'name' => '',
  ];

  /**
   * The snippet block settings.
   *
   * @var array
   */
  protected $access = [
    'type' => 'all',
    'role' => [],
    'permission' => '',
  ];

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    \Drupal::service('plugin.manager.block')->clearCachedDefinitions();

    // Rebuild the router if this is a new snippet, or its page settings has
    // been updated, or its status has been changed.
    if (!$update || $this->get('page') !== $this->original->get('page') || $this->status() != $this->original->status()) {
      \Drupal::service('router.builder')->setRebuildNeeded();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);
    \Drupal::service('plugin.manager.block')->clearCachedDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function getCode() {
    return $this->code ? $this->code : [
      'value' => str_repeat("\n", 10),
      'format' => self::getDefaultCodeFormat(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setCode(array $code) {
    return $this->code = $code;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariables() {
    return $this->variables;
  }

  /**
   * {@inheritdoc}
   */
  public function setVariables($variables) {
    $this->variables = $variables;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariable($key) {
    return isset($this->variables[$key]) ? $this->variables[$key] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setVariable($key, $variable) {
    $this->variables[$key] = $variable;
  }

  /**
   * {@inheritdoc}
   */
  public function removeVariable($key) {
    unset($this->variables[$key]);
  }

  /**
   * {@inheritdoc}
   */
  public function variableExists($key) {
    return isset($this->variables[$key]);
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    return $this->context;
  }

  /**
   * {@inheritdoc}
   */
  public function setContext($context) {
    $this->context = $context;
  }

  /**
   * {@inheritdoc}
   */
  public function pageIsPublished() {
    return $this->page['status'];
  }

  /**
   * Returns the ID of default filter format.
   */
  protected static function getDefaultCodeFormat() {
    // Full HTML is the most suitable format for snippets.
    $formats = filter_formats(\Drupal::currentUser());
    return isset($formats['full_html']) ? 'full_html' : filter_default_format();
  }

}
