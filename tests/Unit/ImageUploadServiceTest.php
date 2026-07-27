<?php

use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('uploads');
    $this->service = new ImageUploadService();
});

test('upload single image successfully', function () {
    $file = UploadedFile::fake()->create('photo.jpg', 100);

    $path = $this->service->upload($file, 'products');

    expect($path)->toBeString();
    expect($path)->toStartWith('products/');
    expect($path)->toEndWith('.jpg');
    Storage::disk('uploads')->assertExists($path);
});

test('upload generates unique filenames', function () {
    $file1 = UploadedFile::fake()->create('photo.jpg', 100);
    $file2 = UploadedFile::fake()->create('photo.jpg', 100);

    $path1 = $this->service->upload($file1, 'products');
    $path2 = $this->service->upload($file2, 'products');

    expect($path1)->not->toBe($path2);
});

test('upload multiple images', function () {
    $files = [
        UploadedFile::fake()->create('img1.png', 100),
        UploadedFile::fake()->create('img2.jpg', 100),
    ];

    $paths = $this->service->uploadMultiple($files, 'banners');

    expect($paths)->toBeArray()->toHaveCount(2);
    foreach ($paths as $path) {
        Storage::disk('uploads')->assertExists($path);
    }
});

test('upload multiple skips non-UploadedFile items', function () {
    $files = [
        UploadedFile::fake()->create('valid.jpg', 100),
        'not-a-file',
        null,
    ];

    $paths = $this->service->uploadMultiple($files, 'general');

    expect($paths)->toHaveCount(1);
});

test('delete image returns true for existing file', function () {
    $file = UploadedFile::fake()->create('delete-me.jpg', 100);
    $path = $this->service->upload($file, 'products');

    Storage::disk('uploads')->assertExists($path);

    $result = $this->service->delete($path);

    expect($result)->toBeTrue();
    Storage::disk('uploads')->assertMissing($path);
});

test('delete returns false for non-existent file', function () {
    $result = $this->service->delete('nonexistent/file.jpg');

    expect($result)->toBeFalse();
});

test('delete strips uploads prefix', function () {
    $file = UploadedFile::fake()->create('prefixed.jpg', 100);
    $path = $this->service->upload($file, 'banners');

    // Prepend 'uploads/' to simulate stored path with prefix
    $result = $this->service->delete('uploads/' . $path);

    expect($result)->toBeTrue();
    Storage::disk('uploads')->assertMissing($path);
});

test('update replaces old image with new one', function () {
    $oldFile = UploadedFile::fake()->create('old.jpg', 100);
    $oldPath = $this->service->upload($oldFile, 'products');

    $newFile = UploadedFile::fake()->create('new.jpg', 100);
    $newPath = $this->service->update($newFile, $oldPath, 'products');

    expect($newPath)->not->toBe($oldPath);
    Storage::disk('uploads')->assertMissing($oldPath);
    Storage::disk('uploads')->assertExists($newPath);
});

test('update works without old path', function () {
    $newFile = UploadedFile::fake()->create('fresh.png', 100);
    $newPath = $this->service->update($newFile, null, 'category');

    expect($newPath)->toBeString();
    Storage::disk('uploads')->assertExists($newPath);
});

test('get url returns storage url for path', function () {
    $url = $this->service->getUrl('products/test.jpg');

    expect($url)->toBeString();
    expect($url)->toContain('products/test.jpg');
});

test('upload uses correct folder in path', function () {
    $file = UploadedFile::fake()->create('cat.jpg', 100);

    $path = $this->service->upload($file, 'category');

    expect($path)->toStartWith('category/');
});
