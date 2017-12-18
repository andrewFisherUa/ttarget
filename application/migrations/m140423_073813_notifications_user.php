<?php

class m140423_073813_notifications_user extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `notifications` ADD COLUMN `user_id` INT UNSIGNED NULL  AFTER `is_new` ;");
        $this->execute("ALTER TABLE `notifications`
              ADD CONSTRAINT `notifications_users`
              FOREIGN KEY (`user_id` )
              REFERENCES `users` (`id` )
              ON DELETE SET NULL
              ON UPDATE SET NULL
            , ADD INDEX `notifications_users` (`user_id` ASC) ;");
	}

	public function down()
	{
        $this->execute("ALTER TABLE `notifications` DROP FOREIGN KEY `notifications_users` ;");
        $this->execute("ALTER TABLE `notifications` DROP COLUMN `user_id`
            , DROP INDEX `notifications_users` ;");
	}
}