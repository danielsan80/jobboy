<?php

namespace JobBoy\Process\Console\Command;

use JobBoy\Process\Application\Service\IsWorkPaused;
use JobBoy\Process\Application\Service\PauseWork;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PauseWorkCommand extends Command
{

    /** @var PauseWork */
    protected $pauseWork;
    /** @var IsWorkPaused */
    protected $isWorkPaused;

    public function __construct(
        PauseWork $pauseWork,
        IsWorkPaused $isWorkPaused
    )
    {
        parent::__construct();
        $this->pauseWork = $pauseWork;
        $this->isWorkPaused = $isWorkPaused;
    }

    protected function configure()
    {
        $this
            ->setName('jobboy:work:pause')
            ->addOption('wait', 'w', InputOption::VALUE_NONE, 'Wait until the worker is actually paused')
            ->setDescription('Pause the Work');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pauseWork->execute();

        if ($input->getOption('wait')) {
            $output->writeln('Waiting until the worker has been paused...');
            while (!$this->isWorkPaused->execute()) {
                sleep(1);
            }
            $output->writeln('The worker is paused');
        } else {
            $output->writeln('The worker will be paused ASAP');
        }
    }

}