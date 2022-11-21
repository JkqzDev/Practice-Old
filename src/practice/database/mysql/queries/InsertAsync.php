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

final class InsertAsync extends QueryAsync {

    public function __construct(
        string    $table,
        array     $data,
        ?\Closure $onComplete = null
    ) {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_map(static fn($value) => "'{$value}'", array_values($data)));
        parent::__construct("INSERT INTO {$table} ({$columns}) VALUES ({$values})", $onComplete);
    }
}