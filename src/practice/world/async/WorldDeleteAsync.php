<?php

declare(strict_types=1);

namespace practice\world\async;

use pocketmine\scheduler\AsyncTask;

final class WorldDeleteAsync extends AsyncTask {

    public function __construct(
        private string $world,
        private string $directory
    ) {}

    public function onRun(): void {
        $world = $this->world;
        $directory = $this->directory;
        $path = $directory . DIRECTORY_SEPARATOR . $world;

        $this->deleteSource($path);
    }

    private function deleteSource(string $source): void {
        if (!is_dir($source)) {
            return;
        }

        if ($source[strlen($source) - 1] !== '/') {
            $source .= '/';
        }

        /** @var array $files */
        $files = glob($source . '*', GLOB_MARK);

        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteSource($file);
            } else {
                unlink($file);
            }
        }
        rmdir($source);
    }
}