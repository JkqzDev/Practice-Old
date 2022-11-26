<?php declare(strict_types=1);
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 25/11/2022
 *
 * Copyright © 2022  <omar@ghostlymc.live> - All Rights Reserved.
 */

namespace practice\database\mysql\queries;

final class UpdateAsync extends QueryAsync {

    public function __construct(
        string    $table,
        array     $data,
        array     $conditions,
        ?\Closure $onComplete = null
    ) {
        $set = implode(', ', array_map(static fn($key, $value) => "{$key} = '{$value}'", array_keys($data), array_values($data)));
        $where = implode(' AND ', array_map(static fn($key, $value) => "{$key} = '{$value}'", array_keys($conditions), array_values($conditions)));
        parent::__construct("UPDATE {$table} SET {$set} WHERE {$where}", $onComplete);
    }
}