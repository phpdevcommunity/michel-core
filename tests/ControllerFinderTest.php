<?php

namespace Test\PhpDevCommunity\Michel\Core;

use PhpDevCommunity\Michel\Core\Routing\ControllerFinder;
use PHPUnit\Framework\TestCase;
use Test\PhpDevCommunity\Michel\Core\Controller\SampleControllerTest;
use Test\PhpDevCommunity\Michel\Core\Controller\UserControllerTest;

class ControllerFinderTest extends TestCase
{
    public function testFound()
    {
        if (PHP_VERSION_ID >= 80000) {
            $controllers = (new ControllerFinder([__DIR__ . '/Controller']))->findControllerClasses();
            $this->assertCount(2, $controllers);
        }
        $this->assertTrue(true);
    }

    public function testFoundCache()
    {
        if (PHP_VERSION_ID >= 80000) {
            $cacheDir = __DIR__ . '/cache';
            $targetDir = __DIR__ . '/Controller';
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0777, true);
            }
            $fileCache = "$cacheDir/" . md5($targetDir) . '.php';
            if (file_exists($fileCache)) {
                unlink($fileCache);
            }

            $this->assertFalse(file_exists($fileCache));
            $controllers = (new ControllerFinder([$targetDir], $cacheDir))->findControllerClasses();
            $this->assertCount(2, $controllers);
            $this->assertTrue(file_exists($fileCache));
            $this->assertEquals([
                UserControllerTest::class,
                SampleControllerTest::class
            ], require $fileCache);

            $controllers = (new ControllerFinder([$targetDir], $cacheDir))->findControllerClasses();
            $this->assertCount(2, $controllers);
            unlink($fileCache);
            rmdir($cacheDir);
        }
        $this->assertTrue(true);
    }

}