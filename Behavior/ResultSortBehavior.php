<?php

namespace Propel\Bundle\PropelBundle\Behavior;

use Propel\Bundle\PropelBundle\Formatter\SortedObjectFormatter;

/**
 * Behavior that ensures a Query class uses the SortedObjectFormatter before returning query results.
 */
class ResultSortBehavior extends \Behavior
{
  /**
   * Returns PHP code injected into the BaseQuery::preSelect() method.
   *
   * @param \QueryBuilder $builder Query builder instance.
   *
   * @return string PHP code that sets the formatter as needed
   */
  public function preSelectQuery($builder)
  {
    $formatterClass = SortedObjectFormatter::class;

    // This code is injected into the BaseQuery::preSelect() method
    return <<<EOT
if (!\$this->getFormatter()) {
    \$this->setFormatter('$formatterClass');
}
EOT;
  }
}
