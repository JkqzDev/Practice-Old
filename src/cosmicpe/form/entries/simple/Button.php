<?php

declare(strict_types=1);

namespace cosmicpe\form\entries\simple;

use cosmicpe\form\types\Icon;
use cosmicpe\form\entries\FormEntry;

final class Button implements FormEntry {

    private string $title;

    private ?Icon $icon;

    public function __construct(string $title, ?Icon $icon = null) {
        $this->title = $title;
        $this->icon = $icon;
    }

    public function jsonSerialize(): array {
        return [
            "text" => $this->title,
            "image" => $this->icon
        ];
    }
}