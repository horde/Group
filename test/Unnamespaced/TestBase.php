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

use PHPUnit\Framework\TestCase;
use Horde_Support_Backtrace;

/**
 * @coversNothing
 */
class TestBase extends TestCase
{
    protected static $group;

    protected static array $groupids = [];

    protected function _create(): void
    {
        self::$groupids[] = self::$group->create('My Group', 'me@example.com');
        $this->assertNotNull(self::$groupids[0]);
        self::$groupids[] = self::$group->create('My Other Group');
        self::$groupids[] = self::$group->create('Not My Group');
    }

    protected function _exists($nonexistant): void
    {
        $this->assertTrue(self::$group->exists(self::$groupids[0]));
        $this->assertFalse(self::$group->exists($nonexistant));
    }

    protected function _getName(): void
    {
        $this->assertEquals(
            'My Group',
            self::$group->getName(self::$groupids[0])
        );
        $this->assertEquals(
            'My Other Group',
            self::$group->getName(self::$groupids[1])
        );
        $this->assertEquals(
            'Not My Group',
            self::$group->getName(self::$groupids[2])
        );
    }

    protected function _getData(): void
    {
        $group = self::$group->getData(self::$groupids[0]);
        $this->assertEquals('My Group', $group['name']);
        $this->assertEquals('me@example.com', $group['email']);
    }

    protected function _listAll(): void
    {
        $groups = self::$group->listAll();
        $this->assertEquals(3, count($groups));
        $this->assertEquals('My Group', $groups[self::$groupids[0]]);
        $this->assertEquals('My Other Group', $groups[self::$groupids[1]]);
        $this->assertEquals('Not My Group', $groups[self::$groupids[2]]);
    }

    protected function _search(): void
    {
        $groups = self::$group->search('My Group');
        $this->assertEquals(2, count($groups));
        $this->assertEquals('My Group', $groups[self::$groupids[0]]);
        $this->assertEquals('Not My Group', $groups[self::$groupids[2]]);
    }

    protected function _addUser(): void
    {
        $this->assertNull(self::$group->addUser(self::$groupids[0], 'joe'));
        self::$group->addUser(self::$groupids[1], 'joe');
        self::$group->addUser(self::$groupids[1], 'jane');
    }

    protected function _listUsers(): void
    {
        $users = self::$group->listUsers(self::$groupids[0]);
        $this->assertEquals(1, count($users));
        $this->assertTrue(in_array('joe', $users));
        $users = self::$group->listUsers(self::$groupids[1]);
        $this->assertEquals(2, count($users));
        $this->assertTrue(in_array('joe', $users));
        $this->assertTrue(in_array('jane', $users));
    }

    protected function _listGroups(): void
    {
        $groups = self::$group->listGroups('joe');
        $this->assertEquals(2, count($groups));
        $this->assertEquals('My Group', $groups[self::$groupids[0]]);
        $this->assertEquals('My Other Group', $groups[self::$groupids[1]]);
        $groups = self::$group->listGroups('jane');
        $this->assertEquals(1, count($groups));
        $this->assertEquals('My Other Group', $groups[self::$groupids[1]]);
    }

    protected function _listAllWithMember(): void
    {
        $groups = self::$group->listAll('joe');
        $this->assertEquals(2, count($groups));
        $this->assertEquals('My Group', $groups[self::$groupids[0]]);
        $this->assertEquals('My Other Group', $groups[self::$groupids[1]]);
    }

    protected function _removeUser(): void
    {
        $this->assertNull(self::$group->removeUser(self::$groupids[1], 'joe'));
        $groups = self::$group->listGroups('joe');
        $this->assertEquals(1, count($groups));
        $this->assertEquals('My Group', $groups[self::$groupids[0]]);
        $this->assertNull(self::$group->removeUser(self::$groupids[1], 'jane'));
        $groups = self::$group->listGroups('jane');
        $this->assertEquals(0, count($groups));
    }

    protected function _rename(): void
    {
        self::$group->rename(self::$groupids[1], 'My Second Group');
        $this->assertEquals(
            'My Second Group',
            self::$group->getName(self::$groupids[1])
        );
    }

    protected function _setData(): void
    {
        self::$group->setData(self::$groupids[0], 'email', 'you@example.com');
        $group = self::$group->getData(self::$groupids[0]);
        $this->assertEquals('you@example.com', $group['email']);
        self::$group->setData(self::$groupids[0], ['email' => 'me@example.com']);
        $group = self::$group->getData(self::$groupids[0]);
        $this->assertEquals('me@example.com', $group['email']);
    }

    protected function _remove(): void
    {
        self::$group->remove(self::$groupids[0]);
        $this->assertFalse(self::$group->exists(self::$groupids[0]));
    }

    public static function tearDownAfterClass(): void
    {
        self::$group = null;
        self::$groupids = [];
    }

    /**
     * Helper method for loading test configuration from a file.
     *
     * The configuration can be specified by an environment variable. If the
     * variable content is a file name, the configuration is loaded from the
     * file. Otherwise it's assumed to be a json encoded configuration hash. If
     * the environment variable is not set, the method tries to load a conf.php
     * file from the same directory as the test case.
     *
     * @param string $env     An environment variable name.
     * @param string $path    The path to use.
     * @param array $default  Some default values that are merged into the
     *                        configuration if specified as a json hash.
     *
     * @return mixed  The value of the configuration file's $conf variable, or
     *                null.
     */
    public static function getConfig(string $env, ?string $path = null, array $default = []): ?array
    {
        $config = getenv($env);
        if ($config) {
            $json = json_decode($config, true);
            if ($json) {
                return array_replace_recursive($default, $json);
            }
        } else {
            if (!$path) {
                $backtrace = new Horde_Support_Backtrace();
                $caller = $backtrace->getCurrentContext();
                $path = dirname($caller['file']);
            }
            $config = $path . '/conf.php';
        }

        if (file_exists($config)) {
            require $config;
            return $conf;
        }

        return null;
    }
}
