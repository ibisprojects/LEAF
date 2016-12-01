<?php
//**************************************************************************************
// FileName: Project_Edit.php for LEAF website
// Author: 
// Owner: 
// Purpose:
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");
require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");
require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/SecurityUtil.php");

require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Projects.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Organizations.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_People.php");

require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/LKU_SampleDesigns.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/LKU_QAQCs.php");

require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/REL_ProjectToSampleDesign.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/REL_ProjectToQAQC.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/REL_WebsiteToProject.php");

require_once("C:/Inetpub/wwwroot/cwis438/utilities/GodmUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/StringUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/ValidUtil.php");

//**************************************************************************************
// Database Connection
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Security
//**************************************************************************************

$UserID=GetUserID();
$WebSiteID=GetWebsiteID();
$ProjectID=GetIntParameter("ProjectID",-1);

$TakeAction=GetStringParameter("TakeAction","New");

CheckLogin2($Database,PERMISSION_INSTIGATOR);

CheckSharingAgreement($Database,$UserID,"/cwis438/Websites/LEAF/Project_Edit.php?TakeAction=$TakeAction");


CheckProjectRole($Database,$ProjectID,"/cwis438/Websites/LEAF/Project_Edit.php?TakeAction=$TakeAction",PROJECT_MANAGER);

//**************************************************************************************
// Server-side functions
//**************************************************************************************

$QAQCCountAnswered=0;
$SampleDesignCountAnswered=0;

function URLExists($url=NULL)
{
    if($url == NULL) return false;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($httpcode>=200 && $httpcode<300)
    {
        return true;
    }
    else
    {
        return false;
    }
}

//**************************************************************************************
// Parameters
//**************************************************************************************


$CallingPage=GetStringParameter("CallingPage");
$Refreshed=GetIntParameter("Refreshed",0);
$ProjName=GetStringParameter("ProjName");

$Description=GetStringParameter("Description");
$Status=GetIntParameter("Status",1);  // defaulting to "in progress"

$OrganizationID=GetIntParameter("OrganizationID",null);  // default to null
$ProjectActive=GetIntParameter("ProjectActive",1);
$Purpose=GetStringParameter("Purpose");

$StartDate=GetDateParameter("StartDate");

$DifficultyRating=GetIntParameter("DifficultyRating",1); // default to 1 for easy, 2=medium, 3=hard (for citsci projects for scistarter entry; gjn

$CallingPage=urldecode($CallingPage);


//**************************************************************************************
// Server-side code
//**************************************************************************************

$StatusStrings=array("Unknown","In progress","Completed");

$TheHost=GetComputerName();

$Title="Create/Edit project information";
//$HelpPage="http://".$TheHost."/DH.php?WC=/cwis438/help/contribute/Project_Edit1.htm?WebS";
$PageMsg="Please fill in the information below. <font color='red'>Required fields are indicated with a red asterisk (*)</font>";
$ErrorText="";
$Error=null;
$Refreshed=0; // same as default from getparam above - gjn

if ($TakeAction=="New") // user entered to create a new record, use defaults from GetParameters()
{
	$TakeAction="Create"; // all we do on a new is switch to a create on the next submit

	$Title="Create a Project"; // Title for when creating a new project record
	//$HelpPage="http://".$TheHost."/DH.php?WC=/cwis438/help/contribute/Project_Edit1.htm";
	
	$StartDate=new Date(); // $StartDateYear,$StartDateMonth,$StartDateDay
}
else if (($TakeAction=="Create")||($TakeAction=="SearchCreateOrganization")) // user has hit submit; submitted the form with TakeAction = Create
{
    //DebugWriteln("here");
    
	// Do the insert and get the new ProjectID
	$ProjectID=TBL_Projects::Insert($Database,"Untitled",GetUserID());
	
	//Insert Tabs for the New Project
	$TabId = 1;
	for($i=1;$i<=7;$i++)
	{
		if($i==1) // make first tab active by default
		{
			$Active = 1;
		}
		else
		{
			$Active = 0;
		}
		
		// insert tab relationship 
		
		$Insert = "INSERT INTO REL_ProjectToTab (ProjectID, TabID, OrderNumber, Show, Active)
			VALUES ('$ProjectID', '$TabId', '$i','1','$Active')";
		
		$Insert_Table=$Database->Execute($Insert);
		$TabId++;
	}
	//Insert Tabs for the New Project
	
	if ($WebsiteID>0)
	{
		// Create a relationship between the new project and the skin it was created on
		REL_WebsiteToProject::Insert($Database,$WebsiteID,$ProjectID);
	}
	
	// Make the instigator who created the project the project manager for the project
	$ID=$Database->DoInsert("EXEC insert_REL_PersonToProject '0'"); // create a new row with requested role=0

	// Update the new record information for the relationship record
	$UpdateString="UPDATE REL_PersonToProject ".
		"SET Role = ".PROJECT_MANAGER.", ". // manager
		"RequestedRole = null, ".
		"PersonID = $UserID, ".
		"ProjectID = $ProjectID ".
		"WHERE (ID = $ID)";
	
	$Database->Execute($UpdateString);

	// Update person's current working project to make it their newly created project
	TBL_People::SetCurrentProjectID($Database,$UserID,$ProjectID);

	if ($TakeAction=="SearchCreateOrganization")
	{
		$TakeAction="SearchUpdateOrganization"; // Change TakeAction to Update so we can now update the new project record
	}
	else
	{
		$TakeAction="Update"; // Change TakeAction to Update so we can now update the new project record
	}
}

if (($TakeAction=="Update")||($TakeAction=="SearchUpdateOrganization")) // record was just created or this is updating an old record
{	
	$Refreshed=1;
	$Error==null;

	//see if any values are checked for the two checkbox required questions
	//get the checkbox values that were filled in and populate the check box on/ off array with them

	//validate answers for required fields
	if (ValidateString($ProjName,1,100)!=null)

	{
		$Error="There was at least one problem on the page highlighted in red below, please correct it and click 'Submit'";
		$PageMsg=$Error;
	}
	 
  	$Title="Edit project information"; // new Title, vanishes anyway if page was sent a CallingPage to return to
  	//$HelpPage="http://".$TheHost."/DH.php?WC=/cwis438/help/contribute/Project_Edit.htm";

	if (($Error==null)||($TakeAction=="SearchUpdateOrganization")) // page was filled in correctly (all required questions were answered)
	{
	   	$UpdateString="UPDATE TBL_Projects ".
			"SET ProjName = '".$ProjName."', ".
			"Code = '', ".
			"Description = '".$Description."', ".
			"Status = '2', ".
			"ContactApproval = '1', ".
			"Purpose = '".$Purpose."', ".
			"StartDate = '".$StartDate->GetSQLString()."', ".
			"EndDate = '', ".
			"StudyExtent = '0', ".
			"Active = '1'";
	   	
	   	$UpdateString.=" WHERE ID=".$ProjectID;

 		$Database->Execute($UpdateString);
 		
		// make sure the project appears on this web site
		
		$WebsiteSet=TBL_Websites::GetSetFromID($Database,$WebSiteID);
        	
		if ($CallingPage=="")
	  	{
	  		RedirectFromRoot("/cwis438/Browse/Project/Project_Info.php?ProjectID=$ProjectID"); //was userprofile.php// do we want this to be projectinfo.php as default?
	  	}
	  	else
	  	{
	  		RedirectFromRoot($CallingPage);
	  	}
	}
}

if (($TakeAction=="")||($TakeAction=="Edit")) // record existed or was just inserted above, get params from the database
{   
	//get all the data in the tbl_projects that has been entered
	$RecordSet=TBL_Projects::GetSetFromID($Database,$ProjectID); //GetSetFromID($Database,$ProjectID)

	$StartDate=new Date();

	if (StringIsEmpty($RecordSet->Field("StartDate"))==false)
	{
		$StartDate->SetDateFromSQLString($RecordSet->Field("StartDate"));
	}

	$Title="Edit project information";
	//$HelpPage="http://".$TheHost."/DH.php?WC=/cwis438/help/contribute/Project_Edit.htm?WebSiteID=20";

	$ProjName=$RecordSet->Field("ProjName");
	$Description=$RecordSet->Field("Description");
	$Status=$RecordSet->Field("Status");
	$Active=$RecordSet->Field("Active");
	$Purpose=$RecordSet->Field("Purpose");
	$TakeAction="Update";
}

//**************************************************************************************
// HTML Header block and client-side includes
//**************************************************************************************

$ThePage=new PageSettings();

$TheTable=new TableSettings(TABLE_FORM);

$ThePage->Header("Create or Edit Project Information");

?>
<SCRIPT type="text/javascript" LANGUAGE="javascript" SRC="/cwis438/includes/FormUtil.js"></SCRIPT>

<SCRIPT LANGUAGE="JavaScript">

<?
$SavedProjectNameSet=TBL_Projects::GetSet($Database);
WriteJavaScriptArrayFromRecordSet("Projects_NameIndexes",$SavedProjectNameSet,"ProjName");
?>

function DoSubmit()
{
	var Name=document.SelectForm.ProjName.value;
	//window.alert("Name="+Name);

	var SavedNameAlreadyExists=false;

	for (i=0;i<Projects_NameIndexes.length;i++)
	{
		//window.alert(document.forms.SelectForm.TakeAction.value);
		if (document.forms.SelectForm.TakeAction.value=="Create")
		{
			if (Projects_NameIndexes[i] == Name)
			{
				window.alert("Sorry, there is already a project with this name. "+
				"Please enter a different name and try again.");

				SavedNameAlreadyExists=true;
			}
		}
	}

	if (SavedNameAlreadyExists==false)
	{
		document.SelectForm.TakeAction.Value="RefreshUpdate"; //so print errors if they exist
		document.SelectForm.submit();
	}
}

</SCRIPT>
<?php

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart();

$ThePage->Heading(0,$Title);

$ThePage->CenteredContent(GetLink("/cwis438/websites/LEAF/UserProfile.php?WebSiteID=$WebSiteID","To My Profile"));

$ThePage->LineBreak();

$ThePage->BodyText($PageMsg);

$ThePage->LineBreak();

$TheTable->FormStart("/cwis438/Websites/LEAF/Project_Edit.php");

$TheTable->TableStart();
$TheTable->NumColumns=2;
$TheTable->ColumnHeading->ColumnSpan=2;
$TheTable->TableColumnHeading("Project Information");

$TheTable->FormRowTextEntry("Project name:","ProjName",$ProjName,100,"* Required",null);
if ($Refreshed==1) CheckValidEntry($TheTable,$TakeAction,$ProjName,1,100,"You must enter a project name",true);

$TheTable->FormRowTextAreaEntry("Description:","Description",$Description,3,6); // was NumCols=60

$TheTable->FormRowTextAreaEntry("Purpose:","Purpose",$Purpose,3,6); // was NumCols=60

$TheTable->FormRowArray("Project status:","Status",$Status,$StatusStrings,0,null,
	true,"-- Select one --",-1,false,0,false,null,"* Required");
if ($Refreshed==1) CheckValidComboBox($TheTable,$TakeAction,$Status);

$TheTable->FormRowDate("Start date:","StartDate",$StartDate->Year,$StartDate->Month,$StartDate->Day);

$TheTable->TableEnd();
$TheTable->FormHidden("TakeAction",$TakeAction);
$TheTable->FormHidden("ProjectID",$ProjectID);
$TheTable->FormHidden("CallingPage",urlencode($CallingPage));

$TheTable->FormHidden("Refreshed",$Refreshed);

$ThePage->CenterStart();
$TheTable->FormButton("Submit","Submit","onclick='DoSubmit()'");
$ThePage->CenterEnd();

$TheTable->FormEnd();

$ThePage->LineBreak();

$ThePage->BodyEnd(); // false,false,true

?>
