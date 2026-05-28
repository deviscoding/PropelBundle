<?php

namespace Propel\Bundle\PropelBundle\Sort;

/**
 * Object representing a column sort that may be applied to a Propel Query class.  If an orderByXXXX method matching
 * the column exists within the object, it will be favored, otherwise the orderBy($column, $direction) method is used.
 *
 * When using a method an attempt is made to identity the order/direction parameter to apply the direction given by
 * matching the default value of the parameter to Criteria::ASC or Criteria::DESC. For parameters without defaults,
 * to account for built Propel methods sometimes lacking types and/or PHPDocs, 'lazy' matching is used and the first
 * parameter that is named 'order' or can be identified as string type will be used.  This behavior can can be
 * controlled with the $lazy parameter at instantiation.
 *
 * If a method has no parameters that match direction and the given direction is not the default of Criteria::ASC,
 * the orderBy method will be used as a fallback.
 */
class QuerySort implements SortInterface
{
  const LAZY_NONE = 0;
  const LAZY_NAME = 1;
  const LAZY_TYPE = 2;

  /** @var string string */
  public $column ;
  /** @var string|null */
  public $direction;
  /** @var int */
  public $lazy;

  /**
   * @param string $column      Reference to a column or method name
   * @param string $direction   Direction
   * @param int    $lazy        When a method is used for sorting, this value controls lazy parameter matching,
   *                            matching a parameter to the sort order/direction if it is a string or named 'order'.
   */
  public function __construct($column, $direction = \Criteria::ASC, $lazy = self::LAZY_NAME | self::LAZY_TYPE)
  {
    $this->column    = $column;
    $this->direction = $direction;
    $this->lazy      = $lazy;
  }

  /**
   * Applies this QuerySort to the given Propel model Query object. If an orderByXXXXX method exists
   * for the column in this QuerySort object, the method is favored when possible.
   *
   * @param \ModelCriteria $query Propel model Query object
   *
   * @return \ModelCriteria       Propel model Query object with order applied
   */
  public function apply(\ModelCriteria $query): \ModelCriteria
  {
    $suffix = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->column)));
    $method = 'orderBy' . $suffix;

    // Prefer method call, if the orderByXXXXX method exists
    // Not using is_callable, as that would return true because of __call
    // Wrapping method call in try/catch so that we can fall back to column value
    if (method_exists($query, $method))
    {
      try
      {
        $this->applyMethod($query, $method);
      }
      catch(\BadMethodCallException $e)
      {
      }
      catch(\ReflectionException $e)
      {
      }

      return $query;
    }

    return $query->orderBy($this->column, $this->direction);
  }

  /**
   * Applies QueryOrder to the query object using the matching method from the query object.
   *
   * @param \ModelCriteria $query     Propel model Query object
   * @param string         $method    Name of the method to call
   *
   * @return \ModelCriteria           Modified Propel model Query object
   * @throws \BadMethodCallException  If the orderByXXXX call fails / Method has no params & object is desc.
   * @throws \ReflectionException     If the method doesn't exist
   */
  protected function applyMethod(\ModelCriteria $query, string $method): \ModelCriteria
  {
    $ref = new \ReflectionMethod($query, $method);

    if ($ref->getNumberOfRequiredParameters() > 1)
    {
      throw new \BadMethodCallException(sprintf(
          'The "%s::%s" requires additional parameters.',
          get_class($query),
          $method
      ));
    }

    // Apply with direction, or throw
    if (isset($this->direction))
    {
      if ($this->isAllowsDirection($ref, $index))
      {
        $params = array_pad([$this->direction], -($index + 1), null);

        call_user_func_array([$query, $method], $params);

        return $query;
      }
      elseif(\Criteria::ASC !== $this->direction)
      {
        throw new \BadMethodCallException(sprintf(
                "The '%s::%s' method does not accomodate '%s' sorting.",
                get_class($query),
                $method,
                $this->direction
        ));
      }
    }

    // Apply without direction
    return $query->$method();
  }

  protected function isAllowsDirection(\ReflectionMethod $method, &$index = 0): bool
  {
    if (0 === $method->getNumberOfParameters())
    {
      return false;
    }

    $params = $method->getParameters();
    foreach($params as $param)
    {
      if ($this->isOrderParameter($param))
      {
        return true;
      }
      elseif (!$param->isOptional())
      {
        // As soon as we hit a required parameter, stop looking
        return false;
      }

      $index++;
    }

    return false;
  }

  /**
   * Evaluates if the given parameter is used to indicate ascending or decending order to Propel.
   *
   * @param \ReflectionParameter $parameter
   *
   * @return bool
   */
  protected function isOrderParameter(\ReflectionParameter $parameter): bool
  {
    if ($parameter->isDefaultValueAvailable())
    {
      if (in_array($parameter->getDefaultValue(), [\Criteria::ASC, \Criteria::DESC]))
      {
        return true;
      }
    }

    if ($this->lazy)
    {
      if (($this->lazy & self::LAZY_NAME) !== 0 && 'order' === $parameter->getName())
      {
        return true;
      }
      elseif (($this->lazy & self::LAZY_TYPE) !== 0 && $this->isStringParameter($parameter))
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Evaluates if the given parameter can be a string.
   *
   * @param \ReflectionParameter $parameter
   *
   * @return bool
   */
  protected function isStringParameter(\ReflectionParameter $parameter): bool
  {
    if ($parameter->hasType()) {
      $type = $parameter->getType();
      // PHP 8.0+ Union Types, PHP 8.1+ Intersection Types
      if (method_exists($type, 'getTypes'))
      {
        foreach ($type->getTypes() as $subType) {
          if ('string' === $subType->getName()) {
            return true;
          }
        }
      }
      elseif (method_exists($type, 'getName') && 'string' === $type->getName())
      {
        // PHP 7.1+
        return true;
      }
      elseif ('string' === (string) $type)
      {
        // Fallback for PHP 7.0
        return true;
      }
    }

    $doc = $parameter->getDeclaringFunction()->getDocComment();
    if (!empty($doc))
    {
      if (preg_match('#@param\s+(([^\s]+)?string([^\s]+)?)\s+\$' . $parameter->getName() . '/', $doc))
      {
        return true;
      }
    }

    return false;
  }
}