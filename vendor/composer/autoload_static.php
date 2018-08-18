<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit7c593b14e31d7d1fc1fd77302157b966
{
    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'LINE\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'LINE\\' => 
        array (
            0 => __DIR__ . '/..' . '/linecorp/line-bot-sdk/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit7c593b14e31d7d1fc1fd77302157b966::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit7c593b14e31d7d1fc1fd77302157b966::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
