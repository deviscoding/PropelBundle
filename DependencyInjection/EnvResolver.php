<?php

namespace Propel\Bundle\PropelBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Allows for the resolution of any env placeholders or parameters, using the ContainerBuilder given at instantiation.
 *
 * Class EnvResolver
 *
 * @internal Designed as workaround for https://github.com/symfony/symfony/issues/27683 and
 *           https://github.com/symfony/symfony/issues/40906 as they affect propel bundle configurations that use
 *           environment variables.  May be removed after the resolution of those bugs.
 *
 * @package Propel\Bundle\PropelBundle\DependencyInjection
 */
class EnvResolver
{
  /** @var ContainerBuilder */
  protected $container;

  /**
   * @param ContainerBuilder $container
   */
  public function __construct(ContainerBuilder $container) { $this->container = $container; }

  /**
   * Resolves the given configs by replacing any env_XXXXX_rand placeholder values or env(ENV_VAR) parameters with
   * their real values.
   *
   * @param array $configs An array of configurations, typically from the Symfony Configuration component.
   *
   * @return array         The array of configurations with the env vars resolved.
   */
  public function resolve($configs): array
  {
    if (static::isPrefixed($configs))
    {
      return $this->container->resolveEnvPlaceholders($configs, true);
    }

    return $configs;
  }

  /**
   * Checks for the prefixed string in the given multi-level array and returns TRUE if any of the placeholders are
   * found at the beginning of a value.
   *
   * @param array $configs        An array of configurations, typically from the Symfony Configuration component.
   * @param string[] $prefixes    An array of prefixes to check for
   *
   * @return bool                 TRUE if any of the prefixes are found in any of the values
   */
  public static function isPrefixed($configs, $prefixes = array('env_','env(')): bool
  {
    foreach ($configs as $value)
    {
      if (is_array($value))
      {
        if (true === static::isPrefixed($value, $prefixes))
        {
          return true;
        }
      }
      elseif (is_string($value))
      {
        foreach ($prefixes as $prefix)
        {
          if (0 === strpos($value, $prefix))
          {
            return true;
          }
        }
      }
    }

    return false;
  }
}
