<?php

namespace SupportYard\MonitoringBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DoctrinePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container
            ->getDefinition('doctrine.dbal.logger')
            ->setClass('SupportYard\\MonitoringBundle\\Monolog\\DbalLogger')
            ->addMethodCall(
                'setQueryExecution',
                [new Reference('support_yard_monitoring.monolog.query_execution')]
            );
    }
}
