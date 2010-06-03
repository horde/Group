<?php
/**
 * Extension of the Horde_Group_DataTreeObject class for storing group
 * information in an LDAP directory.
 *
 * Copyright 2005-2010 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author   Ben Chavet <ben@horde.org>
 * @category Horde
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @package  Group
 */
class Horde_Group_LdapObject extends Horde_Group_DataTreeObject
{
    /**
     * Constructor.
     *
     * @param string $name    The name of this group.
     * @param string $parent  The dn of the parent of this group.
     */
    public function __construct($name, $parent = null)
    {
        parent::__construct($name);
        if ($parent) {
            $this->data['dn'] = Horde_String::lower($GLOBALS['conf']['group']['params']['gid']) . '=' . $name . ',' . $parent;
        } else {
            $this->data['dn'] = Horde_String::lower($GLOBALS['conf']['group']['params']['gid']) . '=' . $name .
                ',' . Horde_String::lower($GLOBALS['conf']['group']['params']['basedn']);
        }
    }

    /**
     * Get a list of every user that is part of this group (and only
     * this group).
     *
     * @return array  The user list.
     */
    public function listUsers()
    {
        return $this->_groupOb->listUsers($this->data['dn']);
    }

    /**
     * Get a list of every user that is a member of this group and any of
     * it's subgroups.
     *
     * @return array  The complete user list.
     */
    public function listAllUsers()
    {
        return $this->_groupOb->listAllUsers($this->data['dn']);
    }

    /**
     * Take in a list of attributes from the backend and map it to our
     * internal data array.
     *
     * @param array $attributes  The list of attributes from the backend.
     */
    protected function _fromAttributes($attributes = array())
    {
        $this->data['users'] = array();
        foreach ($attributes as $key => $value) {
            if (Horde_String::lower($key) == Horde_String::lower($GLOBALS['conf']['group']['params']['memberuid'])) {
                if (is_array($value)) {
                    foreach ($value as $user) {
                        if ($GLOBALS['conf']['group']['params']['attrisdn']) {
                            $pattern = '/^' . $GLOBALS['conf']['auth']['params']['uid'] . '=([^,]+).*$/';
                            $results = array();
                            preg_match($pattern, $user, $results);
                            if (isset($results[1])) {
                                $user = $results[1];
                            }
                        }
                        $this->data['users'][$user] = '1';
                    }
                } else {
                    if ($GLOBALS['conf']['group']['params']['attrisdn']) {
                        $pattern = '/^' . $GLOBALS['conf']['auth']['params']['uid'] . '=([^,]+).*$/';
                        $results = array();
                        preg_match($pattern, $value, $results);
                        if (isset($results[1])) {
                            $value = $results[1];
                        }
                    }
                    $this->data['users'][$value] = '1';
                }
            } elseif ($key == 'mail') {
                $this->data['email'] = $value;
            } else {
                $this->data[$key] = $value;
            }
        }
    }

    /**
     * Map this object's attributes from the data array into a format that
     * can be stored in an LDAP entry.
     *
     * @return array  The entry array.
     */
    protected function _toAttributes()
    {
        $attributes = array();
        foreach ($this->data as $key => $value) {
            if ($key == 'users') {
                foreach ($value as $user => $membership) {
                    if ($GLOBALS['conf']['group']['params']['attrisdn']) {
                        $user = $GLOBALS['conf']['auth']['params']['uid'] .
                            '=' . $user . ',' . $GLOBALS['conf']['auth']['params']['basedn'];
                    }
                    $attributes[Horde_String::lower($GLOBALS['conf']['group']['params']['memberuid'])][] = $user;
                }
            } elseif ($key == 'email') {
                if (!empty($value)) {
                    $attributes['mail'] = $value;
                }
            } elseif ($key != 'dn' && $key != Horde_String::lower($GLOBALS['conf']['group']['params']['memberuid'])) {
                $attributes[$key] = !empty($value) ? $value : ' ';
            }
        }

        return $attributes;
    }

}