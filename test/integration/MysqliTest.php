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
use Horde_Db_Adapter_Mysqli;
use Horde_Group_Sql;
use Horde_Group_Base;

#[CoversClass(Horde_Group_Sql::class)]
#[CoversClass(Horde_Group_Base::class)]
class MysqliTest extends SqlTestBase
{
    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('mysqli')) {
            self::$reason = 'No mysqli extension';
            return;
        }
        $config = self::getConfig(
            'GROUP_SQL_MYSQLI_TEST_CONFIG',
            __DIR__ . '/..'
        );
        if ($config && !empty($config['group']['sql']['mysqli'])) {
            self::$db = new Horde_Db_Adapter_Mysqli($config['group']['sql']['mysqli']);
            parent::setUpBeforeClass();
        } else {
            self::$reason = 'No mysqli configuration';
        }
    }
}
