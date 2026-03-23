<?php


function hex2float($strHex){
    $bin = hex2bin($strHex);
    $array = unpack("Gnum", $bin);
    return $array['num'];
}

echo hex2float("451fea73");



?>

