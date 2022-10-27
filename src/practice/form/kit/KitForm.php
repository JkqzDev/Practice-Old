<?php

declare(strict_types=1);

namespace practice\form\kit;

use practice\kit\Kit;
use pocketmine\player\Player;
use cosmicpe\form\CustomForm;
use pocketmine\utils\TextFormat;
use cosmicpe\form\entries\custom\InputEntry;
use cosmicpe\form\entries\custom\ToggleEntry;

final class KitForm extends CustomForm {

    public function __construct(Kit $kit) {
        parent::__construct(TextFormat::colorize('&bKit Settings'));
        $horizontalKnockback = new InputEntry('Horizontal Knockback', null, (string)$kit->getHorizontalKnockback());
        $verticalKnockback = new InputEntry('Vertical Knockback', null, (string)$kit->getVerticalKnockback());
        $maxHeight = new InputEntry('Max Height', null, (string)$kit->getMaxHeight());
        $attackCooldown = new InputEntry('Attack Cooldown', null, (string)$kit->getAttackCooldown());
        $canRevert = new ToggleEntry('Can Revert', $kit->canRevert());

        // Possible hight limiter

        $this->addEntry($horizontalKnockback,
            static function(Player $player, InputEntry $entry, string $value) use ($kit): void {
                if (!is_numeric($value)) {
                    return;
                }
                $kit->setHorizontalKnockback((float)$value);
            }
        );

        $this->addEntry($verticalKnockback, static function(Player $player, InputEntry $entry, string $value) use ($kit): void {
            if (!is_numeric($value)) {
                return;
            }
            $kit->setVerticalKnockback((float)$value);
        });

        $this->addEntry($maxHeight, static function(Player $player, InputEntry $entry, string $value) use ($kit): void {
            if (!is_numeric($value)) {
                return;
            }
            $kit->setMaxHeight((float)$value);
        });

        $this->addEntry($attackCooldown, static function(Player $player, InputEntry $entry, string $value) use ($kit): void {
            if (!is_numeric($value)) {
                return;
            }
            $kit->setAttackCooldown((int)$value);
        });

        $this->addEntry($canRevert, static function(Player $player, ToggleEntry $entry, bool $value) use ($kit): void {
            $kit->setCanRevert($value);
        });
    }
}