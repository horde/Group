<?php

declare(strict_types=1);

/**
 * Copyright 2010-2026 The Horde Project (http://www.horde.org/)
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
use Horde_Db_Adapter_Pdo_Pgsql;
use Horde_Group_Sql;
use Horde_Group_Base;
use PDO;

#[CoversClass(Horde_Group_Sql::class)]
#[CoversClass(Horde_Group_Base::class)]
class PgsqlTest extends SqlTestBase
{
    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('pdo')
            || !in_array('pgsql', PDO::getAvailableDrivers())) {
            self::$reason = 'No pdo extension or no pgsql PDO driver';
            return;
        }
        $config = self::getConfig(
            'GROUP_SQL_PDO_PGSQL_TEST_CONFIG',
            __DIR__ . '/..'
        );
        if ($config && !empty($config['group']['sql']['pdo_pgsql'])) {
            self::$db = new Horde_Db_Adapter_Pdo_Pgsql($config['group']['sql']['pdo_pgsql']);
            parent::setUpBeforeClass();
        } else {
            self::$reason = 'No pdo_pgsql configuration';
        }
    }
}
