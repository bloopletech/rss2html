$ = document.getElementById.bind(document);

function showAdvanced() {
   document.body.classList.toggle("show-advanced");
   $("showopt").value = document.body.classList.contains("show-advanced") ? "Less options" : "More options";
}

function doParse() {
   var detail = "";
   if($("detailhide").checked) detail = "&detail=-1";
   if($("detailshow").checked) detail = "&detail=" + $("detailnum").value;

   var limit = $("limit").checked ? ("&limit=" + $("limitnum").value) : "";

   var advanced = document.body.classList.contains("show-advanced");
   var showtitle = (advanced && $("showtitle").checked ? "" : "&showtitle=false");
   var showicon = (advanced && $("showicon").checked ? "&showicon=true" : "");
   var showempty = (advanced && $("showempty").checked ? "&showempty=true" : "");
   var striphtml = (advanced && $("striphtml").checked ? "&striphtml=true" : "");
   var forceutf8 = (advanced && $("forceutf8").checked ? "&forceutf8=true" : "");
   var fixbugs = (advanced && $("fixbugs").checked ? "&fixbugs=true" : "");

   var path = "/?url=" + encodeURIComponent($("url").value) + detail + limit + showtitle + showicon + showempty + striphtml + forceutf8 + fixbugs;
   var url = "//rss.bloople.net" + path;
   var code = "";

   var type = $("form").elements["codegen"].value;
   if(type == "php") code = "<?php\ninclude(\"https:" + url + "\");\nphp?>";
   else if(type == "js") code = "<script src=\"" + url + "&type=js\"></script>";
   else if(type == "html") code = "<iframe src=\"" + url + "&type=html\"></iframe>";

   $("codeout").value = code;
   $("live-example").src = path;

   document.body.classList.add("submitted");

   return false;
}

window.onload = function() {
  $("form").onsubmit = doParse;

  $("limitnum").onclick = function() {
    $("limit").checked = true;
  };

  $("detailnum").onclick = function() {
    $("detailshow").checked = true;
  };

  $("url").focus();
};