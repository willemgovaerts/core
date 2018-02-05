<?php

namespace Levaral\Core\Action;

use Illuminate\Support\Facades\Route;

class Action
{
    public static function get($url, $ActionClass)
    {
        Route::get($url, $ActionClass)
            ->name(self::getName($ActionClass));
    }

    public static function post($url, $ActionClass)
    {
        Route::post($url, $ActionClass)
            ->name(self::getName($ActionClass));
    }

    protected static function getName($ActionClass)
    {
        $reflector = new \ReflectionClass($ActionClass);
        $fullPath = $reflector->getFileName();
        $name = ltrim(substr($fullPath, strpos($fullPath, "Actions")), 'Actions/');
        $name = rtrim($name, '.php');

        return str_replace('/', '.', $name);
    }
}