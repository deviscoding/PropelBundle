<?php

namespace Propel\Bundle\PropelBundle\Query;

/**
 * Defines a contract for applying criteria to a model query.
 *
 * The methods in this interface must handle the modification of a query
 * object based on specific criteria, allowing for flexible query building.
 */
interface ApplyInterface
{
  /**
   * MUST apply your custom logic to the provided ModelCriteria query.
   * SHOULD limit modifications to filtering, sorting, grouping, limits, and joins.
   *
   * @param \ModelCriteria $query ModelCriteria object to be modified.
   *
   * @return \ModelCriteria       Modified ModelCriteria object.
   */
  public function apply(\ModelCriteria $query): \ModelCriteria;
}
