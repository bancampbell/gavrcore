<?php

namespace Tests\Unit\Modules\MediaManager\Domain\ValueObjects;

use Tests\TestCase;
use Modules\MediaManager\Domain\ValueObjects\MediaPath;

class MediaPathTest extends TestCase
{
    public function test_media_path_creation(): void
    {
        $path = new MediaPath('uploads/folder/file.txt');
        $this->assertEquals('uploads/folder/file.txt', $path->toString());
    }

    public function test_media_path_creation_with_trailing_slashes(): void
    {
        $path = new MediaPath('/uploads/folder/file.txt/');
        $this->assertEquals('uploads/folder/file.txt', $path->toString());
    }

    public function test_media_path_creation_with_backslashes(): void
    {
        $path = new MediaPath('uploads\\folder\\file.txt');
        $this->assertEquals('uploads/folder/file.txt', $path->toString());
    }

    public function test_media_path_creation_with_multiple_slashes(): void
    {
        $path = new MediaPath('uploads//folder///file.txt');
        $this->assertEquals('uploads/folder/file.txt', $path->toString());
    }

    public function test_media_path_creation_with_empty_string(): void
    {
        $path = new MediaPath('');
        $this->assertEquals('', $path->toString());
    }

    public function test_get_parent_returns_parent_path(): void
    {
        $path = new MediaPath('uploads/folder/file.txt');
        $parent = $path->getParent();
        $this->assertEquals('uploads/folder', $parent->toString());
    }

    public function test_get_parent_of_root_returns_empty(): void
    {
        $path = new MediaPath('file.txt');
        $parent = $path->getParent();
        $this->assertEquals('', $parent->toString());
    }

    public function test_get_parent_of_empty_returns_empty(): void
    {
        $path = new MediaPath('');
        $parent = $path->getParent();
        $this->assertEquals('', $parent->toString());
    }

    public function test_get_name_returns_filename(): void
    {
        $path = new MediaPath('uploads/folder/file.txt');
        $this->assertEquals('file.txt', $path->getName());
    }

    public function test_get_name_returns_folder_name(): void
    {
        $path = new MediaPath('uploads/folder');
        $this->assertEquals('folder', $path->getName());
    }

    public function test_get_name_returns_empty_for_root(): void
    {
        $path = new MediaPath('');
        $this->assertEquals('', $path->getName());
    }

    public function test_get_extension_returns_extension(): void
    {
        $path = new MediaPath('file.txt');
        $this->assertEquals('txt', $path->getExtension());
    }

    public function test_get_extension_returns_empty_for_no_extension(): void
    {
        $path = new MediaPath('file');
        $this->assertEquals('', $path->getExtension());
    }

    public function test_get_extension_returns_empty_for_hidden_file(): void
    {
        $path = new MediaPath('.env');
        $this->assertEquals('', $path->getExtension());
    }

    public function test_get_extension_returns_lowercase(): void
    {
        $path = new MediaPath('file.JPG');
        $this->assertEquals('jpg', $path->getExtension());
    }

    public function test_is_root_returns_true_for_empty(): void
    {
        $path = new MediaPath('');
        $this->assertTrue($path->isRoot());
    }

    public function test_is_root_returns_false_for_non_empty(): void
    {
        $path = new MediaPath('folder');
        $this->assertFalse($path->isRoot());
    }

    public function test_equals_returns_true_for_same_path(): void
    {
        $path1 = new MediaPath('uploads/file.txt');
        $path2 = new MediaPath('uploads/file.txt');
        $this->assertTrue($path1->equals($path2));
    }

    public function test_equals_returns_false_for_different_paths(): void
    {
        $path1 = new MediaPath('uploads/file.txt');
        $path2 = new MediaPath('uploads/file2.txt');
        $this->assertFalse($path1->equals($path2));
    }

    public function test_equals_returns_true_for_normalized_paths(): void
    {
        $path1 = new MediaPath('uploads/folder/file.txt');
        $path2 = new MediaPath('uploads//folder///file.txt');
        $this->assertTrue($path1->equals($path2));
    }

    public function test_to_string_magic_method(): void
    {
        $path = new MediaPath('uploads/file.txt');
        $this->assertEquals('uploads/file.txt', (string) $path);
    }

    // === НОВЫЕ ТЕСТЫ НА БЕЗОПАСНОСТЬ ===

    public function test_double_dot_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new MediaPath('../../../etc/passwd');
    }

    public function test_single_dot_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new MediaPath('folder/./file.txt');
    }

    public function test_double_dot_in_middle_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new MediaPath('uploads/../secret/file.txt');
    }

    public function test_multiple_double_dots_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new MediaPath('a/../../b');
    }
}
