<?php

namespace JobBoy\Process\Console\Command;

use JobBoy\Process\Application\Service\ExecuteProcess;
use JobBoy\Process\Console\Command\Helper\ParametersHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteProcessCommand extends Command
{

    /** @var ExecuteProcess */
    protected $executeProcess;

    public function __construct(ExecuteProcess $executeProcess)
    {
        parent::__construct();
        $this->executeProcess = $executeProcess;
    }

    protected function configure()
    {
        $this
            ->setName('jobboy:process:execute')
            ->addOption('code', 'c', InputOption::VALUE_REQUIRED, 'The code of the job to execute')
            ->addOption('parameters', 'p', InputOption::VALUE_REQUIRED, 'the parameters in json (filename or inline) the job needs', '{}')
            ->setDescription('Execute a process');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getOption('code');
        if (!$code) {
            throw new \InvalidArgumentException('The option "code" is missing');
        }

        $parameters = $input->getOption('parameters');
        $parameters = ParametersHelper::resolveJsonParameters($parameters);

        $output->writeln('Process started');
        $this->executeProcess->execute($code, $parameters);
        $output->writeln('Process ended');

    }

}