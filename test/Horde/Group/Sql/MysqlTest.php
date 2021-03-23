<?php
/**
 * Prepare the test setup.
 */
namespace Horde\Group\Sql;

/**
 * Copyright 2010-2017 Horde LLC (http://www.horde.org/)
 *
 * @author     Jan Schneider <jan@horde.org>
 * @category   Horde
 * @package    Group
 * @subpackage UnitTests
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 */
class MysqlTest extends Base
{
    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('mysql')) {
            self::$reason = 'No mysql extension';
            return;
        }
        $config = self::getConfig('GROUP_SQL_MYSQL_TEST_CONFIG',
                                  __DIR__ . '/..');
        if ($config && !empty($config['group']['sql']['mysql'])) {
            self::$db = new Horde_Db_Adapter_Mysql($config['group']['sql']['mysql']);
            parent::setUpBeforeClass();
        } else {
            self::$reason = 'No mysql configuration';
        }
    }
}
