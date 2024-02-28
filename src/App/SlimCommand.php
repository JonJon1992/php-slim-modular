<?php

namespace Core\App;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class SlimCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;
    /**
     * @var InputInterface
     */
    protected InputInterface $input;
    /**
     * @var OutputInterface
     */
    protected OutputInterface $output;

    abstract protected function handler();

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        app()->command($this);
        $this->input = $input;
        $this->output = $output;
        $this->handler();
        return 0;
    }
}
