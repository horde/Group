<?php
class HordeGroupUpgradeAutoIncrement extends Horde_Db_Migration_Base
{
    /**
     * Upgrade.
     */
    public function up()
    {
        $tableList = $this->tables();
        $this->changeColumn('horde_groups', 'group_uid', 'autoincrementKey');
        // Support removing column on legacy, pre-h5 schemas
        if (in_array('horde_groups_seq', $tableList)) {
            $this->dropTable('horde_groups_seq');
        }
    }

    /**
     * Downgrade
     */
    public function down()
    {
        $this->changeColumn('horde_groups', 'group_uid', 'integer', array('null' => false, 'unsigned' => true));
    }

}