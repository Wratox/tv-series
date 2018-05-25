<?php
/*
 * Setting the charset on 'dsn' helps prevent SQL-injection.
 */
return [
    'database' => [
        'dsn' 		=> 'mysql:host=localhost;dbname=seriesdb;charset=utf8',
        'username'	=> 'dbuser',
        'password' 	=> 'babymetal'
    ]
];

/*
 * CREATE TABLE `seriesdb`.`series` ( `id` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(100) NOT NULL , `imdbid` VARCHAR(100) NOT NULL , `status` ENUM('Dead','Alive') NOT NULL , `season_count` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM;
 */