<?php

namespace RoyalMail\Exception

abstract class RoyalMailException extends Exception {

  protected $error_list = [];


  function withErrors($errors) { $this->error_list = $errors; }


  function toJSON() {

  }
}