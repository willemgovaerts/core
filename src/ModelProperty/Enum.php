<?php
namespace Levaral\Core\ModelProperty;

use ReflectionClass;

class Enum
{
    public static function getOptions($class, $lang = 'en'): array
    {
        $options = [];
        $enum = new ReflectionClass($class);
        if (is_array(trans('enum.' . $class, [], $lang))) {
            $options = trans('enum.' . $class, [], $lang);
        } else {
            foreach ($enum->getConstants() as $key => $value) {
                $options[$value] = $value;
            }
        }

        return $options;
    }

    public static function getValues($class)
    {
        $enum = new ReflectionClass($class);
        return array_values($enum->getConstants());
    }

    public static function getLabelValueOptions($class, $lang = 'en'): array
    {
        $baseOptions = self::getOptions($class, $lang);
        $options = [];

        foreach ($baseOptions as $value => $label) {
            $options[] = ['label' => $label, 'value' => $value];
        }

        return $options;
    }
}
