<?php

namespace Drupal\facetapi\Processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\facetapi\FacetInterface;

abstract class WidgetOrderPluginBase extends ProcessorPluginBase implements \Drupal\facetapi\Processor\WidgetOrderProcessorInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $processor_configs = $facet->getProcessorConfigs();
    $config = $processor_configs[$this->getPluginId()];

    $build['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable processor'),
      '#default_value' => $config['settings']['enabled']
    ];
    $build['sort'] = [
      '#type' => 'radios',
      '#title' => $this->t('Sort order'),
      '#options' => [
        'ASC' => $this->t('Ascending'),
        'DESC' => $this->t('Descending')
      ],
      '#default_value' => $config['settings']['sort'],
    ];


    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    $processor_configs = $facet->getProcessorConfigs();
    $config = $processor_configs[$this->getPluginId()];

    // This should load the facet's config to find the ordering direction.
    return $this->sortResults($results, $config['settings']['sort']);
  }

}
