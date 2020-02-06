<?php

namespace JobBoy\Process\Console\Command;

use JobBoy\Process\Application\Service\IsWorkPaused;
use JobBoy\Process\Application\Service\UnpauseWork;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UnpauseWorkCommand extends Command
{

    /** @var UnpauseWork */
    protected $unpause;

    /** @var IsWorkPaused */
    protected $isWorkPaused;

    public function __construct(
        UnpauseWork $unpause,
        IsWorkPaused $isWorkPaused)
    {
        parent::__construct();
        $this->unpause = $unpause;
        $this->isWorkPaused = $isWorkPaused;
    }

    protected function configure()
    {
        $this
            ->setName('jobboy:work:unpause')
            ->addOption('wait', 'w', InputOption::VALUE_NONE, 'Wait until the worker is no more paused')
            ->setDescription('Unpause the Work');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->unpause->execute();

        if ($input->hasOption('wait')) {
            $output->writeln('Waiting until the worker has been unpaused...');
            while ($this->isWorkPaused->execute()) {
                sleep(1);
            }
            $output->writeln('The worker is restarted');
        } else {
            $output->writeln('The worker will be unpaused ASAP');
        }
    }

}