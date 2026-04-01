<?php

class HordeGroupBaseTables extends Horde_Db_Migration_Base
{
    public function up()
    {
        if (!in_array('horde_groups', $this->tables())) {
            $t = $this->createTable('horde_groups', ['autoincrementKey' => ['group_uid']]);
            $t->column('group_uid', 'integer', ['null' => false, 'unsigned' => true]);
            $t->column('group_name', 'string', ['limit' => 255, 'null' => false]);
            $t->column('group_parents', 'string', ['limit' => 255, 'null' => false]);
            $t->column('group_email', 'string', ['limit' => 255]);
            $t->end();
            $this->addIndex('horde_groups', ['group_name'], ['unique' => true]);
        }
        if (!in_array('horde_groups_members', $this->tables())) {
            $t = $this->createTable('horde_groups_members', ['autoincrementKey' => false]);
            $t->column('group_uid', 'integer', ['null' => false, 'unsigned' => true]);
            $t->column('user_uid', 'string', ['limit' => 255, 'null' => false]);
            $t->end();
            $this->addIndex('horde_groups_members', ['group_uid']);
            $this->addIndex('horde_groups_members', ['user_uid']);
        }
    }

    public function down()
    {
        $this->dropTable('horde_groups');
        $this->dropTable('horde_groups_members');
    }
}
