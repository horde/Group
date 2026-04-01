<?php

declare(strict_types=1);

/**
 * Copyright 2011-2026 The Horde Project (http://www.horde.org/)
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

use PHPUnit\Framework\Attributes\Depends;
use Horde_Log_Logger;
use Horde_Log_Handler_Cli;
use Horde_Db_Migration_Migrator;
use Horde_Group_Sql;

class SqlTestBase extends TestBase
{
    protected static $db;

    protected static $migrator;

    protected static $reason;

    public function testCreate(): void
    {
        $this->_create();
    }

    #[Depends('testCreate')]
    public function testExists(): void
    {
        $this->_exists(99999);
    }

    /**
     * @depends testExists
     */
    public function testGetName(): void
    {
        $this->_getName();
    }

    /**
     * @depends testExists
     */
    public function testGetData(): void
    {
        $this->_getData();
    }

    /**
     * @depends testExists
     */
    public function testListAll(): void
    {
        $this->_listAll();
    }

    /**
     * @depends testExists
     */
    public function testSearch(): void
    {
        $this->_search();
    }

    /**
     * @depends testExists
     */
    public function testAddUser(): void
    {
        $this->_addUser();
    }

    /**
     * @depends testAddUser
     */
    public function testListUsers(): void
    {
        $this->_listUsers();
    }

    /**
     * @depends testAddUser
     */
    public function testListGroups(): void
    {
        $this->_listGroups();
    }

    /**
     * @depends testAddUser
     */
    public function testListAllWithMember(): void
    {
        $this->_listAllWithMember();
    }

    /**
     * @depends testListGroups
     */
    public function testRemoveUser(): void
    {
        $this->_removeUser();
    }

    /**
     * @depends testExists
     */
    public function testRename(): void
    {
        $this->_rename();
    }

    /**
     * @depends testExists
     */
    public function testSetData(): void
    {
        $this->_setData();
    }

    /**
     * @depends testExists
     */
    public function testRemove(): void
    {
        $this->_remove();
    }

    public static function setUpBeforeClass(): void
    {
        $logger = new Horde_Log_Logger(new Horde_Log_Handler_Cli());
        //self::$db->setLogger($logger);
        $dir = __DIR__ . '/../../migration/Horde/Group';
        if (!is_dir($dir)) {
            error_reporting(E_ALL & ~E_DEPRECATED);
            $dir = PEAR_Config::singleton()
                ->get('data_dir', null, 'pear.horde.org')
                . '/Horde_Group/migration';
            error_reporting(E_ALL);
        }
        self::$migrator = new Horde_Db_Migration_Migrator(
            self::$db,
            null,//$logger,
            ['migrationsPath' => $dir,
                'schemaTableName' => 'horde_groups_schema_info']
        );
        self::$migrator->up();

        self::$group = new Horde_Group_Sql(['db' => self::$db]);
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$migrator) {
            if (self::$db) {
                self::$db->delete('DELETE FROM horde_groups');
                self::$db->delete('DELETE FROM horde_groups_members');
            }
            self::$migrator->down();
        }
        if (self::$db) {
            self::$db->disconnect();
        }
        self::$db = self::$migrator = null;
        parent::tearDownAfterClass();
    }

    public function setUp(): void
    {
        if (!self::$db) {
            $this->markTestSkipped(self::$reason);
        }
    }
}
