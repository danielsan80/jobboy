<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\Migrations\AbstractMigration;
use JobBoy\Process\Domain\Repository\Infrastructure\Doctrine\ProcessRepository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
/**
 * Doctrine DBAL Migration of JobBoy ProcessRepository (for Symfony)
 *
 * You can prevent the doctrine:migrations:diff command from dropping the table
 * by setting the $tableName parameter in broadway_event_store_dbal.yaml to ...
 *
 *     - "__domain_events"
 *
 * ... and adding ...
 *
 *     schema_filter: ~^(?!__)~"
 *
 * ... to doctrine.yaml (under the "dbal" section). This will tell Doctrine to
 * ignore all tables starting with "__" (two underscores).
 *
 * @author Andreas Gustafsson <arrgson@gmail.com>
 */
final class Version00000000000000 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    /**
     * @param Schema $schema
     *
     * @throws DBALException
     */
    public function up(Schema $schema) : void
    {
        $platform = $this->getPlatform();
        $table = $this->getProcessTable();
        $createFlags = AbstractPlatform::CREATE_INDEXES|AbstractPlatform::CREATE_FOREIGNKEYS;
        $createTableSqls = $platform->getCreateTableSQL($table, $createFlags);
        foreach($createTableSqls as $createTableSql) {
            $this->addSql($createTableSql);
        }
    }
    /**
     * @param Schema $schema
     *
     * @throws DBALException
     */
    public function down(Schema $schema) : void
    {
        $platform = $this->getPlatform();
        $table = $this->getProcessTable();
        $dropTableSql = $platform->getDropTableSQL($table);
        $this->addSql($dropTableSql);
    }
    /**
     * @return Table
     */
    private function getProcessTable(): Table
    {
        $table = ProcessRepository::configureTable();
        return $table;
    }

    /**
     * @return AbstractPlatform
     *
     * @throws DBALException
     */
    private function getPlatform(): AbstractPlatform
    {
        $connection = $this->container->get('database_connection');
        $platform = $connection->getDatabasePlatform();
        return $platform;
    }
}
