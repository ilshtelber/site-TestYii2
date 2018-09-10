SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

#
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT, #id пользователя
  `alias` varchar(100) NOT NULL UNIQUE, #псевдоним пользователя (должен быть уникальным)
  `username` varchar(100) NOT NULL, #фамилия пользователя
  `surname` varchar(100) NOT NULL, #имя пользователя
  `email` varchar(100), #email дресс пользователя
  `password` varchar(255) NOT NULL, #пароль пользователя, пароль храниться в виде хэша, генерируемый вот таким кодом Yii::$app->security->generatePasswordHash("qwerty123");
  `auth_key` varchar(100) NOT NULL, #токен для аутентификаций пользователя через сессию
  `access_token` varchar(100) UNIQUE, #токен, для авторизировать пользователя по REST API, этот токен передается чере заголовок Authorization для аутентификаций пользователя для REST запросов 
  `expired_at` int(11), #время действия токена access_token
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

#сообщение оставленного пользователем
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT, #id сообщений
  `id_user` int(11) NOT NULL, #id пользователя, который оставил сообщение
  `sender` varchar(100) NOT NULL, #кому было адресованно это сообщение
  `text` text NOT NULL, #текст сообщений
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

#группы, от тестового заданий Beboss
CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT, #id группы
  `id_parent` int(11) NOT NULL, #если это подгруппа, то мы обозначаем, к какому id группа принадлежит эта группа
  `name` varchar(100) NOT NULL, #название подгруппы
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `groups` (`id`, `id_parent`, `name`) VALUES
(1, 0, 'Телевизоры'),
(2, 0, 'Мультимедиа'),
(3, 1, 'ЖК телевизоры'),
(4, 1, 'Плазменные телевизоры'),
(5, 3, 'Диагональю до 45 дюймов'),
(6, 3, 'Диагональю более 40 дюймов'),
(7, 4, 'Диагональю до 45 дюймов'),
(8, 4, 'Диагональю более 40 дюймов'),
(9, 2, 'DVD-плееры'),
(10, 2, 'Blu-Ray плееры');

#продукты, каждый продукт привязан к какому либо группе в таблице groups, тест задание от Beboss
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT, #id продукта
  `id_group` int(11) NOT NULL DEFAULT '0', #к какому продукту из таблицы groups принадлежит продукт
  `name` varchar(250) NOT NULL, #название продукта
  PRIMARY KEY (`id`),
  KEY `id_group` (`id_group`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `products` (`id`, `id_group`, `name`) VALUES
(1, 9, 'DVD-плеер BBK DVP 753HD'),
(2, 9, 'DVD-плеер BBK DVP 953HD'),
(3, 9, 'DVD-плеер BBK DMP1024HD (+ 3 DVD диска)'),
(4, 2, 'Магнитола HYUNDAI H-1404'),
(5, 10, 'Blu-ray плеер PHILIPS DVP3700 (51)'),
(6, 8, 'Плазменный телевизор LG 50PZ250 (3D)'),
(7, 8, 'Плазменный телевизор Samsung PS51D450'),
(8, 7, 'Плазменный телевизор LG 42PT250'),
(9, 7, 'Плазменный телевизор LG 42PW451 (3D)'),
(10, 4, 'Плазменный телевизор LG 50PZ551 (3D)'),
(11, 5, 'Телевизор-ЖК LG 26LK330'),
(12, 5, 'Телевизор-ЖК Fusion FLTV-16W7'),
(13, 6, 'Телевизор-ЖК LG 42LK530'),
(14, 6, 'Телевизор-ЖК LG 42LK551'),
(15, 6, 'Телевизор-ЖК LG 47LK530'),
(16, 3, 'Телевизор-ЖК Samsung LE32D403'),
(17, 1, 'Телевизор Erisson 1435');

#таблица с графами, в одном графе содержаться вершины (из таблицы vertices) и ребра (из таблицы edges), тестовое задание от ДЕСК
CREATE TABLE IF NOT EXISTS `graphs` (
  `id` int(11) NOT NULL AUTO_INCREMENT, #id графа
  `user_id` int(11) NOT NULL DEFAULT 1, #id пользователя, то есть какому пользователя принадлежит этот графф
  `name` varchar(12) NOT NULL UNIQUE, #название граффа
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

#вершины привязанные к определенному графу, тестовое задание от ДЕСК
CREATE TABLE IF NOT EXISTS `vertices` (
  `id` int(11) NOT NULL AUTO_INCREMENT, #id вершины
  `graph_id` int(11) NOT NULL, #к какому графу из таблицы graph принадлежит эта вершина
  `vertex_number` int(11) NOT NULL, #хронологический номер вершины
  `alias` varchar(11) NOT NULL, #уникальное название вершины для определенного графа
  `X` int(5) DEFAULT 0, #координат графа по X
  `Y` int(5) DEFAULT 0, #координат графа по Y
  PRIMARY KEY (`id`),
  FOREIGN KEY(`graph_id`) REFERENCES graphs(`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

#ребра, привязанные к определенному графу
CREATE TABLE IF NOT EXISTS `edges` (
  `id` int(11) NOT NULL AUTO_INCREMENT, #id ребра
  `graph_id` int(11) NOT NULL, #к какому графу из таблицы graph принадлежит этот ребро
  `vertex_from` varchar(11) NOT NULL, #начало вершины ребра
  `vertex_to` varchar(11) NOT NULL, #конец вершины ребра
  `weight` int(11) NOT NULL, #вес вершины
  PRIMARY KEY (`id`),
  FOREIGN KEY(`graph_id`) REFERENCES graphs(`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;