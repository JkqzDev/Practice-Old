<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 27/10/2022
 *
 * Copyright © 2022  <omar@ghostlymc.live> - All Rights Reserved.
 */
declare(strict_types=1);

namespace practice\database\mysql;

use pocketmine\Server;

final class MySQL {

    public static string $host, $username, $password, $database;

    public static int $port;

    public static function runAsync(AsyncQuery $query): void {
        $query->setHost(self::$host)
            ->setPort(self::$port)
            ->setUsername(self::$username)
            ->setPassword(self::$password)
            ->setDatabase(self::$database);

        Server::getInstance()->getAsyncPool()->submitTask($query);
    }
}