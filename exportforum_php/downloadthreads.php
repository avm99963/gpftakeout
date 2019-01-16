<?php
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

$listfile = $folder."/threads.txt";
$threads = fopen($listfile, 'w');

/**
  * SAVE EACH THREAD
  */

foreach ($json["threads"] as $i => $thread) {
  echo "Working with ".$thread." (".$i.")\n";

  $filename = utf8_encode($thread).".html";

  if (file_exists($folder."/".$filename)) {
    echo "Skipping file download...\n";
    $content = file_get_contents($folder."/".$filename);
    echo "Loaded content\n";
  } else {
    $url = "https://productforums.google.com/forum/print/".$topic."/".$json["forum"]."/".$thread."?hl=es";

    $content = file_get_contents($url);

    file_put_contents($folder."/".$filename, $content);
  }

  preg_match('/<h2[^<]*>(.+)<\/h2>/', $content, $matches);
  if (!isset($matches[1]))
    die("Didn't find the title");

  fwrite($threads, $matches[1]."\n");
  echo "Ok\n";
}

echo "Done.\n\nCreating index page...\n";

/**
  * CREATE INDEX PAGE
  */

fclose($threads);
$threads_read = fopen($listfile, 'r');
$index = fopen($folder."/index.html", 'w');

$index_txt = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{forum}}</title><style>body {font-family: Tahoma, Geneva, sans-serif; font-size: 85%; background-color: #FAFAFA;} h1 {font-size: 150%;}</style></head><body><h1>{{forum}}</h1><ul>';

$index_txt = str_replace("{{forum}}", $json["forum"], $index_txt);

fwrite($index, $index_txt);

foreach ($json["threads"] as $id) {
  $id = trim($id);
  $title = trim(fgets($threads_read));
  fwrite($index, "<li><a href='".utf8_encode($id).".html'>".$title."</a></li>");
  echo $id."\n";
}

fwrite($index, '</ul></body></html>');

echo "Done\n";
