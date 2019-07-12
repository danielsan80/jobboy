<?php

namespace JobBoy\Bundle\JobBoyBundle\Command;

use JobBoy\Bundle\JobBoyBundle\Command\Helper\ParametersHelper;
use JobBoy\Process\Application\Service\StartProcess;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StartProcessCommand extends Command
{

    /** @var StartProcess */
    protected $startProcess;

    public function __construct(StartProcess $startProcess)
    {
        parent::__construct();
        $this->startProcess = $startProcess;
    }

    protected function configure()
    {
        $this
            ->setName('jobboy:process:start')
            ->addOption('code', 'c', InputOption::VALUE_REQUIRED, 'The code of the job to execute')
            ->addOption('parameters', 'p', InputOption::VALUE_REQUIRED, 'the parameters in json (filename or inline) the job needs', '{}')
            ->setDescription('Start a process');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getOption('code');
        if (!$code) {
            throw new \InvalidArgumentException('The option "code" is missing');
        }

        $parameters = $input->getOption('parameters');
        $parameters = ParametersHelper::resolveJsonParameters($parameters);

        $this->startProcess->execute($code, $parameters);

        $output->writeln('Process started');
    }

}