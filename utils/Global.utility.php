<?php
namespace euu_boilerplate;

if (!defined('_PS_VERSION_')) exit;

function str_replace_limit(string $search, string $replace,string $subject,int $limit, &$count = null) : string {
    $count = 0;
    if ($limit <= 0) return $subject;
    $occurrences = substr_count($subject, $search);
    if ($occurrences === 0) return $subject;
    else if ($occurrences <= $limit) return str_replace($search, $replace, $subject, $count);
    //Do limited replace
    $position = 0;
    //Iterate through occurrences until we get to the last occurrence of $search we're going to replace
    for ($i = 0; $i < $limit; $i++)
        $position = strpos($subject, $search, $position) + strlen($search);
    $substring = substr($subject, 0, $position + 1);
    $substring = str_replace($search, $replace, $substring, $count);
    return substr_replace($subject, $substring, 0, $position + 1);
}


function sql_replace(string $sql,array $values){
    for($i = 0, $iMax = count($values); $i < $iMax; $i++){
        $sql = str_replace_limit('?', !is_int($values[$i]) ? "'". $values[$i] ."'" : $values[$i],$sql,1);
    }
    return $sql;
}