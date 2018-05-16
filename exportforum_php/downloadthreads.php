<?php
require_once("ganon.php");

$input = file_get_contents("php://stdin");

$json = json_decode($input, true);

$folder = "./".utf8_encode($json["forum"])."_html";

// Create folder if it doesn't exist.
if (!file_exists($folder)) {
  mkdir($folder);
}

$explode = explode("+", $json["forum"]);

if (isset($explode[1])) {
  $topic = "private-topic";
} else {
  $topic = "topic";
}

/**
  * SAVE EACH THREAD
  */

$threads = array();

foreach ($json["threads"] as $i => $thread) {
  echo "Working with ".$thread." (".$i.")\n";

  $filename = utf8_encode($thread).".html";

  if (file_exists($folder."/".$filename)) {
    $dom = file_get_dom($folder."/".$filename);
  } else {
    $url = "https://productforums.google.com/forum/print/".$topic."/".$json["forum"]."/".$thread;

    print($url);

    $content = file_get_contents($url);

    file_put_contents($folder."/".$filename, $content);

    $dom = str_get_dom($content);
  }

  $threads[] = array(
    "id" => $thread,
    "title" => (trim($dom("h2", 0)->getPlainText()))
  );
}

/**
  * CREATE INDEX PAGE
  */

$urls = "";

foreach ($threads as $thread) {
  $urls .= "<li><a href='".utf8_encode($thread["id"]).".html'>".$thread["title"]."</a></li>";
}

$index = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{forum}}</title><style>body {font-family: Tahoma, Geneva, sans-serif; font-size: 85%; background-color: #FAFAFA;} h1 {font-size: 150%;}</style></head><body><h1>{{forum}}</h1><ul>{{urls}}</ul></body></html>';

$index = str_replace("{{forum}}", $json["forum"], $index);
$index = str_replace("{{urls}}", $urls, $index);

file_put_contents($folder."/index.html", $index);
