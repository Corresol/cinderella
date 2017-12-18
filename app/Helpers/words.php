<?php

if (!function_exists('random_words')){
    function random_words($length)
    {
        $words = explode("\n", file_get_contents(resource_path('wordlist.txt')));
        $word = '';

        for($i = 0; $i < $length; $i++){
            $word .= $words[array_rand($words)] . ' ';
        }

        return trim($word);
    }
}

if (!function_exists('get_recovery_phrase')){
    function get_recovery_phrase()
    {
        return random_words(8);
    }
}