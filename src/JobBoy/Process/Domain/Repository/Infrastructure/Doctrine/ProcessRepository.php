<?php

namespace JobBoy\Process\Domain\Repository\Infrastructure\Doctrine;

use Assert\Assertion;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use JobBoy\Clock\Domain\Clock;
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
            ->orderBy('updated_at', 'asc');

        $processes = $this->_hydrateProcesses($qb, $start, $length);

        return $processes;
    }

    public function handled(?int $start = null, ?int $length = null): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->tableName)
            ->orderBy('updated_at', 'asc')
            ->andWhere('handled_at IS NOT NULL');

        $processes = $this->_hydrateProcesses($qb, $start, $length);

        return $processes;
    }

    public function byStatus(ProcessStatus $status, ?int $start = null, ?int $length = null): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->tableName)
            ->orderBy('updated_at', 'asc')
            ->andWhere('status = :status')
            ->setParameter('status', $status->toScalar());

        $processes = $this->_hydrateProcesses($qb, $start, $length);

        return $processes;

    }


    public function stale(?\DateTimeImmutable $until = null, ?int $start = null, ?int $length = null): array
    {

        if (!$until) {
            $until = ProcessRepositoryUtil::aFewDaysAgo(self::DEFAULT_STALE_DAYS);
        }

        $qb = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->tableName)
            ->orderBy('updated_at', 'asc')
            ->andWhere('updated_at < :until')
            ->setParameter('until', $until->format(\DateTime::ISO8601));

        $processes = $this->_hydrateProcesses($qb, $start, $length);

        return $processes;

    }

    public function configureSchema(Schema $schema)
    {
        if ($schema->hasTable($this->tableName)) {
            return null;
        }

        return self::configureTable($this->tableName, $schema);
    }


    public static function configureTable($tableName = self::DEFAULT_TABLE, Schema $schema = null)
    {
        $schema = $schema ?: new Schema();

        $table = $schema->createTable($tableName);

        $table->addColumn('id', Type::STRING, ['length' => 36]);
        $table->addColumn('code', Type::STRING, ['length' => 255]);
        $table->addColumn('parameters', Type::TEXT);
        $table->addColumn('status', Type::STRING, ['length' => 255]);
        $table->addColumn('created_at', Type::STRING, []);
        $table->addColumn('updated_at', Type::STRING, []);
        $table->addColumn('started_at', Type::STRING, ['notnull' => false]);
        $table->addColumn('ended_at', Type::STRING, ['notnull' => false]);
        $table->addColumn('handled_at', Type::STRING, ['notnull' => false]);
        $table->addColumn('store', Type::TEXT);

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
                'parameters' => json_encode($process->parameters()->toScalar()),
                'status' => $process->status()->toScalar(),
                'created_at' => $this->_datetimeToString($process->createdAt()),
                'updated_at' => $this->_datetimeToString($process->updatedAt()),
                'started_at' => $this->_datetimeToString($process->startedAt()),
                'ended_at' => $this->_datetimeToString($process->endedAt()),
                'handled_at' => $this->_datetimeToString($process->handledAt()),
                'store' => json_encode($process->store()->toScalar()),
            ]);

        $qb->execute();

    }


    protected function _update(TouchCallbackProcess $process): void
    {

        $this->connection->executeUpdate('
            UPDATE ' . $this->tableName . ' SET
              status = ?,
              updated_at = ?,
              started_at = ?,
              ended_at = ?,
              handled_at = ?,
              store = ?
            WHERE id = ?',
            [
                $process->status()->toScalar(),
                $this->_datetimeToString($process->updatedAt()),
                $this->_datetimeToString($process->startedAt()),
                $this->_datetimeToString($process->endedAt()),
                $this->_datetimeToString($process->handledAt()),
                json_encode($process->store()->toScalar()),
                $process->id()->toScalar(),
            ]);
    }

    protected function _get(string $id): ?TouchCallbackProcess
    {

        $statement = $this->connection->executeQuery('
            SELECT *
            FROM ' . $this->tableName . '
            WHERE id = ?',
            [
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
            'parameters' => new ProcessParameters(json_decode($data['parameters'], true)),
        ]));

        Assertion::isInstanceOf($process, HydratableProcess::class);

        $process->hydrate(new HydratableProcessData([
            'status' => new ProcessStatus($data['status']),
            'createdAt' => $this->_stringToDateTime($data['created_at']),
            'updatedAt' => $this->_stringToDateTime($data['updated_at']),
            'startedAt' => $this->_stringToDateTime($data['started_at']),
            'endedAt' => $this->_stringToDateTime($data['ended_at']),
            'handledAt' => $this->_stringToDateTime($data['handled_at']),
            'store' => new ProcessStore(json_decode($data['store'], true)),
        ]));

        $process->addTouchCallback($this->touchCallback);

        return $process;
    }

    protected function _delete(TouchCallbackProcess $process): void
    {
        $this->connection->executeUpdate('
            DELETE FROM ' . $this->tableName . ' 
            WHERE id = ?',
            [
                $process->id()->toScalar(),
            ]);
        $process->removeTouchCallback($this->touchCallback);
    }

    protected function _hydrateProcesses(QueryBuilder $qb, ?int $start = null, ?int $length = null): array
    {

        if ($start !== null) {
            $qb->setFirstResult($start);
        }
        if ($length !== null) {
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

    protected function _datetimeToString(?\DateTimeImmutable $date): ?string
    {
        if (!$date) {
            return null;
        }

        return $date->format(\DateTime::ISO8601);
    }

    protected function _stringToDateTime(?string $string): ?\DateTimeImmutable
    {
        if (!$string) {
            return null;
        }

        return \DateTimeImmutable::createFromFormat(\DateTime::ISO8601, $string);
    }

}