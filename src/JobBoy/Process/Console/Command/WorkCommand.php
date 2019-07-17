<?php

namespace JobBoy\Process\Console\Command;

use JobBoy\Process\Application\Service\Work;
use JobBoy\Process\Console\Command\Helper\ParametersHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WorkCommand extends Command
{

    /** @var Work */
    protected $work;

    public function __construct(Work $work)
    {
        parent::__construct();
        $this->work = $work;
    }

    protected function configure()
    {
        $this
            ->setName('jobboy:work')
            ->addOption('timeout', 't', InputOption::VALUE_REQUIRED, 'The timeout in seconds', 300)
            ->addOption('idle-time', 'i', InputOption::VALUE_REQUIRED, 'The time to wait in seconds if there are no work to do', 30)
            ->setDescription('Iterate processes for a few');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Worker started');
        $this->work->execute($input->getOption('timeout'), $input->getOption('idle-time'));
        $output->writeln('Worker stopped');
    }

}