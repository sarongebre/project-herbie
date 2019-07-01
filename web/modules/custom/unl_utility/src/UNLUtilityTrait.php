<?php

namespace Drupal\unl_utility;

use Drupal\Core\Form\FormStateInterface;

/**
 * Utility methods.
 */
trait UNLUtilityTrait {

  /**
   * Clears a given error on a FormState object.
   *
   * FormStateInterface provides methods to set individual errors and
   * to clear all errors; however, it does not provide a method to
   * clear an individual error. This method provides that missing
   * functionality.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An interface for an object containing the current state of a form.
   * @param string $error_name
   *   The name of the error being cleared.
   */
  public static function formStateClearError(FormStateInterface &$form_state, string $error_name) {
    $form_errors = $form_state->getErrors();
    $form_state->clearErrors();
    if (isset($form_errors[$error_name])) {
      unset($form_errors[$error_name]);
    }
    foreach ($form_errors as $name => $error_message) {
      $form_state->setErrorByName($name, $error_message);
    }
  }

  /**
   * Returns an entity object from the current route.
   *
   * @return object|null
   *   Entity object, if one exists; otherwise NULL
   */
  public static function getRouteEntity() {
    $route_match = \Drupal::routeMatch();
    // Entity will be found in the route parameters.
    if (($route = $route_match->getRouteObject()) && ($parameters = $route->getOption('parameters'))) {
      // Determine if the current route represents an entity.
      foreach ($parameters as $name => $options) {
        if (isset($options['type']) && strpos($options['type'], 'entity:') === 0) {
          $entity = $route_match->getParameter($name);
          if (!empty($entity)) {
            return $entity;
          }
          // Since entity was found, no need to iterate further.
          return NULL;
        }
      }
    }
  }

}
