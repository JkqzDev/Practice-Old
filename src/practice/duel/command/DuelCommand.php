<?php

declare(strict_types=1);

namespace practice\duel\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\duel\DuelFactory;
use practice\duel\invite\Invite;
use practice\duel\invite\InviteFactory;
use practice\form\duel\DuelForm;
use practice\form\duel\manage\DeleteDuelForm;
use practice\form\duel\manage\SetupDuelForm;
use practice\session\SessionFactory;

final class DuelCommand extends Command {

    public function __construct() {
        parent::__construct('duel', 'Duel command');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof Player) {
            return;
        }
        $session = SessionFactory::get($sender);

        if ($session === null) {
            return;
        }

        if (!$session->inLobby() || $session->inQueue()) {
            $sender->sendMessage(TextFormat::colorize('&cYou can\'t use this command.'));
            return;
        }

        if (!isset($args[0])) {
            $sender->sendMessage(TextFormat::colorize('&cUse /duel [player]'));
            return;
        }

        $subCommand = strtolower($args[0]);
        if ($sender->getGamemode()->equals(GameMode::CREATIVE()) && $sender->hasPermission('duel.command')) {

            if ($subCommand === 'setup') {
                $form = new SetupDuelForm;
                $sender->sendForm($form);
            } elseif ($subCommand === 'delete') {
                $form = new DeleteDuelForm;
                $sender->sendForm($form);
            }
        } else {
            if ($subCommand === 'accept') {
                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::colorize('&cUse /duel accept [player]'));
                    return;
                }
                $invites = InviteFactory::get($session);

                if ($invites === null || count($invites) === 0) {
                    $sender->sendMessage(TextFormat::colorize('&cYou don\'t have invites.'));
                    return;
                }

                if (!isset($invites[$args[1]])) {
                    $sender->sendMessage(TextFormat::colorize('&cYou don\'t have invite from this player.'));
                    return;
                }
                /** @var Invite $invite */
                $invite = $invites[$args[1]];
                $target = $invite->getSession();

                if ($invite->isExpired()) {
                    InviteFactory::removeFromPlayer($session, $target);
                    $sender->sendMessage(TextFormat::colorize('&cInvite was expired.'));
                    return;
                }

                if (!$invite->isOnline()) {
                    InviteFactory::removeFromPlayer($session, $target);
                    $sender->sendMessage(TextFormat::colorize('&cPlayer inviter offline.'));
                    return;
                }

                if (!$target->inLobby() || $target->inQueue()) {
                    InviteFactory::removeFromPlayer($session, $target);
                    $sender->sendMessage(TextFormat::colorize('&cPlayer don\'t play duel.'));
                    return;
                }
                DuelFactory::create($session, $target, $invite->getDuelType(), false, $invite->getWorldName());

                $target->getPlayer()?->sendMessage(TextFormat::colorize('&a' . $session->getName() . ' accepted your party duel request'));
                $sender->sendMessage(TextFormat::colorize('&aYou have accepted ' . $target->getName() . '\'s request'));

                InviteFactory::remove($session);
                InviteFactory::remove($target);
                return;
            }
            $player = $sender->getServer()->getPlayerByPrefix($args[0]);

            if (!$player instanceof Player || !$player->isOnline()) {
                $sender->sendMessage(TextFormat::colorize('&cPlayer is offline.'));
                return;
            }

            if ($player === $sender) {
                $sender->sendMessage(TextFormat::colorize('&cYou can\'t send duel yourself.'));
                return;
            }
            $target = SessionFactory::get($player);

            if ($target === null) {
                $sender->sendMessage(TextFormat::colorize('&cPlayer not found.'));
                return;
            }
            $form = new DuelForm($session, $target);
            $sender->sendForm($form);
        }
    }
}