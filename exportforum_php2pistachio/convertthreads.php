<?php
require_once("ganon.php");

require 'vendor/autoload.php';

use zz\Html\HTMLMinify;

$forum = "Google Calendar - Foro de ayuda";
$original = "calendar-es_html";
$destination = "calendar-es_pistachio";

function minify($html) {
  return HTMLMinify::minify($html, array("emptyElementAddSlash" => HTMLMinify::DOCTYPE_HTML5));
}

// Function array_orderby developed by jimpoz at jimpoz dot com, and available at http://php.net/manual/en/function.array-multisort.php#100534
function array_orderby() {
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row)
                $tmp[$key] = $row[$field];
            $args[$n] = $tmp;
            }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

// Create folder if it doesn't exist.
if (!file_exists($destination)) {
  mkdir($destination);
}

$indextemplate = file_get_contents(dirname(__FILE__)."/index.html");
$threadtemplate = file_get_contents(dirname(__FILE__)."/thread.html");
$replytemplate = file_get_contents(dirname(__FILE__)."/reply.html");

/**
  * COPY NECESSARY FILES
  */

copy(dirname(__FILE__)."/discussions.css", $destination."/discussions.css");
copy(dirname(__FILE__)."/forum.css", $destination."/forum.css");
copy(dirname(__FILE__)."/thread.css", $destination."/thread.css");
copy(dirname(__FILE__)."/google.svg", $destination."/google.svg");

/**
  * CONVERT EACH THREAD
  */

$filestoconvert = scandir($original);

$filestoconvert = array_diff($filestoconvert, array('.', '..', 'index.html'));

$threads = array();

foreach ($filestoconvert as $thread) {
  echo "Working with ".$thread."\n";

  $dom = file_get_dom($original."/".$thread);

  $title = trim($dom("h2", 0)->getPlainText());

  $messages = array();

  $trs = $dom("body > table > tr");

  foreach ($trs as $tr) {
    $subject = $tr(".subject", 0);
    $author = $tr("td.author", 0);
    $lastPostDate = $tr("td.lastPostDate", 0);
    $snippet = $tr("td.snippet > div", 0);
    $messages[] = array(
      "subject" => ($subject ? trim($subject->getPlainText()) : ""),
      "author" => ($author ? trim($author->getPlainText()) : ""),
      "lastPostDate" => ($lastPostDate ? trim($lastPostDate->getPlainText()) : ""),
      "snippet" => ($snippet ? trim($snippet->html()) : ($tr("td.snippet", 0) ? trim($tr("td.snippet", 0)->html()) : ""))
    );
  }

  $op = array_shift($messages);

  $replies = "";

  foreach ($messages as $message) {
    $replies .= str_replace(["{{username}}", "{{timestamp}}", "{{msg}}"], [$message["author"], $message["lastPostDate"], $message["snippet"]], $replytemplate)."\n";
  }

  $answercount = count($messages);

  // Create copy of templates and fill data of the thread:
  $html = $threadtemplate;

  $html = str_replace(["{{forum}}", "{{title}}", "{{opusername}}", "{{optimestamp}}", "{{opmessage}}", "{{answercount}}", "{{allreplies}}"], [$forum, $title, $op["author"], $op["lastPostDate"], $op["snippet"], $answercount, $replies], $html);

  $minifiedhtml = minify($html);

  file_put_contents($destination."/".$thread, $minifiedhtml);

  $last = array_pop($messages);

  if ($last["lastPostDate"]) {
		$explode = explode(" ", $last["lastPostDate"]);
    $date = explode("/", $explode[0]);
    $hour = explode(":", $explode[1]);
    $timestamplastmodified = mktime($hour[0], $hour[1], 0, $date[1], $date[0], $date[2]);
	} elseif ($op["lastPostDate"]) {
    $explode = explode(" ", $op["lastPostDate"]);
    $date = explode("/", $explode[0]);
    $hour = explode(":", $explode[1]);
    $timestamplastmodified = mktime($hour[0], $hour[1], 0, $date[1], $date[0], $date[2]);
	} else {
    $timestamplastmodified = mktime(0, 0, 0, 1, 1, 1970);
  }

  $threads[] = array(
    "thread" => $thread,
    "title" => $title,
    "author" => $op["author"],
    "lastmodified" => $last["lastPostDate"],
    "published" => $op["lastPostDate"],
    "replies" => $answercount,
    "timestamplastmodified" => $timestamplastmodified
  );
}

// Order threads by last modified date

$threads = array_orderby($threads, "timestamplastmodified", SORT_DESC);

/**
  * CREATE INDEX PAGE
  */

$urls = "";

foreach ($threads as $thread) {
  $urls .= "<tr><td><a href=\"".$thread["thread"]."\">".$thread["title"]."</a></td><td>".$thread["published"]."<br><span class=\"userlink\">".$thread["author"]."</span></td><td>".$thread["replies"]."</td><td>".$thread["lastmodified"]."</td></tr>";
}

$index = str_replace(["{{forum}}", "{{threads}}"], [$forum, $urls], $indextemplate);

file_put_contents($destination."/index.html", minify($index));
