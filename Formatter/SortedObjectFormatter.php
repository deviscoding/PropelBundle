<?php

namespace Propel\Bundle\PropelBundle\Formatter;

use Propel\Bundle\PropelBundle\Sort\SortInvokable;

/**
 * PropelObjectFormatter that sorts collections by a SortInvokable before returning them. It is important to note that
 * this sorting method will be applied after all orderBy* sorting in a query has been performed.
 */
class SortedObjectFormatter extends \PropelObjectFormatter
{
  /** @var SortInvokable  */
  public $sorter;

  /**
   * @param SortInvokable  $sorter
   * @param \ModelCriteria|null $criteria
   */
  public function __construct(SortInvokable $sorter, \ModelCriteria $criteria = null)
  {
    $this->sorter = $sorter;

    parent::__construct($criteria);
  }

  /**
   * Override to apply the SortInvokable sort via PropelObjectCollection->uasort
   *
   * @param \PDOStatement $stmt
   *
   * @return \PropelObjectCollection
   * @throws \PropelException
   */
  public function format(\PDOStatement $stmt)
  {
    /** @var \PropelObjectCollection $collection */
    $collection = parent::format($stmt);

    // Apply custom sorting
    if($this->sorter instanceof SortInvokable)
    {
      $collection->uasort($this->sorter);
    }

    return $collection;
  }
}
