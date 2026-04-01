<?php

declare(strict_types=1);

/**
 * Copyright 2011-2026 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 */

namespace Horde\Group;

use PHPUnit\Framework\Attributes\CoversClass;
use Horde_Db_Adapter_Pdo_Sqlite;
use Horde_Group_Sql;
use Horde_Group_Base;

#[CoversClass(Horde_Group_Sql::class)]
#[CoversClass(Horde_Group_Base::class)]
class SqliteTest extends SqlTestBase
{
    public static function setUpBeforeClass(): void
    {
        self::$db = new Horde_Db_Adapter_Pdo_Sqlite([
            'database' => ':memory:',
            'charset' => 'utf-8',
        ]);

        parent::setUpBeforeClass();
    }
}
