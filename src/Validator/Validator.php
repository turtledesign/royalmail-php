<?php

namespace RoyalMail\Validator;

/**
 * Trait to provice YML schema based validation for requests (and possibly responses).
 * 
 * Originally planned to use the Symfony validator component, but working with a slimline 
 * custom implementation using the same setup/name structure as the Symfony component for now.
 * 
 */
trait Validator {

  /**
   * Validate the given values against the constraints given.
   * 
   * @param mixed $value
   * @param array $constraints
   * 
   * @return mixed $value - validated and cleaned.
   */
  static function validate($value, $constraints) {
    if (! empty($constraints)) foreach ($constraints as $c) { # The constraints are in a numeric array as the order matters.
      list($constraint, $params) = each($c);                  # Split to get the actual values here.

      $value = self::constrain($value, $constraint, $params);
    }

    return $value;
  }



  /**
   * Apply the given constraints with the parameters given.
   * 
   * @param mixed  $value
   * @param string $constraint
   * @param array  $params
   * 
   * @return mixed
   */
  static function constrain($value, $constraint, $params = []) {
    $constraint_method = get_called_class() . '::check' . $constraint;

    if (! is_callable($constraint_method)) throw new \InvalidArgumentException('Invalid constraint method ' . $constraint_method . ' called');

    return call_user_func_array($constraint_method, [$value, (array) $params]);
  }



  /**
   * NotBlank
   *  
   */
  static function checkNotBlank($value, $params) {
    if  (! self::isBlank($value)) return $value;

    self::fail($value, $params, ['message' => 'can not be blank']);
  }



  static function checkRange($value, $params) {
    if (! is_numeric($value)) {
      self::fail($value, $params, ['message' => 'numeric value required']);
    }

    if (isset($params['min']) && ($value < $params['min'])) {
      self::fail($value, $params, ['message' => @$params['min_message'] ?: 'value should be over ' . $params['min']]);
    }

    return $value;
  }



  static function checkRegex($value, $params) {
    if (! preg_match($params['pattern'], $value)) self::fail($value, $params, ['message' => $params['pattern'] . ' regex not matched']);

    return $value;
  }


  static function isBlank($value) { 
    return ($value === FALSE || $value === '' || $value === NULL); 
  }


  /**
   * Handle failed validation constraint.
   * 
   * @param mixed $value
   * @param array $params
   * @param array $defaults
   * 
   * @throws \RoyalMail\Validator\ValidatorException
   */
  static function fail($value, $params, $defaults = []) {
    $params   = array_merge(['message' => 'value is invalid'], $defaults, $params);
    $show_val = is_scalar($value) ? ' [' . $value . ']' : '';

    throw new \RoyalMail\Validator\ValidatorException($params['message'] . $show_val);
  }
}