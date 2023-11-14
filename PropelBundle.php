<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */
namespace Propel\Bundle\PropelBundle;

use Propel\Bundle\PropelBundle\DependencyInjection\PropelServiceFactory;
use Propel\Bundle\PropelBundle\DependencyInjection\Security\UserProvider\PropelFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * PropelBundle.
 *
 * @author William DURAND <william.durand1@gmail.com>
 */
class PropelBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        require_once $this->container->getParameter('propel.path').'/runtime/lib/Propel.php';

        if (0 === strncasecmp(PHP_SAPI, 'cli', 3)) {
            set_include_path($this->container->getParameter('kernel.root_dir').'/..'.PATH_SEPARATOR.
                             $this->container->getParameter('propel.phing_path').PATH_SEPARATOR.
                             $this->container->getParameter('propel.phing_path').'/classes'.PATH_SEPARATOR.
                             get_include_path());
        }

        // Create the Service Factory
        $Factory = new PropelServiceFactory();

        // Set the Build Properties Service
        $Factory->properties($this->container);

        // Initialize Propel
        if (!\Propel::isInit()) {
            // Create Configuration object at runtime to prevent issues with environment variables
            // used in the configuration.  If this is done at compile time, they aren't resolved.
            // https://github.com/symfony/symfony/issues/27683
            // https://github.com/symfony/symfony/issues/40906
            $config = $Factory->config($this->container);

            // Set the configuration created above, which has also been injected into the container as a service.
            \Propel::setConfiguration($config);

            // Initialize the propel.data_collector Service
            $Factory->collector($this->container);

            // The factory above sets the logging parameters in PropelConfiguration, if param 'propel.logging' is set.
            if ($Factory->isPropelLogging($this->container)) {
                \Propel::setLogger($this->container->get('propel.logger'));
            }

            \Propel::initialize();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        if ($container->hasExtension('security')) {
            $container->getExtension('security')->addUserProviderFactory(new PropelFactory('propel', 'propel.security.user.provider'));
        }
    }
}
