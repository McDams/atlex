<?php

namespace App\Core;

class AssetBuilder
{
    public function shouldRebuild(string $projectRoot): bool
    {
        $cssSource = $projectRoot . '/public/assets/css/app.css';
        $cssTarget = $projectRoot . '/public/assets/css/app.min.css';
        $jsSource = $projectRoot . '/public/assets/js/app.js';
        $jsTarget = $projectRoot . '/public/assets/js/app.min.js';

        if (!is_file($cssTarget) || !is_file($jsTarget)) {
            return true;
        }

        return filemtime($cssSource) > filemtime($cssTarget)
            || filemtime($jsSource) > filemtime($jsTarget);
    }

    public function rebuild(string $projectRoot): void
    {
        $packageJson = $projectRoot . '/package.json';
        if (!is_file($packageJson)) {
            return;
        }

        $command = 'cd ' . escapeshellarg($projectRoot) . ' && npm run build';
        $output = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException('Asset rebuild failed: ' . implode(PHP_EOL, $output));
        }
    }
}
