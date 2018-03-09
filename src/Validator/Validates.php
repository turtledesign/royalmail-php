<?php

namespace RoyalMail\Validator;


use \Valitron\Validator;

/**
 * Trait to provice YML schema based validation for requests (and possibly responses).
 * 
 * Originally planned to use the Symfony validator component, but working with a slimline 
 * custom implementation using the same setup/name structure as the Symfony component for now.
 * 12/11/2015 - Switching over to https://github.com/vlucas/valitron
 * 
 * PONDER: Should this be returning values or just checking and throwing exceptions? (value returns are used in tests).
 * TODO: could send failure type keys/code to self::fail, these could be linked to custom err messages on the fly.
 * 
 */
trait Validates {

  /**
   * Validate the given values against the constraints given.
   * 
   * @param mixed $value
   * @param array $constraints
   * 
   * @return mixed $value - validated and cleaned.
   */
  static function validate($schema, $value, $helper = NULL) {
    foreach (self::parseConstraints($schema) as $c) {  // The constraints are in a numeric array as the order matters.
      $constraint = key($c);
      $params     = $c[$constraint];

      if (self::isBlank($value) && ! preg_match('/require|notblank/i', $constraint)) continue; // Only *require* validators should check blank values.

      $value = self::constrain($value, $constraint, self::parseParams($params, $schema), $helper);
    }

    return $value;
  }



  static function parseConstraints($schema) {
    $constraints = isset($schema['_validate']) ? $schema['_validate'] : [];

    if (is_scalar($constraints))                         $constraints = [$constraints => []]; // Shorthand version for single named validator e.g. _validate: NotBlank
    if (count($constraints) && ! isset($constraints[0])) $constraints = [$constraints];       // Shorthand version for single validator with options. e.g. _validate: { Length: 20 }

    if (! empty($schema['_required'])) array_unshift($constraints, ['NotBlank' => TRUE]);     // Shorthand required values, always first test.

    return $constraints;
  }



  /**
   * Adds extra settings values from the schema as they may be used by the validator as well as other things.
   * 
   * @param mixed $params
   * @param array $schema
   * 
   * @return array
   */
  static function parseParams($params, $schema) {
    $params = (array) $params;

    foreach (array_diff_key($schema, ['_validate' => 1]) as $k => $v) if (preg_match('/^_/', $k)) $params[$k] = $v;

    return $params;
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
  static function constrain($value, $constraint, $params = [], $helper = NULL) {
    $constraint_method = get_called_class() . '::check' . $constraint;

    if (! is_callable($constraint_method)) throw new \InvalidArgumentException('Invalid constraint method ' . $constraint_method . ' called');

    return call_user_func_array($constraint_method, [$value, (array) $params, $helper]);
  }



  /**
   * NotBlank
   *  
   */
  static function checkNotBlank($value, $params, $helper) {
    if  (! self::isBlank($value)) return $value;

    self::fail($value, $params, ['message' => 'can not be blank']);
  }



  static function checkRange($value, $params, $helper) {
    if (! is_numeric($value)) {
      self::fail($value, $params, ['message' => 'numeric value required']);
    }

    if (isset($params['min']) && ($value < $params['min'])) {
      self::fail($value, $params, ['message' => @$params['min_message'] ?: 'value should be over or equal to ' . $params['min']]);
    }

    if (isset($params['max']) && ($value > $params['max'])) {
      self::fail($value, $params, ['message' => @$params['max_message'] ?: 'value should be under or equal to ' . $params['min']]);
    }

    return $value;
  }



  static function checkRegex($value, $params, $helper) {
    if (! preg_match($params['pattern'], $value)) self::fail($value, $params, ['message' => $params['pattern'] . ' regex not matched']);

    return $value;
  }



  static function checkChoice($value, $params, $helper) {
    $choices = isset($params['choices']) ? $params['choices'] : $params['_options']; 

    if (is_scalar($choices)) $choices = array_keys($helper[$choices]);

    if (! in_array($value, $choices)) self::fail($value, $params, ['message' => 'accepted values are ' . implode(', ', $choices)]);

    return $value;
  }



  static function checkDate($value, $params, $helper) {
    $original = $value;

    if (! $value instanceof \DateTime) try {
      $value = (empty($params['format'])) ? date_create($value) : date_create_from_format($params['format'], $value);
    
    } catch (Exception $e) {
      self::fail($value, $params, ['message' => 'value not in a valid date format.']);
    }
    
    if (isset($params['min']) && $value < date_create()->setTime(00, 00, 01)->modify($params['min'])) { 
      self::fail($value->format('Y-m-d'), $params, ['message' => 'date earlier than ' . date_create()->modify($params['min'])->format('Y-m-d') .  ' | now ' . $params['min']]); // TODO: mebbe possible to display date in i18n format.
    }

    if (isset($params['max']) && $value > date_create()->modify($params['max'])->setTime(23, 59, 59)) {
      self::fail($value->format('Y-m-d'), $params, ['message' => 'date later than now ' . $params['max']]);
    }

    return $original;
  }


  static function checkLength($value, $params, $helper) {
    $func = function_exists('mb_strlen') 
      ? function ($str) { return mb_strlen($str); }
      : function ($str) { return strlen($str); };

    if (isset($params['min']) && $func($value) < $params['min']) {
      self::fail($value, $params, ['message' => 'value should be at least ' . $params['min'] . ' characters long']);
    }

    if (isset($params['max']) && $func($value) > $params['max']) {
      self::fail($value, $params, ['message' => 'value should be at least ' . $params['max'] . ' characters long']);
    }

    return $value;
  }


  static function checkNumeric($value, $params, $helper) {
    if (! is_numeric($value)) {
      self::fail($value, $params, ['message' => 'value contains non-numeric characters']);
    }

    return $value;
  }


  static function checkEmail($value, $params, $helper) {
    if (filter_var($value, \FILTER_VALIDATE_EMAIL) === FALSE) {
      self::fail($value, $params, ['message' => 'not a valid email']);
    }

    return $value;
  }


  static function checkGBPostcode($value, $params, $helper) {
    if (! (isset($params['check_country'])) || self::checkPath($params['check_country'], ['in' => ['GB']], $helper)) {
    
      if (! preg_match('/^((([A-PR-UWYZ][0-9])|([A-PR-UWYZ][0-9][0-9])|([A-PR-UWYZ][A-HK-Y][0-9])|([A-PR-UWYZ][A-HK-Y][0-9][0-9])|([A-PR-UWYZ][0-9][A-HJKSTUW])|([A-PR-UWYZ][A-HK-Y][0-9][ABEHMNPRVWXY]))\s?([0-9][ABD-HJLNP-UW-Z]{2})|(GIR)\s?(0AA))$/', $value)) {
        self::fail($value, $params, ['message' => 'not a valid UK postcode']);
      }

    }

    return $value;
  }


  /**
   * Check field exists based on the value of another field.
   * 
   * NB: Valitron's equals method checks against a field val, not a string, so 'in' is used for strings and arrays.
   */
  static function checkThisRequiredWhenThat($value, $params, $helper) {
    if (self::checkPath($params['that'], ['required' => TRUE, 'in' => (array) $params['is']], $helper)) {
      $params = array_merge(['message' => 'required when ' . $params['that'] . ' in (' . implode(', ', (array) $params['is']) . ')'], $params);
      
      return self::checkNotBlank($value, $params, $helper);
    }

    return $value;
  }


  static function isBlank($value) { 
    return ($value === FALSE || $value === '' || $value === NULL); 
  }
 

  static function hasValue($arr, $field) {
    return self::is($arr, ['required' => $field]);
  }


  /**
   * Convert the validation from the schema with has a 
   *  -- Field => [Validation settings, /...]
   * layout to
   *  -- Validation Rule => [[Field, Settings], [], ... ]
   * 
   * Need to add custom rules for checks not covered by the default Valitron rules (i.e. ThisRequiredWhenThat()).
   */
  static function buildValitronRules($schema) {
    return [];
  }


  /**
   * Build a Valitron ruleset from a given path and constraints.
   * 
   * @param string  $path        (input|output):dot.path.to.var.*.vars
   * @param array   $constraints constraints and other settings, defaults to ['required' => TRUE] if empty
   * @param $helper ArrayObject  data checks are run on.
   * 
   * @return Boolean True if rules validate, False otherwise.
   */
  static function checkPath($path, $constraints, $helper) {
    list($where, $path) = explode(':', $path);

    if (empty($constraints)) $constraints = ['required' => TRUE];

    foreach ($constraints as $con => $params) {
      if ($con === 'required') $constraints['required'] = $path;

      elseif ($con === 'in')   $constraints['in'] = [[$path, $params]]; // !REM!:  params for Valitron rule in indexed array.

      else unset($constraints[$con]); 
    }


    return self::is($helper[$where], $constraints);
  }


  static function is($arr, $rules) {
    return ! is_array(self::callValitron($arr, $rules, $throw = FALSE));
  }


  static function callValitron($arr, $rules, $throw = TRUE) {
    $v = new Validator($arr);

    $v->rules($rules);

    if (! $v->validate()) {
      if ($throw) throw (new \RoyalMail\Exception\RequestException())->withErrors($v->errors());

      return $v->errors();
    
    } else return TRUE;
  }


  /**
   * Handle failed validation constraint.
   * 
   * @param mixed $value
   * @param array $params
   * @param array $defaults
   * 
   * @throws \RoyalMail\Exception\ValidatorException
   */
  static function fail($value, $params, $defaults = []) {
    $params   = array_merge(['message' => 'value is invalid'], $defaults, $params);
    $show_val = is_scalar($value) ? ' [' . $value . ']' : '';

    throw new \RoyalMail\Exception\ValidatorException($params['message'] . $show_val);
  }
}