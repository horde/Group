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

use PHPUnit\Framework\Attributes\{CoversClass, Depends};
use Horde_Group_Ldap;
use Horde_Group_Base;
use Horde_Ldap;
use Horde_Ldap_Exception;

/**
 * @coversNothing
 */
#[CoversClass(Horde_Group_Ldap::class)]
#[CoversClass(Horde_Group_Base::class)]
class LdapTest extends TestBase
{
    protected static $ldap;

    protected static $reason;

    public function testCreate(): void
    {
        $this->_create();
    }

    #[Depends('testCreate')]
    public function testExists(): void
    {
        $this->_exists('cn=some_none_existing_id');
    }

    #[Depends('testExists')]
    public function testGetName(): void
    {
        $this->_getName();
    }

    #[Depends('testExists')]
    public function testGetData(): void
    {
        $this->_getData();
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
    public function testAddUser(): void
    {
        $this->_addUser();
    }

    #[Depends('testAddUser')]
    public function testListUsers(): void
    {
        $this->_listUsers();
    }

    #[Depends('testAddUser')]
    public function testListGroups(): void
    {
        $this->_listGroups();
    }

    #[Depends('testAddUser')]
    public function testListAllWithMember(): void
    {
        $this->_listAllWithMember();
    }

    #[Depends('testListGroups')]
    public function testRemoveUser(): void
    {
        $this->_removeUser();
    }

    #[Depends('testExists')]
    public function testSetData(): void
    {
        $this->_setData();
    }

    #[Depends('testExists')]
    public function testRemove(): void
    {
        $this->_remove();
    }

    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('ldap')) {
            self::$reason = 'No ldap extension';
            return;
        }
        $config = self::getConfig('GROUP_LDAP_TEST_CONFIG');
        if ($config && !empty($config['group']['ldap'])) {
            self::$ldap = new Horde_Ldap($config['group']['ldap']);
            $config['group']['ldap']['ldap'] = self::$ldap;
            self::$group = new Horde_Group_Ldap($config['group']['ldap']);
        } else {
            self::$reason = 'No ldap configuration';
        }
    }

    public static function tearDownAfterClass(): void
    {
        $config = self::getConfig('GROUP_LDAP_TEST_CONFIG');
        if (self::$ldap) {
            $possibleids = ['My Group', 'My Other Group', 'My Second Group', 'Not My Group'];
            self::$ldap->bind(
                $config['group']['ldap']['writedn'],
                $config['group']['ldap']['writepw']
            );
            foreach ($possibleids as $id) {
                try {
                    self::$ldap->delete('cn=' . $id . ',' . $config['group']['ldap']['basedn']);
                } catch (Horde_Ldap_Exception $e) {
                }
            }
            self::$ldap = null;
        }
        parent::tearDownAfterClass();
    }

    public function setUp(): void
    {
        if (!self::$ldap) {
            $this->markTestSkipped(self::$reason);
        }
    }
}
