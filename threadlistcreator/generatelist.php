<?php
require_once("ganon.php");

$baseurl = "https://productforums.google.com/";
$categoriesurl = $baseurl."forum/print/categories/";

if ($argc < 3) {
  die("USAGE:\n======\nRun 'php generatelist.php {forum_name} {file}',\nwhere {forum_name} is the name of the forum that\nyou want to export and {file} is\nthe name of the file where you want to save\nthe thread list.\n");
}

echo "Welcome to the thread list generator!\n\n";

$forum = $argv[1];
$file = $argv[2];
$json = [
  "forum" => $forum,
  "threads" => []
];
$start = 1;

while (true) {
  echo "Extracting topics ".$start." through ".($start + 99)."...\n";

  $html = file_get_contents($categoriesurl.urlencode($forum)."%5B".$start."-".($start + 99)."%5D");
  
  $dom = str_get_dom($html);
  $rows = $dom("table tr");

  if (!count($rows)) break;

  foreach ($rows as $row) {
    $url = $row("td.subject a", 0)->href;
    $urlcomponents = explode("/", $url);
    $json["threads"][] = $urlcomponents[count($urlcomponents) - 1];
  }

  $start += 100;
}

echo "We're done!\n";

file_put_contents($file, json_encode($json));
