<?php /* -*- c-basic-offset: 2 -*- */

/**
 * @file lib-unit.php
 * This file is part of Movicon.
 *
 * @brief Unit-testing library for Movicon.
 *
 * @author Guillaume Pasquet <gpasquet@lewisday.co.uk>
 *
 * @version 1.0
 * @date  7 March 2011
 *
 * Copyright (C)2011 Movicon project.
 *
 * See COPYING for licensing information.
 */


/**
 * Signals an error if the contained code returns false.
 */
function ut_assert($stuff)
{
  printtest($stuff);
}

function ut_nassert($stuff)
{
  printtest(!$stuff);
}

/**
 * Checks if $stuff is equal to $expectedstuff.
 */
function ut_equals($stuff, $expectedstuff)
{
  if(is_array($stuff) && is_array($expectedstuff)) {
    $stuff = sort($stuff);
    $expectedstuff = sort($expectedstuff);
  }
  printtest($stuff == $expectedstuff);
}

/**
 * Compares two associative arrays.
 */
function _ut_compare_aarray($array1, $array2)
{
  $same = true;
  if(!is_array($array1) || !is_array($array2)) {
    $same = false;
  }
  if(count($array1) != count($array2)) {
    $same = false;
  }
  foreach($array1 as $key => $val) {
    if(!isset($array2[$key]) || $array2[$key] != $val) {
      $same = false;
    }
  }
  return $same;
}

function ut_aarray_equals($array1, $array2)
{
  printtest(_ut_compare_aarray($array1, $array2));
}

function ut_aarray_differs($array1, $array2)
{
  printtest(!_ut_compare_aarray($array1, $array2));
}

function ut_differs($stuff, $expectedstuff)
{
  if(is_array($stuff) && is_array($expectedstuff)) {
    $stuff = sort($stuff);
    $expectedstuff = sort($expectedstuff);
  }
  printtest($stuff != $expectedstuff);
}

function ut_match($regex, $value)
{
  printtest(preg_match($regex, $value));
}

function ut_whitespace($pattern, $value)
{
  $replacements = array(
    '(' => '\\(',
    ')' => '\\)',
    '[' => '\\[',
    ']' => '\\]',
    '{' => '\\{',
    '}' => '\\}',
    '*' => '\\*',
    '+' => '\\+',
    '?' => '\\s*',
    );

  foreach($replacements as $f => $r) {
    $pattern = str_replace($f, $r, $pattern);
  }

  $regex = '#^' . $pattern . '$#';
  printtest(preg_match($regex, $value));
}

?>
