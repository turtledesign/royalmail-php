<?php

namespace RoyalMail\tests\unit\Validator;

use atoum;

class Validates extends atoum {
  use \RoyalMail\Validator\Validates;



  function testNotBlank() {
    $this->string(self::constrain('not a blank value', 'NotBlank'))->isEqualTo('not a blank value');
  }


  function testDate() {
    $date_constraint = [
      'format' => 'Y-m-d',
      'min'    => '+0 days',
      'max'    => '+30 days'
    ];

    // Too Soon!
    $this
      ->exception(function () use ($date_constraint) { self::constrain(date_create()->sub(new \DateInterval('P1D'))->format('Y-m-d'), 'Date', $date_constraint); })
      ->message
      ->contains('earlier than');


    // Too Late!
    $this
      ->exception(function () use ($date_constraint) { self::constrain(date_create()->add(new \DateInterval('P32D'))->format('Y-m-d'), 'Date', $date_constraint); })
      ->message
      ->contains('later than');


    // Too Late! (with DateTime object).
    $this
      ->exception(function () use ($date_constraint) { self::constrain(date_create()->add(new \DateInterval('P32D')), 'Date', $date_constraint); })
      ->message
      ->contains('later than');

    $ds = date_create()->format('Y-m-d');
    $this
      ->string(self::constrain($ds, 'Date', $date_constraint))
      ->isEqualTo($ds);

    $ds = date_create()->add(new \DateInterval('P29D'))->format('Y-m-d');
    $this
      ->string(self::constrain($ds, 'Date', $date_constraint))
      ->isEqualTo($ds);
  }


  function testEmail() {
    $this
      ->exception(function () { self::constrain('not.valid.email', 'Email', []); })
      ->message
      ->contains('not a valid email');
  }


  function testCustomValidationException() {
    $this->exception(function () { self::constrain(NULL, 'NotBlank'); })->hasMessage('can not be blank'); // Default NotBlank message NULL != scalar.
    $this->exception(function () { self::constrain('', 'NotBlank'); })->hasMessage('can not be blank []');
    $this->exception(function () { self::constrain('', 'NotBlank', ['message' => 'foo']); })->hasMessage('foo []');
  }


  function testHasValue() {
    $this->boolean(self::is(['foo' => 'bar'], ['required' => 'foo']))->isTrue();
    $this->boolean(self::is(['foo' => ['bar' => 'baz']], ['required' => 'foo.bar']))->isTrue();
  }


  function testThisRequiredWhenThat() {
    $this // Required, and set (array).
      ->string(self::constrain('foo', 'ThisRequiredWhenThat', ['that' => 'input:bar', 'is' => ['baz', 'kaboom']], ['input' => ['bar' => 'kaboom']]))
      ->isEqualTo('foo');

    $this // Required, and set (string).
      ->string(self::constrain('foo', 'ThisRequiredWhenThat', ['that' => 'input:bar', 'is' => 'kaboom'], ['input' => ['bar' => 'kaboom']]))
      ->isEqualTo('foo');

    $this // Required, and empty.
      ->exception(function () { 
        self::constrain('', 'ThisRequiredWhenThat', ['that' => 'input:bar', 'is' => ['baz', 'kaboom']], ['input' => ['bar' => 'kaboom']]);
      })->message->contains('required when');

    $this // Not Required, and not set.
      ->string(self::constrain('', 'ThisRequiredWhenThat', ['that' => 'input:bar', 'is' => ['baz', 'kaboom']], ['input' => ['bar' => 'boing']]))
      ->isEqualTo('');

    $this // Not Required, and set (currently no option to skip if not required).
      ->string(self::constrain('foo', 'ThisRequiredWhenThat', ['that' => 'input:bar', 'is' => ['baz', 'kaboom']], ['input' => ['bar' => 'boing']]))
      ->isEqualTo('foo');
  }


  function testGBPostcode() {
    $this // Required and valid.
      ->string(self::constrain('EH10 4BF', 'GBPostcode', ['check_country' => 'input:country'], ['input' => ['country' => 'GB']]))
      ->isEqualTo('EH10 4BF');

    $this // Required and valid - no country check.
      ->string(self::constrain('EH10 4BF', 'GBPostcode', [], []))
      ->isEqualTo('EH10 4BF');

    $this // Overseas (and would be invalid).
      ->string(self::constrain('123456789', 'GBPostcode', ['check_country' => 'input:country'], ['input' => ['country' => 'FR']]))
      ->isEqualTo('123456789');

    $this // Invalid.
      ->exception(function () { self::constrain('123456789', 'GBPostcode', ['check_country' => 'input:country'], ['input' => ['country' => 'GB']]); })
      ->message->contains('not a valid UK postcode');

    $this // Invalid (more subtle).
      ->exception(function () { self::constrain('EH101 4BF', 'GBPostcode', ['check_country' => 'input:country'], ['input' => ['country' => 'GB']]); })
      ->message->contains('not a valid UK postcode');
  }


  function testRange() {
    $this->integer(self::constrain(100, 'Range', ['min' => 10, 'max' => 9999]))->isEqualTo(100);
    
    $this
      ->exception(function () { self::constrain(1, 'Range', ['min' => 10, 'max' => 9999]); })
      ->message->contains('value should be over');

    $this
      ->exception(function () { self::constrain(10000, 'Range', ['min' => 10, 'max' => 9999]); })
      ->message->contains('value should be under');
        
  }
}