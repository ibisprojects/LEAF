<?php
//**************************************************************************************
// FileName: Add_Data.php
// Author: GN, RS, BF, NK
// Purpose:  Allows user to:
//      1) Add single plant (links to Add_Single_Plant.php page)
//      2) Upload file (opens Add_File_Modal.php pop-up which submits to this page)
//      3) Add story (opens Add_Story_Modal.php pop-up which submits to this page)
//**************************************************************************************

//**************************************************************************************
// Includes
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");

require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");
require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");
require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/classes/DBTable/TBL_Projects.php");
require_once("C:/Inetpub/wwwroot/cwis438/classes/DBTable/TBL_Permissions.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/SecurityUtil.php");

require_once("C:/Inetpub/wwwroot/Classes/Date.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/LKU_CoordinateSystems.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/LKU_AreaSubtypes.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_SpatialLayerData.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/ProjectionUtil.php");

require_once("C:/Inetpub/wwwroot/cwis438/classes/DBTable/TBL_InsertLogs.php");
require_once("C:/Inetpub/wwwroot/cwis438/classes/DBTable/TBL_JobOutputs.php");

//**************************************************************************************
// Security
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//CheckLogin2($Database,PERMISSION_USER);


//$CallingPage="/cwis438/websites/LEAF/Add_Data.php?WebSiteID=20";

//**************************************************************************************
// Security
//**************************************************************************************

// No security needed to view this page.

//**************************************************************************************
// Server-side functions
//**************************************************************************************

function AlterArrayElement(&$val)
{
    $val=preg_replace('/\s+/','',$val);
}

//**************************************************************************************
// Parameters
//**************************************************************************************

$TakeAction=GetStringParameter("TakeAction","");

$UploadedFilePath=GetStringParameter("UploadedFilePath","");

$PersonID=GetUserID();

$ProjectID=GetIntParameter("ProjectID",-999);

//get projid

if ($ProjectID==-999)
{
    $SelectString="SELECT ID, ProjName 
    FROM TBL_Projects 
    WHERE (ProjName='LEAF')";

$ProjectIDSet=$Database->Execute($SelectString);

$ProjectID=$ProjectIDSet->Field("ID");
}
//$InsertLogID=null;

//**************************************************************************************
// Server-Side code
//**************************************************************************************

$Success=false; 
$NumRecordsInserted=null;

switch($TakeAction)
{
        case "AddPlant":
                
                $Success=true;
                break;
                
        case "AddFile":            
                  // server side parse file and do an "AddPoint"
                  $array=TBL_Projects::AddData($Database,$UploadedFilePath,$ProjectID,$PersonID,INSERT_LOG_FILE);

                  $InsertLogID=$array[1];
                  $NumRecordsInserted=$array[0];

                  $Success=true;
                
                break;
                
        case "AddStory":
                
                $Success=true;
                break;
       
        case "Delete":    // Delete the Visits, Areas, InsertLog, Attributes
              
              $InsertLogID=GetStringParameter("InsertLogID");
              
              // Deleting the InsertLog via DBTables::Delete cause cascade delete of all voisits and asociated areas and attributes...
              
              TBL_InsertLogs::Delete($Database,$InsertLogID);  // Deletes only the InsertLog
              
              break;

}

//**************************************************************************************
// HTML Header block, client-side includes, and client-side functions
//**************************************************************************************

$ThePage=new PageSettings();

$ThePage->HeaderStart("LEAF - Contribute");

echo("<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js'></script>"); // /cwis438/Includes/JQuery/jquery-1.7.2.min.js
echo("<link href='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' rel='stylesheet' type='text/css' />");
echo("<script src='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js'></script>");


?>

<script>

function AddSingleReport()
{
	//alert("about to redirect...");
	window.location="/cwis438/Websites/LEAF/Submit_Observation.php?WebSiteID=20";
}

function UploadFile()
{	
        //alert("about to upload a file...");
        $( "#dialog_uploadfile").dialog({ modal: true });
        $( "#dialog_uploadfile").dialog({ minHeight: 300 });
        $( "#dialog_uploadfile").dialog({ width: 600 });
        $( "#dialog_uploadfile").dialog({ resizable: true });
        $( "#dialog_uploadfile").dialog('open');

        var overlay = $(".ui-widget-overlay");
        baseBackground = overlay.css("background");
        baseOpacity = overlay.css("opacity");
        overlay.css("background", "black").css("opacity", "1");
        
        $(".ui-widget-overlay").css("backgroundColor", baseBackground).css("opacity", baseOpacity).css("filter", "Alpha(Opacity=60)");
}

function AddStory()
{	
        //alert("about to upload a file...");
        $( "#dialog_addstory").dialog({ modal: true });
        $( "#dialog_addstory").dialog({ minHeight: 300 });
        $( "#dialog_addstory").dialog({ width: 590 });
        $( "#dialog_addstory").dialog({ resizable: false });
        $( "#dialog_addstory").dialog('open');
}

function DoDelete()
{
      if (window.confirm("Are you REALLY sure you want to delete these records."))
      {
        //alert("hi");    
        document.Russ.TakeAction.value='Delete';
        //alert("TakeAction="+document.Russ.TakeAction.value);
        document.Russ.submit();
      }
}	

</script>

<style>
   
</style>    

<?php 

$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart();

require_once("C:/Inetpub/wwwroot/cwis438/websites/LEAF/Add_File_Modal.php");
require_once("C:/Inetpub/wwwroot/cwis438/websites/LEAF/Add_Story_Modal.php");

?>

<p style="font-size:20px;">Contribute</p>
<br>

<!--<input type='button' value='Add Single Report' id='add-data-button' onclick='AddSingleReport();'>
<input type='button' value='Upload File' id='add-data-button' onclick='UploadFile();'>
<input type='button' value='Add Story' id='add-data-button' onclick='AddStory();'>-->

<?php

echo ("<h2 style='width:90%;'></h2>");

echo("<div class='section' position:relative; style='height:200px; width:90%;'>");
    echo ("<div style='display:inline-block; *display:inline; zoom:1; margin-right:30px; width:250px; height:175px; border:2px solid black; background-image:url(images/Home_4.jpg)'>");
        echo ("<div style='margin-top:10px; '>");
            echo("<a href='/cwis438/Websites/LEAF/Submit_Observation.php?WebSiteID=20' class='button orange' onclick='AddSingleReport();'>Add Plant</a>");
        echo ("</div>");
    echo ("</div>"); 
    
    echo("<div class='sectiontext' style='margin-top:0px; display:inline-block; *display:inline; zoom:1; height:150px; width:500px; '>");
        echo("<a href='/cwis438/Websites/LEAF/Submit_Observation.php?WebSiteID=20' style='text-decoration:none; font:bold 18px trebuchet ms; color:#3A5C83;'>Add A Single Plant Observation</a>");
        echo("<p style='font:16px trebuchet ms;'>Report the location of a single plant you observed.</p>");
    echo ("</div>");  // end sectiontext div
    
echo ("</div>"); // end section div
/*
echo ("<h2 style='width:90%;'></h2>");

echo("<div class='section' style='height:200px; width:90%;'>");
    echo ("<div style='display:inline-block; *display:inline; zoom:1; margin-right:30px; width:250px; height:175px; border:2px solid black; background-image:url(images/Home_7.jpg)'>");
        echo ("<div style='margin-top:10px;'>");
            echo("<a class='button orange' onclick='UploadFile();'>Upload File</a>");
        echo ("</div>");
    echo ("</div>");
    
    echo("<div class='sectiontext' style='display:inline-block; *display:inline; zoom:1; height:150px; width:500px; '>");
        echo("<a onclick='UploadFile();' style='text-decoration:none; font:bold 18px trebuchet ms; color:#3A5C83;'>Upload A File</a>");
        echo("<p style='font:16px trebuchet ms;'>Upload a file containing multiple plant observations.</p>");
    echo ("</div>");  // end sectiontext div
    
echo ("</div>");   // end section div 
*/
echo ("<h2 style='width:90%;'></h2>");

echo("<div class='section' style='height:200px; width:90%;'>");
    echo ("<div style='display:inline-block; *display:inline; zoom:1; margin-right:30px; width:250px; height:175px; border:2px solid black; background-image:url(images/Home_5.jpg)'>");
        echo ("<div style='margin-top:10px; '>");
            echo("<a class='button orange' onclick='AddStory();'>Add Story</a>");
        echo ("</div>");
    echo ("</div>");
    
    echo("<div class='sectiontext' style='display:inline-block; *display:inline; zoom:1; height:150px; width:500px; '>");
        echo("<a onclick='AddStory();' style='text-decoration:none; font:bold 18px trebuchet ms; color:#3A5C83;'>Add A Story</a>");
        echo("<p style='font:16px trebuchet ms;'>Contribute a story about plants in your region.</p>");
    echo ("</div>");  // end sectiontext div
    
echo ("</div>");  //end section div


if ($Success==true)
{   
      echo("<h1>Thanks for submitting $NumRecordsInserted plant observation(s) to the Atlas.</h1>"); 
}

$ThePage->BodyEnd();

?>
