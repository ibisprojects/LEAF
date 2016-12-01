<?php
//**************************************************************************************
// FileName: Download.php
// Author: GN, RS, BF, NK
// The Download Data page for the LEAF website (Living Atlas of East African Flora)
//**************************************************************************************

//**************************************************************************************
// Includes
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");

require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");
require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");
require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/classes/DBTable/TBL_Projects.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/SecurityUtil.php");

//**************************************************************************************
// Security
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Security
//**************************************************************************************

// No security needed to view this page.

//**************************************************************************************
// Server-side functions
//**************************************************************************************

//**************************************************************************************
// Parameters
//**************************************************************************************

//**************************************************************************************
// Server-Side code
//**************************************************************************************

//**************************************************************************************
// HTML Header block, client-side includes, and client-side functions
//**************************************************************************************

$ThePage=new PageSettings();

$ThePage->HeaderStart("Living Atlas of East African Flora");

?>

<style>

#Big
{
	font-size:11pt;
}

</style>

<?php

$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart();

echo("<h2>Download Data</h2>");

echo("There are several ways to download information from LEAF. Data may be downloaded 
        that includes all species observation data and associated attributes for a given 
        <a href='/cwis438/Browse/Project/Project_List.php?WebSiteID=20' id='Big'>project</a> or 
        all data for a given <a href='/cwis438/websites/LEAF/Species_List.php?WebSiteID=20' id='Big'>
        species</a>. <br/><br/>To do so, navigate to the specific  
        <a href='/cwis438/Browse/Project/Project_List.php?WebSiteID=20' id='Big'>project</a> you 
        are interested in and then select the project from the list and then click the 
        'Observations' button shown below to be able to choose data format and save the data.
        You may also find your desired <a href='/cwis438/websites/LEAF/Species_List.php?WebSiteID=20' id='Big'>
        species</a> by looking through our <a href='/cwis438/websites/LEAF/Species_List.php?WebSiteID=20' id='Big'>
        list of species</a> and clicking the 'View Profile' button next to the species you wish to obtain data.
        Once you get to the Species Profile page, click the 'Download Data' button to get all data for the selected species.");

$ThePage->BodyEnd();

?>
