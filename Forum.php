<?php
//**************************************************************************************
// FileName: Forum.php
// Author: GN, RS, BF, NK
// The main Forum page for the LEAF website (Living Atlas of East African Flora)
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

//CheckLogin2($Database,PERMISSION_USER);

//**************************************************************************************
// Server-side functions
//**************************************************************************************

//**************************************************************************************
// Parameters
//**************************************************************************************

$WebsiteID=GetWebsiteID();

$UserID = GetUserID();

//**************************************************************************************
// Server-Side code
//**************************************************************************************

//**************************************************************************************
// HTML Header block, client-side includes, and client-side functions
//**************************************************************************************

$ThePage=new PageSettings();

$ThePage->HeaderStart("Forum");

?>

<style>

.BigLink
{
    font-size:14px; font-family:Arial; color:#2F4F40;
}

.hide {
  display: none;
}

.show {
  display: block;
}

.commentButton {
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    -moz-border-right-colors: none;
    -moz-border-top-colors: none;
    background: none repeat scroll 0 0 rgba(0, 0, 0, 0) !important;
    border-color: -moz-use-text-color -moz-use-text-color #444;
    border-image: none;
    border-style: none none solid;
    border-width: medium medium 1px;
    cursor: pointer;
    padding: 0 !important;
}
</style>

<script src='/cwis438/Includes/JQuery/jquery-1.9.1.code.jquery.js'></script>
<script src='/cwis438/Includes/JQuery/jquery-ui-1.9.1.code.jquery.com.js'></script>

<link rel='stylesheet' href='http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css' />
<link rel='stylesheet' type='text/css' href='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' />

<script>

//-----------------------------------------------------------------------------------------------------------------
//global variables
//-----------------------------------------------------------------------------------------------------------------

var WebsiteID=<?php echo($WebsiteID); ?>;

//-----------------------------------------------------------------------------------------------------------------
//ready function to prepare the page for first load
//-----------------------------------------------------------------------------------------------------------------

$(document).ready(function()
{
	LoadCommentsTable();	
});

//-----------------------------------------------------------------------------------------------------------------
//AJAX JQuery functions
//-----------------------------------------------------------------------------------------------------------------
function ShowHideReply(ParentID, Button) 
{
    var selector = 'replies' + ParentID;
    $("#" + selector).toggle('hide');
    if ($.trim($(Button).prop('value')) === 'View Replies') {
        $(Button).prop('value', 'Hide Replies');
    } else {
        $(Button).prop('value', 'View Replies');
    }
}
    
function LoadCommentsTable(WebsiteID) // get all comments for DatabaseTableID=83 (TBL_WEbsites) and RecordID=20 (LEAF)
{	
    var url="/cwis438/websites/LEAF/WebServices/GetCommentsTable.php?DatabaseTableID=83&RecordID=20"; //83=TBL_WEbsites; 20=LEAF record,

    //$.post(url,null,function(html){$('#CommentsTableDiv').html(html);});
    
    var Result = (jQuery.ajax({url: url, async: false}).responseText);

    $('#CommentsTableDiv').html(Result);
    var parentID = null;
    if (typeof SelectedParentCommentID !== 'undefined' && SelectedParentCommentID !== 0 && !isNaN(SelectedParentCommentID)) {
         parentID = SelectedParentCommentID;
    }
    if ($.isNumeric(parentID)) {
        var button = $("#showreplies" + parentID);
        var parent = $(button).attr('parentid');
        button.click();
        console.log($.isNumeric(parent));
        while ($.isNumeric(parent)) {
            button = $("#showreplies" + parent);
            parent = $(button).attr('parentid');
            button.click();
        }
    }
}

</script>

<script>

var UserID=<?php echo($UserID);?>;

function AddComment()
{	
    if (UserID<=0)
    {
        alert("Please login to add a comment");
    }
    else
    {
        //alert('Add comment for...');
        //console.debug(jQuery.ui);

        $( "#dialog_addcomment").dialog({ modal: true });
        $( "#dialog_addcomment").dialog({ minHeight: 400 });
        $( "#dialog_addcomment").dialog({ width: 600 });
        $( "#dialog_addcomment").dialog({ resizable: true });
        $( "#dialog_addcomment").dialog('open');

        var overlay = $(".ui-widget-overlay");
        baseBackground = overlay.css("background");
        baseOpacity = overlay.css("opacity");
        overlay.css("background", "black").css("opacity", "1");
        $(".ui-widget-overlay").css("backgroundColor", baseBackground).css("opacity", baseOpacity).css("filter","Alpha(Opacity=60)");

        var dialog = $(".ui-dialog-titlebar");
        dialog.css("background","#E9E8B8");
    }
}

function AddReply(ParentID)
{	
    if (UserID<=0)
    {
        alert("Please login to add your reply");
    }
    else
    {
        //alert("ParentID="+ParentID);

        $( "#dialog_addreply").dialog({ modal: true });
        $( "#dialog_addreply").dialog({ minHeight: 400 });
        $( "#dialog_addreply").dialog({ width: 600 });
        $( "#dialog_addreply").dialog({ resizable: true });
        $( "#dialog_addreply").dialog('open');

        var overlay = $(".ui-widget-overlay");
        baseBackground = overlay.css("background");
        baseOpacity = overlay.css("opacity");
        overlay.css("background", "black").css("opacity", "1");
        $(".ui-widget-overlay").css("backgroundColor", baseBackground).css("opacity", baseOpacity).css("filter","Alpha(Opacity=60)");

        var dialog = $(".ui-dialog-titlebar");
        dialog.css("background","#E9E8B8");

        //$("#dialog_addreply").dialog({ title: ParentID });

        SelectedParentCommentID=ParentID;
    }
}

</script>

<?php

$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart();

if ($UserID>0)
{

require_once("Add_Comment_Modal.php"); // this is an include of a file that contains the html/php for the contents of the modal dialog
require_once("Reply_Comment_Modal.php"); // this is an include of a file that contains the html/php for the contents of the modal dialog

}

//echo("<h2>Forum</h2>");

echo("<div id='CommentsTableDiv' style='float:left; width:97%; height:auto; padding:10px; border: solid 2px #DBD78B;'></div>"); // E4EAED

$ThePage->LineBreak();
$ThePage->LineBreak();

echo("<div style='width:100%; height:600px;'></div>");

$ThePage->BodyEnd(); 

?>
