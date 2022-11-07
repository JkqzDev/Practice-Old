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

final class Table {
    public const DUEL_STATS = "
create table if not exists duel_stats
(
    id       int auto_increment
        primary key,
    xuid     varchar(50)   not null,
    player   varchar(36)   not null,
    kills    int default 0 not null,
    deaths   int default 0 not null,
    elo      int default 0 not null,
    wins     int default 0 not null,
    losses   int default 0 not null,
    streak   int default 0 not null,
    longest  int default 0 not null,
    ranked   int default 0 not null,
    unranked int default 0 not null,
    constraint xuid
        unique (xuid)
);";

    public const PLAYER_SETTINGS = "
create table if not exists player_settings
(
    id           int auto_increment
        primary key,
    xuid         varchar(50)                 not null,
    player       varchar(36)                 not null,
    language     varchar(16) default 'en_US' not null,
    scoreboard   tinyint(1)  default 1       not null,
    cps_counter  tinyint(1)  default 1       not null,
    auto_respawn tinyint(1)  default 1       not null,
    potion_color varchar(16) default 'default'   not null,
    constraint xuid
        unique (xuid)
);";

}