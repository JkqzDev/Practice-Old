<?php

declare(strict_types=1);

namespace practice\world\async;

use Closure;
use Directory;
use pocketmine\Server;
use pocketmine\nbt\TreeRoot;
use pocketmine\utils\Binary;
use Webmozart\PathUtil\Path;
use pocketmine\scheduler\AsyncTask;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\world\format\io\data\BedrockWorldData;

final class WorldCopyAsync extends AsyncTask {

    public function __construct(
        private string   $world,
        private string   $directory,
        private string   $newName,
        private string   $newDirectory,
        private ?Closure $callback = null
    ) {}

    public function onRun(): void {
        $directory = $this->directory;
        $world = $this->world;

        $newDirectory = $this->newDirectory;
        $newName = $this->newName;

        $path = $directory . DIRECTORY_SEPARATOR . $world;
        $newPath = $newDirectory . DIRECTORY_SEPARATOR . $newName;

        $this->copySource($path, $newPath);
        $this->serializeWorld($newDirectory, $newName);
    }

    private function copySource(string $source, string $target): void {
        if (!is_dir($source)) {
            @copy($source, $target);
            return;
        }
        @mkdir($target);

        /** @var Directory $dir */
        $dir = dir($source);

        while (($entry = $dir->read()) !== false) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $Entry = $source . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($Entry)) {
                $this->copySource($Entry, $target . DIRECTORY_SEPARATOR . $entry);
                continue;
            }
            @copy($Entry, $target . DIRECTORY_SEPARATOR . $entry);
        }
        $dir->close();
    }

    private function serializeWorld(string $newDirectory, string $newName): void {
        $path = $newDirectory . DIRECTORY_SEPARATOR . $newName;

        /** @var string $rawLevelData */
        $rawLevelData = file_get_contents(Path::join($path, 'level.dat'));

        $nbt = new LittleEndianNbtSerializer;

        $worldData = $nbt->read(substr($rawLevelData, 8))->mustGetCompoundTag();
        $worldData->setString('LevelName', $newName);
        $newNbt = new LittleEndianNbtSerializer;
        $buffer = $newNbt->write(new TreeRoot($worldData));
        file_put_contents(Path::join($path, 'level.dat'), Binary::writeLInt(BedrockWorldData::CURRENT_STORAGE_VERSION) . Binary::writeLInt(strlen($buffer)) . $buffer);
    }

    public function onCompletion(): void {
        $worldName = $this->newName;
        $callback = $this->callback;

        if ($callback !== null) {
            Server::getInstance()->getWorldManager()->loadWorld($worldName);
            $callback(Server::getInstance()->getWorldManager()->getWorldByName($worldName));
        }
    }
}