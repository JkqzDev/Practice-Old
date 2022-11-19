<?php declare(strict_types=1);
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 19/11/2022
 *
 * Copyright © 2022  <omar@ghostlymc.live> - All Rights Reserved.
 */

namespace practice\database\mysql\queries;

use mysqli;
use mysqli_result;
use practice\database\mysql\AsyncQuery;

final class SelectAsync extends AsyncQuery {

    private ?string $rows = null;

    public function __construct(
        private string  $table,
        private ?string $conditionKey,
        private ?string $conditionValue,
    ) {}

    public function query(mysqli $mysqli): void {
        if (!isset($this->conditionKey)) {
            $result = $mysqli->query("SELECT * FROM $this->table");
        } else {
            $result = $mysqli->query("SELECT * FROM $this->table WHERE $this->conditionKey = '$this->conditionValue'");
        }

        if ($result instanceof mysqli_result):
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }

            $this->rows = serialize($rows);
        endif;
    }

    public function onCompletion(): void {
        if (!isset($this->closure)) {
            return;
        }
        $rows = $this->rows;

        if ($rows !== null) {
            /** @noinspection UnserializeExploitsInspection */
            $rows = unserialize($rows);
        }
        $this->closure->__invoke($rows);
    }
}