<?php

declare(strict_types=1);

namespace practice\event\duel;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\world\Position;

final class EventDuel {

    public const STARTING = 0;
    public const RUNNING = 1;
    public const RESTARTING = 2;

    public function __construct(
        private int $type,
        private Event $event,
        private Player $firstPlayer,
        private Player $secondPlayer,
        private Position $firstPosition,
        private Position $secondPosition,
        private int $status = self::STARTING,
        private int $starting = 5,
        private int $running = 0,
        private int $restarting = 5,
        private string $winner = '',
        private string $loser = ''
    ) {
        
    }

    public function update(): void {
        
    }

    private function prepare(): void {

    }

    private function finish(Player $loser): void {

    }
}