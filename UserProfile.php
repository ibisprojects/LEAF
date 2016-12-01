<?php

//**************************************************************************************
// FileName: UserProfile.php for LEAF
//
// Copyright (c) 2006, 
//
// Permission is hereby granted, free of charge, to any person obtaining a
// copy of this software and associated documentation files (the "Software"),
// to deal in the Software without restriction, including without limitation
// the rights to use, copy, modify, merge, publish, distribute, sublicense,
// and/or sell copies of the Software, and to permit persons to whom the
// Software is furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included
// in all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
// THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
// FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
// DEALINGS IN THE SOFTWARE.
//**************************************************************************************

//**************************************************************************************
// replaces the old UserProfile page
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/utilities/StringUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");

require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");

require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");
require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_UserSessions.php");

require_once("C:/Inetpub/wwwroot/cwis438/utilities/GodmUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/SecurityUtil.php");

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/Profile.php");

require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_UserSettings.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_People.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Projects.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Jobs.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/REL_PersonToProject.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/REL_PersonToWebsite.php");

//**************************************************************************************
// Database connections
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Security
//**************************************************************************************

CheckLogin2($Database,PERMISSION_USER);

//**************************************************************************************
// Definitions
//**************************************************************************************

define("NUM_ROWS",5); // Display 5 projects at a time in the My Projects table - GJN

//**************************************************************************************
// Server side functions
//**************************************************************************************

//**************************************************************************************
// Parameters
//**************************************************************************************

$TakeAction=GetStringParameter("TakeAction","");
$NewCurrentProjectID=GetIntParameter("NewCurrentProjectID",0);
$CurrentRow=GetIntParameter("CurrentRow");
$ShowOnlyProjectsForThisWebsite=GetCheckboxParameter("ShowOnlyProjectsForThisWebsite",-1); // $DefaultValue; default value is -1
$NewMediaFileName=GetStringParameter("NewMediaFileName");
$PersonID=GetIntParameter("PersonID");
$ProjectID=GetIntParameter("ProjectID",-1);

//**************************************************************************************
// Server-side code
//**************************************************************************************

$WebSiteID=WEBSITE_LEAF;

$UserID=GetUserID();
$UserName=TBL_People::GetPersonsName($Database,$UserID);

//

$MyCallingPage=urlencode("/cwis438/Websites/LEAF/UserProfile.php?WebSiteID=20".
	"&CurrentRow=$CurrentRow".
	"&ShowOnlyProjectsForThisWebsite=$ShowOnlyProjectsForThisWebsite");

$MyCallingLabel="To My Profile";

//

switch ($TakeAction)
{
	case "ChangeCurrentProject":
		TBL_People::SetCurrentProjectID($Database,$UserID,$NewCurrentProjectID);
		break;
	case "RequestNewPermission":
		RedirectFromRoot("/cwis438/UserManagement/RequestNewUserLevel.php");
		break;
	case "EditMyProfile":
			RedirectFromRoot("/cwis438/UserManagement/People_Edit.php");
		break;
	case "ChangePassword":
		RedirectFromRoot("/cwis438/UserManagement/password_edit.php?WebSiteID=$WebSiteID");
		break;
	case "UploadPhoto":
		$MyCallingPage=urlencode("/cwis438/Websites/LEAF/UserProfile.php?WebSiteID=20".
			"&CurrentRow=$CurrentRow&ShowOnlyProjectsForThisWebsite=$ShowOnlyProjectsForThisWebsite".
			"&TakeAction=ReturnedFromMediaUpload&NewMediaFileName=");
		RedirectFromRoot("/cwis438/contribute/Upload/upload_media.php?CallingPage=$MyCallingPage&CallingLabel=$MyCallingLabel");
		break;
	case "AddPhoto":
		$MyCallingPage=urlencode("/cwis438/Websites/LEAF/UserProfile.php?WebSiteID=20".
			"&CurrentRow=$CurrentRow&ShowOnlyProjectsForThisWebsite=$ShowOnlyProjectsForThisWebsite".
			"&TakeAction=ReturnedFromMediaTable&NewMediaID=");
		RedirectFromRoot("/cwis438/Browse/Media_Table.php?PersonID=$UserID&CallingPage=$MyCallingPage&CallingLabel=$MyCallingLabel");
		break;
	case "ReturnedFromMediaUpload":
		// Update the person's designated MediaID in TBL_People
		$NewMediaSet=TBL_Media::GetSetFromFilePath($Database,$UserID,$NewMediaFileName);
		$NewMediaID=$NewMediaSet->Field("ID");
		TBL_People::SetMediaID($Database,$UserID,$NewMediaID);
		break;
	case "ReturnedFromMediaTable":
		// Update the person's designated MediaID in TBL_People
		$NewMediaID=GetIntParameter("NewMediaID");
		TBL_People::SetMediaID($Database,$UserID,$NewMediaID);
		break;
	case "Update":
		TBL_UserSettings::Set($Database,"ShowOnlyProjectsForThisWebsite",$ShowOnlyProjectsForThisWebsite);
		break;
	case "JoinProject":
		$MyCallingPage=urlencode("/cwis438/Websites/LEAF/UserProfile.php?WebSiteID=20".
			"&CurrentRow=$CurrentRow&ShowOnlyProjectsForThisWebsite=$ShowOnlyProjectsForThisWebsite");
		RedirectFromRoot("/cwis438/UserManagement/AddNewProjectRole.php?CallingPage=$MyCallingPage&CallingLabel=$MyCallingLabel");
		break;
	case "CreateProject":
	    $MyCallingPage=urlencode("/cwis438/Websites/LEAF/UserProfile.php?WebSiteID=20".
	            "&CurrentRow=$CurrentRow&ShowOnlyProjectsForThisWebsite=$ShowOnlyProjectsForThisWebsite");
	    RedirectFromRoot("/cwis438/websites/LEAF/Project_Edit.php?WebSiteID=20&TakeAction=New&CallingPage=$MyCallingPage&CallingLabel=$MyCallingLabel");
		break;
	case "Edit": // Edit a persons roles for this project
	{
		$WebsiteID=GetWebsiteID();
		
		$MyCallingPage=urlencode("/cwis438/Websites/LEAF/UserProfile.php?WebSiteID=20");
		
		$MyCallingLabel="To Manage My Project";

		RedirectFromRoot("/cwis438/UserManagement/RequestNewProjectRole.php?".
			"ProjectID=$ProjectID&PersonID=$PersonID&CallingPage=$MyCallingPage&CallingLabel=$MyCallingLabel&WebSiteID=$WebsiteID");
	}
	break;
	default:
		//
		break;
}

if ($TakeAction=="ChangeCurrentProject")
{
	TBL_People::SetCurrentProjectID($Database,$UserID,$NewCurrentProjectID);
}
else if ($TakeAction=="Update")
{
	TBL_UserSettings::Set($Database,"ShowOnlyProjectsForThisWebsite",$ShowOnlyProjectsForThisWebsite);
}

$ShowOnlyProjectsForThisWebsite=TBL_UserSettings::Get($Database,"ShowOnlyProjectsForThisWebsite",$ShowOnlyProjectsForThisWebsite);

$TotalRows=REL_PersonToProject::GetTotalRows($Database,$UserID);

// get a page object so we can use it to write out links arrays

$CallingPage=urlencode("/cwis438/Websites/LEAF/UserProfile.php?WebSiteID=20");
$CallingLabel="To User Profile";

$ThePage=new PageSettings;
$TheTable=new TableSettings();

$TakeAction="Update";

// -----------------------------------------------------------------------------

$PersonName=TBL_People::GetPersonsName($Database,$UserID);

    $PersonSet=TBL_People::GetSetFromID($Database,$UserID);
    $StateID=$PersonSet->Field("StateID");
    //$StateName=LKU_States::GetValue($Database,"Name",$StateID);
    $Email=$PersonSet->Field("Email");
    $FirstName=$PersonSet->Field("FirstName");
    $LastName=$PersonSet->Field("LastName");
    $Login=$PersonSet->Field("Login");
    $WorkPhone=$PersonSet->Field("WorkPhone");
    $City=$PersonSet->Field("City");
    $ZipCode=$PersonSet->Field("ZipCode");
    $CountryID=$PersonSet->Field("CountryID");
    $MediaID=$PersonSet->Field("MediaID");
    $ReceivesNewsletter=$PersonSet->Field("ReceivesNewsletter");
    
    // Measurements
    
    $NumMeasurements=TBL_People::GetTotalMeasurements($Database,$UserID);
	if ($NumMeasurements=="") $NumMeasurements=0;
    
    //Observations
    
    $VisitsSelectString="SELECT COUNT(1) as Count " .
                "FROM TBL_Visits " .
                "WHERE (RecorderID=$UserID)";
    $VisitSet=$Database->Execute($VisitsSelectString);
    $NumObservations=$VisitSet->Field("Count");
    
    // Projects
    
    $NumCitSciProjectsQuery="SELECT COUNT(DISTINCT TBL_Projects.ID) AS NumProjects
            FROM REL_PersonToProject INNER JOIN
                      TBL_Projects ON REL_PersonToProject.ProjectID = TBL_Projects.ID INNER JOIN
                      REL_WebsiteToProject ON TBL_Projects.ID = REL_WebsiteToProject.ProjectID
            WHERE (REL_PersonToProject.PersonID = $UserID) AND (REL_WebsiteToProject.WebsiteID = 20)";
    $NumProjectsSet=$Database->Execute($NumCitSciProjectsQuery);
    $NumProjects=$NumProjectsSet->Field("NumProjects");
    
    // Locations
    
    $NumLocationsQuery="SELECT COUNT(DISTINCT AreaID) AS NumAreas
        FROM TBL_Visits
        WHERE (RecorderID = $UserID)";
    $NumLocationsSet=$Database->Execute($NumLocationsQuery);
    $NumLocations=$NumLocationsSet->Field("NumAreas");
    
    // photos
    
    $PhotoQuery="SELECT COUNT(1) AS Count
        FROM TBL_Media
        WHERE (PersonID = $UserID)";
    $PhotoSet=$Database->Execute($PhotoQuery);
    $NumPhotos=$PhotoSet->Field("Count");
    
    if ($PersonSet->Field("StateID") != null)
    {
        $SelectString = "SELECT AreaName " .
        "FROM TBL_Areas " .
        "WHERE ID = " . $PersonSet->Field("StateID");

        $StateSet = $Database->Execute($SelectString);
    }
    else
    {
        $StateSet=null;
    }
    
    if ($StateSet)
    {
        $StateName = $StateSet->Field('AreaName');
        if ($StateName == "Unknown") $StateName = "";
    }
    else
    {
        $StateName = "";
    }
    
    $FullAddress="";
    
    if (($City!=null) && ($City!=""))
    {
        $FullAddress=$City.", ".$StateName." ".$ZipCode;
    }
    else
    {
        $FullAddress=$StateName." ".$ZipCode;
    }
    
    $MyRolesText="Member";
    $IsManager=REL_PersonToProject::IsManager($Database);
    if ($IsManager) $MyRolesText="Project Manager";
    
    $UserImagePath="/cwis438/Websites/CitSci/images/default_avatar.png"; // default		
    
    if ($MediaID>0)
    {
        $UserImagePath=TBL_Media::GetPathFromID($Database,$MediaID,$Version=0,$UseRoot=false);
    }
    
    if ($TakeAction=="ReturnedFromMediaUpload")
    {
        // Update the person's designated MediaID in TBL_People
        $NewMediaSet=TBL_Media::GetSetFromFilePath($Database,$UserID,$NewMediaFileName);
        $NewMediaID=$NewMediaSet->Field("ID");
        TBL_People::SetMediaID($Database,$UserID,$NewMediaID);
        
        // update previous path in form
        
        $UserImagePath=TBL_Media::GetPathFromID($Database,$NewMediaID,$Version=0,$UseRoot=false);
    }

//**************************************************************************************
// HTML Header block, client-side includes, and client-side functions
//**************************************************************************************

$ThePage->HeaderStart("$UserName's User Profile");

?>

<!--<script src='/cwis438/websites/LEAF/js/jquery-1.9.1.code.jquery.js' type='text/javascript'></script>-->

<SCRIPT TYPE="text/javascript" LANGUAGE="javascript" SRC="/cwis438/includes/FormUtil.js"></SCRIPT>

<SCRIPT LANGUAGE="JavaScript">

$(function()
{
    
});

function DoSetCurrentProject()
{
	document.forms.SelectForm.TakeAction.value="ChangeCurrentProject";
	document.forms.SelectForm.submit();
}

function DoFilterProjects()
{
	document.forms.SelectForm.submit(); // re-submit the form to acquire Checkbox Parameter
}

function RequestNewPermission()
{
	document.SelectForm.TakeAction.value="RequestNewPermission";
	document.SelectForm.submit();
}

function EditMyProfile()
{
	document.SelectForm.TakeAction.value="EditMyProfile";
	document.SelectForm.submit();
}

function ChangePassword()
{
	document.SelectForm.TakeAction.value="ChangePassword";
	document.SelectForm.submit();
}

function UploadPhoto()
{
	document.SelectForm.TakeAction.value="UploadPhoto";
	document.SelectForm.submit();
}

function AddPhoto()
{
	document.SelectForm.TakeAction.value="AddPhoto";
	document.SelectForm.submit();
}
function DoEditProject(PersonID,ProjectID)
{
	//alert("ProjectID="+ProjectID);
	
	document.SelectForm.TakeAction.value="Edit";
	document.SelectForm.ProjectID.value=ProjectID;
	document.SelectForm.PersonID.value=PersonID;
	
	document.SelectForm.submit();
	//document.forms.SelectForm.submit();
}
</SCRIPT>

<style>
#RequestNew
{
	margin-bottom:5px;
}

.info
{
    margin-bottom: 0px;
    margin-top: 0px;    
}


.tip
{
    position: absolute;
    border: 1px solid #DDDDDD; /* #333 */
    border-radius: 4px;
    padding: 3px;
    z-index:100;
    background-color:#FFFFFF;
}

.tip
{ 
    display: none;
}

.nav-tabs > li.active > a, .nav-tabs > li.active > a:hover, .nav-tabs > li.active > a:focus 
{
    background-color: #fff !important;
}

.container 
{
    width:980px !important;
    padding-left:0px !important;
    padding-right:0px !important;
}

.collapse {
    display: inline !important;
}

.navbar .nav > li > a
{
    color: #ffffff !important;
    display: block !important;
    float: none !important;
    font-size: 13px !important;
    font-weight: bold !important;
    height: 25px !important;
    padding-top: 6px !important;
    text-align: center !important;
    text-decoration: none !important;
}

.navbar .nav > li > a:hover
{
    color: #000 !important;
}

.DonateButton
{
    width:80px;
}

.description h4
{
    text-align:center;
}

#footer-license
{
    margin-top: 20px !important;
}

</style>

<script>

$(function()
{    
    // Tooltip
    
    $(".info").click(function(event){
            $(".tip", this).toggle().css({});
            event.stopPropagation();
    });

    $(".info").hover(function(event){
            $(".tip", this).toggle().css({});
            event.stopPropagation();
    });

    // closes tooltip 
    $(window).click(function() {
            $(".tip").hide();
    });
    
});
    
</script>

<link rel="stylesheet" href="https://fortawesome.github.io/Font-Awesome/assets/font-awesome/css/font-awesome.css">
<link rel="stylesheet" href="/cwis438/websites/LEAF/stylesheets/bootstrap-tabs-x.min.css" media="all" type="text/css" />
<link rel="stylesheet" href="/cwis438/websites/LEAF/stylesheets/styles.css"/>

<script language="JavaScript">

$(function()
{
    LoadProfileHTML();

    //$("#tab1primary").trigger('click');

    $("[data-hide]").on("click", function()
    {
            $(this).closest("." + $(this).attr("data-hide")).hide();
    });

    $("#SubmitButton").click(function()
    {
        // submit new data into the database

        var serializedarray = $("#ProfileForm").serializeArray(); // gets NAME and value of each input

        jQuery.post("/cwis438/websites/citsci/WebServices/ProfileCard_Insert.php",serializedarray,function(data){},'json').success(function(data)
        {
            var FirstName=data.FirstName;
            var LastName=data.LastName;
            var PersonName=""+FirstName+" "+LastName+"";

            $("#TopLoginName").text(FirstName);

            $("#MostRecentPersonNameChange").val(PersonName);

            // reload Profile content html

            LoadProfileHTML();

            // show newly reloaded profile content / card

            $("#ProfileContent").show();

            // hide the editable form

            $("#MyProfile_EditFormDiv").hide();

            // show notification

            $("#Notification").show();
        });
    });
});

function LoadProfileHTML()
{
    // get Profile card content
    var url='/cwis438/websites/citsci/WebServices/GetProfileCard_HTML.php';
    var PersonID='<?php echo($UserID);?>';
    $.ajax({type:"POST",url:url,data:{PersonId:PersonID},success:function(html){$('#ProfileContent').html(html);$("#ProfileDetails").hide();},dataType: "html"});
    //alert("hi");
    //$("#ProfileDetails").hide();
    //$("#ProfileDetails").fadeOut(500);
}

function DoEditProfile()
{    
    $("#ProfileContent").hide();

    $("#MyProfile_EditFormDiv").show();
}

function DoShowProfileDetails()
{
    //$("#ProfileDetails").show();

    var HTML=$("#MoreLess").html();

    if (HTML=="Show Less")
    {
        //$("#ProfileDetails").hide();
        $("#ProfileDetails").fadeOut(500);
        $("#MoreLess").html("Show More<span class='caret'>");
    }
    else
    {
        //$("#ProfileDetails").show();
        $("#ProfileDetails").fadeIn(500);
        $("#MoreLess").html("Show Less"); //<span class='dropup'><span class='caret'></span></span>
    }
}

function DoCloseForm()
{
    //alert("close form now...");

    $("#MyProfile_EditFormDiv").hide();

    $("#ProfileContent").show();

}

function DoShareWithSciStarter()
{
    var ShareWithSciStarter=document.forms.PreferencesForm.ShareWithSciStarter.checked;
    //alert("changed scistarter..."+ShareWithSciStarter);

    var url="/cwis438/websites/citsci/webservices/EditPreferences.php";

    $.ajax({
        type:"POST",
        url: url,
        data: ShareWithSciStarter,
        success:function(data)
        {
            //$("#Notification").show();
        },
        error: function(XMLHttpRequest,textStatus,errorThrown)
        {
            //alert("some error");
        }
    });
}

</script>

<style>
.card-user .content
{
    min-height: 0px;
}
</style>
    
<?php

$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart();

$TheHost=GetComputerName();
$HelpLink="http://ibis.colostate.edu/DH.php?WC=/cwis438/help/UserManagement/UserProfile.htm&WebSiteID=$WebSiteID";

//$ThePage->Heading(0,"$UserName's User Profile"); // ,$HelpLink

?>
    <div id="Notification" class="alert alert-success fade in" hidden="hidden"> <!-- alert-dismissible collapse fade in -->
        <button type="button" class="close" data-hide="alert" style="margin-right:12px;">&times;</button>
        <b>Success!</b> You just edited your profile!
    </div>
    <?php

    $ThePage->Heading(2,"My Profile");

    ?>
    <div class="row">
        <div class="col-md-8">

            <div id="ProfileContent">
                <!-- filled in by LoadProfileCard() via ajax call to GetProfileCard_HTML.php--->
            </div>

            
            <!-- ************************************************************** -->
            <!-- *********************  Editing Form Div  ********************* -->
            <!-- ************************************************************** -->
            
            <div id="MyProfile_EditFormDiv" style="display:none;">

                <form id="ProfileForm">
                 
                <div class="card card-user">
                    <div class="image">
                         <img src="/cwis438/websites/citsci/images/thumb.png" alt="Profile Card Picture" title="Profile Card Picture"/>
                    </div>

                    <div class="content">
                        <div class="author">
                            <img class="avatar" src="<?php echo $UserImagePath; ?>" alt="My Profile Picture" title="My Profile Picture"/>						
                            <h4 class="title"><div id="PersonName"class="text-muted">--- Your Edited Name Will Appear Here---</div><br />
                                 <small><?php echo $MyRolesText; ?></small>
                            </h4>
                        </div>
                    </div>
                    
                    <div id="ProfileDetailsEditingMode">
                        <div class="pull-right">
                            <button type="button" class="close" aria-label="Close" style="padding:6px;" onclick="DoCloseForm();">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <dl class="dl-horizontal well">
                            <dt>First Name</dt><dd><input type="text" id="FirstName" name="FirstName" value="<?echo $FirstName;?>"></dd>
                            <dt>Last Name</dt><dd><input type="text" id="LastName" name="LastName" value="<?echo $LastName;?>"></dd>
                            <dt>Login</dt><dd><input type="text" id="Login" name="Login" value="<?echo $Login;?>"></dd>
                            <dt><i class="fa fa-envelope-o text-muted"></i>Email</dt><dd><input type="text" id="Email" name="Email" value="<?echo $Email;?>"></dd>
                            <dt><i class="fa fa-phone-square text-muted"></i>Phone</dt><dd><input type="text" id="WorkPhone" name="WorkPhone" value="<?echo $WorkPhone;?>"></dd>
                            <dt><i class="fa fa-map-marker text-muted"></i>Location</dt>
                                <dd>
                                    <label for="City">City:&nbsp;&nbsp;</label><input type="text" id="City" name="City" value="<?echo $City;?>"><br/>
                                    <div>
                                    <label for="State" style="float:left; margin-top:9px;">State:&nbsp;</label>
                                        <?php
                                        
                                        $ParentQuery="SELECT ID, AreaName, Code FROM TBL_Areas WHERE (AreaName='United States') AND (Code='US')";
                                        $ParentSet=$Database->Execute($ParentQuery);
                                        $ParentID=$ParentSet->Field("ID");
                                        
                                        $StatesQuery="SELECT ID, AreaName, Code
                                            FROM TBL_Areas
                                            WHERE (AreaSubtypeID = 3) AND (ParentID='$ParentID') ORDER BY AreaName";
                                        
                                        $StateSet=$Database->Execute($StatesQuery);
                                        
                                        //$StateSet=TBL_Areas::GetSetFromParentID($Database,$CountryID,3);
                                        
                                        $TheTable->FormRecordSet("StateID",$StateID,$StateSet,
                                            array("AreaName"),"ID",false,"None",0,0,false,
                                            $OtherAttributes="id='StateID' class='form-control' style='width:214px;'",false,null,null);
                                        ?>
                                    </div>
                                    <br/>
                                    <label for="ZipCode">Zip:&nbsp;&nbsp;</label><input type="text" id="ZipCode" name="ZipCode" value="<?echo $ZipCode;?>"><br/>
                                </dd>
                                <dt>Receive Newletter?</dt>
                                <dd>
                                    <div class=""><!-- switch has-switch -->
                                        <!--<label>Required?</label>-->
                                        <div class="switch-animate switch-on">
                                            <input name="ReceivesNewsletter" id="ReceivesNewsletter" class="ct-info" type="checkbox" data-toggle="switch" <? if ($ReceivesNewsletter==true) echo "checked=''"; ?>>&nbsp;&nbsp; 
                                            <span class="text-muted">Yes &mdash; sign me up for CitSci.org e-newlsetters</span>
                                        </div>
                                    </div>
                                </dd>
                        </dl>
                    </div>
                    
                    <div class="text-center">
                        <a href="/cwis438/UserManagement/Password_Edit.php?WebSiteID=20" class="btn-sm btn-default" style="background-color:#E6E6E6; padding:6px;">
                            Edit Password
                        </a>
                        &nbsp;&nbsp;
                        <?php 
                            $MyCallingPage=urlencode("/cwis438/UserManagement/UserProfile.php?&TakeAction=ReturnedFromMediaUpload&NewMediaFileName=");
                        ?>
                        <a href="/cwis438/contribute/Upload/upload_media.php?CallingPage=<?php echo $MyCallingPage; ?>&WebSiteID=20" class="btn-sm btn-default" style="background-color:#E6E6E6; padding:6px;">
                            Edit Photo
                        </a>
                    </div>
                    
                    <br/>
                    
                    <hr>

                    <div id="EditProfileButton">
                        <div class="text-center">
                            <input id='SubmitButton' type='button' class='btn btn-simple' value='Submit' />
                        </div>
                    </div>
                </div> <!-- end card -->

                <input type="hidden" name="PersonID" value="<?php echo $UserID;?>"/>
                
                <input type="hidden" id="MostRecentPersonNameChange" name="MostRecentPersonNameChange" value=""/>
                
                </form>

            </div>

        </div>

        <div class="col-md-3">
            <div class="well text-center">
                <!--<h3 class="panel-title">Options</h3>-->
                <a href="/cwis438/websites/LEAF/Project_Edit.php?WebSiteID=20" class="btn btn-simple" style="width:150px; margin-bottom: 12px;" type="button">Start a Project</a>
                <a href="/cwis438/Browse/Project/Project_List.php?WebSiteID=20" class="btn btn-simple" style="width:150px; margin-bottom: 12px;" type="button">Join a Project</a>
                <!--<button class="btn btn-simple" style="width:180px; margin-bottom: 12px;" type="button">Add Data</button>-->
            </div>
        </div>
    </div>
    
    <!-- ***************** Tabs ********************** -->

    <div class="row">
        <div class="col-md-11">
            <div class="panel with-nav-tabs panel-default tabs-x">
                <div class="panel-heading">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a href="#tab0primary" data-toggle="tab">
                                    <i class="fa fa-home text-muted" aria-hidden="true"></i>Home
                                </a>
                            </li>
                            <li>
                                <a href="#tab1primary" data-toggle="tab" role="tab-kv" data-url="/cwis438/Websites/LEAF/WebServices/GetMyProjects.php">
                                    <i class="fa fa-th text-muted" aria-hidden="true"></i>My Projects
                                </a>
                            </li>
                            <li>
                                <a href="#tab2primary" data-toggle="tab" role="tab-kv" data-url="/cwis438/Websites/LEAF/WebServices/GetMyObservations.php">
                                    <i class="fa fa-binoculars text-muted" aria-hidden="true"></i>My Observations
                                </a>
                            </li>
                            <li>
                                <a href="#tab3primary" data-toggle="tab" role="tab-kv" data-url="/cwis438/Websites/LEAF/WebServices/GetMyLocations.php">
                                    <i class="fa fa-map-marker text-muted" aria-hidden="true"></i>My Locations
                                </a>
                            </li>
                            <li>
                                <a href="#tab4primary" data-toggle="tab">
                                    <i class="fa fa-bars text-muted" aria-hidden="true"></i>My Preferences
                                </a>
                            </li>
                        </ul>
                </div>
                <div class="panel-body" style="min-height:200px; padding:0px;">
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="tab0primary">
                            
                            <h4>My Contributions</h4>

                            <div class="col-md-2">
                                <div class="info">
                                    <div class="icon icon-azure">
                                        <!--<i class="pe-7s-clock"></i>-->
                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                    </div>
                                    <div class="description">
                                        <h4> <?php echo $NumMeasurements; ?> </h4>
                                        <p class="text-muted">measurements</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="info">
                                    <div class="icon icon-green">
                                        <!--<i class="pe-7s-clock"></i>-->
                                        <i class="fa fa-binoculars" aria-hidden="true"></i>
                                    </div>
                                    <div class="description">
                                        <h4> <?php echo $NumObservations; ?> </h4>
                                        <p class="text-muted">observations</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="info">
                                    <div class="icon icon-orange">
                                        <!--<i class="pe-7s-clock"></i>-->
                                        <i class="fa fa-map-marker" aria-hidden="true"></i>
                                    </div>
                                    <div class="description">
                                        <h4> <?php echo $NumLocations; ?> </h4>
                                        <p class="text-muted">locations</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="info">
                                    <div class="icon icon-black">
                                        <!--<i class="pe-7s-clock"></i>-->
                                        <i class="fa fa-picture-o" aria-hidden="true"></i>
                                    </div>
                                    <div class="description">
                                        <h4> <?php echo $NumPhotos; ?> </h4>
                                        <p class="text-muted">pictures</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="width:100%; display:inline-block;">
                                <h4>Recent Activity</h4>
                                <div class="well">
                                    <p class="text-muted">coming soon</p>
                                </div>
                            </div>
                            
                        </div>
                        <div class="tab-pane fade in active" id="tab1primary"></div>
                        <div class="tab-pane fade" id="tab2primary"></div>
                        <div class="tab-pane fade" id="tab3primary"><h4>My Locations</h4></div>
                        <div class="tab-pane fade" id="tab4primary">
                            <form id="PreferencesForm">
                                <h4>My Preferences</h4>
                                <!--
                                <dl class="dl-horizontal well">
                                    <dt>
                                        SciStarter &nbsp; 
                                        <div class="switch" data-on-label="<i class='fa fa-check'></i>" data-off-label="<i class='fa fa-times'></i>">
                                            <input id="ShareWithSciStarter" name="ShareWithSciStarter" type="checkbox" onchange="DoShareWithSciStarter()" checked/>
                                        </div>
                                    </dt>
                                    <dd>
                                        Recruit more participants through SciStarter (free service) and help advance research 
                                        Recruit more participants through SciStarter (free service) and help advance research
                                        Recruit more participants through SciStarter (free service) and help advance research
                                    </dd>

                                    <dt>
                                        SciStarter &nbsp; 
                                        <div class="switch" data-on-label="<i class='fa fa-check'></i>" data-off-label="<i class='fa fa-times'></i>">
                                            <input type="checkbox"/>
                                        </div>
                                    </dt>
                                    <dd>
                                        Recruit more participants through SciStarter (free service) and help advance research 
                                        Recruit more participants through SciStarter (free service) and help advance research
                                        Recruit more participants through SciStarter (free service) and help advance research
                                    </dd>
                                </dl>
                                -->
                            </form>
                        </div>
                        <!--<div class="tab-pane fade" id="tab5primary">Primary 5</div>-->
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    
    <!--
    <div class="">
    <div class="switch-animate switch-on">
    <div class="switch has-switch">
    <div class="switch-on switch-animate" style="">
    <input class="ct-info" type="checkbox" checked="" data-toggle="switch">
    <span class="switch-left">ON</span>
    <label> </label>
    <span class="switch-right">OFF</span>
    </div>
    </div>
    </div>
    </div>
    -->
    <?php

$ThePage->BodyEnd();

//**************************************************************************************
// Client-side javascript for after load
//**************************************************************************************

?>

<!--  jQuery and Bootstrap core files    -->
<!--<script src="/cwis438/websites/citsci/assets/js/jquery.js" type="text/javascript"></script>-->

<script src="/cwis438/websites/citsci/assets/js/jquery-ui.custom.min.js" type="text/javascript"></script>
<script src="/cwis438/websites/citsci/bootstrap3/js/bootstrap.min.js" type="text/javascript"></script>
    
<!-- Plugins -->
<script src="/cwis438/websites/citsci/assets/js/gsdk-checkbox.js"></script>
<script src="/cwis438/websites/citsci/assets/js/gsdk-morphing.js"></script>
<script src="/cwis438/websites/citsci/assets/js/gsdk-radio.js"></script>
<script src="/cwis438/websites/citsci/assets/js/gsdk-bootstrapswitch.js"></script>
<script src="/cwis438/websites/citsci/assets/js/bootstrap-select.js"></script>
<script src="/cwis438/websites/citsci/assets/js/bootstrap-datepicker.js"></script>
<script src="/cwis438/websites/citsci/assets/js/chartist.min.js"></script>
<script src="/cwis438/websites/citsci/assets/js/jquery.tagsinput.js"></script>

<!--  Get Shit Done Kit PRO Core javascript -->
<script src="/cwis438/websites/citsci/assets/js/get-shit-done.js"></script> 
