<?php

namespace Bdf\Fixtures;

use Bdf\Api\Protocol\ProtocolFactory;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    private function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        $container->extension('framework', [
            'error_controller' => null,
        ]);
    }

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->register(ProtocolFactory::class)
            ->setPublic(true)
            ->setArguments([new Reference('service_container')])
        ;
    }
}
