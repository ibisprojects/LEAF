<?php

//**************************************************************************************
// FileName: GetTaxonomy.php
// Purpose: To get the taxonomy for LEAF species profile - Classification Tab
//**************************************************************************************

//**************************************************************************************
// Includes
//**************************************************************************************
require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");

require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_TaxonUnits.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_OrganismInfos.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Kingdoms.php");

$Database=NewConnection(INVASIVE_DATABASE);
//**************************************************************************************
// Server-side functions
//**************************************************************************************

//$OrganismInfoID=1106;

$OrganismInfoID=GetIntParameter("OrganismInfoID");

//DebugWriteln("OrganismInfoID=$OrganismInfoID");

//**************************************************************************************
// Server-side functions
//**************************************************************************************

$SelectString="SELECT REL_OrganismInfoToTSN.TSN, TBL_OrganismInfos.ID AS OrganismInfoID, TBL_Kingdoms.Name AS Kingdom, TBL_TaxonUnits_1.UnitName1 AS Phylum, 
                      TBL_TaxonUnits_2.UnitName1 AS Class, TBL_TaxonUnits_3.UnitName1 AS [Order], TBL_TaxonUnits_4.UnitName1 AS Family, TBL_TaxonUnits.UnitName1 AS Genus, 
                      TBL_TaxonUnits.UnitName2 AS Species
                FROM REL_OrganismInfoToTSN INNER JOIN
                      TBL_OrganismInfos ON REL_OrganismInfoToTSN.OrganismInfoID = TBL_OrganismInfos.ID INNER JOIN
                      TBL_TaxonUnits ON REL_OrganismInfoToTSN.TSN = TBL_TaxonUnits.TSN INNER JOIN
                      TBL_TaxonUnits AS TBL_TaxonUnits_1 ON TBL_TaxonUnits.PhylumTSN = TBL_TaxonUnits_1.TSN INNER JOIN
                      TBL_TaxonUnits AS TBL_TaxonUnits_2 ON TBL_TaxonUnits.ClassTSN = TBL_TaxonUnits_2.TSN INNER JOIN
                      TBL_TaxonUnits AS TBL_TaxonUnits_3 ON TBL_TaxonUnits.OrderTSN = TBL_TaxonUnits_3.TSN INNER JOIN
                      TBL_TaxonUnits AS TBL_TaxonUnits_4 ON TBL_TaxonUnits.FamilyTSN = TBL_TaxonUnits_4.TSN INNER JOIN
                      TBL_Kingdoms ON TBL_TaxonUnits.KingdomID = TBL_Kingdoms.ID
                WHERE (TBL_OrganismInfos.ID = $OrganismInfoID)";

$Set=$Database->Execute($SelectString);

$Kingdom=$Set->Field("Kingdom");
$Phylum=$Set->Field("Phylum");
$Class=$Set->Field("Class");
$Order=$Set->Field("Order");
$Family=$Set->Field("Family");
$Genus=$Set->Field("Genus");
$Species=$Set->Field("Species");

echo("<h2>Taxonomy</h2>");

echo("<p>");

echo("<span><b>Kingdom:</b> $Kingdom</span><br/><br/>");
echo("<span style='float:left; text-indent:20px;'><b>Phylum:</b> $Phylum</span><br/><br/>");
echo("<span style='float:left; text-indent:40px;'><b>Class:</b> $Class</span><br/><br/>");
echo("<span style='float:left; text-indent:60px;'><b>Order:</b> $Order</span><br/><br/>");
echo("<span style='float:left; text-indent:80px;'><b>Family:</b> $Family</span><br/><br/>");
echo("<span style='float:left; text-indent:100px;'><b>Genus:</b> $Genus</span><br/><br/>");
echo("<span style='float:left; text-indent:120px;'><b>Species:</b> $Species</span><br/><br/><br/>");

echo("</p>");

echo("<span style='font-size:10px;'>Note: Taxonomy is partially based on the <a href='http://www.itis.gov' target='NewWindow' style='font-size:10px;'>Integrated Taxonomic Information System (ITIS)</a>.</span>");
?>