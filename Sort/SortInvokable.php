<?php

namespace Propel\Bundle\PropelBundle\Sort;

/**
 * Interface for classes that implement an invokable sorting function for Propel model objects
 */
interface SortInvokable extends SortInterface
{
  /**
   * MUST sort two model objects and return an integer.
   * MUST return a positive integer if the first object is considered to be greater than the second.
   * MUST return a negative integer if the first object is considered to be less than the second.
   * MUST return zero if the two objects are considered to be equal.
   *
   * @param object $a First model object
   * @param object $b Second model object
   *
   * @return int      Positive if a > b; Negative if a < b; Zero if a == b
   */
  public function __invoke($a, $b): int;
}