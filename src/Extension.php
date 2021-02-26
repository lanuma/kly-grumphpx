<?php

declare(strict_types=1);

namespace Kly\GrumPhpX;

use Kly\GrumPhpX\Task\Larastan;
use GrumPHP\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Extension implements ExtensionInterface
{
    public function load(ContainerBuilder $container): void
    {
        $container->register('task.larastan', Larastan::class)
            ->addArgument(new Reference('config'))
            ->addArgument(new Reference('process_builder'))
            ->addArgument(new Reference('formatter.raw_process'))
            ->addTag('grumphp.task', ['config' => 'larastan']);
    }
}
