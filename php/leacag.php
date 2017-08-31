<?php

$action = $_GET["action"];

switch ($action) {

  case "getEnglish":
    $query = $_GET["term"];
    echo getEnglish($query);
    break;

  case "getGaelic":
    $query = $_GET["term"];
    echo getGaelic($query);
    break;

  case "getRandom":
    echo getRandom();
    break;
}

function getEnglish($q) {
  $englishIndex = file_get_contents("../../lexicopia/gd/cache/english-index.json");
  $results = array();
  $json = json_decode($englishIndex, true);
  foreach ($json["english_index"] as $item) {
    if (strtolower(substr($item["en"], 0, strlen($q))) == strtolower($q)) {
      $results[] = array("id"=>$item["gds"][0], "value"=>$item["en"], "label"=>$item["en"], "item"=>$item);
    }
  }
  return json_encode($results);
}

function getGaelic($q) {
  $aiQ = getAccentInsensitive($q);
  $gaelicIndex = file_get_contents("../../lexicopia/gd/cache/target-index.json");
  $results = array();
  $json = json_decode($gaelicIndex, true);
  $pattern = "/^" . $aiQ . ".*/ui";
  foreach ($json["target_index"] as $item) {
    if (preg_match($pattern, $item["word"])) {
      $results[] = array("id"=>$item["id"], "value"=>$item["word"], "label"=>$item["word"], "item"=>$item);
    }
  }
  return json_encode($results);
}

function getRandom() {
  $gaelicIndex = file_get_contents("../../lexicopia/gd/cache/target-index.json");
  $json = json_decode($gaelicIndex, true);
  $randomKey = array_rand($json["target_index"]);
  $randomEntry = $json["target_index"][$randomKey];
  return json_encode(array("randomEntry"=>$randomEntry));

}

function getAccentInsensitive($text) {
  $regExp = "";
  $accentMappedChars = array(
    "aàá", "eèé", "iìí", "oòó", "uùú"
  );
  foreach (str_split_unicode($text) as $char) {
    $replaced = false;
    foreach ($accentMappedChars as $accentMap) {
      if (stristr($accentMap, $char)) {
        $regExp .= "[{$accentMap}]";
        $replaced = true;
      }
    }
    if ($replaced == false)
      $regExp .= $char;
  }
  return $regExp;
}

function str_split_unicode($str, $l = 0) {
  if ($l > 0) {
    $ret = array();
    $len = mb_strlen($str, "UTF-8");
    for ($i = 0; $i < $len; $i += $l) {
      $ret[] = mb_substr($str, $i, $l, "UTF-8");
    }
    return $ret;
  }
  return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
}
