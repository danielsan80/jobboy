<?php

namespace JobBoy\Bundle\JobBoyBundle;

use JobBoy\Bundle\JobBoyBundle\DependencyInjection\Compiler\RegisterProcessHandlersPass;
use JobBoy\Bundle\JobBoyBundle\DependencyInjection\Compiler\RegisterProcessRepositoryPass;
use JobBoy\Bundle\JobBoyBundle\DependencyInjection\Compiler\RegisterStepsPass;
use JobBoy\Bundle\JobBoyBundle\DependencyInjection\JobBoyExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JobBoyBundle extends Bundle
{

    public function build(ContainerBuilder $containerBuilder)
    {
        parent::build($containerBuilder);

        $containerBuilder->addCompilerPass(new RegisterProcessRepositoryPass());

        $containerBuilder->addCompilerPass(new RegisterProcessHandlersPass());
        $containerBuilder->addCompilerPass(new RegisterStepsPass());
    }

    protected function getContainerExtensionClass()
    {
        return JobBoyExtension::class;
    }

}