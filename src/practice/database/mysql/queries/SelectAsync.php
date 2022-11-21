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

final class SelectAsync extends QueryAsync {
    public function __construct(
        string    $table,
        array     $conditions,
        string    $_extra = '',
        ?\Closure $onComplete = null,
        string    $columns = '*',
    ) {
        $where = implode(' AND ', array_map(static fn($key, $value) => "{$key} = '{$value}'", array_keys($conditions), array_values($conditions)));
        parent::__construct(sprintf('SELECT %s FROM %s WHERE %s %s;', $columns, $table, $where, $_extra), $onComplete);
    }
}