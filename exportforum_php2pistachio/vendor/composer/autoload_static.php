<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit58dc2fa2e5bd130555e5bfc55af54189
{
    public static $prefixesPsr0 = array (
        'z' => 
        array (
            'zz' => 
            array (
                0 => __DIR__ . '/..' . '/zaininnari/html-minifier/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit58dc2fa2e5bd130555e5bfc55af54189::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
