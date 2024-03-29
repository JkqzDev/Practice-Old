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

use mysqli;
use pocketmine\Server;
use practice\Practice;

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

    public static function run(string $query, ?\Closure $closure = null): void {
        try {
            $result = self::mysqli()->query($query);

            if (isset($closure)) {
                if (!$result instanceof \mysqli_result) {
                    $closure();

                } else {
                    $rows = [];

                    while ($row = $result->fetch_assoc()) {
                        $rows[] = $row;
                    }

                    $closure($rows);
                }
            }
        } catch (\mysqli_sql_exception $exception) {
            Practice::getInstance()->getLogger()->error('MySQL Query Error: ' . $exception->getMessage());
        }
    }

    public static function mysqli(): \mysqli {
        return new mysqli(
            self::$host,
            self::$username,
            self::$password,
            self::$database,
            self::$port
        );
    }

    public static function setCredentials(array $credentials): void {
        foreach ($credentials as $credential) {
            if (!isset($credential)) {
                throw new \InvalidArgumentException('Missing MySQL credentials.');
            }
        }
        self::$host = $credentials['host'];
        self::$port = $credentials['port'];
        self::$username = $credentials['username'];
        self::$password = $credentials['password'];
        self::$database = $credentials['database'];
    }
}