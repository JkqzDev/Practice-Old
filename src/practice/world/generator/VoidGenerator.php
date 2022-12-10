<?php

declare(strict_types=1);

namespace practice\world\generator;

use pocketmine\block\BlockLegacyIds;
use pocketmine\world\ChunkManager;
use pocketmine\world\generator\Generator;

final class VoidGenerator extends Generator {

    public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void {
        $chunk = $world->getChunk($chunkX, $chunkZ);

        if ($chunkX === 16 && $chunkZ === 16) {
            $chunk->setFullBlock(0, 64, 0, BlockLegacyIds::GRASS >> 4);
        }
    }

    public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void {}
}