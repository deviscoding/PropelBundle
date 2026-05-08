<?php

namespace Propel\Bundle\PropelBundle\Sort;

use Propel\Bundle\PropelBundle\Formatter\SortedObjectFormatter;

/**
 * Object representing a sort closure that can be applied to a Query. This class is used by SortedObjectFormatter to
 * sort objects in a PropelObjectCollection before it is returned from a query.
 */
class ResultSort implements SortInterface, SortInvokable
{
  /** @var \Closure */
  public $closure;
  /** @var string */
  public $direction;

  /**
   * @param \Closure $closure    Closure appropriate for uasort
   * @param string   $direction  Either \Criteria::ASC or \Criteria::DESC
   */
  public function __construct(\Closure $closure, $direction = \Criteria::ASC)
  {
    $this->closure   = $closure;
    $this->direction = $direction;
  }

  /**
   * Sets the Formatter for the given ModelCriteria object to a SortedObjectFormatter with this ResultSort.
   *
   * @param \ModelCriteria $query
   *
   * @return \ModelCriteria
   * @throws \PropelException
   */
  public function apply(\ModelCriteria $query): \ModelCriteria
  {
    $query->setFormatter(new SortedObjectFormatter($this));

    return $query;
  }

  /**
   * Sorting method for two Propel objects, appropriate for use via invokation via uasort.
   * @param object $a Propel Model Object
   * @param object $b Propel Model Object
   *
   * @return int      Positive if a > b; Negative if a < b; Zero if a == b
   */
  public function __invoke($a, $b): int
  {
    $results = call_user_func($this->closure, $a, $b);

    return !isset($this->direction) || \Criteria::ASC === $this->direction ? $results : -$results;
  }
}
