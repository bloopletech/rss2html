<?
set_time_limit(240);
ini_set("default_socket_timeout", 120);
#ini_set("display_errors", "0");
ini_set("display_errors", "1");
set_magic_quotes_runtime(0);

if(isset($_GET["url"]))
{
   function eschtml($str)
   {
      return str_replace(array(">", "<", "\""), array("&gt;",  "&lt;", "&quot;"), $str);
   }

   function remtags($str)
   {
      $str = html_entity_decode($str, ENT_COMPAT, "UTF-8");
      return strip_tags($str);
   }

   if(version_compare(PHP_VERSION,'5','>='))
      require_once('domxml45.php');

   header("Content-Type: text/html; charset=utf-8");
   
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
   $id = (isset($_GET["id"]) ? ereg_replace("[^0-9]*", "", $_GET["id"]) : "");
   $fixbugs = (isset($_GET["fixbugs"]) ? ($_GET["fixbugs"] == "true") : false);
   $forceutf8 = (isset($_GET["forceutf8"]) ? ($_GET["forceutf8"] == "true") : false);
   $cache = !(isset($_GET["nocache"]) ? ($_GET["nocache"] == "true") : false);

   $cache_id = rawurlencode($_SERVER['QUERY_STRING']);

   if($cache && file_exists("cache/$cache_id") && ((time() - filemtime("cache/$cache_id")) < 3600))
   {
      echo file_get_contents("cache/$cache_id");
      die();
   }

   if($cache)
   {
     ob_start();
   }

   if($type == "html")
   {
      echo "<html>\n<head>\n<title></title>\n</head>\n<body>\n";
   }
   else if($type == "js")
   {
      echo "snode = document.getElementById(\"feed-$id\");\nnewele = document.createElement(\"div\");\nnewele.innerHTML = \"";
      ob_start();
   }

   $feedtext = file_get_contents($url, false, stream_context_create(array('http' => array('method' => "GET", 'header' => "Accept: application/rss+xml,*/*\r\n"))));
   $feedtext = trim($feedtext);

   if($fixbugs)
   {
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

   if($forceutf8)
   {
      $feedtext = preg_replace("/<\?xml(.*?)encoding=['\"].*?['\"](.*?)\?>/m", "<?xml$1encoding=\"utf-8\"$2?>", $feedtext);
   }
   
   $doc = domxml_open_mem($feedtext);
   
   if($showtitle == true)
   {
      $channel = $doc->get_elements_by_tagname("channel");
      $channel = $channel[0];
   
      $title = $channel->get_elements_by_tagname("title");
      $title = eschtml((count($title) > 0 ? $titleprefix.$title[0]->get_content() : "(No feed title)"));
      if($titlereplacement) $title = $titlereplacement;
      if($striphtml) $title = remtags($title);


      $link = $channel->get_elements_by_tagname("link");
      $link = (count($link) > 0 ? ($eschtml ? eschtml($link[0]->get_content()) : $link[0]->get_content()) : "");
      if($link != "") $title = "<a href=\"$link\">$title</a>";
      if($striphtml) $link = remtags($link);

   
      $desc = $channel->get_elements_by_tagname("description");
      $desc = eschtml(count($desc) > 0 ? $desc[0]->get_content() : "");
      if($striphtml) $desc = remtags($desc);

   
      $image = $channel->get_elements_by_tagname("image");
      if(count($image) > 0)
      {
         $image = $image[0];
         $image = $image ->get_elements_by_tagname("url");
         $image = (count($image) > 0 ? $image[0]->get_content() : "");
      }


      if($showicon && $image != "") $title = "<img class=\"feed-title-image\" src=\"$image\" />$title";
   
      if($showempty || (!$showempty && $title != "")) echo "<h3 class=\"feed-title\">$title</h3>\n";
      if($showtitledesc && ($showempty || (!$showempty && $desc != ""))) echo "<p class=\"feed-desc\">$titledescprefix$desc</p>\n";
   }
   
      
      
   $items = $doc->get_elements_by_tagname("item");
   
   foreach($items as $i => $item)
   {
      if($i == $limit) break;

      $title = $item->get_elements_by_tagname("title");
      $title = (count($title) > 0 ? eschtml($title[0]->get_content()) : "(No title)");
   
      $link = $item->get_elements_by_tagname("link");
      $link = (count($link) > 0 ? eschtml($link[0]->get_content()) : "");
      if($link != "") $title = "<a href=\"$link\">$title</a>";
   
      $desc = $item->get_elements_by_tagname("description");
      $desc = (count($desc) > 0 ? $desc[0]->get_content() : "");
      if($striphtml) $desc = remtags($desc);
      
   
      if($showempty || (!$showempty && $title != "")) echo "<h4 class=\"feed-item-title\">$itemtitleprefix$title</h4>\n";
      if(($detail > 0) && ($showempty || (!$showempty && $desc != "")))
      {
         $words = explode(" ", $desc);
         if(count($words) > $detail)
         {
            $words = array_slice($words, 0, $detail);
             $desc = implode(" ", $words)."...";
         }
         echo "<p class=\"feed-item-desc\">$itemdescprefix$desc</p>\n";
      }
   }

   echo "<div class=\"rss2html-note\" style=\"float: right;\"><a href=\"http://rss.bloople.net/\" style=\"color: #000000;\">Powered by rss2html</a></div>\n<div class=\"rss2html-note-clear\" style=\"clear: right; height: 0;\"></div>\n";

   if($type == "html")
   {
      echo "</body>\n</html>";
   }
   else if($type == "js")
   {
      $text = ob_get_contents();
      ob_end_clean();

      $text = str_replace("\n", "", str_replace("\"", "\\\"", $text));

      echo "$text\";\nsnode.parentNode.insertBefore(newele, snode);";
   }

   if($cache)
   {
     $output = ob_get_contents();
     ob_end_clean();
     
     file_put_contents("cache/$cache_id", $output);

     echo $output;
   }
}
else
{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>RSS 2 HTML</title>
<link rel="StyleSheet" type="text/css" href="style.css" />
<script type="text/javascript" src="scripts/parse.js"></script>
<!--[if lt IE 7]>
<script defer type="text/javascript" src="scripts/pngfix.js"></script>
<![endif]-->

<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
</head>
<body id="body">

<div class="header">
<div class="shoutboxcon"><a href="javascript:showShoutbox();" id="shoutboxlink">show shoutbox!</a>
   <div id="shoutbox">
      <!-- Begin Shoutbox - http://www.shoutmix.com -->
      <iframe title="bloopletech" src="http://www.shoutmix.com/?bloopletech" width="160" height="400" frameborder="0" scrolling="auto">
         <a href="http://www.shoutmix.com/?bloopletech">View shoutbox</a>
      </iframe>
      <!-- End Shoutbox -->
   </div>
</div>
<img src="images/logo.png" alt="rss 2 html" />
</div>

<div class="mainbody">
   <div id="bodywrap" class="bodywrap"></div>

    <h3>Latest News</h3>
    <script id="feed-122179699713281" type="text/javascript" src="http://rss.bloople.net/?url=http%3A%2F%2Fblog.bloople.net%2Frss%2FRSS2HTML&amp;detail=10&amp;limit=3&amp;showtitle=false&amp;striphtml=true&amp;type=js&amp;id=122179699713281"></script>

    <hr class="p-after" />
    <h3>Welcome to RSS 2 HTML!</h3>
    <p class="mast">This page offers an easy way to embed RSS feeds in HTML webpages. One line of code in your webpage, and easily-styled HTML will be generated, with no advertisements or other restrictions.</p>
    <p class="mast">Use the form below to generate the code to include in your webpage:</p>
    <div class="clear"></div>

    <form onsubmit="doParse(); return false;">
      <table>
         <tr>
            <td class="l">URL of RSS feed:</td>
            <td><input class="text" type="text" name="url" id="url" /></td>
         </tr>
         <tr id="advtitle">
            <td class="l">Show feed title:</td>
            <td><input type="checkbox" name="showtitle" id="showtitle" /></td>
         </tr>
         <tr id="advicon">
            <td class="l">Show feed icon:</td>
            <td><input type="checkbox" name="showicon" id="showicon" /></td>
         </tr>
         <tr>
            <td class="l">Length of feed item descriptions:</td>
            <td><input type="radio" name="detail" id="detailhide" value="-1" /> Hide descriptions<br />
                <input type="radio" name="detail" id="detailshow" value="0" /> Show up to <input class="num" type="text" name="detailnum" onclick="$('detailshow').checked = true;" /> words.<br />
                <input type="radio" name="detail" id="detailnolimit" value="1" /> Don't limit length.</td>
         </tr>
         <tr id="advshow">
            <td class="l">Output HTML for empty feed titles and descriptions:</td>
            <td><input type="checkbox" name="showempty" id="showempty" /></td>
         </tr>
         <tr id="advstrip">
            <td class="l">Strip HTML from feed item descriptions:</td>
            <td><input type="checkbox" name="striphtml" id="striphtml" /></td>
         </tr>
         <tr id="advforce">
            <td class="l">Assume RSS feed is UTF-8, ignoring XML prolog:</td>
            <td><input type="checkbox" name="forceutf8" id="forceutf8" /></td>
         </tr>
         <tr id="advfix">
            <td class="l">Attempt to convert Windows-1252 -&gt; UTF-8:</td>
            <td><input type="checkbox" name="fixbugs" id="fixbugs" /></td>
         </tr>
         <tr id="advnocache">
            <td class="l">Disable caching:</td>
            <td><input type="checkbox" name="nocache" id="nocache" /></td>
         </tr>
         <tr>
            <td class="l">Limit number of feed items shown:</td>
            <td><input type="radio" name="limit" id="limitnone" value="-1" /> No limit<br />
                <input type="radio" name="limit" id="limitsome" value="0" /> Limit to the first <input class="num" type="text" name="limitnum" onclick="$('limitsome').checked = true;" /> items.</td>
         </tr>
         <tr id="advembed">
            <td class="l">How should the feed be embedded in your webpage:</td>
            <td><input type="radio" name="codegen" id="codephp" value="php" /> PHP code - this option will only work if your webpage is already coded in PHP.<br />
                <input type="radio" name="codegen" id="codejs" value="js" checked="checked" /> JavaScript code - this option is best if you want to style the embedded feed.<br />
                <input type="radio" name="codegen" id="codehtml" value="html" /> HTML code that generates an iframe - this option is best if the people using your website don't have JavaScript enabled.</td>
         </tr>
         <tr id="codeoutwrap">
            <td class="l">Embed this code in your webpage:</td>
            <td><textarea id="codeout"></textarea></td>
         </tr>
         <tr id="stylingwrap">
            <td class="l">Styling the output:</td>
            <td><p>The HTML generated by this code contains CSS hooks so you can style the output in your stylesheet.<br />
                The title of the feed is displayed in a h3 element, with the class feed-title.<br />
                The feed description is displayed in a p tag with class feed-desc.<br />
                The feed icon is displayed in an img with class feed-title-image.<br />
                Each feed item title is displayed in a h4 with class feed-item-title.<br />
                Each feed item description is displayed in a p tag with class feed-item-desc.</p>
                <p>Note that this code does not generate HTML for empty feed titles, descriptions, etc.
                If you want empty HTML to be generated, click "more options" and tick the "Output HTML for empty..." checkbox.</p></td>
         </tr>
      </table>
      <div class="rfloat"><input type="button" id="showopt" value="more options" onclick="showAdvanced();" /> <input type="submit" id="submit" value="submit &amp; get code" onclick="doParse(); return false;" /></div>
      <div class="lfloat"><input type="reset" id="reset" value="reset" onclick="clearPage();" /></div>
      <div class="clear"></div>
      <hr />
   </form>
   <div class="footer">Check out my portfolio at <a href="http://i.bloople.net">i.bloople.net</a>. &copy; Brenton Fletcher. Comments? e-mail me: <a href="mailto:i@bloople.net">i@bloople.net</a>.</div>
</div>
</body>
</html>
<?
}
?>