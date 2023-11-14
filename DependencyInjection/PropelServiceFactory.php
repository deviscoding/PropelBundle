<?php

namespace Propel\Bundle\PropelBundle\DependencyInjection;

use Propel\Bundle\PropelBundle\DataCollector\PropelDataCollector;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Factory to create the 'propel.configuration', 'propel.data_collector', and 'propel.build_properties' services at
 * runtime. Services must be configured as synthetic in the bundle configuration, then the factory methods called
 * during the PropelBundle::boot() method.
 *
 * Class PropelConfigurationFactory
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @package Propel\Bundle\PropelBundle\DependencyInjection
 */
class PropelServiceFactory
{
    const CONFIG           = 'propel.configuration';
    const DATA_COLLECTOR   = 'propel.data_collector';
    const BUILD_PROPERTIES = 'propel.build_properties';

    /**
     * Creates a PropelDataCollector object using the 'propel.data_collector' parameter and 'propel.logger' service,
     * then injects the object into the container as the 'propel.build_properties' service, replacing the synthetic.
     *
     * If the container does not already have the 'propel.configuration' service, it will be created and injected into
     * the container as well.
     *
     * @param ContainerInterface $container
     *
     * @return PropelDataCollector|object
     */
    public function collector(ContainerInterface $container): PropelDataCollector
    {
        $name = static::DATA_COLLECTOR;
        if (!$container->has($name))
        {
            // Make sure that we have a propel.configuration service
            $config = $this->config($container);
            // Get the logger Service
            $logger = $container->get('propel.logger');
            // Get the class & create object
            $class  = $this->getClass($container, $name);
            $object = new $class($logger, $config);

            $container->set($name, $object);
        }

        return $container->get($name);
    }

    /**
     * Creates a \PropelConfiguration object using the 'propel.configuration.class' and 'propel.dbal' parameters,
     * then injects the object into the container as the 'propel.configuration' service, replacing the synthetic.
     *
     * @param ContainerInterface $container
     *
     * @return \PropelConfiguration|object
     */
    public function config(ContainerInterface $container): \PropelConfiguration
    {
        $name = static::CONFIG;
        if (!$container->has($name))
        {
            $class  = $this->getClass($container, $name) ?? \PropelConfiguration::class;
            $dbal   = $this->normalizeDbal($this->getArrayParameter($container, 'propel.dbal'));
            $config = new $class($dbal);

            if ($this->isPropelLogging($container))
            {
                $config->setParameter('debugpdo.logging.methods', array(
                    'PropelPDO::exec',
                    'PropelPDO::query',
                    'PropelPDO::prepare',
                    'DebugPDOStatement::execute',
                ), false);

                $config->setParameter('debugpdo.logging.details', array(
                    'time'       => array('enabled' => true),
                    'mem'        => array('enabled' => true),
                    'connection' => array('enabled' => true),
                ));
            }

            $container->set($name, $config);
        }

        return $container->get($name);
    }

    /**
     * Creates a Properties object using the 'propel.build_properties' and 'propel.build_properties.class' parameters,
     * then injects the object into the container as the 'propel.build_properties' service, replacing the synthetic.
     * 
     * @param ContainerInterface $container
     *
     * @return Properties|object
     */
    public function properties(ContainerInterface $container): Properties
    {
        $name = static::BUILD_PROPERTIES;
        if (!$container->has($name))
        {
            $class = $this->getClass($container, $name) ?? Properties::class;
            $props = $this->getArrayParameter($container, $name);
            $obj   = new $class($props);

            $container->set($name, $obj);
        }

        return $container->get($name);
    }

    /**
     * Evaluates if the 'logging' option in the propel configuration is set to TRUE.
     * Requires that the 'propel.logging' parameter is previously injected into the container,
     * typically in PropelExtension.
     *
     * @param ContainerInterface $container
     *
     * @return bool
     */
    public function isPropelLogging(ContainerInterface $container)
    {
        return $container->hasParameter('propel.logging') && (bool)$container->getParameter('propel.logging');
    }

    /**
     * Normalizes config settings containing the database driver. While this is also done in the configuration at
     * compile time, some values may be environment value placeholders, which cannot be normalized.
     *
     * @param array $dbal
     *
     * @return array
     */
    private function normalizeDbal($dbal)
    {
        $normalizer = new DriverNormalizer();
        $datasources = $dbal['datasources'] ?? array();

        foreach($datasources as $name => $datasource)
        {
            $datasources[$name] = $normalizer->normalizeDatasource($datasource);
        }

        $dbal['datasources'] = $datasources;

        return $dbal;
    }

    /**
     * Checks for and returns a parameter as an array. Returns an empty array if the parameter
     * is not present in the container, or is not an array value.
     *
     * @param ContainerInterface $container
     * @param string             $name
     *
     * @return array
     */
    private function getArrayParameter(ContainerInterface $container, string $name): array
    {
        if ($container->hasParameter($name))
        {
            $props = $container->getParameter($name);

            if (is_array($props))
            {
                return $props;
            }
        }

        return array();
    }

    /**
     * Gets the fully qualified class name for the given prefix, as long as the parameter is set in the container.
     *
     * @param ContainerInterface $container The container, which should have the <prefix>.class parameter.
     * @param string             $prefix    The string to put before .class when retrieving the parameter.
     *
     * @return string|null
     */
    private function getClass(ContainerInterface $container, string $prefix)
    {
        $name = $prefix . '.class';
        if ($container->hasParameter($name))
        {
            $class = $container->getParameter($name);
            if (is_string($class) && class_exists($class))
            {
                return $class;
            }
        }

        return null;
    }
}