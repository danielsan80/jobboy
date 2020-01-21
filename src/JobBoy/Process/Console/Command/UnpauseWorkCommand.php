<?php

namespace JobBoy\Process\Console\Command;

use JobBoy\Process\Application\Service\UnpauseWork;
use JobBoy\Process\Domain\Event\EventBusInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UnpauseWorkCommand extends Command
{

    /** @var UnpauseWork */
    protected $unpause;
    /** @var EventBusInterface */
    protected $eventBus;

    public function __construct(UnpauseWork $unpause)
    {
        parent::__construct();
        $this->unpause = $unpause;
    }

    protected function configure()
    {
        $this
            ->setName('jobboy:work:unpause')
            ->setDescription('unpause the Work');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('The Work will be unpaused ASAP');
        $this->unpause->execute();
    }

}