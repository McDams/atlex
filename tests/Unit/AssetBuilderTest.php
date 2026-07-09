<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\AssetBuilder;
use PHPUnit\Framework\TestCase;

final class AssetBuilderTest extends TestCase
{
    public function testRebuildIsRequiredWhenTargetAssetIsMissing(): void
    {
        $projectRoot = sys_get_temp_dir() . '/atlex-assetbuilder-' . uniqid('', true);
        mkdir($projectRoot . '/public/assets/css', 0777, true);
        file_put_contents($projectRoot . '/public/assets/css/app.css', 'body { color: red; }');

        $builder = new AssetBuilder();

        $this->assertTrue($builder->shouldRebuild($projectRoot));

        $this->removeDirectory($projectRoot);
    }

    public function testRebuildIsNotRequiredWhenTargetAssetIsNewer(): void
    {
        $projectRoot = sys_get_temp_dir() . '/atlex-assetbuilder-' . uniqid('', true);
        mkdir($projectRoot . '/public/assets/css', 0777, true);
        mkdir($projectRoot . '/public/assets/js', 0777, true);
        file_put_contents($projectRoot . '/public/assets/css/app.css', 'body { color: red; }');
        file_put_contents($projectRoot . '/public/assets/css/app.min.css', 'body{color:red}');
        file_put_contents($projectRoot . '/public/assets/js/app.js', 'console.log(1);');
        file_put_contents($projectRoot . '/public/assets/js/app.min.js', 'console.log(1);');

        touch($projectRoot . '/public/assets/css/app.min.css', time());
        touch($projectRoot . '/public/assets/js/app.min.js', time());
        touch($projectRoot . '/public/assets/css/app.css', time() - 5);
        touch($projectRoot . '/public/assets/js/app.js', time() - 5);

        $builder = new AssetBuilder();

        $this->assertFalse($builder->shouldRebuild($projectRoot));

        $this->removeDirectory($projectRoot);
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = array_diff(scandir($path) ?: [], ['.', '..']);
        foreach ($files as $file) {
            $fullPath = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($fullPath)) {
                $this->removeDirectory($fullPath);
            } else {
                unlink($fullPath);
            }
        }

        rmdir($path);
    }
}
