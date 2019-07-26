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
            ->addOption('show', 's', InputOption::VALUE_REQUIRED, 'The id of the process to show')
            ->addOption('active', 'a', InputOption::VALUE_NONE, 'List only active processes')
            ->setDescription('List the processes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processes =  $this->listProcesses->execute();

        $this->writeShowTable($output, $processes[0]);
        $this->writeListTable($output, $processes);

    }



    protected function writeShowTable(OutputInterface $output, Process $process)
    {
        $rows = [
            [ 'id', $process->id() ]
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
        $headers = null;
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
                'status' => $process->status(),
//                'store' => json_encode($process->store()),
            ];
            $rows[] = $row;
            if (!$headers) {
                $headers = array_keys($row);
            }
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

}