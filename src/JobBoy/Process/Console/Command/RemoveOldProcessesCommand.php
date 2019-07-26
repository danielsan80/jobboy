<?php

namespace JobBoy\Process\Console\Command;

use JobBoy\Process\Application\Service\ExecuteProcess;
use JobBoy\Process\Application\Service\RemoveOldProcesses;
use JobBoy\Process\Console\Command\Helper\ParametersHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveOldProcessesCommand extends Command
{

    /** @var RemoveOldProcesses */
    protected $removeOldProcesses;

    public function __construct(RemoveOldProcesses $removeOldProcesses)
    {
        parent::__construct();
        $this->removeOldProcesses = $removeOldProcesses;
    }

    protected function configure()
    {
        $this
            ->setName('jobboy:process:clear')
            ->addOption('days', 'd', InputOption::VALUE_REQUIRED, 'days to keep',90)
            ->setDescription('Remove old processes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->removeOldProcesses->execute($input->getOption('days'));
        $output->writeln('<comment>DONE</comment>');
    }

}