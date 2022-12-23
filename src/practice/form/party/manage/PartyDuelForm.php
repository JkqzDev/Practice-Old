<?php

declare(strict_types=1);

namespace practice\form\party\manage;

use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\party\duel\Duel;
use practice\party\duel\queue\QueueFactory;
use practice\session\SessionFactory;

final class PartyDuelForm extends SimpleForm {

    private array $types = [
        'No Debuff' => Duel::TYPE_NODEBUFF,
        'Gapple' => Duel::TYPE_GAPPLE,
        'Fist' => Duel::TYPE_FIST,
        'Combo' => Duel::TYPE_COMBO,
        'Build UHC' => Duel::TYPE_BUILDUHC,
        'Cave UHC' => Duel::TYPE_CAVEUHC,
        'Final UHC' => Duel::TYPE_FINALUHC
    ];

    public function __construct() {
        parent::__construct(TextFormat::colorize('&bParty duels'));

        foreach ($this->types as $type => $typeId) {
            $this->addButton(new Button(TextFormat::colorize('&7' . $type)),
                static function (Player $player, int $button_index) use ($typeId): void {
                    $session = SessionFactory::get($player);

                    if ($session === null) {
                        return;
                    }
                    $party = $session->getParty();

                    if ($party === null) {
                        return;
                    }
                    QueueFactory::create($party, $typeId);
                });
        }
    }
}