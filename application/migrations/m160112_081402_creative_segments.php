<?php

class m160112_081402_creative_segments extends CDbMigration
{
	public function up()
	{
        $this->execute("CREATE TABLE `creative_segments` (
              `creative_id` int(10) unsigned NOT NULL,
              `segment_id` int(10) unsigned NOT NULL,
              PRIMARY KEY (`creative_id`,`segment_id`),
              KEY `creative_segments_segments_idx` (`segment_id`),
              CONSTRAINT `creative_segments_creative` FOREIGN KEY (`creative_id`) REFERENCES `campaigns_creative` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `creative_segments_segments` FOREIGN KEY (`segment_id`) REFERENCES `segments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB;
        ");
	}

	public function down()
	{
		echo "m160112_081402_creative_segments does not support migration down.\n";
		return false;
	}
}