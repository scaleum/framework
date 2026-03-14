<?php

use PHPUnit\Framework\TestCase;
use Scaleum\Stdlib\Helper\PathHelper;

class PathHelperTest extends TestCase
{
    public function testOverlapPathNoOverlap()
    {
        $path = '/home/user/project';
        $overlap = '/var/www/html';
        $result = PathHelper::overlapPath($path, $overlap);
        $this->assertEquals('/home/user/project', $result);
    }

    public function testOverlapPathPartialOverlap()
    {
        $path = '/home/user/project';
        $overlap = '/home/user';
        $result = PathHelper::overlapPath($path, $overlap);
        $this->assertEquals('/project', $result);
    }

    public function testOverlapPathCompleteOverlap()
    {
        $path = '/home/user/project';
        $overlap = '/home/user/project';
        $result = PathHelper::overlapPath($path, $overlap);
        $this->assertEquals('/', $result);
    }

    public function testOverlapPathNonStringInput()
    {
        $path = '/home/user/project';
        $overlap = 123;
        $result = PathHelper::overlapPath($path, $overlap);
        $this->assertEquals('/home/user/project', $result);
    }

    public function testRelativePathCommonSegments()
    {
        $path = '/home/user/project/file.php';
        $to = '/home/user/docs/readme.md';
        $result = PathHelper::relativePath($path, $to);
        $this->assertEquals('../docs/readme.md', $result);
    }

    public function testRelativePathNoCommonSegments()
    {
        $path = '/home/user/project/file.php';
        $to = '/var/www/html/index.php';
        $result = PathHelper::relativePath($path, $to);
        $this->assertEquals('../../../var/www/html/index.php', $result);
    }

    public function testRelativePathSubdirectory()
    {
        $path = '/home/user/project';
        $to = '/home/user/project/subdir/file.php';
        $result = PathHelper::relativePath($path, $to);
        $this->assertEquals('subdir/file.php', $result);
    }

    public function testRelativePathDifferentDepths()
    {
        $path = '/home/user/project/file.php';
        $to = '/home/user/project/subdir/subsubdir/file.php';
        $result = PathHelper::relativePath($path, $to);
        $this->assertEquals('./subdir/subsubdir/file.php', $result);
    }
}