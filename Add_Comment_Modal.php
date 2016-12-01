<?php
//**************************************************************************************
// FileName: Add_Comment_Modal.php
// Author: RS
// Owner:
// Purpose: Allows users to enter comments to LEAF project profile FORUM
//
//
// Note: this file is included (via require_once) in Project_Info.php and serves as the 
// model dialog content for the Project Info page. All variables defined in Project_Info.php
// are locally avauilable to this file (e.g., $UploadFileCallingPage) and thus we know 
// where to redirect aftre uplaoding a file.
// 
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/GodmUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/StringUtil.php");
require_once("C:/Inetpub/wwwroot/Classes/Date.php");

require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Blogs.php");

//**************************************************************************************
// Database Connection
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Security
//**************************************************************************************

CheckLogin2($Database,PERMISSION_USER);

//**************************************************************************************
// Parameters
//**************************************************************************************

$PersonID=GetUserID();
$WebsiteID=GetWebSiteID();
$ProjectID=GetIntParameter("ProjectID");

//$ProjectID=GetIntParameter("ProjectID");

$VisitDateObject=GetDateParameter("Date");
$Title=GetStringParameter("Title");
$Text=GetStringParameter("Text");
$DatabaseTableID=GetIntParameter("DatabaseTableID",255); // id for LKU_AdaptiveManagmentSteps
$RecordID=GetIntParameter("RecordID");

//**************************************************************************************
// Server-side code
//**************************************************************************************

if ($Text!=="")
{       
    $CommentID=TBL_Blogs::Insert($Database,$Text);
    
    $CommentID=TBL_Blogs::Update($Database,$CommentID,$DatabaseTableID,$RecordID,NULL,$Title,$PersonID,$ProjectID);
}   

// Get list of all projects for website to populate pick list for adding an event

//**************************************************************************************
// HTML Header block and client-side includes
//**************************************************************************************

?>

<script type="text/javascript" src="/cwis438/Includes/JQuery/jquery.form.js"></script>

<!-- <script src='/cwis438/Includes/JQuery/jquery-1.9.1.code.jquery.js'></script> -->
<!-- <script src='/cwis438/Includes/JQuery/jquery-ui-1.10.3.code.jquery.js'></script> -->

<script>

$(document).ready(function()
{
    //alert("ActveTabID="+ActiveTabID);

    //var SelectedTabID=<?php //echo($ActiveTabID);?>;
    //var SelectedTabID=ActiveTabID;
});


function DoInsertComment(DatabaseTableID,RecordID)
{
	//alert("about to insert the new comment data...");

	//alert("ActveTabID===="+ActiveTabID);
    
    var Title=$('#Title').val();
    var BlogText=$('#BlogText').val();
    var ProjectID=<?php echo($ProjectID);?>;
    var PersonID=<?php echo($PersonID);?>;
    
	var url='/cwis438/Websites/LEAF/WebServices/InsertComment.php?PersonID='+PersonID+'&DatabaseTableID=83&RecordID=20&Title='+Title+'&BlogText='+BlogText+'&ProjectID='+ProjectID;

	var Result=jQuery.parseJSON(jQuery.ajax({url:url,async:false,dataType:'json'}).responseText);
    
	if (Result['InsertCommentResponse'][0]=="Success")
	{		
	    LoadCommentsTable(20);
	}

	$("#dialog_addcomment").dialog('close');
}

</script>

<?php 

//**************************************************************************************
// HTML Generation
//**************************************************************************************
?>

<div id="dialog_addcomment" title="Add Comment" style="display:none;">
	<div  style="text-align:left;">
		<div class="profileoverlay_bdy p_signin">
			<div class="profileoverlay_bdy2">
			
			    <p></p>
			    
				<div style="margin: 0px auto 0px auto; width:400px;">
				
					<div style="width:400px;">

					<?php						
                      
                    echo("<form name='Comment' id='Comment' action='#' method='POST' enctype='multipart/form-data'>\n");   
                          
                    $TheTable=new TableSettings;
                    $TheTable->TableStart();
                    
                    $TheTable->ColumnHeading->ColumnSpan=2;
                                                
                    echo("<label style='display:inline-block; width:110px;'>Title:</label>");
                    $TheTable->FormTextEntry("Title",$Title,200,"id='Title'"); 
                    echo("<br/><br/>");
                    echo("<label style='display:inline-block; float:left; width:110px;'>Text:</label>");
                    $TheTable->FormTextAreaEntry("BlogText",$Text,2,1,"style='width:70%' id='BlogText'"); 
                    echo("<br/><br/>");
                    
                    echo("<div>");
                    

                    $TheTable->TableEnd();
                    
                    echo("<br>");
                    echo("<center>");
                    //echo("<input type='submit' id='submit' value='Add Comment' />");
                    echo("<input type='button' id='submit' value='Add Comment' onclick='DoInsertComment(255,$RecordID);' />"); // DoInsertComment() function located in this file locally...
                    echo("</center>");
                    echo("</form>");
                    
                    ?>
                                                    
					<br>								
					<div id="Error" Style='margin-top:20px;color:red;'></div>
					
					</div>
				</div>
				
				<div class="clear"></div>
				
			</div>
		</div>
	</div>
</div>
		