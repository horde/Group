<?php

/**
 * Public API for the Horde group system.
 *
 * Copyright 1999-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * This should actually be something like Horde_Group_Driver or Horde_Group_Api but existing type hints bind against
 * a non-existing Horde_Group so we fill that gap and rename when upgrading to a PSR-4 API
 *
 * @author    Jan Schneider <jan@horde.org>
 * @category  Horde
 * @copyright 1999-2026 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Group
 */
interface Horde_Group
{
    /**
     * Returns whether the group backend is read-only.
     *
     * @return boolean
     */
    public function readOnly();

    /**
     * Returns whether groups can be renamed.
     *
     * @return boolean
     */
    public function renameSupported();

    /**
     * Creates a new group.
     *
     * @param string $name   A group name.
     * @param string $email  The group's email address.
     *
     * @return mixed  The ID of the created group.
     * @throws Horde_Group_Exception
     */
    public function create($name, $email = null);

    /**
     * Renames a group.
     *
     * @param mixed $gid    A group ID.
     * @param string $name  The new name.
     *
     * @throws Horde_Group_Exception
     * @throws Horde_Exception_NotFound
     */
    public function rename($gid, $name);

    /**
     * Removes a group.
     *
     * @param mixed $gid  A group ID.
     *
     * @throws Horde_Group_Exception
     */
    public function remove($gid);

    /**
     * Checks if a group exists.
     *
     * @param mixed $gid  A group ID.
     *
     * @return boolean  True if the group exists.
     * @throws Horde_Group_Exception
     */
    public function exists($gid);

    /**
     * Returns a group name.
     *
     * @param mixed $gid  A group ID.
     *
     * @return string  The group's name.
     * @throws Horde_Group_Exception
     * @throws Horde_Exception_NotFound
     */
    public function getName($gid);

    /**
     * Returns all available attributes of a group.
     *
     * @param mixed $gid  A group ID.
     *
     * @return array  The group's data.
     * @throws Horde_Group_Exception
     * @throws Horde_Exception_NotFound
     */
    public function getData($gid);

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
    public function setData($gid, $attribute, $value = null);

    /**
     * Returns a list of all groups a user may see, with IDs as keys and names
     * as values.
     *
     * @param string $member  Only return groups that this user is a member of.
     *
     * @return array  All existing groups.
     * @throws Horde_Group_Exception
     */
    public function listAll($member = null);

    /**
     * Returns a list of users in a group.
     *
     * @param mixed $gid  A group ID.
     *
     * @return array  List of group users.
     * @throws Horde_Group_Exception
     * @throws Horde_Exception_NotFound
     */
    public function listUsers($gid);

    /**
     * Returns a list of groups a user belongs to.
     *
     * @param string $user  A user name.
     *
     * @return array  A list of groups, with IDs as keys and names as values.
     * @throws Horde_Group_Exception
     */
    public function listGroups($user);

    /**
     * Add a user to a group.
     *
     * @param mixed $gid    A group ID.
     * @param string $user  A user name.
     *
     * @throws Horde_Group_Exception
     * @throws Horde_Exception_NotFound
     */
    public function addUser($gid, $user);

    /**
     * Removes a user from a group.
     *
     * @param mixed $gid    A group ID.
     * @param string $user  A user name.
     *
     * @throws Horde_Group_Exception
     * @throws Horde_Exception_NotFound
     */
    public function removeUser($gid, $user);

    /**
     * Searches for group names.
     *
     * @param string $name  A search string.
     *
     * @return array  A list of matching groups, with IDs as keys and names as
     *                values.
     * @throws Horde_Group_Exception
     */
    public function search($name);
}
