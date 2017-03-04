<?
set_time_limit(240);
ini_set("default_socket_timeout", 120);

if(isset($_GET["url"])) {
   function eschtml($str) {
      return str_replace(array(">", "<", "\""), array("&gt;",  "&lt;", "&quot;"), $str);
   }

   function remtags($str) {
      $str = html_entity_decode($str, ENT_COMPAT, "UTF-8");
      return strip_tags($str);
   }

   $url = $_GET["url"];
   $detail = (isset($_GET["detail"]) ? intval($_GET["detail"]) : 2147483647);
   $limit = (isset($_GET["limit"]) ? $_GET["limit"] : 2147483647);
   $striphtml = (isset($_GET["striphtml"]) ? ($_GET["striphtml"] == "true") : false);
   $showtitle = (isset($_GET["showtitle"]) ? ($_GET["showtitle"] == "true") : true);
   $showtitledesc = (isset($_GET["showtitledesc"]) ? ($_GET["showtitledesc"] == "true") : false);
   $titleprefix = (isset($_GET["titleprefix"]) ? $_GET["titleprefix"] : "");
   $titlereplacement = (isset($_GET["titlereplacement"]) ? $_GET["titlereplacement"] : "");
   $titledescprefix = (isset($_GET["titledescprefix"]) ? $_GET["titledescprefix"] : "");
   $itemtitleprefix = (isset($_GET["itemtitleprefix"]) ? $_GET["itemtitleprefix"] : "");
   $itemdescprefix = (isset($_GET["itemdescprefix"]) ? $_GET["itemdescprefix"] : "");
   $showicon = (isset($_GET["showicon"]) ? ($_GET["showicon"] == "true") : false);
   $showempty = (isset($_GET["showempty"]) ? ($_GET["showempty"] == "true") : false);
   $type = (isset($_GET["type"]) ? $_GET["type"] : "php");
   $fixbugs = (isset($_GET["fixbugs"]) ? ($_GET["fixbugs"] == "true") : false);
   $forceutf8 = (isset($_GET["forceutf8"]) ? ($_GET["forceutf8"] == "true") : false);

   if($type == "html") {
      header("Content-Type: text/html; charset=utf-8");
      echo "<html>\n<head>\n<title></title>\n</head>\n<body>\n";
   }
   else if($type == "js") {
      header("Content-Type: text/javascript; charset=utf-8");
      ob_start();
   }

   $headers = "Connection: close\r\n".
     "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36\r\n".
     "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n".
     "Accept-Language: en-us,en;q=0.5\r\n".
     "Referer: /\r\n";

   $http_context = array(
     'protocol_version' => 1.1,
     'method' => 'GET',
     'header' => $headers
   );
   $feedtext = file_get_contents($url, false, stream_context_create(array('http' => $http_context)));
   $feedtext = trim($feedtext);

   if($fixbugs) {
      $feedtext = str_replace("& ", " &amp; ", $feedtext);

      $feedtext = str_replace("&x80;", "&euro;", $feedtext);
      $feedtext = str_replace("&x81;", "&lsquo;", $feedtext);
      $feedtext = str_replace("&x85;", "&hellip;", $feedtext);
      $feedtext = str_replace("&x86;", "&dagger;", $feedtext);
      $feedtext = str_replace("&x87;", "&Dagger;", $feedtext);
      $feedtext = str_replace("&x88;", "&circ;", $feedtext);
      $feedtext = str_replace("&x89;", "&permil;", $feedtext);
      $feedtext = str_replace("&x8A;", "&Scaron;", $feedtext);
      $feedtext = str_replace("&x8B;", "&lsaquo;", $feedtext);
      $feedtext = str_replace("&x8C;", "&OElig;", $feedtext);
      $feedtext = str_replace("&x8E", "", $feedtext);
      $feedtext = str_replace("&x91;", "&lsquo;", $feedtext);
      $feedtext = str_replace("&x92;", "&rsquo;", $feedtext);
      $feedtext = str_replace("&x93;", "&ldquo;", $feedtext);
      $feedtext = str_replace("&x94;", "&rdquo;", $feedtext);
      $feedtext = str_replace("&x95;", "&bull;", $feedtext);
      $feedtext = str_replace("&x96;", "-", $feedtext);
      $feedtext = str_replace("&x97;", "&mdash;", $feedtext);
      $feedtext = str_replace("&x98;", "&tilde;", $feedtext);
      $feedtext = str_replace("&x99;", "&trade;", $feedtext);
      $feedtext = str_replace("&x9A;", "&scaron;", $feedtext);
      $feedtext = str_replace("&x9B;", "&rsaquo;", $feedtext);
      $feedtext = str_replace("&x9C;", "&eolig;", $feedtext);
      $feedtext = str_replace("&x9E;", "", $feedtext);
      $feedtext = str_replace("&x9F;", "&Yuml;", $feedtext);
   }

   if($forceutf8) {
      $feedtext = preg_replace("/<\?xml(.*?)encoding=['\"].*?['\"](.*?)\?>/m", "<?xml$1encoding=\"utf-8\"$2?>", $feedtext);
   }

   $doc = new DOMDocument();
   $doc->loadXML($feedtext);

   function search($tag, $context, $single = true) {
     $nodes = $context->getElementsByTagName($tag);

     if($nodes->length > 0) return $single ? $nodes->item(0) : $nodes;
     return NULL;
   }

   if($showtitle == true) {
      $channel = search("channel", $doc);

      $title = search("title", $channel);
      $title = eschtml((isset($title) ? $titleprefix.$title->textContent : "(No feed title)"));
      if($titlereplacement) $title = $titlereplacement;
      if($striphtml) $title = remtags($title);

      $link = search("link", $channel);
      $link = ($link ? (isset($eschtml) ? eschtml($link->textContent) : $link->textContent) : "");
      if($link != "") $title = "<a href=\"$link\" target=\"_blank\">$title</a>";
      if($striphtml) $link = remtags($link);

      $desc = search("description", $channel);
      $desc = eschtml(isset($desc) ? $desc->textContent : "");
      if($striphtml) $desc = remtags($desc);

      $image = search("url", $channel);
      $image = (isset($image) ? $image->textContent : "");

      if($showicon && $image != "") $title = "<img class=\"feed-title-image\" src=\"$image\">$title";

      if($showempty || (!$showempty && $title != "")) echo "<h3 class=\"feed-title\">$title</h3>\n";
      if($showtitledesc && ($showempty || (!$showempty && $desc != ""))) echo "<p class=\"feed-desc\">$titledescprefix$desc</p>\n";
   }

   $items = search("item", $doc, false);

   foreach($items as $i => $item) {
      if($i == $limit) break;

      $title = search("title", $item);
      $title = (isset($title) ? eschtml($title->textContent) : "(No title)");

      $link = search("link", $item);
      $link = (isset($link) ? eschtml($link->textContent) : "");
      if($link != "") $title = "<a href=\"$link\" target=\"_blank\">$title</a>";

      $desc = search("description", $item);
      $desc = (isset($desc) ? $desc->textContent : "");
      if($striphtml) $desc = remtags($desc);

      if($showempty || (!$showempty && $title != "")) echo "<h4 class=\"feed-item-title\">$itemtitleprefix$title</h4>\n";
      if(($detail > 0) && ($showempty || (!$showempty && $desc != ""))) {
         $words = explode(" ", $desc);
         if(count($words) > $detail) {
            $words = array_slice($words, 0, $detail);
             $desc = implode(" ", $words)."...";
         }
         echo "<p class=\"feed-item-desc\">$itemdescprefix$desc</p>\n";
      }
   }

   echo "<div class=\"rss2html-note\" style=\"float: right;\"><a href=\"https://rss.bloople.net/\" style=\"color: #000000;\">Powered by rss2html</a></div>\n<div class=\"rss2html-note-clear\" style=\"clear: right; height: 0;\"></div>\n";

   if($type == "html") {
      echo "</body>\n</html>";
   }
   else if($type == "js") {
      $text = ob_get_contents();
      ob_end_clean();

      $text = str_replace("\n", "", str_replace("\"", "\\\"", $text));

?>
var container = document.createElement("div");
container.innerHTML = "<?= $text ?>";
var nodes = [].slice.call(container.childNodes);

var script = document.scripts[document.scripts.length - 1];
var parent = script.parentNode;

while(nodes.length > 0) parent.insertBefore(nodes.shift(), script);

parent.removeChild(script);
<?
   }
}
else {
?>
<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>RSS 2 HTML</title>
    <link rel="StyleSheet" type="text/css" href="style.css">
    <script type="text/javascript" src="scripts/parse.js"></script>

    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  </head>
  <body id="body">
    <header>
      <h1>RSS 2 HTML</h1>
    </header>

    <div id="mainbody">
        <h3>Welcome to RSS 2 HTML!</h3>
        <p class="mast">This page offers an easy way to embed RSS feeds in HTML webpages. One line of code in your webpage, and easily-styled HTML will be generated, with no advertisements or other restrictions.</p>
        <p class="mast">Use the form below to generate the code to include in your webpage:</p>

        <form id="form">
          <table>
             <tr>
                <td class="l">URL of RSS feed:</td>
                <td><input class="text" type="text" name="url" id="url"></td>
             </tr>
             <tr class="advanced" id="advtitle">
                <td class="l"></td>
                <td>
                  <label for="showtitle">
                    <input type="checkbox" name="showtitle" id="showtitle">
                    Show feed title
                  </label>
                </td>
             </tr>
             <tr class="advanced" id="advicon">
                <td class="l"></td>
                <td>
                  <label for="showicon">
                    <input type="checkbox" name="showicon" id="showicon">
                    Show feed icon
                  </label>
                </td>
             </tr>
             <tr>
                <td class="l">Length of feed item descriptions:</td>
                <td>
                  <label for="detailhide">
                    <input type="radio" name="detail" id="detailhide" value="-1">
                    Hide descriptions
                  </label>
                  <label for="detailshow">
                    <input type="radio" name="detail" id="detailshow" value="0">
                    Show up to <input class="num" type="text" id="detailnum"> words
                  </label>
                  <label id="detailnolimit">
                    <input type="radio" name="detail" id="detailnolimit" value="1">
                    Don't limit length
                  </label>
                </td>
             </tr>
             <tr class="advanced" id="advshow">
                <td class="l"></td>
                <td>
                  <label for="showempty">
                    <input type="checkbox" name="showempty" id="showempty">
                    Output HTML for empty feed titles and descriptions
                  </label>
                </td>
             </tr>
             <tr class="advanced" id="advstrip">
                <td class="l"></td>
                <td>
                  <label for="striphtml">
                    <input type="checkbox" name="striphtml" id="striphtml">
                    Strip HTML from feed item descriptions
                  </label>
                </td>
             </tr>
             <tr class="advanced" id="advforce">
                <td class="l"></td>
                <td>
                  <label for="forceutf8">
                    <input type="checkbox" name="forceutf8" id="forceutf8">
                    Assume RSS feed is UTF-8, ignoring XML prolog
                  </label>
                </td>
             </tr>
             <tr class="advanced" id="advfix">
                <td class="l"></td>
                <td>
                  <label for="fixbugs">
                    <input type="checkbox" name="fixbugs" id="fixbugs">
                    Attempt to convert Windows-1252 -&gt; UTF-8
                  </label>
                </td>
             </tr>
             <tr>
                <td class="l"></td>
                <td>
                  <label for="limit">
                    <input type="checkbox" name="limit" id="limit">
                    Limit to the first <input class="num" type="text" id="limitnum"> items in the feed
                  </label>
                </td>
             </tr>
             <tr class="advanced" id="advembed">
                <td class="l">How should the feed be embedded in your webpage:</td>
                <td>
                  <label for="codejs">
                    <input type="radio" name="codegen" value="js" id="codejs" checked="checked">
                    JavaScript code - this option is best if you want to style the embedded feed.
                  </label>
                  <label for="codehtml">
                    <input type="radio" name="codegen" id="codehtml" value="html">
                    &lt;iframe&gt; embed - this option is best if the people using your website don't have JavaScript enabled.
                  </label>
                  <label for="codephp">
                    <input type="radio" name="codegen" id="codephp" value="php">
                    PHP code - this option will only work if your webpage is already coded in PHP.
                  </label>
                </td>
             </tr>
             <tr class="results">
                <td class="l">Embed this code in your webpage:</td>
                <td><textarea id="codeout"></textarea></td>
             </tr>
             <tr class="results">
               <td class="l">Live example:</td>
               <td>
                 <iframe id="live-example"></iframe>
               </td>
             </tr>
             <tr>
               <td colspan="2" class="actions">
                 <input type="button" id="showopt" value="More options" onclick="showAdvanced();">
                 <input type="submit" id="submit" value="Submit &amp; get code">
               </td>
             </tr>
          </table>
       </form>

       <h3>Styling the output</h3>
       <p>
         The HTML generated by this code contains CSS hooks so you can style the output in your stylesheet.<br>
         The title of the feed is displayed in a <code>&lt;h3&gt;</code> tag, with the class <code>feed-title</code>.<br>
         The feed description is displayed in a <code>&lt;p&gt;</code> tag with class <code>feed-desc</code>.<br>
         The feed icon is displayed in an <code>&lt;img&gt;</code> tag with class <code>feed-title-image</code>.<br>
         Each feed item title is displayed in a <code>&lt;h4&gt;</code> tag with class <code>feed-item-title</code>.<br>
         Each feed item description is displayed in a <code>&lt;p&gt;</code> tag with class <code>feed-item-desc</code>.
       </p>
       <p>
         Note that this code does not generate HTML for empty feed titles, descriptions, etc.<br>
         If you want empty HTML to be generated, click "more options" and tick the "Output HTML for empty..." checkbox.
       </p>

       <hr>
       <div class="footer">&copy; Brenton Fletcher. Comments? e-mail me: <a href="mailto:i@bloople.net">i@bloople.net</a>.</div>
    </div>
  </body>
</html>
<?
}
?>
