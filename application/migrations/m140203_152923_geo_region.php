<?php

class m140203_152923_geo_region extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("set foreign_key_checks=0;");
        $this->truncateTable("campaigns_cities");
        $this->truncateTable("campaigns_countries");
        $this->truncateTable("cities");
        $this->execute("INSERT INTO `cities` VALUES (1,'Омская область',1),(2,'Республика Хакасия',1),(3,'Кемеровская область',1),(4,'Красноярский край',1),(5,'Новосибирская область',1),(6,'Брянская область',1),(7,'Астраханская область',1),(8,'Тверская область',1),(9,'Москва',1),(10,'Ярославская область',1),(11,'Ивановская область',1),(12,'Саратовская область',1),(13,'Орловская область',1),(14,'Смоленская область',1),(15,'Ставропольский край',1),(16,'Краснодарский край',1),(17,'Ульяновская область',1),(18,'Курская область',1),(19,'Ростовская область',1),(20,'Костромская область',1),(21,'Томская область',1),(22,'Калужская область',1),(23,'Пермский край',1),(24,'Тульская область',1),(25,'Приморский край',1),(26,'Тюменская область',1),(27,'Свердловская область',1),(28,'Санкт-Петербург',1),(29,'Новгородская область',1),(30,'Псковская область',1),(31,'Московская область',1),(32,'Белгородская область',1),(33,'Хабаровский край',1),(34,'Алтайский край',1),(35,'Вологодская область',1),(36,'Нижегородская область',1),(37,'Калининградская область',1),(38,'Рязанская область',1),(39,'Самарская область',1),(40,'Республика Северная Осетия (Алания)',1),(41,'Республика Коми',1),(42,'Республика Бурятия',1),(43,'Ханты-Мансийский автономный округ',1),(44,'Воронежская область',1),(45,'Челябинская область',1),(46,'Липецкая область',1),(47,'Волгоградская область',1),(48,'Республика Карачаево-Черкессия',1),(49,'Ямало-Ненецкий автономный округ',1),(50,'Курганская область',1),(51,'Архангельская область',1),(52,'Пензенская область',1),(53,'Республика Чувашия',1),(54,'Республика Удмуртия',1),(55,'Иркутская область',1),(56,'Республика Башкортостан',1),(57,'Республика Татарстан',1),(58,'Республика Марий Эл',1),(59,'Кировская область',1),(60,'Владимирская область',1),(61,'Оренбургская область',1),(62,'Камчатский край',1),(63,'Республика Кабардино-Балкария',1),(64,'Республика Адыгея',1),(65,'Мурманская область',1),(66,'Забайкальский край',1),(67,'Ленинградская область',1),(68,'Республика Мордовия',1),(69,'Чукотский автономный округ',1),(70,'Республика Алтай',1),(71,'Республика Дагестан',1),(72,'Тамбовская область',1),(73,'Амурская область',1),(74,'Республика Карелия',1),(75,'Республика Саха (Якутия)',1),(76,'Республика Калмыкия',1),(77,'Сахалинская область',1),(78,'Еврейская автономная область',1),(79,'Магаданская область',1),(80,'Республика Ингушетия',1),(81,'Республика Чечня',1),(82,'Республика Тыва (Тува)',1),(83,'Ненецкий автономный округ',1);");
        $this->execute("set foreign_key_checks=1;");
	}

	public function safeDown()
	{
        echo "m140203_152923_geo_region does not support migration down.\n";
        return false;
	}
}
