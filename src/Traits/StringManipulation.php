<?php

namespace Nobir\MiniWizard\Traits;

use Illuminate\Support\Str;

trait StringManipulation
{

    public static function modelToForeignKey($modelName)
    {
        return Str::snake(Str::singular($modelName)) . '_id';
    }
    public static function modelToTableName($modelName)
    {
        return Str::snake(Str::plural($modelName));
    }
    public static function modelToBelongsToName($modelName)
    {
        return Str::snake(Str::singular($modelName));
    }

    public static function mdoelNameFormat($string)
    {
        return ucfirst(str()->camel($string));
    }
    public static function PascalToCabab($string)
    {
        return str()->kebab($string);
    }
    public static function PascalToSnacke($string)
    {
        return str()->snake($string);
    }

    public static function foreignKeyToModelName($foreing_key)
    {
        $foreing_key = str_replace('_id', '', $foreing_key);

        // Convert to Pascal Case
        $foreing_key = ucwords(str_replace('_', ' ', $foreing_key));
        $modelName = str_replace(' ', '', $foreing_key);

        return $modelName;
    }

    public function removeAfterBefore($haystack, $remove = null)
    {
        // Default to '/' if $remove is null
        if (is_null($remove)) {
            $remove = '/';
        }

        // Escape special characters in the $remove string for use in the regular expression
        $escapedRemove = preg_quote($remove, '/');

        // Create a regular expression pattern to match the text at the start and end of the string
        $pattern = '/^' . $escapedRemove . '|' . $escapedRemove . '$/';

        // Replace the matched parts with an empty string
        $text = preg_replace($pattern, '', $haystack);

        return $text;
    }
}
