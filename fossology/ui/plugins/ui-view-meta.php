<?php
/***********************************************************
 Copyright (C) 2008 Hewlett-Packard Development Company, L.P.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 version 2 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License along
 with this program; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
***********************************************************/

/*************************************************
 Restrict usage: Every PHP file should have this
 at the very beginning.
 This prevents hacking attempts.
 *************************************************/
global $GlobalReady;
if (!isset($GlobalReady)) { exit; }

class ui_view_meta extends FO_Plugin
  {
  var $Name       = "view_meta";
  var $Title      = "View Meta Data";
  var $Version    = "1.0";
  var $Dependency = array("db","browse");
  var $DBaccess   = PLUGIN_DB_READ;
  var $LoginFlag  = 0;

  /***********************************************************
   RegisterMenus(): Customize submenus.
   ***********************************************************/
  function RegisterMenus()
    {
    menu_insert("Browse-Pfile::Meta",5,$this->Name,"View meta information");
    // For the Browse menu, permit switching between detail and summary.
    $Parm = Traceback_parm_keep(array("upload","item","format"));
    $URI = $this->Name . $Parm;
    if (GetParm("mod",PARM_STRING) == $this->Name)
	{
	menu_insert("View::Meta",1);
	menu_insert("View-Meta::Meta",1);
	}
    else
	{
	menu_insert("View::Meta",1,$URI,"View meta information");
	menu_insert("View-Meta::Meta",1,$URI,"View meta information");
	}
    } // RegisterMenus()

  /***********************************************************
   ShowView(): Display the meta data associated with the file.
   ***********************************************************/
  function ShowView($ShowMenu=0,$ShowHeader=0)
  {
    global $DB;
    $V = "";
    $Upload = GetParm("upload",PARM_INTEGER);
    $Folder = GetParm("folder",PARM_INTEGER);
    $Item = GetParm("item",PARM_INTEGER);
    if (empty($Item) || empty($Upload))
	{ return; }

    /**********************************
     Display micro header
     **********************************/
    if ($ShowHeader)
      {
      $V .= Dir2Browse("browse",$Item,NULL,1,"View-Meta");
      } // if ShowHeader

    /**********************************
     Display meta data
     **********************************/

    $SQL = "SELECT *
	FROM uploadtree
	INNER JOIN pfile ON uploadtree_pk = $Item
	AND pfile_fk = pfile_pk
	INNER JOIN mimetype ON pfile_mimetypefk = mimetype_pk;";
    $Results = $DB->Action($SQL);
    $Count=1;

    $V .= "<table width='100%' border='1'>\n";
    $V .= "<tr><th width='5%'>Item</th><th width='20%'>Meta Data</th><th>Value</th></tr>\n";
    foreach($Results as $R)
    for($i=0; !empty($Results[$i]['mimetype_pk']); $i++)
	{
	$R = &$Results[$i];
	$V .= "<tr><td align='right'>$Count</td><td>Unpacked file type";
	$V .= "</td><td>" . htmlentities($R['mimetype_name']) . "</td></tr>\n";
	$Count++;
	}

    $SQL = "SELECT DISTINCT key_name,attrib_value FROM attrib
	INNER JOIN key ON key_pk = attrib_key_fk
	AND key_parent_fk IN
	(SELECT key_pk FROM key WHERE key_parent_fk=0 AND
	  (key_name = 'pkgmeta' OR key_name = 'specagent') )
	INNER JOIN uploadtree ON uploadtree_pk = $Item
	AND key_name != 'Processed' ORDER BY key_name;";
    $Results = $DB->Action($SQL);

    for($i=0; !empty($Results[$i]['key_name']); $i++)
	{
	$R = &$Results[$i];
	$V .= "<tr><td align='right'>$Count</td><td>" . htmlentities($R['key_name']);
	$Val = htmlentities($R['attrib_value']);
	$Val = preg_replace("@((http|https|ftp)://[^{}<>&[:space:]]*)@i","<a href='\$1'>\$1</a>",$Val);
	$V .= "</td><td>$Val</td></tr>\n";
	$Count++;
	}

    $V .= "</table>\n";
    $Count--;
    $V .= "<P />Total meta data records: " . number_format($Count,0,"",",") . "<br />\n";
    return($V);
  } // ShowView()

  /***********************************************************
   Output(): This function is called when user output is
   requested.  This function is responsible for content.
   (OutputOpen and Output are separated so one plugin
   can call another plugin's Output.)
   This uses $OutputType.
   The $ToStdout flag is "1" if output should go to stdout, and
   0 if it should be returned as a string.  (Strings may be parsed
   and used by other plugins.)
   ***********************************************************/
  function Output()
    {
    if ($this->State != PLUGIN_STATE_READY) { return; }
    $V="";
    switch($this->OutputType)
      {
      case "XML":
	break;
      case "HTML":
	$V .= $this->ShowView(1,1);
	break;
      case "Text":
	break;
      default:
	break;
      }
    if (!$this->OutputToStdout) { return($V); }
    print("$V");
    return;
    }

  };
$NewPlugin = new ui_view_meta;
$NewPlugin->Initialize();
?>
