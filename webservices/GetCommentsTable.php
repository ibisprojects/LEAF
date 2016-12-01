<?php

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");
require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");
require_once("C:/Inetpub/wwwroot/Classes/Date.php");
require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/classes/DBTable/TBL_Projects.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/SecurityUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Media.php");

require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Events.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Permissions.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Models.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Blogs.php");

//**************************************************************************************
// Database Connection
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Security
//**************************************************************************************

$UserID=0;

$UserID = GetUserID();

//**************************************************************************************
// Parameters
//**************************************************************************************

//$PersonID=GetUserID();
$WebsiteID=GetWebSiteID();
$DatabaseTableID=GetIntParameter("DatabaseTableID");
$RecordID=GetIntParameter("RecordID");
$ProjectID=null;

//DebugWriteln("DatabaseTableID=$DatabaseTableID");
//DebugWriteln("RecordID=$RecordID");

//**************************************************************************************
// Server side functions
//**************************************************************************************

function count_children($Database,$ParentID) {
    $ChildSelectString = "SELECT COUNT(1) As Count FROM TBL_Blogs WHERE ParentID = $ParentID";
    $ChildSet = $Database->Execute($ChildSelectString);
    $Count = 0;
    if ($ChildSet->FetchRow()) {
        $Count = $ChildSet->Field("Count");
    }
    return $Count;
}

function display_children($ID,$Level,$RecordID,$DatabaseTableID,$ProjectID)
{
    global $Database, $TheTable;
    
    $UserID = GetUserID();
    
    $ChildSelectString=NULL;

    if($ID===NULL)
    {
        $ChildSelectString="SELECT * FROM TBL_Blogs 
            WHERE (ParentID IS NULL) 
            AND (DatabaseTableID=$DatabaseTableID)
            AND (RecordID=$RecordID)
            AND (ProjectID IS NULL)";
        
        //DebugWriteln("cheldselectstrting=$ChildSelectString");
    }
    else
    {
        $ChildSelectString="SELECT * FROM TBL_Blogs WHERE ParentID = $ID";
    }
    
    //DebugWriteln("cheldselectstrting=$ChildSelectString");
    
    $ChildSet=$Database->Execute($ChildSelectString);

    while($ChildSet->FetchRow())
    {
        $PersonID=$ChildSet->Field("PersonID");
        $PersonSelectString="SELECT * FROM TBL_People WHERE ID = $PersonID";
        $PersonSet=$Database->Execute($PersonSelectString);
        
        $FullName=$PersonSet->Field("FirstName")."&nbsp;".$PersonSet->Field("LastName");

        $Text=$ChildSet->Field("Blog"); // was Text
        $ParentID=$ChildSet->Field("ID");
        $Title=$ChildSet->Field("Title");
        $Date=$ChildSet->Field("Date");
        $DateString=date("F j, Y", strtotime($Date));    
        
        $Color=$Level % 2 == 0 ? "#fff" : "#EEEFCB"; // E8E8C6 E4EAED "#fff" : "#F3F5F7"     E4EAED

        if($Title===null)
        {
            echo("<div style='margin:5px 15px 0px 15px; background-color:$Color; padding:5px; border:1px solid #E8E8C6;'><i>posted by: $FullName</i> <br/>$Text<br/>"); //E9EE88
        }
        else
        {
            echo("<div style='margin:10px 0px 10px 0px; padding:5px; border:2px solid #E8E8C6; background-color:$Color;'>"); //red outer bin... E9EE88
            echo("<div><strong>$Title</strong></div><i>posted by:</i> $FullName <i>on</i> $DateString <br/><div style='margin-top:4px; margin-bottom:12px;'>$Text</div>");
        }
        
        if ($UserID > 0 && $Level <= 1) 
        {
            if(count_children($Database,$ParentID)>0) 
            {                
                echo("<input type='button' id='showreplies$ParentID' parentid='$ID' onclick='ShowHideReply(\"$ParentID\",this);' class='commentButton' value='View Replies' alt='View Replies' title='View Replies'/>");
                echo(" ");
            }
            echo("<input type='button' onclick='AddReply(\"$ParentID\");' value='Reply' class='commentButton' alt='Reply' title='Reply'/>");            
        }

        echo("<div class='hide' id='replies$ParentID' >");
            display_children($ParentID,$Level+1,$RecordID,$DatabaseTableID,$ProjectID); //  $ID, $Level+1,$RecordID
        echo("</div>");
        echo("</div>");
    }
}

//**************************************************************************************
// HTML
//**************************************************************************************

$TheTable=new TableSettings(TABLE_LAYOUT);

echo("<h2>Discussion Forum</h2>");

if ($UserID > 0)
{
    echo("<input type='button' onclick='AddComment();' value='Add New Discussion' class='registerbutton' style='width:140px;' alt='Add New Discussion Thread' title='Add New Discussion Thread'/>"); //AddCOmment($RecordID)
}
display_children(null,0,$RecordID,$DatabaseTableID,$ProjectID); //null,$Level,$RecordID; first param is ParentID

//display_children($RecordID,1,$RecordID,$DatabaseTableID,$ProjectID); //null,$Level,$RecordID; first param is ParentID

?>