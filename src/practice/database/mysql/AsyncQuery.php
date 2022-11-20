<?php

declare(strict_types=1);

namespace practice\database\mysql;

use mysqli;
use practice\Practice;
use pocketmine\Server;
use mysqli_sql_exception;
use pocketmine\scheduler\AsyncTask;

abstract class AsyncQuery extends AsyncTask {

    public string $host, $username, $password, $database;
    public int $port;

    public function setHost(string $host): self {
        $this->host = $host;
        return $this;
    }

    public function setUsername(string $username): self {
        $this->username = $username;
        return $this;
    }

    public function setPassword(string $password): self {
        $this->password = $password;
        return $this;
    }

    public function setDatabase(string $database): self {
        $this->database = $database;
        return $this;
    }

    public function setPort(int $port): self {
        $this->port = $port;
        return $this;
    }

    public function onRun(): void {
        try {
            $mysqli = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->database,
                $this->port
            );
            $this->query($mysqli);
            $mysqli->close();
        } catch (mysqli_sql_exception $exception) {
            $this->onError();
        }
    }

    abstract public function query(mysqli $mysqli): void;


    public function onError(): void {
        Server::getInstance()->getLogger()->error("An error occurred while executing an async query.");
    }
}