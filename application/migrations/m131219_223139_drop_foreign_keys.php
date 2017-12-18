<?php

class m131219_223139_drop_foreign_keys extends CDbMigration
{
	public function safeUp()
	{
        $this->dropForeignKey('tizer_news', 'teasers');
        $this->dropForeignKey('news_company', 'news');
        $this->dropForeignKey('camaign_client', 'campaigns');
	}

	public function safeDown()
	{
        $this->execute("
            ALTER TABLE `campaigns`
	        ADD CONSTRAINT `camaign_client`
	        FOREIGN KEY (`client_id`)
	        REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
        ");

        $this->execute("
            ALTER TABLE `news`
	        ADD CONSTRAINT `news_company`
	        FOREIGN KEY (`campaign_id`)
	        REFERENCES `campaigns` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
        ");

        $this->execute("
            ALTER TABLE `teasers`
	        ADD CONSTRAINT `tizer_news`
	        FOREIGN KEY (`news_id`)
	        REFERENCES `news` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
        ");
	}
}