<?php

namespace Propel\Bundle\PropelBundle\Sort;

/**
 * Object representing multiple sort objects, consisting of multiple QuerySort objects, and an optional
 * ResultSort object.
 */
class CompoundSort extends \ArrayIterator implements SortInterface
{
  /** @var ResultSort|null  */
  protected $final;

  public function __construct(array $querySorts, ResultSort $final = null)
  {
    $objects = array();
    foreach($querySorts as $sort)
    {
      if (!$sort instanceof QuerySort)
      {
        throw new \InvalidArgumentException(sprintf(
              'The $column parameter must be an array of %s objects.',
              QuerySort::class
        ));
      }

      $objects[] = $sort;
    }

    if (isset($final))
    {
      $this->final = $final;
    }

    parent::__construct($objects);
  }

  /**
   * Applies each QuerySort in this object to the given Propel model Query, in original order given.
   *
   * If a final ResultSort was given at instantiation of this object, it will be applied to any result of the
   * query via a SortedObjectFormatter when the query's find method is called.
   *
   * @param \ModelCriteria $query
   *
   * @return \ModelCriteria
   * @throws \PropelException
   */
  public function apply(\ModelCriteria $query): \ModelCriteria
  {
    foreach($this as $sorter)
    {
      $sorter->apply($query);
    }

    if ($this->final)
    {
      $this->final->apply($query);
    }

    return $query;
  }
}
