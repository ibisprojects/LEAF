<?php

//**************************************************************************************
// FileName: GetCollectionName.php
//
//**************************************************************************************

//**************************************************************************************
// Includes
//**************************************************************************************
require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/SecurityUtil.php");

require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Collections.php");

$Database=NewConnection(INVASIVE_DATABASE);
//**************************************************************************************
// Server-side functions
//**************************************************************************************

$SelectedCollectionID=GetIntParameter("SelectedCollectionID");

//DebugWriteln("CollectionID=$CollectionID");

//**************************************************************************************
// Server-side functions
//**************************************************************************************

$SelectString="SELECT Name
                FROM TBL_Collections
                WHERE ID='$SelectedCollectionID'";

$Set=$Database->Execute($SelectString);

$CollectionName=$Set->Field("Name");

echo("$CollectionName");
?>