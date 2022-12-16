<?php

declare(strict_types=1);

namespace practice\form\duel;

use practice\duel\Duel;
use pocketmine\player\Player;
use cosmicpe\form\SimpleForm;
use pocketmine\utils\TextFormat;
use practice\duel\queue\PlayerQueue;
use practice\duel\queue\QueueFactory;
use cosmicpe\form\entries\simple\Button;

final class DuelQueueForm extends SimpleForm {

    private array $types = [
        'No Debuff' => Duel::TYPE_NODEBUFF,
        'Battle Rush' => Duel::TYPE_BATTLERUSH,
        'Bridge' => Duel::TYPE_BRIDGE,
        'Combo' => Duel::TYPE_COMBO,
        'Boxing' => Duel::TYPE_BOXING,
        'Sumo' => Duel::TYPE_SUMO,
        'Fist' => Duel::TYPE_FIST,
        'SG' => Duel::TYPE_SG,
        'Gapple' => Duel::TYPE_GAPPLE,
        'Build UHC' => Duel::TYPE_BUILDUHC,
        'Final UHC' => Duel::TYPE_FINALUHC,
        'Cave UHC' => Duel::TYPE_CAVEUHC
    ];

    public function __construct(bool $ranked = false) {
        parent::__construct(TextFormat::colorize($ranked ? '&bRanked duels' : '&9Unranked duels'));

        foreach ($this->types as $type => $typeId) {
            $queues = count(array_filter(QueueFactory::getAll(), function (PlayerQueue $queue) use ($ranked, $typeId): bool {
                return $queue->isRanked() === $ranked && $queue->getDuelType() === $typeId;
            }));
            $this->addButton(new Button(TextFormat::colorize('&7' . $type . PHP_EOL . '&fIn queue: ' . $queues)),
                static function (Player $player, int $button_index) use ($typeId, $ranked): void {
                    $queue = QueueFactory::get($player);

                    if ($queue !== null) {
                        return;
                    }
                    QueueFactory::create($player, $typeId, $ranked);
                }
            );
        }
    }
}