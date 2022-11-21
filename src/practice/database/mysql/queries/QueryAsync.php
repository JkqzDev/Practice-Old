<?php declare(strict_types=1);
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 21/11/2022
 *
 * Copyright © 2022  <omar@ghostlymc.live> - All Rights Reserved.
 */

namespace practice\database\mysql\queries;

use practice\database\mysql\AsyncQuery;

class QueryAsync extends AsyncQuery {

    private ?string $rows = null;

    public function __construct(
        private string    $sqlQuery,
        private ?\Closure $onComplete = null
    ) {}

    public function query(\mysqli $mysqli): void {
        $result = $mysqli->query($this->sqlQuery);

        if ($result instanceof \mysqli_result):
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }

            $this->rows = serialize($rows);
        endif;
    }

    public function onCompletion(): void {
        if (!isset($this->onComplete)) {
            return;
        }

        if (isset($this->rows)) {
            $this->onComplete->__invoke(unserialize($this->rows, ['allowed_classes' => false]));
            return;
        }

        $this->onComplete->__invoke([]);
    }
}