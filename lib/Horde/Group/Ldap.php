<?php

/**
 * Copyright 2005-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author   Ben Chavet <ben@horde.org>
 * @author   Jan Schneider <jan@horde.org>
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Group
 */

/**
 * This class provides an LDAP driver for the Horde group system.
 *
 * @author    Ben Chavet <ben@horde.org>
 * @author    Jan Schneider <jan@horde.org>
 * @category  Horde
 * @copyright 2005-2017 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Group
 */
class Horde_Group_Ldap extends Horde_Group_Base
{
    /**
     * Handle for the current LDAP connection.
     *
     * @var Horde_Ldap
     */
    protected $_ldap;

    /**
     * Any additional parameters for the driver.
     *
     * @var array
     */
    protected $_params;

    /**
     * LDAP filter for searching groups.
     *
     * @var Horde_Ldap_Filter
     */
    protected $_filter;

    /**
     * Constructor.
     *
     * @throws Horde_Group_Exception
     */
    public function __construct($params)
    {
        parent::__construct($params);

        $params = array_merge(
            ['binddn'               => '',
                'bindpw'               => '',
                'gid'                  => 'cn',
                'memberuid'            => 'memberUid',
                'objectclass'          => ['posixGroup'],
                'newgroup_objectclass' => ['posixGroup']],
            $params
        );

        /* Check mandatory parameters. */
        foreach (['ldap', 'basedn'] as $param) {
            if (!isset($params[$param])) {
                throw new Horde_Group_Exception('The \'' . $param . '\' parameter is missing.');
            }
        }

        /* Set Horde_Ldap object. */
        $this->_ldap = $params['ldap'];
        unset($params['ldap']);

        /* Lowercase attribute names. */
        $params['gid']       = Horde_String::lower($params['gid']);
        $params['memberuid'] = Horde_String::lower($params['memberuid']);
        if (!is_array($params['newgroup_objectclass'])) {
            $params['newgroup_objectclass'] = [$params['newgroup_objectclass']];
        }
        foreach ($params['newgroup_objectclass'] as &$objectClass) {
            $objectClass = Horde_String::lower($objectClass);
        }

        /* Generate LDAP search filter. */
        try {
            $this->_filter = Horde_Ldap_Filter::build($params['search']);
        } catch (Horde_Ldap_Exception $e) {
            throw new Horde_Group_Exception($e);
        }

        $this->_params = $params;
    }

    /**
     * Returns whether the group backend is read-only.
     *
     * @return boolean
     */
    public function readOnly()
    {
        return !isset($this->_params['writedn'])
               || !isset($this->_params['writepw']);
    }

    /**
     * Returns whether groups can be renamed.
     *
     * @return boolean
     */
    public function renameSupported()
    {
        return false;
    }

    /**
     * Creates a new group.
     *
     * @param string $name   A group name.
     * @param string $email  The group's email address.
     *
     * @return mixed  The ID of the created group.
     * @throws Horde_Group_Exception
     */
    protected function _create($name, $email = null)
    {
        if ($this->readOnly()) {
            throw new Horde_Group_Exception('This group backend is read-only.');
        }

        $attributes = [
            $this->_params['gid'] => $name,
            'objectclass'         => $this->_params['newgroup_objectclass'],
            'gidnumber'           => $this->_nextGid(),
        ];
        if (!empty($email)) {
            $attributes['mail'] = $email;
        }

        $dn = Horde_Ldap::quoteDN([[$this->_params['gid'], $name]])
            . ',' . $this->_params['basedn'];
        try {
            $entry = Horde_Ldap_Entry::createFresh($dn, $attributes);
            $this->_rebind(true);
            $this->_ldap->add($entry);
            $this->_rebind(false);
        } catch (Horde_Ldap_Exception $e) {
            throw new Horde_Group_Exception($e);
        }

        return $dn;
    }

    /**
     * Renames a group.
     *
     * @param mixed $gid    A group ID.
     * @param string $name  The new name.
     *
     * @throws Horde_Group_Exception
     */
    protected function _rename($gid, $name)
    {
        throw new Horde_Group_Exception('Renaming groups is not supported with the LDAP driver.');
    }

    /**
     * Removes a group.
     *
     * @param mixed $gid  A group ID.
     *
     * @throws Horde_Group_Exception
     */
    protected function _remove($gid)
    {
        if ($this->readOnly()) {
            throw new Horde_Group_Exception('This group backend is read-only.');
        }

        try {
            $this->_rebind(true);
            $this->_ldap->delete($gid);
            $this->_rebind(false);
        } catch (Horde_Ldap_Exception $e) {
            throw new Horde_Group_Exception($e);
        }
    }

    /**
     * Checks if a group exists.
     *
     * @param mixed $gid  A group ID.
     *
     * @return boolean  True if the group exists.
     * @throws Horde_Group_Exception
     */
    protected function _exists($gid)
    {
        try {
            return $this->_ldap->exists($gid);
        } catch (Horde_Ldap_Exception $e) {
            throw new Horde_Group_Exception($e);
        }
    }

    /**
     * Returns a group name.
     *
     * @param mixed $gid  A group ID.
     *
     * @return string  The group's name.
     * @throws Horde_Group_Exception
     * @throws Horde_Exception_NotFound
     */
    protected function _getName($gid)
    {
        try {
            $entry = $this->_ldap->getEntry($gid);
            return $entry->getValue($this->_params['gid'], 'single');
        } catch (Horde_Ldap_Exception $e) {
            throw new Horde_Group_Exception($e);
        }
    }

    /**
     * Returns all available attributes of a group.
     *
     * @param mixed $gid  A group ID.
     *
     * @return array  The group's date.
     * @throws Horde_Group_Exception
     * @throws Horde_Exception_NotFound
     */
    protected function _getData($gid)
    {
        try {
            $entry = $this->_ldap->getEntry($gid);
            $attributes = $entry->getValues();
        } catch (Horde_Ldap_Exception $e) {
            throw new Horde_Group_Exception($e);
        }
        $data = [];
        foreach ($attributes as $attribute => $value) {
            switch ($attribute) {
                case $this->_params['gid']:
                    $attribute = 'name';
                    break;
                case 'mail':
                    $attribute = 'email';
                    break;
            }
            $data[$attribute] = $value;
        }
        return $data;
    }

    /**
     * Sets one or more attributes of a group.
     *
     * @param mixed $gid               A group ID.
     * @param array|string $attribute  An attribute name or a hash of
     *                                 attributes.
     * @param string $value            An attribute value if $attribute is a
     *                                 string.
     *
     * @throws Horde_Group_Exception
     * @throws Horde_Exception_NotFound
     */
    protected function _setData($gid, $attribute, $value = null)
    {
        if ($this->readOnly()) {
            throw new Horde_Group_Exception('This group backend is read-only.');
        }

        $attributes = is_array($attribute)
            ? $attribute
            : [$attribute => $value];
        try {
            $entry = $this->_ldap->getEntry($gid);
            foreach ($attributes as $attribute => $value) {
                switch ($attribute) {
                    case 'name':
                        $attribute = $this->_params['gid'];
                        break;
                    case 'email':
                        $attribute = 'mail';
                        break;
                }
                $entry->replace([$attribute => $value]);
            }
            $this->_rebind(true);
            $entry->update();
            $this->_rebind(false);
        } catch (Horde_Ldap_Exception $e) {
            throw new Horde_Group_Exception($e);
        }
    }

    /**
     * Returns a list of all groups a user may see, with IDs as keys and names
     * as values.
     *
     * @return array  All existing groups.
     * @throws Horde_Group_Exception
     */
    protected function _listAll()
    {
        $attr = $this->_params['gid'];
        try {
            $search = $this->_ldap->search(
                $this->_params['basedn'],
                $this->_filter,
                [$attr]
            );
        } catch (Horde_Ldap_Exception $e) {
            throw new Horde_Group_Exception($e);
        }

        $entries = [];
        foreach ($search->sortedAsArray([$attr]) as $entry) {
            if (isset($entry[$attr][0])) {
                $entries[$entry['dn']] = $entry[$attr][0];
            }
        }
        return $entries;
    }

    /**
     * Returns a list of users in a group.
     *
     * @param mixed $gid  A group ID.
     *
     * @return array  List of group users.
     * @throws Horde_Group_Exception
     * @throws Horde_Exception_NotFound
     */
    protected function _listUsers($gid)
    {
        /**
         *  If the group driver is LDAP, we must ignore groups with numeric IDs
         *
         * One of the possible root causes is the sharesng driver of any app.
         * It can contain numeric group IDs when migrating from the SQL group
         * backend to the ldap group backend with existing user data.
         */
        if (is_numeric($gid)) {
            return [];
        }
        $attr = $this->_params['memberuid'];
        try {
            $entry = $this->_ldap->getEntry($gid, [$attr]);
            if (!$entry->exists($attr)) {
                return [];
            }

            if (empty($this->_params['attrisdn'])) {
                return $entry->getValue($attr, 'all');
            }

            $users = [];
            foreach ($entry->getValue($attr, 'all') as $user) {
                $dn = Horde_Ldap_Util::explodeDN(
                    $user,
                    ['onlyvalues' => true]
                );
                // Very simplified approach: assume the first element of the DN
                // contains the user ID.
                $user = $dn[0];
                // Check for multi-value RDNs.
                if (is_array($user)) {
                    $user = $user[0];
                }
                $users[] = $user;
            }
            return $users;
        } catch (Horde_Exception_NotFound $e) {
            return [];
        } catch (Horde_Ldap_Exception $e) {
            throw new Horde_Group_Exception($e);
        }
    }

    /**
     * Returns a list of groups a user belongs to.
     *
     * @param string $user  A user name.
     *
     * @return array  A list of groups, with IDs as keys and names as values.
     * @throws Horde_Group_Exception
     */
    protected function _listGroups($user)
    {
        $attr = $this->_params['gid'];
        try {
            if (!empty($this->_params['attrisdn'])) {
                $user =  $this->_ldap->findUserDN($user);
            }
            $filter = Horde_Ldap_Filter::create(
                $this->_params['memberuid'],
                'equals',
                $user
            );
            $filter = Horde_Ldap_Filter::combine('and', [$this->_filter, $filter]);
            $search = $this->_ldap->search(
                $this->_params['basedn'],
                $filter,
                [$attr]
            );
        } catch (Horde_Ldap_Exception $e) {
            throw new Horde_Group_Exception($e);
        }
        $entries = [];
        foreach ($search->sortedAsArray([$attr]) as $entry) {
            if (isset($entry[$attr][0])) {
                $entries[$entry['dn']] = $entry[$attr][0];
            }
        }
        return $entries;
    }

    /**
     * Add a user to a group.
     *
     * @param mixed $gid    A group ID.
     * @param string $user  A user name.
     *
     * @throws Horde_Group_Exception
     * @throws Horde_Exception_NotFound
     */
    protected function _addUser($gid, $user)
    {
        if ($this->readOnly()) {
            throw new Horde_Group_Exception('This group backend is read-only.');
        }

        $attr = $this->_params['memberuid'];
        try {
            if (!empty($this->_params['attrisdn'])) {
                $user =  $this->_ldap->findUserDN($user);
            }
            $entry = $this->_ldap->getEntry($gid, [$attr]);
            $entry->add([$attr => $user]);
            $this->_rebind(true);
            $entry->update();
            $this->_rebind(false);
        } catch (Horde_Ldap_Exception $e) {
            throw new Horde_Group_Exception($e);
        }
    }

    /**
     * Removes a user from a group.
     *
     * @param mixed $gid    A group ID.
     * @param string $user  A user name.
     *
     * @throws Horde_Group_Exception
     * @throws Horde_Exception_NotFound
     */
    protected function _removeUser($gid, $user)
    {
        if ($this->readOnly()) {
            throw new Horde_Group_Exception('This group backend is read-only.');
        }

        $attr = $this->_params['memberuid'];
        try {
            if (!empty($this->_params['attrisdn'])) {
                $user =  $this->_ldap->findUserDN($user);
            }
            $entry = $this->_ldap->getEntry($gid, [$attr]);
            $entry->delete([$attr => $user]);
            $this->_rebind(true);
            $entry->update();
            $this->_rebind(false);
        } catch (Horde_Ldap_Exception $e) {
            throw new Horde_Group_Exception($e);
        }
    }

    /**
     * Searches for group names.
     *
     * @param string $name  A search string.
     *
     * @return array  A list of matching groups, with IDs as keys and names as
     *                values.
     * @throws Horde_Group_Exception
     */
    protected function _search($name)
    {
        $attr = $this->_params['gid'];
        try {
            $result = $this->_ldap->search(
                $this->_params['basedn'],
                Horde_Ldap_Filter::create($attr, 'contains', $name),
                [$attr]
            );
        } catch (Horde_Ldap_Exception $e) {
            throw new Horde_Group_Exception($e);
        }
        $entries = [];
        foreach ($result->sortedAsArray([$attr]) as $entry) {
            if (isset($entry[$attr][0])) {
                $entries[$entry['dn']] = $entry[$attr][0];
            }
        }
        return $entries;
    }

    /**
     * Searches existing groups for the highest gidnumber, and returns one
     * higher.
     *
     * @return integer  The next group ID.
     *
     * @throws Horde_Group_Exception
     */
    protected function _nextGid()
    {
        try {
            $search = $this->_ldap->search(
                $this->_params['basedn'],
                $this->_filter,
                ['attributes' => ['gidnumber']]
            );
        } catch (Horde_Ldap_Exception $e) {
            throw new Horde_Group_Exception($e);
        }

        if (!$search->count()) {
            return 1;
        }

        $nextgid = 0;
        foreach ($search as $entry) {
            $nextgid = max($nextgid, $entry->getValue('gidnumber', 'single'));
        }

        return $nextgid + 1;
    }

    /**
     * Rebinds to the LDAP server.
     *
     * @param boolean $write  Whether to rebind for write access. Use false
     *                        after finishing write actions.
     *
     * @throws Horde_Ldap_Exception
     */
    protected function _rebind($write)
    {
        if ($write) {
            $this->_ldap->bind($this->_params['writedn'], $this->_params['writepw']);
        } else {
            $this->_ldap->bind();
        }
    }
}
