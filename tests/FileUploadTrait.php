<?php
namespace Tests;

use Mockery;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File as LaravelFile;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

trait FileUploadTrait
{

    public static function setupFile($path, $name){
        $filepath = base_path('tests/Support/Fixtures/') . $path . '/' . $name;

        $tmp = tempnam(sys_get_temp_dir(), 'astro_test_');
        copy($filepath, $tmp);
        return new LaravelFile($tmp); //replaced with below so that tests stop exploding...
//        return new UploadedFile($tmp, $name);
    }

    public static function setupFileUpload($path, $name){
        $filepath = base_path('tests/Support/Fixtures/') . $path . '/' . $name;

        $tmp = tempnam(sys_get_temp_dir(), 'astro_test_');
        copy($filepath, $tmp);

        return new UploadedFile($tmp, $name, null, null, null, true); // Final argument sets a Symfony test flag
    }

    public static function mockFileUpload($path, $name){
        $filepath = base_path('tests/Support/Fixtures/') . $path . '/' . $name;

        $file = new SymfonyFile($filepath);

        $upload = Mockery::mock(UploadedFile::class, [ $filepath, $name ])->makePartial();
        $upload->shouldReceive('isValid')->andReturn(true);
        $upload->shouldReceive('move')->andReturn($file);

        return $upload;
    }

}
