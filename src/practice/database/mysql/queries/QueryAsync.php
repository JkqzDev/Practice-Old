<?php

declare(strict_types=1);

namespace practice\database\mysql\queries;

use mysqli;
use Closure;
use mysqli_result;
use practice\database\mysql\AsyncQuery;

final class QueryAsync extends AsyncQuery {

    private ?string $rows = null;

    public function __construct(
        private string   $sqlQuery,
        private ?Closure $closure = null
    ) {}

    public function query(mysqli $mysqli): void {
        $result = $mysqli->query($this->sqlQuery);

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

        if (isset($this->rows)) {
            /** @noinspection UnserializeExploitsInspection */
            $this->closure->__invoke(unserialize($this->rows));
            return;
        }

        $this->closure->__invoke();
    }
}