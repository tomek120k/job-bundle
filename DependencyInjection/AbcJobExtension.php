<?php
/*
* This file is part of the job-bundle package.
*
* (c) Hannes Schulz <hannes.schulz@aboutcoders.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Abc\Bundle\JobBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Hannes Schulz <hannes.schulz@aboutcoders.com>
 */
class AbcJobExtension extends Extension
{
    const NAMESPACE_PREFIX = 'abc.job.';

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config/services'));

        // default services
        $container->setAlias('abc.job.manager', $config['service']['manager']);
        $container->setAlias('abc.job.job_manager', $config['service']['job_manager']);
        $container->setAlias('abc.job.agent_manager', $config['service']['agent_manager']);
        $container->setAlias('abc.job.queue_engine', $config['service']['queue_engine']);
        $container->setAlias('abc.job.schedule_manager', $config['service']['schedule_manager']);
        $container->setAlias('abc.job.schedule_iterator', $config['service']['schedule_iterator']);

        if('custom' !== $config['db_driver'])
        {
            $loader->load(sprintf('%s.xml', $config['db_driver']));
        }

        $this->remapParametersNamespaces(
            $config,
            $container,
            array(
                '' => array(
                    'model_manager_name' => self::NAMESPACE_PREFIX . 'model_manager_name',
                    'register_default_jobs' => self::NAMESPACE_PREFIX . 'register_default_jobs',
                )
            )
        );

        $this->remapParametersNamespaces(
            $config,
            $container,
            array(
                'logging' => array(
                    'directory' => self::NAMESPACE_PREFIX . 'logging.directory',
                )
            )
        );

        $loader->load('agent.xml');
        $loader->load('manager.xml');
        $loader->load('schedule.xml');
        $loader->load('listener.xml');
        $loader->load('eraser.xml');
        $loader->load('sonata.xml');
        $loader->load('metadata.xml');
        $loader->load('forms.xml');

        if($config['register_default_jobs'])
        {
            $loader->load('default_jobs.xml');
        }

        $this->loadLogger($config['logging'], $container, $loader);

    }

    private function loadLogger(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        if('custom' !== $config['handler'])
        {
            $loader->load('logger_'. $config['handler'] .'.xml');
        }

        if (isset($config['formatter']))
        {
            $jobType = $container->getDefinition('abc.job.logger.factory');
            $jobType->addMethodCall('setFormatter', array(new Reference($config['formatter'])));
        }

        if (!empty($config['processor']))
        {
            $jobType = $container->getDefinition('abc.job.logger.factory');

            foreach($config['processor'] as $serviceId)
            {
                $jobType->addMethodCall('addProcessor', array(new Reference($serviceId)));
            }
        }

        $container->setParameter('abc.job.logging.default_level', $config['default_level']);
        $container->setParameter('abc.job.logging.custom_level', $config['custom_level']);
    }

    protected function remapParameters(array $config, ContainerBuilder $container, array $map)
    {
        foreach($map as $name => $paramName)
        {
            if(array_key_exists($name, $config))
            {
                $container->setParameter($paramName, $config[$name]);
            }
        }
    }

    protected function remapParametersNamespaces(array $config, ContainerBuilder $container, array $namespaces)
    {
        foreach($namespaces as $ns => $map)
        {
            if($ns)
            {
                if(!array_key_exists($ns, $config))
                {
                    continue;
                }
                $namespaceConfig = $config[$ns];
            }
            else
            {
                $namespaceConfig = $config;
            }
            if(is_array($map))
            {
                $this->remapParameters($namespaceConfig, $container, $map);
            }
            else
            {
                foreach($namespaceConfig as $name => $value)
                {
                    $container->setParameter(sprintf($map, $name), $value);
                }
            }
        }
    }
}