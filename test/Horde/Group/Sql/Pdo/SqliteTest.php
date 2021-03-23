<?php
/**
 * Prepare the test setup.
 */
namespace Horde\Group\Sql\Pdo;
use Horde\Group\Sql\Base;
use \Horde_Test_Factory_Db;
use \Horde_Db_Migration_Migrator;
use \Horde_Db_Adapter_Pdo_Sqlite;

/**
 * Copyright 2011-2017 Horde LLC (http://www.horde.org/)
 *
 * @author     Jan Schneider <jan@horde.org>
 * @category   Horde
 * @package    Group
 * @subpackage UnitTests
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 */
class SqliteTest extends Base
{
    public static function setUpBeforeClass(): void
    {
        $factory_db = new Horde_Test_Factory_Db();

        self::$db = $factory_db->create();
        
        parent::setUpBeforeClass();
    }
}
