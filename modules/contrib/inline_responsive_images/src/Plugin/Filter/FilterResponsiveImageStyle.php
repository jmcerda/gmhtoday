<?php

/**
 * @file
 * Contains \Drupal\inline_responsive_images\Plugin\Filter\FilterResponsiveImageStyle.
 */

namespace Drupal\inline_responsive_images\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\filter\Annotation\Filter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Core\Form\FormStateInterface;
/**
 * Provides a filter to render inline images as responsive images.
 *
 * @Filter(
 *   id = "filter_responsive_image_style",
 *   module = "inline_responsive_images",
 *   title = @Translation("Display responsive images"),
 *   description = @Translation("Uses the data-responsive-image-style attribute on &lt;img&gt; tags to display responsive images."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class FilterResponsiveImageStyle extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $responsive_image_styles = \Drupal::entityTypeManager()->getStorage('responsive_image_style')->loadMultiple();
    $form['responsive_styles'] = array(
      '#type' => 'markup',
      '#markup' => 'Select the responsive styles that are avaliable in the editor',
    );
    foreach($responsive_image_styles as $style){
      $form['responsive_style_'.$style->id()] = array(
        '#type' => 'checkbox',
        '#title' => $style->label(),
        '#default_value' => $this->settings['responsive_style_'.$style->id()],
      );      
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $search = array();
    $replace = array();

    if (stristr($text, 'data-responsive-image-style') !== FALSE) {
      $responsive_image_styles = \Drupal::entityTypeManager()->getStorage('responsive_image_style')->loadMultiple();

      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);
      foreach ($xpath->query('//*[@data-entity-uuid and @data-responsive-image-style]') as $node) {
        $file_uuid = $node->getAttribute('data-entity-uuid');
        //$node->removeAttribute('data-entity-uuid');
        $responsive_image_style_id = $node->getAttribute('data-responsive-image-style');
        //$node->removeAttribute('data-responsive-image-style');

        // If the responsive image style is not a valid one, then don't
        // transform the HTML.
        if (empty($file_uuid) || !in_array($responsive_image_style_id, array_keys($responsive_image_styles))) {
          continue;
        }

        // Retrieved matching file in array for the specified uuid.
        $matching_files = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uuid' => $file_uuid]);
        $file = reset($matching_files);

        // Stop further element processing, if it's not a valid file.
        if (!$file) continue;
 
        $width = null;
        $height = null;
        $image = \Drupal::service('image.factory')->get($file->getFileUri());

        // Stop further element processing, if it's not a valid image.
        if (!$image->isValid()) continue;

        $width = $image->getWidth();
        $height = $image->getHeight();        

        // Make sure all non-regenerated attributes are retained.
        $node->removeAttribute('width');
        $node->removeAttribute('height');
        $node->removeAttribute('src');
        $attributes = array();
         
        
        for ($i = 0; $i < $node->attributes->length; $i++) {
          $attr = $node->attributes->item($i);          
          $attributes[$attr->name] = $attr->value;
        }

        // Re-render as a responsive image.
        $responsive_image = array(
          '#theme' => 'responsive_image',
          '#uri' => $file->getFileUri(),
          '#width' => $width,
          '#height' => $height,
          '#attributes' => $attributes,
          '#responsive_image_style_id' => $responsive_image_style_id,
        );

        $altered_html = \Drupal::service('renderer')->render($responsive_image);

        // Load the altered HTML into a new DOMDocument and retrieve the element.
        $updated_node = Html::load(trim($altered_html))->getElementsByTagName('body')
          ->item(0)
          ->childNodes
          ->item(0);

        // Import the updated node from the new DOMDocument into the original
        // one, importing also the child nodes of the updated node.
        $updated_node = $dom->importNode($updated_node, TRUE);
        // Finally, replace the original image node with the new image node!
        $node->parentNode->replaceChild($updated_node, $node);
      }

      return new FilterProcessResult(Html::serialize($dom));
    }

    return new FilterProcessResult($text);
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    if ($long) {
      $responsive_image_styles = \Drupal::entityTypeManager()->getStorage('responsive_image_style')->loadMultiple();
      $list = '<code>' . implode('</code>, <code>', array_keys($responsive_image_styles)) . '</code>';
      return t('
        <p>You can make images responsive by adding a <code>data-responsive-image-style</code> attribute, whose values is one of the responsive image style machine names: !responsive-image-style-machine-name-list.</p>', array('!responsive-image-style-machine-name-list' => $list));
    }
    else {
      return t('You can make images responsive by adding a data-responsive-image-style attribute.');
    }
  }
}