<?php

namespace JobBoy\Process\Console\Command\Event;

use JobBoy\Process\Domain\Event\EventListenerInterface;
use JobBoy\Process\Domain\Event\Message\HasMessageInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OutputEventListener implements EventListenerInterface
{
    /** @var OutputInterface */
    protected $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function handle($event): void
    {
        if ($event instanceof HasMessageInterface) {
            $message = $event->message();

            $parameters = '';
            if ($message->parameters()) {
                $parameters = ' '.json_encode($message->parameters());
            }

            $this->output->writeln($message->text().$parameters);
        }
    }
}