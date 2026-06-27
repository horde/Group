<?php

declare(strict_types=1);

/**
 * Copyright 2011-2026 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author     Thomas Jarosch <thomas.jarosch@intra2net.com>
 * @category   Horde
 * @package    Group
 * @subpackage UnitTests
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 */

namespace Horde\Group;

use PHPUnit\Framework\Attributes\{CoversClass, Depends};
use Horde_Util;
use Horde_Group_File;
use Horde_Group_Base;
use Horde\Util\Util;

/**
 * @coversNothing
 */
#[CoversClass(Horde_Group_File::class)]
#[CoversClass(Horde_Group_Base::class)]
class FileTest extends TestBase
{
    /**
     * Group file to write to
     *
     * @var string
     */
    protected static $_groupfile = '';

    public function testExists(): void
    {
        $this->_exists('some_none_existing_id');
    }

    #[Depends('testExists')]
    public function testGetName(): void
    {
        $this->_getName();
    }

    #[Depends('testExists')]
    public function testListAll(): void
    {
        $this->_listAll();
    }

    #[Depends('testExists')]
    public function testSearch(): void
    {
        $this->_search();
    }

    #[Depends('testExists')]
    public function testListUsers(): void
    {
        $this->_listUsers();
    }

    #[Depends('testExists')]
    public function testListGroups(): void
    {
        $this->_listGroups();
    }

    public function testGroupWithUmlaut(): void
    {
        $filename = Util::getTempFile('Horde_Group_FileTest');

        $group_name = 'Group with Umläut';
        $user_name = 'joe';

        $fp = fopen($filename, 'w');
        fwrite($fp, "$group_name:x:1:$user_name\n");
        fclose($fp);

        $params = ['filename' => $filename];
        $group = new Horde_Group_File($params);

        $this->assertTrue($group->exists($group_name));
        $this->assertEquals($group_name, $group->getName($group_name));
        $this->assertEquals([$user_name], $group->listUsers($group_name));
    }

    public function testGidFromFile(): void
    {
        $params = ['filename' => self::$_groupfile, 'use_gid' => true];
        self::$group = new Horde_Group_File($params);
        self::$groupids = [1, 2, 3];

        $this->assertTrue(self::$group->exists(self::$groupids[0]));
        $this->assertTrue(self::$group->exists(self::$groupids[1]));
        $this->assertTrue(self::$group->exists(self::$groupids[2]));
        $this->assertFalse(self::$group->exists(4242424));

        $this->assertEquals('My Other Group', self::$group->getName(self::$groupids[1]));
    }

    public static function setUpBeforeClass(): void
    {
        self::$_groupfile = Util::getTempFile('Horde_Group_FileTest');

        $fp = fopen(self::$_groupfile, 'w');
        fwrite($fp, "My Group:x:1:joe\n");
        fwrite($fp, "My Other Group:x:2:joe,jane\n");
        fwrite($fp, "Not My Group:x:3:jeff,steve\n");
        fclose($fp);

        self::$group = new Horde_Group_File(['filename' => self::$_groupfile]);
        self::$groupids = ['My Group', 'My Other Group', 'Not My Group'];
    }

    public static function tearDownAfterClass(): void
    {
        unlink(self::$_groupfile);
        parent::tearDownAfterClass();
    }
}
