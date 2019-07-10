<?php

namespace JobBoy\Process\Domain\Repository\Infrastructure\Doctrine;

use Assert\Assertion;
use Dan\Clock\Domain\Clock;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use JobBoy\Process\Domain\Entity\Data\ProcessData;
use JobBoy\Process\Domain\Entity\Factory\ProcessFactory;
use JobBoy\Process\Domain\Entity\Id\ProcessId;
use JobBoy\Process\Domain\Entity\Infrastructure\Hydratable\Data\HydratableProcessData;
use JobBoy\Process\Domain\Entity\Infrastructure\TouchCallback\HydratableProcess;
use JobBoy\Process\Domain\Entity\Infrastructure\TouchCallback\Process as TouchCallbackProcess;
use JobBoy\Process\Domain\Entity\Process;
use JobBoy\Process\Domain\ProcessParameters;
use JobBoy\Process\Domain\ProcessStatus;
use JobBoy\Process\Domain\ProcessStore;
use JobBoy\Process\Domain\Repository\Infrastructure\Util\ProcessRepositoryUtil;
use JobBoy\Process\Domain\Repository\ProcessRepositoryInterface;

class ProcessRepository implements ProcessRepositoryInterface
{
    const DEFAULT_TABLE = '__process';
    const DEFAULT_STALE_DAYS = 90;


    /** @var ProcessFactory */
    protected $processFactory;
    /** @var Connection */
    protected $connection;
    /** @var string|null */
    protected $tableName;

    protected $touchCallback;

    public function __construct(
        ProcessFactory $processFactory,
        Connection $connection,
        ?string $tableName = null
    )
    {
        if (!$tableName) {
            $tableName = self::DEFAULT_TABLE;
        }

        $this->processFactory = $processFactory;
        $this->connection = $connection;
        $this->tableName = $tableName;

        $this->touchCallback = function (Process $process) {
            $this->onTouch($process);
        };
    }

    protected function onTouch(Process $process)
    {
        $this->_update($process);
    }

    public function add(Process $process): void
    {
        $this->_insert($process);
    }

    public function remove(Process $process): void
    {
        $this->_delete($process);
    }


    public function byId(ProcessId $id): ?Process
    {
        return $this->_get((string)$id);
    }

    public function all(?int $start = null, ?int $length = null): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->tableName)
            ->orderBy('updated_at','desc')
        ;

        $processes = $this->_hydrateProcesses($qb, $start, $length);

        return $processes;
    }

    public function handled(?int $start = null, ?int $length = null): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->from($this->tableName)
            ->orderBy('updated_at','desc')
            ->andWhere('handled_at IS NOT NULL')
        ;

        $processes = $this->_hydrateProcesses($qb, $start, $length);

        return $processes;
    }

    public function byStatus(ProcessStatus $status, ?int $start = null, ?int $length = null): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->from($this->tableName)
            ->orderBy('updated_at','desc')
            ->andWhere('status = :status')
            ->setParameter('status', $status->toScalar())
        ;

        $processes = $this->_hydrateProcesses($qb, $start, $length);

        return $processes;

    }


    public function stale(?\DateTimeImmutable $until = null, ?int $start = null, ?int $length = null): array
    {
        if (!$until) {
            $until = Clock::createDateTimeImmutable(sprintf('- %d days', self::DEFAULT_STALE_DAYS));
        }

        $qb = $this->connection->createQueryBuilder()
            ->from($this->tableName)
            ->orderBy('updated_at','desc')
            ->andWhere('updated_at < :until')
            ->setParameter('until', $until)
        ;

        $processes = $this->_hydrateProcesses($qb, $start, $length);

        return $processes;

    }

    public function configureSchema(Schema $schema)
    {
        if ($schema->hasTable($this->tableName)) {
            return null;
        }

        return $this->configureTable($schema);
    }


    public function configureTable(Schema $schema = null)
    {
        $schema = $schema ?: new Schema();

        $table = $schema->createTable($this->tableName);

        $table->addColumn('id', Type::GUID, ['length' => 36]);
        $table->addColumn('code', Type::STRING, ['length' => 255]);
        $table->addColumn('parameters', Type::JSON);
        $table->addColumn('status', Type::STRING, ['length' => 255]);
        $table->addColumn('created_at', Type::DATETIMETZ_IMMUTABLE, []);
        $table->addColumn('updated_at', Type::DATETIMETZ_IMMUTABLE, []);
        $table->addColumn('started_at', Type::DATETIMETZ_IMMUTABLE, ['notnull' => false]);
        $table->addColumn('ended_at', Type::DATETIMETZ_IMMUTABLE, ['notnull' => false]);
        $table->addColumn('handled_at', Type::DATETIMETZ_IMMUTABLE, ['notnull' => false]);
        $table->addColumn('store', Type::JSON);

        $table->setPrimaryKey(['id']);

        return $table;
    }


    protected function _insert(TouchCallbackProcess $process): void
    {
        $process->addTouchCallback($this->touchCallback);

        $qb = $this->connection->createQueryBuilder()
            ->insert($this->tableName)
            ->values([
                'id' => ':id',
                'code' => ':code',
                'parameters' => ':parameters',
                'status' => ':status',
                'created_at' => ':created_at',
                'updated_at' => ':updated_at',
                'started_at' => ':started_at',
                'ended_at' => ':ended_at',
                'handled_at' => ':handled_at',
                'store' => ':store',
            ])
            ->setParameters([
                'id' => (string)$process->id(),
                'code' => $process->code(),
                'parameters' => $process->parameters()->toScalar(),
                'status' => $process->status()->toScalar(),
                'created_at' => $process->createdAt(),
                'updated_at' => $process->updatedAt(),
                'started_at' => $process->startedAt(),
                'ended_at' => $process->endedAt(),
                'handled_at' => $process->handledAt(),
                'store' => $process->store()->toScalar(),
            ],[
                'id' => Type::GUID,
                'code' => Type::STRING,
                'parameters' =>Type::JSON,
                'status' => Type::STRING,
                'created_at' => Type::DATETIMETZ_IMMUTABLE,
                'updated_at' => Type::DATETIMETZ_IMMUTABLE,
                'started_at' =>  Type::DATETIMETZ_IMMUTABLE,
                'ended_at' => Type::DATETIMETZ_IMMUTABLE,
                'handled_at' =>Type::DATETIMETZ_IMMUTABLE,
                'store' => Type::JSON,
            ])
        ;

        $qb->execute();

    }


    protected function _update(TouchCallbackProcess $process): void
    {

        $this->connection->executeUpdate('
            UPDATE ? SET
              status = ?,
              updated_at = ?,
              started_at = ?,
              ended_at = ?,
              handled_at = ?,
              store = ?
            WHERE id = ?',
            [
                $this->tableName,
                $process->status()->toScalar(),
                $process->updatedAt(),
                $process->startedAt(),
                $process->endedAt(),
                $process->handledAt(),
                $process->store()->toScalar(),
                $process->id()->toScalar(),
            ]);
    }

    protected function _get(string $id): ?TouchCallbackProcess
    {

        $statement = $this->connection->executeQuery('
            SELECT *
            FROM ?
            WHERE id = ?',
            [
                $this->tableName,
                $id
            ]);

        $data = $statement->fetch();
        if (!$data) {
            return null;
        }

        $process = $this->_hydrateProcess($data);

        return $process;
    }

    protected function _hydrateProcess(array $data): Process
    {
        $process = $this->processFactory->create(new ProcessData([
            'id' => new ProcessId($data['id']),
            'code' => $data['code'],
            'parameters' => new ProcessParameters($data['parameters']),
        ]));

        Assertion::isInstanceOf($process, HydratableProcess::class);

        $process->hydrate(new HydratableProcessData([
            'status' => new ProcessStatus($data['status']),
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
            'started_at' => $data['started_at'],
            'ended_at' => $data['ended_at'],
            'handled_at' => $data['handled_at'],
            'store' => new ProcessStore($data['store']),
        ]));

        $process->addTouchCallback($this->touchCallback);
    }

    protected function _delete(TouchCallbackProcess $process): void
    {
        $this->connection->executeUpdate('
            DELETE FROM ? 
            WHERE id = ?',
            [
                $this->tableName,
                $process->id()->toScalar(),
            ]);
        $process->removeTouchCallback($this->touchCallback);
    }

    protected function _hydrateProcesses(QueryBuilder $qb, ?int $start = null, ?int $length = null): array
    {

        if ($start!== null) {
            $qb->setFirstResult($start);
        }
        if ($length!== null) {
            $qb->setMaxResults($length);
        }

        $statement = $qb->execute();

        $records = $statement->fetchAll();
        $processes = [];
        foreach ($records as $record) {
            $process = $this->_hydrateProcess($record);
            $process->addTouchCallback($this->touchCallback);
            $processes[] = $process;
        }

        return $processes;
    }

}