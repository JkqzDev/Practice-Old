<?php

declare(strict_types=1);

namespace practice\duel\invite;

use practice\session\Session;

final class Invite {

    public function __construct(
        private Session $session,
        private int $duelType,
        private int $time = 0
    ) {
        $this->time = time() + 60;
    }

    public function getSession(): Session {
        return $this->session;
    }

    public function getDuelType(): int {
        return $this->duelType;
    }

    public function isExpired(): bool {
        return $this->time < time();
    }

    public function isOnline(): bool {
        $session = $this->getSession();

        return $session->getPlayer() !== null;
    }
}