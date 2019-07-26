<?php

namespace JobBoy\Process\Console\Command;

use JobBoy\Process\Application\DTO\Process;
use JobBoy\Process\Application\Service\ListProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
            ->setDescription('List the processes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processes =  $this->listProcesses->execute();

        $table = new Table($output);
        $table
            ->setHeaders([
                'id',
                'code',
                'parameters',
                'created at',
                'updated at',
                'started at',
                'ended at',
                'status',
                'store'
            ]);

        $rows = [];

        /** @var Process $process */
        foreach ($processes as $process) {
            $rows[] = [
                $process->id(),
                $process->code(),
                json_encode($process->parameters()),
                $this->formatDate($process->createdAt()),
                $this->formatDate($process->updatedAt()),
                $this->formatDate($process->startedAt()),
                $this->formatDate($process->endedAt()),
                json_encode($process->status()),
                json_encode($process->store()),
            ];
        }


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