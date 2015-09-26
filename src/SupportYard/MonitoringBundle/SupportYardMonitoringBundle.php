<?php

namespace SupportYard\MonitoringBundle;

use SupportYard\MonitoringBundle\DependencyInjection\Compiler\DoctrinePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SupportYardMonitoringBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DoctrinePass());
    }
}
