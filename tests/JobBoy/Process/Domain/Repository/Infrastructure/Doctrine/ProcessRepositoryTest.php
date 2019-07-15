<?php

namespace Tests\JobBoy\Process\Domain\Repository\Infrastructure\Doctrine;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\Entity\Infrastructure\TouchCallback\HydratableProcess;
use JobBoy\Process\Domain\Repository\Infrastructure\Doctrine\ProcessRepository;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;
use Tests\JobBoy\Process\Domain\Repository\ProcessRepositoryInterfaceTest;

class ProcessRepositoryTest extends ProcessRepositoryInterfaceTest
{
    protected $processFactory;

    protected function createRepository(): ProcessRepositoryInterface
    {
        $processFactory = $this->createFactory();

        $config = new Configuration();
        $params = array(
            'url' => 'mysql://root:root@db',
        );

        $connection = DriverManager::getConnection($params, $config);
        $connection->getSchemaManager()->dropAndCreateDatabase('jobboy_test');

        $params = array(
            'url' => 'mysql://root:root@db/jobboy_test',
        );

        $connection = DriverManager::getConnection($params, $config);
        $schemaManager = $connection->getSchemaManager();
        $schema = $schemaManager->createSchema();

        $processRepository = new ProcessRepository($processFactory, $connection);

        $table = $processRepository->configureSchema($schema);
        $schemaManager->createTable($table);


        return new ProcessRepository($processFactory, $connection);
    }

    protected function createFactory(): ProcessFactory
    {
        if (!$this->processFactory) {
            $this->processFactory = new ProcessFactory(HydratableProcess::class);
        }

        return $this->processFactory;
    }


}
