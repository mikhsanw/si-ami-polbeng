<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Menu;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class Helper
{
    public static function menu($code=null): ?object
    {
        return Menu::where('code', explode(".", $code ?? Route::currentRouteName())[0])->first();
    }

    public static function listFile($path, $extension): array
    {
        $model=[];
        foreach (File::files($path) as $files) {
            if (in_array($files->getExtension(), $extension)) {
                foreach ($extension as $ext) {
                    $name=Arr::first(explode('.', $files->getFilename()));
                    $model[$name]=$name;
                }
            }
        }
        return $model;
    }

    public static function displayDateTime($updatedAt)
    {
        // Parse kolom updated_at menjadi instance Carbon
        $updatedAt = Carbon::parse($updatedAt);

        // Set locale ke Bahasa Indonesia
        $updatedAt->setLocale('id');

        // Format tanggal dan waktu dengan hari dan bulan dalam Bahasa Indonesia
        // $dateTimeTerformat = $updatedAt->isoFormat('dddd, D MMMM Y H:mm:ss');
        $dateTimeTerformat = $updatedAt->isoFormat('dddd, D MMMM Y H:mm:ss');

        return $dateTimeTerformat;
    }

    public static function shortDescription($content, $length)
    {
        $sentence=strip_tags($content);
        if (str_word_count($sentence) > $length) {
            $limit_sentence=implode(" ", array_slice(explode(" ", $sentence), 0, $length))." ...";
        }
        return $limit_sentence ?? $sentence;
    }

    public static function formatSizeUnits($binner)
    {
        if ($binner >= 1073741824) {
            $binner=number_format($binner / 1073741824, 2).' GB';
        } elseif ($binner >= 1048576) {
            $binner=number_format($binner / 1048576, 2).' MB';
        } elseif ($binner >= 1024) {
            $binner=number_format($binner / 1024, 2).' KB';
        } elseif ($binner > 1) {
            $binner=$binner.' bytes';
        } elseif ($binner == 1) {
            $binner=$binner.' byte';
        } else {
            $binner='0 bytes';
        }
        return $binner;
    }

    public static function generateSeoURL($string, $wordLimit = 0){ 
        $separator = '-'; 
         
        if($wordLimit != 0){ 
            $wordArr = explode(' ', $string); 
            $string = implode(' ', array_slice($wordArr, 0, $wordLimit)); 
        } 
     
        $quoteSeparator = preg_quote($separator, '#'); 
     
        $trans = array( 
            '&.+?;'                 => '', 
            '[^\w\d _-]'            => '', 
            '\s+'                   => $separator, 
            '('.$quoteSeparator.')+'=> $separator 
        ); 
     
        $string = strip_tags($string); 
        foreach ($trans as $key => $val){ 
            $string = preg_replace('#'.$key.'#iu', $val, $string); 
        } 
     
        $string = strtolower($string); 
     
        return trim(trim($string, $separator)); 
    }
}