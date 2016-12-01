<?php
//**************************************************************************************
// FileName: Visit_Info.php
// Author: needs header info
// Owner:
// Purpose: Displays a table of information specific to a user-selected visit from the
// Visit_List.php page
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");
require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");
require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/SecurityUtil.php");
//require_once("C:/Inetpub/wwwroot/cwis438/utilities/ResultUtil.php");

require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Areas.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_ControlAgents.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Visits.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_People.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_AttributeData.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Projects.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/LKU_AttributeTypes.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Treatments.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_TaxonUnits.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/LKU_DatabaseFields.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Media.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/REL_MediaToVisit.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/LKU_SubplotTypes.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_UserSettings.php");

require_once("C:/Inetpub/wwwroot/cwis438/utilities/GodmUtil.php");

//**************************************************************************************
// Database Connection
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Security
//**************************************************************************************

// no security required to view this file

//$result=SetGlobalCookie("UserID",""); // "" matches asp
//$result=setcookie("UserID",false);
//DebugWriteln("result=".$result);
//DebugWriteln($_COOKIE["UserID"]);

//$Database=NewConnection();

//TBL_UserSessions::SetNew($Database,GetUserID()); // overrides old user session so the user doesn't see logined in information

//**************************************************************************************
// Server-side functions
//**************************************************************************************

//**************************************************************************************
// Parameters
//**************************************************************************************

$VisitID=GetIntParameter("VisitID",0); 
$FormID=GetIntParameter("FormID",0); // jjg - ?

$TakeAction=GetStringParameter("TakeAction","");
$CallingPage=GetStringParameter("CallingPage");
$CallingLabel=GetStringParameter("CallingLabel");

//**************************************************************************************
// Server-side code
//**************************************************************************************

// take actions

TBL_UserSettings::Set($Database,"Visit_Info_CallingPage",$CallingPage);
TBL_UserSettings::Set($Database,"Visit_Info_CallingLabel",$CallingLabel);

// setup calling page

$MyCallingPage="/cwis438/Browse/Project/Visit_Info.php".
	"?VisitID=$VisitID".
	"&FormID=$FormID".
	"&TakeAction=Returned";
	
$MyCallingPage=urlencode($MyCallingPage);

$MyCallingLabel="To Visit Info";

// take action

if ($TakeAction=="DeleteAuxVisitData")
{
	TBL_AttributeData::Delete($Database,GetIntParameter("AuxVisitDataID",0));
}

if ($TakeAction=="NewMedia")
{
//	$FilePath=GetStringParameter("FilePath");
	
	$MediaID=GetIntParameter("MediaID");
	
//	DebugWriteln("MediaID=$MediaID");
	
	if ($MediaID>0) REL_MediaToVisit::Insert($Database,$MediaID,$VisitID,GetUserID());
}
$VisitSet=TBL_Visits::GetSetFromID($Database,$VisitID);

// get the area info

$AreaID=$VisitSet->Field("AreaID");

$AreaSet=TBL_Areas::GetSetFromID($Database,$AreaID);


$AreaTypeString=LKU_AreaSubtypes::GetNameFromID($Database,$AreaSet->Field("AreaSubtypeID"));

// get the project info

$SelectString="SELECT * ".
	"FROM TBL_Projects ".
	"WHERE (ID = ".$VisitSet->Field("ProjectID").")";

//DebugWriteln("SelectString=$SelectString");

$Name="Untitled";
$ProjectID=0;
	
$ProjectSet=$Database->Execute($SelectString);

//DebugWriteln("commentlate=".$VisitSet->Field("Comments"));

if ($ProjectSet->FetchRow()) 
{
	$ProjectID=$ProjectSet->Field("ID");
	$Name=$ProjectSet->Field("ProjName");
}

// get the data source info

$SelectString="SELECT *, TBL_Visits.InsertLogID, TBL_InsertLogs.Name AS InsertLogName, ".
	"TBL_InsertLogs.UploaderID, TBL_InsertLogs.DateUploaded, ".
	"TBL_InsertLogs.DateReceived, TBL_InsertLogs.FilePath, ".
	"TBL_InsertLogs.Comment ".
	"FROM TBL_InsertLogs INNER JOIN TBL_Visits ON TBL_InsertLogs.ID = TBL_Visits.InsertLogID ".
	"WHERE (TBL_Visits.ID = ".$VisitID.")";

//DebugWriteln("SelectString=".$SelectString);

$InsertLogSet=$Database->Execute($SelectString);

if ($InsertLogSet->FetchRow()==false) $InsertLogSet=null;

// get the authority

$AuthoritySet=TBL_People::GetSetFromID($Database,$VisitSet->Field("AuthorityID"));

if ($AuthoritySet->FetchRow()==false) $AuthoritySet=null;

// get the recorder

$RecorderSet=TBL_People::GetSetFromID($Database,$VisitSet->Field("RecorderID"));

if ($RecorderSet->FetchRow()==false) $RecorderSet=null;

// get the organism data

$OrganismDataSet=null;
//$OrganismDataID=0;

$SelectString="SELECT Count(ID) FROM TBL_OrganismData WHERE VisitID=".$VisitID;

$Set=$Database->Execute($SelectString);

$NumOrganismDatas=$Set->Field(1);

if ($NumOrganismDatas>0)
{
	$OrganismDataSet=TBL_OrganismData::GetSet($Database,$VisitID);
}

// get treatment data

$TreatmentSet=null;

$SelectString="SELECT Count(ID) FROM TBL_Treatments WHERE VisitID=".$VisitID;

$Set=$Database->Execute($SelectString);

$NumTreatments=$Set->Field(1);

if ($NumTreatments>0)
{
	$TreatmentSet=TBL_Treatments::GetSetFromVisitID($Database,$VisitID);
}

// get auxiliary data

$AuxiliaryDataSet=null;

$SelectString="SELECT Count(ID) FROM TBL_AttributeData ".
	"WHERE VisitID=".$VisitID;

$Set=$Database->Execute($SelectString);

$NumAuxiliaryDatas=$Set->Field(1);

if ($NumAuxiliaryDatas>0)
{
	$AuxiliaryDataSet=TBL_AttributeData::GetSet($Database,$VisitID);
}

// get the sub plot info

$HasSubplots=false;
$SubplotSet=TBL_Areas::GetSubplotTypeSet($Database,$AreaSet->Field("AreaSubtypeID"));
if ($SubplotSet->FetchRow())
{
	$HasSubplots=true;
}

$CanEditData=TBL_Projects::CanEditData($Database,GetUserID(),-1,$VisitID);

$MediaSet=TBL_Media::GetSetFromVisitID($Database,$VisitID);

$CallingPage="/cwis438/Browse/Project/Visit_Info.php?VisitID=$VisitID";
$CallingPage=urlencode($CallingPage);
$CallingLabel="To Survey Information";

//**************************************************************************************
// HTML Header block and client-side includes
//**************************************************************************************

$ThePage=new PageSettings;

$TheTable=new TableSettings(TABLE_FORM);

$ThePage->HeaderStart("New Observation");

?>
<SCRIPT TYPE="text/javascript" LANGUAGE="JavaScript" SRC="/cwis438/includes/BrowserUtil.js"></SCRIPT>

<style>

.Greg
{
	background-color:#D7DFE1;
	text-align:center;
	font-size:10pt;
	font-weight:bold;
}

</style>

<?php

TBL_Treatments::WriteFunctions();
TBL_OrganismData::WriteFunctions();
TBL_AttributeData::WriteFunctions();

$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart();

$ThePage->Heading(0,"Thank you for your observation!");

$ThePage->BodyText("<br>");

// we know there can only be one org data record for new ob

$OrganismDataSet->FetchRow();

$OrganismDataID=$OrganismDataSet->Field("ID");
$VisitID=$OrganismDataSet->Field("VisitID");
$OrganismInfoID=$OrganismDataSet->Field("OrganismInfoID");
$OrgName=TBL_OrganismInfos::GetName($Database,$OrganismInfoID);
if (strpos($OrgName,"(")==1)
{
	$OrgName=str_replace("(","",$OrgName);
	$OrgName=str_replace(")","",$OrgName);
}
$AreaName="";
if ($AreaSet!=null)
{
	$AreaName=$AreaSet->Field("AreaName");
}

TBL_Areas::GetExtent($Database,$AreaID,$RefX,$RefY,$RefWidth,$RefHeight);
$RefX=round($RefX,4);
$RefY=round($RefY,4);

$ThePage->CenteredContent("Thank you adding $OrgName ($RefY Latitude, $RefX Longitude).");

$ThePage->BodyText("<br>");


$UserID=getUserID(); // default to 0
	
if ($UserID>0) // user is logged in
{
	$Link=GetLink("/cwis438/Websites/LEAF/Submit_Observation.php?WebSiteID=20","Submit another observation!");
}
else // guest
{
	$Link=GetLink("/cwis438/Websites/LEAF/Submit_Observation.php?WebSiteID=20&RunAsGuest=17","Submit another observation!");
}

$ThePage->CenteredContent($Link);

$ThePage->BodyText("<br>");


//$Link=GetLink("/cwis438/Browse/TiledMap/Scene_Basic.php","View your observation on our map!","AreaID=$AreaID");

//$ThePage->CenteredContent($Link);

// write out the main table

$TheTable->TableStart();

$TheTable->ColumnHeading->ColumnSpan=2;

//$TheTable->TableRow(array("Detailed Observation Information"));
$TheTable->TableColumnHeading("Detailed Observation Information");

//$TheTable->Columns[0]->ColumnSpan=1;
//$TheTable->Columns[0]->Class="";

// Date

$Date=Date::GetPrintDateFromSQLDate($VisitSet->Field("VisitDate"));

$TheTable->TableRow(array("Date:",$Date));

// species

$TheTable->TableRow(array("Species:",$OrgName));

// Location name

if ($AreaSet!=null)
{
	$AreaName=$AreaSet->Field("AreaName");
	if ($AreaName==null) $AreaName="Untitled";
    
    $MyCallingPage="/cwis438/Websites/LEAF/Submit_Confirmation.php?VisitID=$VisitID&WebSiteID=20";
    $MyCallingLabel="To Confirmation Page";
    $Link="$AreaName ".GetLink("/cwis438/websites/citsci/Map/Location_Info.php","Map","CallingPage=$MyCallingPage&CallingLabel=$MyCallingLabel&AreaID=$AreaID");
    
	//$Link=GetLink("/cwis438/Browse/Location/Location_Info.php",$AreaName,
	//"CallingPage=$MyCallingPage&CallingLabel=$MyCallingLabel&AreaID=".$AreaID);

	$TheTable->TableRow(array("Location:",$Link));
}

// write out the project name

if ($ProjectSet!=null)
{
	$Link=GetLink("/cwis438/Browse/Project/Project_Info.php",$Name,"ProjectID=".$ProjectID);

	$TheTable->TableRow(array("Project:",$Link));
}

if (($VisitSet->Field("Status")!=null)||($VisitSet->Field("Status")!=0))
{
	$VisitStatus=$VisitSet->Field("Status");
	if ($VisitStatus==0) $TheTable->TableRow(array("Status:","Unknown"));
	else $TheTable->TableRow(array("Status:",$VisitStatusStrings[$VisitStatus]));
}

// Comments

$Comments=$VisitSet->Field("Comments");

if (!StringIsEmpty($Comments))
{
	$CommentsArray=explode(";",$Comments);
	
	if ($CommentsArray[0]) $TheTable->TableRow(array("Name:",$CommentsArray[0])); // Name
	if ($CommentsArray[1]) $TheTable->TableRow(array("Email:",$CommentsArray[1])); // Email
	if ($CommentsArray[2]) $TheTable->TableRow(array("Phone:",$CommentsArray[2])); // Comments
}

$TheTable->TableEnd();

$ThePage->ParagraphBreak();

// *********************************************
// write out any additional media
// *********************************************

TBL_Media::WriteTableFromSet($Database,$MediaSet);


$ThePage->LineBreak();

$ThePage->BodyEnd();


