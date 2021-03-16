<?php
/**
 * Prepare the test setup.
 */
namespace Horde\Group\Sql;
use Horde_Group_Test_Sql_Base as Base;

require_once __DIR__ . '/Base.php';

/**
 * Copyright 2013-2017 Horde LLC (http://www.horde.org/)
 *
 * @author     Jan Schneider <jan@horde.org>
 * @category   Horde
 * @package    Group
 * @subpackage UnitTests
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 */
class Oci8Test extends Base
{
    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('oci8')) {
            self::$reason = 'No oci8 extension';
            return;
        }
        $config = self::getConfig('GROUP_SQL_OCI8_TEST_CONFIG',
                                  __DIR__ . '/..');
        if ($config && !empty($config['group']['sql']['oci8'])) {
            self::$db = new Horde_Db_Adapter_Oci8($config['group']['sql']['oci8']);
            //self::$db->setLogger(new Horde_Log_Logger(new Horde_Log_Handler_Cli()));
            parent::setUpBeforeClass();
        } else {
            self::$reason = 'No oci8 configuration';
        }
    }
}
