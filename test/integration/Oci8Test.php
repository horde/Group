<?php

declare(strict_types=1);

/**
 * Copyright 2013-2026 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author     Jan Schneider <jan@horde.org>
 * @category   Horde
 * @package    Group
 * @subpackage UnitTests
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 */

namespace Horde\Group;

use PHPUnit\Framework\Attributes\CoversClass;
use Horde_Db_Adapter_Oci8;
use Horde_Group_Sql;
use Horde_Group_Base;

#[CoversClass(Horde_Group_Sql::class)]
#[CoversClass(Horde_Group_Base::class)]
class Oci8Test extends SqlTestBase
{
    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('oci8')) {
            self::$reason = 'No oci8 extension';
            return;
        }
        $config = self::getConfig(
            'GROUP_SQL_OCI8_TEST_CONFIG',
            __DIR__ . '/..'
        );
        if ($config && !empty($config['group']['sql']['oci8'])) {
            self::$db = new Horde_Db_Adapter_Oci8($config['group']['sql']['oci8']);
            //self::$db->setLogger(new Horde_Log_Logger(new Horde_Log_Handler_Cli()));
            parent::setUpBeforeClass();
        } else {
            self::$reason = 'No oci8 configuration';
        }
    }
}
