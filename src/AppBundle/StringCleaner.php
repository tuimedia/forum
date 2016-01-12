<?php
namespace AppBundle;

class StringCleaner
{
    public static function clean($string)
    {
        $string = filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

        // Ensure bytestream is valid
        if (!mb_check_encoding($string, 'UTF-8')) {
            throw new \InvalidArgumentException('Invalid unicode input.');
        }

        // Clean and normalise unicode
        $string = iconv('UTF-8', 'UTF-8//IGNORE', $string);
        $string = normalizer_normalize($string);

        // Strip control characters
        $string = preg_replace('~\p{C}+~u', '', $string);

        return $string;
    }
}
