<?php
//**************************************************************************************
// FileName: Partners.php
// Author: GN, RS, BF, NK
// The main Partners page for the LEAF website (Living Atlas of East African Flora)
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

$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart();

echo("<h2>Partners</h2>");

echo("<div style='height:100px; width:100%;'>");
echo("<a href='http://www.ibc.gov.et/' target='NewWindow'><img src='/cwis438/websites/LEAF/images/Logos/EIB_Logo.png' style='margin-right:12px;' border='0' alt='Ethiopian Institute of Biodiversity (EIB)' title='Ethiopian Institute of Biodiversity (EIB)'/></a>");
echo("<a href='http://www.aau.edu.et/' target='NewWindow'><img src='/cwis438/websites/LEAF/images/Logos/AAU_Logo.png' style='margin-right:12px;' border='0' alt='Addis Ababa University' title='Addis Ababa University'/></a>");
echo("<a href='http://www.hu.edu.et/' target='NewWindow'><img src='/cwis438/websites/LEAF/images/Logos/HawssaUniversity_Logo.png' style='margin-right:12px;' border='0' alt='Hawassa University' title='Hawassa University'/></a>");
echo("<a href='http://www.uog.edu.et/en/' target='NewWindow'><img src='/cwis438/websites/LEAF/images/Logos/UniversityOfGondar_Logo.png' style='margin-right:12px;' border='0' alt='University of Gondar' title='University of Gondar'/></a>");
echo("<a href='http://www.bdu.edu.et/' target='NewWindow'><img src='/cwis438/websites/LEAF/images/Logos/Bahir_Dar_University_Logo.png' style='margin-right:12px;' border='0' alt='Bahir Dar University' title='Bahir Dar University'/></a>");
echo("<a href='http://www.hu.edu.et/cfnr/' target='NewWindow'><img src='/cwis438/websites/LEAF/images/Logos/WondoGenet_Logo.png' style='margin-right:12px;' border='0' alt='Wondo Genet College of Forestry' title='Wondo Genet College of Forestry'/></a>");
echo("<a href='http://www.nrel.colostate.edu/' target='NewWindow'><img src='/cwis438/websites/LEAF/images/Logos/NREL_Logo.png' style='margin-right:12px;' border='0' alt='NREL' title='NREL'/></a>");
//echo("<a href='http://www.colostate.edu/' target='NewWindow'><img src='/cwis438/websites/LEAF/images/Logos/CSU_Logo.png' style='margin-right:12px;' border='0' alt='Colorado State University' title='Colorado State University'/></a>");

echo("</div>");

echo("<div style='height:100px; width:100%; margin-top:12px;'>");
echo("<a href='http://www.gbif.org' target='NewWindow'><img src='/cwis438/websites/LEAF/images/Logos/GBIF_Logo.png' style='margin-right:12px;' border='0' alt='GBIF' title='GBIF'/></a>");
echo("<a href='http://www.etflora.net' target='NewWindow'><img src='/cwis438/websites/LEAF/images/Logos/ETFlora_Logo.png' style='margin-right:12px;' border='0' alt='ETFlora.net' title='ETFlora.net'/></a>");
echo("<a href='http://www.iucnredlist.org/' target='NewWindow'><img src='/cwis438/websites/LEAF/images/Logos/IUCN_Logo.png' style='margin-right:12px;' border='0' alt='IUCN' title='IUCN'/></a>");
echo("<a href='http://www.colostate.edu/' target='NewWindow'><img src='/cwis438/websites/LEAF/images/Logos/CSU_Logo.png' style='margin-right:12px;' border='0' alt='Colorado State University' title='Colorado State University'/></a>");
echo("</div>");

$ThePage->LineBreak();
$ThePage->LineBreak();

echo("<h2>Funding Support</h2>");

echo("<div style='height:100px; width:100%;'>");
echo("<a href='http://www.jrsbiodiversity.org' target='NewWindow'><img src='/cwis438/websites/LEAF/images/Logos/JRS_Logo.png' border='0' alt='JRS Biodiversity Foundation' title='JRS Biodiversity Foundation' /></a>");
echo("</div>");

$ThePage->BodyEnd(); 

?>
