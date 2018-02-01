<?php

function cleanString($string)
{
    $utf8 = array(
        '/[áàâãªäåæā]/u' => 'a',
        '/[ÁÀÂÃÄÅÆĀ]/u' => 'A',
        '/[ÍÌÎÏĪ]/u' => 'I',
        '/[íìîïī]/u' => 'i',
        '/[éèêëēė]/u' => 'e',
        '/[ÉÈÊËĒ]/u' => 'E',
        '/[óòôõºöøœō]/u' => 'o',
        '/[ÓÒÔÕÖŒØŌ]/u' => 'O',
        '/[úùûüū]/u' => 'u',
        '/[ÚÙÛÜŪ]/u' => 'U',
        '/çćč/' => 'c',
        '/ÇĆČ/' => 'C',
        '/ñń/' => 'n',
        '/ÑŃ/' => 'N',
        '/–/' => '-', // UTF-8 hyphen to "normal" hyphen
        '/[’‘‹›‚]/u' => ' ', // Literally a single quote
        '/[“”«»„]/u' => ' ', // Double quote
        '/ /' => ' ', // nonbreaking space (equiv. to 0x160)
        '/ß/' => 'ss', //https://german.stackexchange.com/questions/25550/use-of-ss-or-%C3%9F-in-a-surname/25551
    );
    $cleanString = preg_replace(array_keys($utf8), array_values($utf8), $string);
    $cleanString = preg_replace('/[^A-Za-z0-9\ ]/', '', $cleanString);
    $cleanString = preg_replace('/\s+|\r+|\n+|\t+/', ' ', $cleanString);


    $cleanString = str_replace(' ', '-', trim($cleanString));
    $cleanString = strtolower($cleanString);
    return $cleanString;
}