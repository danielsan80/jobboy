<?php
declare(strict_types=1);

namespace JobBoy\Process\Console\Command;

use JobBoy\Process\Domain\ProcessHandler\ProcessHandlerInterface;
use JobBoy\Process\Domain\ProcessHandler\ProcessHandlerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListRegisteredProcessHandlersCommand extends Command
{

    /** @var ProcessHandlerRegistry */
    protected $processHandlerRegistry;

    public function __construct(ProcessHandlerRegistry $processHandlerRegistry)
    {
        parent::__construct();
        $this->processHandlerRegistry = $processHandlerRegistry;
    }

    protected function configure()
    {
        $this
            ->setName('jobboy:process-handler:list')
            ->setDescription('List the registered process handlers');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processHandlers = $this->processHandlerRegistry->all();

        $this->writeListTable($output, $processHandlers);
        $output->writeln('');
    }

    protected function writeListTable(OutputInterface $output, array $processHandlers)
    {
        $rows = [];
        $headers = [];

        $prevChannel = null;
        $prevPriority = null;
        foreach ($processHandlers as $channel => $channelData) {
            foreach ($channelData as $priority => $priorityData) {
                /** @var ProcessHandlerInterface $processHandler */
                foreach ($priorityData as $processHandler) {
                    $row = [
                        'channel' => $this->formatChannel($channel, $prevChannel),
                        'priority' => $this->formatPriority($channel, $priority, $prevChannel, $prevPriority),
                        'handler' => $this->formatHandler($processHandler),
                    ];
                    $rows[] = $row;
                    if (!$headers) {
                        $headers = array_keys($row);
                    }
                    $prevChannel = $channel;
                    $prevPriority = $priority;
                }
            }
        }

        if (!$rows) {
            $output->writeln('<comment>No process handlers found</comment>');
            return;
        }

        $table = new Table($output);
        $table->setHeaders($headers);
        $table->setRows($rows);

        $table->render();
    }

    private function formatChannel(string $channel, ?string $prevChannel): string
    {
        if ($channel !== $prevChannel) {
            return $channel;
        }
        return '';
    }

    private function formatPriority(string $channel, int $priority, ?string $prevChannel, ?int $prevPriority): string
    {
        if ($channel !== $prevChannel) {
            return (string)$priority;
        }

        if ($priority !== $prevPriority) {
            return (string)$priority;
        }

        return '';
    }

    private function formatHandler(ProcessHandlerInterface $handler): string
    {
        $class = get_class($handler);

        $interfaces = implode(',', class_implements($handler));

        if (strpos($interfaces, 'ProxyManager\Proxy') === false) {
            return $class;
        }

        return get_parent_class($class);
    }
}
