<?php

function getCSVFileData($filename, $delimiter) {
    $data = array();
    if (file_exists($filename)) {
        $fp = fopen($filename, 'r');

        while (!feof($fp)) {
            $line = fgets($fp, 2048);

            $columns = str_getcsv($line, $delimiter);

            $data[] = $columns;

        }

        fclose($fp);
    }

    return $data;
}

function getFormatedDate($from_format, $str_datetime, $to_format) {
    $datetime = DateTime::createFromFormat($from_format, $str_datetime);
    if($datetime)
        return $datetime->format($to_format);
    else
        false;
}

function MySqlDate($str_date = '') {
    if($str_date == '') {
        $str_date = date(STD_DATE);
    }
    return getFormatedDate(STD_DATE, $str_date, MYSQL_DATE);
}

function stdDate($str_date = '') {
    if($str_date == '') {
        $str_date = date(MYSQL_DATE);
    }
    return getFormatedDate(MYSQL_DATE, $str_date, STD_DATE);
}

function MySqlDateTime($str_datetime) {
    return getFormatedDate(STD_DATETIME, $str_datetime, MYSQL_DATETIME);
}

function stdDateTime($str_datetime) {
    return getFormatedDate(MYSQL_DATETIME, $str_datetime, STD_DATETIME);
}

function validateDate($format, $date)
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
//function getFilterString($filter) {
//    $cond = array();
//    if(isset($filter['RAW']) && $filter['RAW']) {
//        return $filter['RAW'];
//    } else {
//        if(isset($filter['EQ'])) {
//            $cond = array_merge($cond,getFilterEQ($filter['EQ']));
//        }
//        if(isset($filter['NEQ'])) {
//            $cond = array_merge($cond,getFilterNEQ($filter['NEQ']));
//        }
//        if(isset($filter['LT'])) {
//            $cond = array_merge($cond,getFilterLT($filter['LT']));
//        }
//        if(isset($filter['LTE'])) {
//            $cond = array_merge($cond,getFilterLTE($filter['LTE']));
//        }
//        if(isset($filter['GT'])) {
//            $cond = array_merge($cond,getFilterGT($filter['GT']));
//        }
//        if(isset($filter['GTE'])) {
//            $cond = array_merge($cond,getFilterGTE($filter['GTE']));
//        }
//        if(isset($filter['LKB'])) {
//            $cond = array_merge($cond,getFilterLKB($filter['LKB']));
//        }
//        if(isset($filter['LKF'])) {
//            $cond = array_merge($cond,getFilterLKF($filter['LKF']));
//        }
//        if(isset($filter['LKE'])) {
//            $cond = array_merge($cond,getFilterLKE($filter['LKE']));
//        }
//        return implode(' AND ', $cond);
//    }
//}
//
//function getFilterEQ($data) {
//    $cond = array();
//    foreach($data as $column => $value) {
//        if(!empty($value)) {
//            $cond[] = $column . "='" . addslashes($value) . "'";
//        }
//    }
//    return $cond;
//}
//
//function getFilterNEQ($data) {
//    $cond = array();
//    foreach($data as $column => $value) {
//        if(!empty($value)) {
//            $cond[] = $column . "!='" . addslashes($value) . "'";
//        }
//    }
//    return $cond;
//}
//
//function getFilterGT($data) {
//    $cond = array();
//    foreach($data as $column => $value) {
//        if(!empty($value)) {
//            $cond[] = $column . ">'" . addslashes($value) . "'";
//        }
//    }
//    return $cond;
//}
//
//function getFilterGTE($data) {
//    $cond = array();
//    foreach($data as $column => $value) {
//        if(!empty($value)) {
//            $cond[] = $column . ">='" . addslashes($value) . "'";
//        }
//    }
//    return $cond;
//}
//
//function getFilterLT($data) {
//    $cond = array();
//    foreach($data as $column => $value) {
//        if(!empty($value)) {
//            $cond[] = $column . " < '" . addslashes($value) . "'";
//        }
//    }
//    return $cond;
//}
//
//function getFilterLTE($data) {
//    $cond = array();
//    foreach($data as $column => $value) {
//        if(!empty($value)) {
//            $cond[] = $column . "<='" . addslashes($value) . "'";
//        }
//    }
//    return $cond;
//}
//
//function getFilterLKB($data) {
//    $cond = array();
//    foreach($data as $column => $value) {
//        if(!empty($value)) {
//            $cond[] = $column . " LIKE '%" . addslashes($value) . "%'";
//        }
//    }
//    return $cond;
//}
//
//function getFilterLKF($data) {
//    $cond = array();
//    foreach($data as $column => $value) {
//        if(!empty($value)) {
//            $cond[] = $column . " LIKE '%" . addslashes($value) . "'";
//        }
//    }
//    return $cond;
//}
//
//function getFilterLKE($data) {
//    $cond = array();
//    foreach($data as $column => $value) {
//        if(!empty($value)) {
//            $cond[] = $column . " LIKE '" . addslashes($value) . "%'";
//        }
//    }
//    return $cond;
//}
//
//function getCOADisplayName($title,$delimeter,$codes) {
//    $delimeter1 = (is_array($delimeter)?$delimeter[0]:$delimeter);
//    $delimeter2 = (is_array($delimeter) && isset($delimeter[1])?$delimeter[1]:' ');
//    $strCode = implode($delimeter1,$codes);
//    return $strCode . $delimeter2 . $title;
//}
//
//
////function hex2dec
////returns an associative array (keys: R,G,B) from
////a hex html code (e.g. #3FE5AA)
//function hex2dec($couleur = "#000000"){
//    $R = substr($couleur, 1, 2);
//    $rouge = hexdec($R);
//    $V = substr($couleur, 3, 2);
//    $vert = hexdec($V);
//    $B = substr($couleur, 5, 2);
//    $bleu = hexdec($B);
//    $tbl_couleur = array();
//    $tbl_couleur['R']=$rouge;
//    $tbl_couleur['V']=$vert;
//    $tbl_couleur['B']=$bleu;
//    return $tbl_couleur;
//}
//
////conversion pixel -> millimeter at 72 dpi
//function px2mm($px){
//    return $px*25.4/72;
//}
//
//function txtentities($html){
//    $trans = get_html_translation_table(HTML_ENTITIES);
//    $trans = array_flip($trans);
//    return strtr($html, $trans);
//}

function Number2Words($num) {
    //$num="123,456.78";
    $number = parseNumber($num);
    //d([$num, $number], true);
    $hyphen      = '-';
    $conjunction = ' And ';
    $separator   = '  ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = array(
        0                   => 'Zero',
        00                  => 'Zero',
        1                   => 'One',
        2                   => 'Two',
        3                   => 'Three',
        4                   => 'Four',
        5                   => 'Five',
        6                   => 'Six',
        7                   => 'Seven',
        8                   => 'Eight',
        9                   => 'Nine',
        10                  => 'Ten',
        11                  => 'Eleven',
        12                  => 'Twelve',
        13                  => 'Thirteen',
        14                  => 'Fourteen',
        15                  => 'Fifteen',
        16                  => 'Sixteen',
        17                  => 'Seventeen',
        18                  => 'Eighteen',
        19                  => 'Nineteen',
        20                  => 'Twenty',
        30                  => 'Thirty',
        40                  => 'Forty',
        50                  => 'Fifty',
        60                  => 'Sixty',
        70                  => 'Seventy',
        80                  => 'Eighty',
        90                  => 'Ninety',
        100                 => 'Hundred',
        1000                => 'Thousand',
        1000000             => 'Million',
        1000000000          => 'Billion',
        1000000000000       => 'Trillion',
        1000000000000000    => 'Quadrillion',
        1000000000000000000 => 'Quintillion'
    );

    if (!is_numeric($number)) {
        return false;
    }
    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . Number2Words(abs($number));

    }

    $string = $fraction = null;
    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . Number2Words($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = Number2Words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= Number2Words($remainder);
            }
            break;
    }

    if (null !== $fraction && is_numeric($fraction) && $fraction>0) {
        $string .= ' Rupees And ';
        // d($fraction,true);
        $arrZeros= array();
        $index=0;
        for($i=strlen($fraction);$i>=0;$i--){
            for($times=$i-1;$times>=1;$times--){
                $arrZeros[$index]=$arrZeros[$index].'0';
            }
            $index++;
        }
        
        $index=0;
        $words = array();
        foreach (str_split((string) $fraction) as $number) {
            if($dictionary[$number.$arrZeros[$index]]!="Zero"){
                $words[] = $dictionary[$number.$arrZeros[$index]];
            }
            $index++;
        }
        $string .= implode(' ', $words);

        $string .= ' Paisa';
    }

    // $string .= ' Rupees '.$dictionary[$fraction].' ';
    return $string;
}

function parseNumber($str_number) {
    return floatval(str_replace(",","",$str_number));
}

function getBrowserLanguage($browser_language, $available_languages, $default_language) {
    $langs = array();

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        // break up string into pieces (languages and q factors)
        preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $browser_language, $lang_parse);

        if (count($lang_parse[1])) {
            // create a list like "en" => 0.8
            $langs = array_combine($lang_parse[1], $lang_parse[4]);

            // set default to 1 for any without q factor
            foreach ($langs as $lang => $val) {
                if ($val === '') $langs[$lang] = 1;
            }

            // sort list based on value
            arsort($langs, SORT_NUMERIC);
        }
    }

    $lang_code = "";
    foreach($langs as $lang) {
        if(in_array($lang, $available_languages)) {
            $lang_code = $lang;
        }
    }

    return ($lang_code==""?$default_language:$lang_code);
}

function getTimeZoneList() {
    $zones = timezone_identifiers_list();

    foreach ($zones as $zone)
    {
        $zone = explode('/', $zone); // 0 => Continent, 1 => City

        // Only use "friendly" continent names
        if (in_array($zone[0],array('Africa','America','Antarctica','Arctic','Asia','Atlantic','Australia','Europe','Indian','Pacific')))
        {
            if (isset($zone[1]) != '')
            {
                $locations[$zone[0]. '/' . $zone[1]] = str_replace('_', ' ', $zone[0]. '/' . $zone[1]); // Creates array(DateTimeZone => 'Friendly name')
            }
        }
    }

    return $locations;
}

function splitString($string,$length) {
    $words = explode(' ', $string);

    $maxLineLength = $length;

    $currentLength = 0;
    $index = 0;
    $output = array();

    foreach ($words as $word) {
        // +1 because the word will receive back the space in the end that it loses in explode()
        $wordLength = strlen($word) + 1;

        if (($currentLength + $wordLength) <= $maxLineLength) {
            $output[$index] .= $word . ' ';
            $currentLength += $wordLength;
        } else {
            $index += 1;
            $currentLength = $wordLength;
            $output[$index] = $word . ' ';
        }
    }

    return $output;
}

function multili_var_length_check($vars, $length=0){
    $check = 0;
    foreach($vars as $var){
        if(isset($var[0]) && isset($var[1])){
            if(strlen($var[0])>$var[1]) $check++;
        } else {
            if(strlen($var[0])>$length) $check++;
        }
    }

    return ($check==0) ? true: false;
}

function max_array_index_count(...$vars){

    $count = [];
    foreach($vars as $var){
        $count[] = count($var);
    }
    return max($count);
}

?>