<?php

namespace JobBoy\Process\Console\Command;

use JobBoy\Process\Application\Service\PauseWork;
use JobBoy\Process\Domain\Event\EventBusInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PauseWorkCommand extends Command
{

    /** @var PauseWork */
    protected $pause;
    /** @var EventBusInterface */
    protected $eventBus;

    public function __construct(PauseWork $pause, EventBusInterface $eventBus)
    {
        parent::__construct();
        $this->pause = $pause;
        $this->eventBus = $eventBus;
    }

    protected function configure()
    {
        $this
            ->setName('jobboy:work:pause')
            ->setDescription('Pause the Work');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('The Work will be paused ASAP');
        $this->pause->execute();
    }

}