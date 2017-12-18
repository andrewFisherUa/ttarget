<?php

class m140112_224822_alter_clickcost_table extends CDbMigration
{
	public function safeUp()
	{
        $this->renameTable('clickcost', 'platforms_cpc');
        $this->dropForeignKey('clickcost_platform', 'platforms_cpc');
        $this->dropIndex('clickcost_platform', 'platforms_cpc');
        $this->execute('ALTER TABLE `platforms_cpc` DROP PRIMARY KEY;');

        $this->execute("ALTER TABLE `platforms_cpc` ADD COLUMN `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
        $this->renameColumn('platforms_cpc', 'cost_date', 'date');
        $this->createIndex('p_cpc_date_platform_id_unique', 'platforms_cpc', 'date, platform_id', true);

        $this->execute("
            ALTER TABLE `platforms_cpc`
	        ADD CONSTRAINT `p_cpc_platform_fk`
	        FOREIGN KEY (`platform_id`)
	        REFERENCES `platforms` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
        ");

        $this->dropForeignKey('platform_currency', 'platforms');
        $this->dropIndex('platform_currency', 'platforms');
        $this->dropColumn('platforms', 'currency_id');
        $this->execute("ALTER TABLE `platforms` ADD COLUMN `currency` CHAR(3) AFTER tag_id");
        $this->update('platforms', array('currency' => 'RUB'));
        $this->execute("ALTER TABLE `platforms` MODIFY currency CHAR(3) NOT NULL;");

        $this->dropTable('currency');
    }

	public function safeDown()
	{
        $this->renameTable('platforms_cpc', 'clickcost');
        $this->dropIndex('p_cpc_date_platform_id_unique', 'clickcost');
        $this->dropColumn('clickcost', 'id');
        $this->renameColumn('clickcost', 'date', 'cost_date');

        $this->dropForeignKey('p_cpc_platform_fk', 'clickcost');
        $this->execute("
            ALTER TABLE `clickcost`
	        ADD CONSTRAINT `clickcost_platform`
	        FOREIGN KEY (`platform_id`)
	        REFERENCES `platforms` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
        ");

        $this->addPrimaryKey('clickcost_pk', 'clickcost', 'cost_date, platform_id');

        $this->execute("
            CREATE TABLE `currency` (
                `id` INT(10) UNSIGNED NOT NULL,
                `name` VARCHAR(45) NOT NULL,
                PRIMARY KEY (`id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB
            ROW_FORMAT=DEFAULT
        ");

        $this->insert('currency', array('id' => 1, 'name' => 'руб'));
        $this->insert('currency', array('id' => 2, 'name' => '$'));

        $this->dropColumn('platforms', 'currency');
        $this->execute("ALTER TABLE `platforms` ADD COLUMN `currency_id` INT(10) UNSIGNED NOT NULL DEFAULT '1' AFTER tag_id");
        $this->execute("
            ALTER TABLE `platforms`
	        ADD CONSTRAINT `platform_currency`
	        FOREIGN KEY (`currency_id`)
	        REFERENCES `currency` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION;
        ");







	}
}