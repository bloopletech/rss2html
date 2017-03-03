//Code copyright (c) Brenton Fletcher 2006-2007.
//Check out my portfolio at http://i.budgetwebdesign.org

var msie = (document.all && !window.opera)

function $(ele)
{
   var t = document.getElementById(ele);
   if(t == null) t = document.getElementsByName(ele);
   if(t.length == 1) t = t.item(0);
   return t;
}

function escapeHTML(str)
{
   //code portion borrowed from prototype library
   var div = document.createElement('div');
   var text = document.createTextNode(str);
   div.appendChild(text);
   return div.innerHTML;
   //end portion
}

function showAdvanced()
{
   if($("showopt").value == "more options")
   {
      $("advtitle").style.display = (msie ? "block" : "table-row");
      $("advicon").style.display = (msie ? "block" : "table-row");
      $("advshow").style.display = (msie ? "block" : "table-row");
      $("advstrip").style.display = (msie ? "block" : "table-row");
      $("advembed").style.display = (msie ? "block" : "table-row");
      $("advforce").style.display = (msie ? "block" : "table-row");
      $("advfix").style.display = (msie ? "block" : "table-row");

      $("showopt").value = "less options";
   }
   else
   {
      $("advtitle").style.display = "none";
      $("advtitle").checked = false;
      $("advicon").style.display = "none";
      $("advicon").checked = false;
      $("advshow").style.display = "none";
      $("advshow").checked = false;
      $("advstrip").style.display = "none";
      $("advstrip").checked = false;
      $("advembed").style.display = "none";
      $("advforce").style.display = (msie ? "block" : "table-row");
      $("advforce").style.display = "none";
      $("advfix").style.display = (msie ? "block" : "table-row");
      $("advfix").style.display = "none";
      $("codephp").checked = false;
      $("codejs").checked = false;
      $("codehtml").checked = false;

      $("showopt").value = "more options";
   }
}
   

function doParse()
{
   detail = "";
   if($("detailhide").checked) detail = "&detail=-1";
   if($("detailshow").checked) detail = "&detail=" + $("detailnum").value;

   limit = "";
   if($("limitsome").checked) limit = "&limit=" + $("limitnum").value;

   showtitle = ($("showtitle").checked ? "" : "&showtitle=false");
   showicon = ($("showicon").checked ? "&showicon=true" : "");
   showempty = ($("showempty").checked ? "&showempty=true" : "");
   striphtml = ($("striphtml").checked ? "&striphtml=true" : "");
   forceutf8 = ($("forceutf8").checked ? "&forceutf8=true" : "");
   fixbugs = ($("fixbugs").checked ? "&fixbugs=true" : "");

   type = "js";
   if($("codephp").checked) type = "php";
   if($("codehtml").checked) type = "html";

   url = "//rss.bloople.net/?url=" + encodeURIComponent($("url").value) + detail + limit + showtitle + showicon + showempty + striphtml + forceutf8 + fixbugs;
   code = "";

   if(type == "php")
   {
      code = "<?php\ninclude(\"https:" + url + "\");\nphp?>";
   }
   else if(type == "js")
   {
      code = "<script src=\"" + url + "&type=js\"></script>";
   }
   else if(type == "html")
   {
      code = "<iframe src=\"" + url + "&type=html\"></iframe>";
   }

   $("codeoutwrap").style.display = (msie ? "block" : "table-row");
   $("stylingwrap").style.display = (msie ? "block" : "table-row");
   $("codeout").value = code;
}

function clearPage()
{
   $("codeoutwrap").style.display = "none";
   $("stylingwrap").style.display = "none";
   $("url").focus();
}

function load()
{
   $("url").focus();
}

window.onload = load;