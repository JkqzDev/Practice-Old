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
use practice\Practice;
use mysqli_sql_exception;
use pocketmine\scheduler\AsyncTask;

abstract class AsyncQuery extends AsyncTask {
    public function onRun(): void {
        try {
            $mysqli = new mysqli(
                MySQL::$host,
                ''
            );
            $this->query($mysqli);
            $mysqli->close();
        } catch (mysqli_sql_exception $exception) {
            $this->onError();
        }
    }

    abstract public function query(mysqli $mysqli): void;


    public function onError(): void {
        Practice::getInstance()->getLogger()->error("An error occurred while executing an async query.");
    }
}