<?php

namespace JobBoy\Process\Console\Command;

use JobBoy\Process\Application\DTO\Process;
use JobBoy\Process\Application\Service\ListProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class ListProcessesCommand extends Command
{

    /** @var ListProcesses */
    protected $listProcesses;

    public function __construct(ListProcesses $listProcesses)
    {
        parent::__construct();
        $this->listProcesses = $listProcesses;
    }

    protected function configure()
    {
        $this
            ->setName('jobboy:process:list')
            ->addOption('show', 's', InputOption::VALUE_OPTIONAL, 'The id of the process to show')
            ->addOption('active', 'a', InputOption::VALUE_NONE, 'List only active processes')
            ->setDescription('List the processes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processes =  $this->listProcesses->execute();


        if ($input->getOption('active')) {
            $processes = array_filter($processes, function(Process $process){
                return $process->isActive();
            });
        }

        $processes = array_reverse($processes);

        $this->writeListTable($output, $processes);
        $output->writeln('');


        if ($input->hasArgument('show')) {
            $id = $input->getOption('show');

            $this->writeShowTables($output, $processes, $id);
        }
    }


    protected function writeShowTables(OutputInterface $output, array $processes, string $id)
    {
        if (!$processes) {
            $output->writeln('<comment>No processes to show</comment>');
            return;
        }

        if (!$id) {
            $this->writeShowTable($output, $processes[0]);
            $output->writeln('');
            return;
        }

        $matchingProcesses = array_filter($processes, function (Process $process) use ($id) {
            return strpos($process->id(), $id) === 0;
        });
        foreach ($matchingProcesses as $process) {
            $this->writeShowTables($output, $processes, $id);
            $output->writeln('');
        }

    }

    protected function writeShowTable(OutputInterface $output, Process $process)
    {
        
        $rows = [
            ['id', '<fg=cyan;options=bold>'.$process->id().'</>'],
            ['code', $process->code()],
            ['parameters', $this->formatArray($process->parameters())],
            ['created at', $this->formatDate($process->createdAt())],
            ['updated at', $this->formatDate($process->updatedAt())],
            ['started at', $this->formatDate($process->startedAt())],
            ['ended at', $this->formatDate($process->endedAt())],
            ['status', $this->formatStatus($process->status())],
            ['store', $this->formatArray($process->store())],
            ['handled at', $this->formatBoolean($process->isHandled()).($process->isHandled()?': '.$this->formatDate($process->handledAt()):'')],
        ];

        array_walk_recursive($rows, function (&$item, $i) {
            if ($i==0) {
                $item = sprintf('<info>%s</info>', $item);
            }
        });



        $table = new Table($output);
        $table
            ->setRows($rows);

        $table->render();
    }

    protected function writeListTable(OutputInterface $output, array $processes)
    {
        $rows = [];
        $headers = [];
        /** @var Process $process */
        foreach ($processes as $process) {
            $row = [
                'id' => substr($process->id(),0,5),
                'code' => $process->code(),
//                'parameters' => json_encode($process->parameters()),
                'created at' => $this->formatDate($process->createdAt()),
                'updated at' => $this->formatDate($process->updatedAt()),
                'started at' => $this->formatDate($process->startedAt()),
                'ended at' => $this->formatDate($process->endedAt()),
                'status' => $this->formatStatus($process->status()),
                'handled' => $this->formatBoolean($process->isHandled()),
//                'store' => json_encode($process->store()),
            ];
            $rows[] = $row;
            if (!$headers) {
                $headers = array_keys($row);
            }
        }

        if (!$rows) {
            $output->writeln('<comment>No processes found</comment>');
            return;
        }

        $table = new Table($output);
        $table
            ->setHeaders($headers);




        $table
            ->setRows($rows);


        $table->render();
    }

    protected function formatDate(?\DateTimeImmutable $date): string
    {
        if ($date) {
            return $date->format('Y-m-d H:i:s');
        }

        return '';
    }

    protected function formatStatus(string $status): string
    {
        switch ($status) {
            case 'starting':
                $format = '<fg=blue>%s</>';
                break;
            case 'running':
            case 'ending':
                $format = '<fg=yellow>%s</>';
                break;
            case 'failing':
            case 'failed':
                $format = '<fg=red>%s</>';
                break;
            case 'completed':
                $format = '<fg=green>%s</>';
                break;
            default:
                $format = '%s';
        }

        return sprintf($format, $status);
    }

    protected function formatBoolean(bool $bool): string
    {
        return $bool?'✔':'';
    }

    protected function formatArray(array $array): string
    {
        return json_encode($array, JSON_PRETTY_PRINT ^ JSON_UNESCAPED_SLASHES);
    }

}