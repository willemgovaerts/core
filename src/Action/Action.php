<?php

namespace Levaral\Core\Action;

use Illuminate\Support\Facades\Route;

class Action
{
    public static function get($url, $ActionClass, $routeName = null)
    {
        if ($routeName) {
            Route::get($url, $ActionClass)
                ->name($routeName);
        } else {
            Route::get($url, $ActionClass)
                ->name(self::getName($ActionClass));
        }

    }

    public static function post($url, $ActionClass, $routeName = null)
    {
        if ($routeName) {
            Route::post($url, $ActionClass)
                ->name($routeName);
        } else {
            Route::post($url, $ActionClass)
                ->name(self::getName($ActionClass));
        }
    }

    protected static function getName($ActionClass)
    {
        $reflector = new \ReflectionClass($ActionClass);
        $fullPath = $reflector->getFileName();
        $name = ltrim(substr($fullPath, strpos($fullPath, "Actions")), 'Actions');
        $name = ltrim($name, "/");
        $name = rtrim($name, '.php');

        return str_replace('/', '.', $name);
    }
}