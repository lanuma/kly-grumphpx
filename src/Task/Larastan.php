<?php

declare(strict_types=1);

namespace Kly\GrumPhpX\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Larastan extends AbstractExternalTask
{
    public function getName(): string
    {
        return 'larastan';
    }

    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'config' => null, //relative to project root dir
            'level' => 5,
            'paths' => ['app', 'config', 'tests'],
        ]);

        $resolver->addAllowedTypes('config', ['null', 'string']);
        $resolver->addAllowedTypes('level', ['null', 'int']);
        $resolver->addAllowedTypes('paths', ['array']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfigurableOptions();

        $arguments = $this->processBuilder->createArgumentsForCommand('php');
        $arguments->add('./vendor/bin/phpstan');
        $arguments->add('analyse');

        if ($config['paths']) {
            foreach ($config['paths'] as $path) {
                $arguments->add($path);
            }
        }

        if ($config['config']) {
            $arguments->add('--configuration='.getcwd().'/'.$config['config']);
        }

        if ($config['level']) {
            $arguments->add('--level='.$config['level']);
        }

        $arguments->add('--memory-limit=2G');

        $process = $this->processBuilder->buildProcess($arguments);

        $process->run();

        if (! $process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
