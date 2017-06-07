<?php
/**
 * Created by PhpStorm.
 * User: stephenbarrett
 * Date: 06/06/2017
 * Time: 10:26
 */

session_start();
ini_set("display_errors", 1);
date_default_timezone_set("Europe/London");

/*
 * Access Levels
 */
define("SUBMIT_ACCESS_LEVEL", 2);
define("EDITOR_ACCESS_LEVEL", 3);
define("ADMIN_ACCESS_LEVEL", 5);

$accessLabels = array(
  0 => "Anonymous",
  1 => 'Registered User',
  2 => 'Contributor',
  3 => 'Editor',
  5 => 'Administrator'
);

/*
 * XML and JSON filepaths
 */
define("USERFILE_PATH", "../lexicopia/lexicopia-xml/gd/terminology/user-generated.xml");
define("ENGLISH_INDEX_PATH", "../lexicopia/lexicopia-cache/gd/english-index.json");
define("TARGET_INDEX_PATH", "../lexicopia/lexicopia-cache/gd/target-index.json");

