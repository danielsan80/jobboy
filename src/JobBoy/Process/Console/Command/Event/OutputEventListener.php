<?php

namespace JobBoy\Process\Console\Command\Event;

use JobBoy\Process\Domain\Event\EventListenerInterface;
use JobBoy\Process\Domain\Event\Message\HasMessageInterface;
use JobBoy\Process\Domain\Event\Message\Message;
use Symfony\Component\Console\Output\OutputInterface;

class OutputEventListener implements EventListenerInterface
{
    /** @var OutputInterface */
    protected $output;
    /** @var callable|null */
    protected $filter;

    public function __construct(OutputInterface $output, ?callable $filter = null)
    {
        $this->output = $output;
        $this->filter = $filter;
    }

    public function handle($event): void
    {
        if ($event instanceof HasMessageInterface) {

            if ($this->filter && !call_user_func($this->filter, $event)) {
                return;
            }


            $message = $event->message();

            $message = $this->transformMessage($message);

            $parameters = '';
            if ($message->parameters()) {
                $parameters = ' '.self::resolveValue($message->parameters());
            }

            $this->output->writeln($message->text().$parameters);
        } else {
            try {
                $this->output->writeln((string)$event);
            } catch (\Throwable $e) {
                
            }
        }
    }


    protected function transformMessage(Message $message): Message
    {


        $missingParameters = [];
        $placeholders = [];
        foreach ($message->parameters() as $key => $value) {
            if (strpos($message->text(), '{{' . $key . '}}') === false) {
                $missingParameters[$key] = $value;
            } else {
                $placeholders['{{' . $key . '}}'] = self::resolveValue($value);
            }
        }

        $renderedMessage = strtr($message->text(), $placeholders);

        return new Message($renderedMessage, $missingParameters);
    }

    protected static function resolveValue($value): string
    {
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_array($value)) {
            
            array_walk_recursive($value, function(&$item) {
                $item = self::resolveValue($item);
            });

            return json_encode($value);
        }

        return (string)$value;
    }
}