<?php

namespace JobBoy\Process\Console\Command;

use Assert\Assertion;
use JobBoy\Process\Application\DTO\Process;
use JobBoy\Process\Application\Service\KillProcess;
use JobBoy\Process\Application\Service\ListProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class KillProcessCommand extends Command
{

    /** @var KillProcess */
    protected $killProcess;

    /** @var ListProcesses */
    protected $listProcesses;

    public function __construct(
        KillProcess $killProcess,
        ListProcesses $listProcesses
    )
    {
        parent::__construct();
        $this->killProcess = $killProcess;
        $this->listProcesses = $listProcesses;
    }

    protected function configure()
    {
        $this
            ->setName('jobboy:process:kill')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'The id of the process to kill or `current`')
            ->addOption('current', 'c', InputOption::VALUE_NONE, 'Kill the current process')
            ->setDescription('Kill a process');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if (!$input->getOption('id') && !$input->getOption('current')) {
            throw new \InvalidArgumentException('No process to kill specified');
        }

        if ($input->getOption('id') && $input->getOption('current')) {
            throw new \InvalidArgumentException('Only `id` OR `current` is allowed');
        }

        $id = $input->getOption('id');
        $processes = $this->listProcesses->execute();

        if ($id === 'current' || $input->getOption('current')) {
            $current = array_pop($processes);
            if ($current) {
                $id = $current->id();
            }
        }

        $matchingProcesses = array_filter($processes, function (Process $process) use ($id) {
            return strpos($process->id(), $id) === 0;
        });

        if (count($matchingProcesses)===0) {
            $output->writeln('No processes to kill found');
            return;
        }

        Assertion::count($matchingProcesses, 1, 'More then one processes found: specify the whole process id, please');

        $this->killProcess->execute($id);

        if ($id==='current') {
            $output->writeln('The current process will be killed ASAP');
        } else {
            $output->writeln(sprintf('The process "%s" will be killed ASAP', $id));
        }

    }

}