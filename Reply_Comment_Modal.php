<?php
//**************************************************************************************
// FileName: Reply_Comment_Modal.php
// Author: RS
// Owner:
// Purpose: Allows users to enter replies to othre user comments in cyberlearning forum
//
// Note: this file is included (via require_once) in Training.php and serves as the 
// model dialog content for the Training page. All variables defined in Training.php
// are locally avauilable to this file.
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

$VisitDateObject=GetDateParameter("Date");
$Title=GetStringParameter("Title");
$Text=GetStringParameter("Text");
$DatabaseTableID=GetIntParameter("DatabaseTableID",255); // 255=LKU_AdaptiveManagmentSteps
$RecordID=GetIntParameter("RecordID");
$ParentID=GetIntParameter("ParentID");

//**************************************************************************************
// Server-side code
//************************************************************************************** 

if ($Text!=="")
{
    $CommentID=TBL_Blogs::Insert($Database,$Text);

    $CommentID=TBL_Blogs::Update($Database,$CommentID,$DatabaseTableID,$RecordID,NULL,$Title,$PersonID,$ProjectID);
}

//**************************************************************************************
// HTML Header block and client-side includes
//**************************************************************************************

$ThePage = new PageSettings;

//$ThePage->HeaderStart("Blog Information");

?>

<script type="text/javascript" src="/cwis438/Includes/JQuery/jquery.form.js"></script>

<script>

function DoInsertReply(DatabaseTableID,RecordID)
{
	//alert("about to insert the new reply data...");

    //alert("SelectedParentCommentID="+SelectedParentCommentID);
	
	//var ParentID=$("#dialog_addreply").data("uiDialog").options.title;

    var ParentID=SelectedParentCommentID;
    
    //var ParentID=$("#dialog_addreply").dialog.title;
    //alert("title is..."+ParentID);
    
    var Title=$('#ReplyTitle').val();
    var BlogText=$('#ReplyText').val();
    var ProjectID=<?php echo($ProjectID);?>;
    var PersonID=<?php echo($PersonID);?>;
    
	var url='/cwis438/Websites/LEAF/WebServices/InsertComment.php?PersonID='+PersonID+'&ParentID='+ParentID+'&DatabaseTableID=83&RecordID=20&Title='+Title+'&BlogText='+BlogText;

	var Result=jQuery.parseJSON(jQuery.ajax({url:url,async:false,dataType:'json'}).responseText);
    
	if (Result['InsertCommentResponse'][0]=="Success")
	{		
	    LoadCommentsTable(20);
	}

	$("#dialog_addreply").dialog('close');
}

</script>

<?php 

//$ThePage->HeaderEnd("Model Information");

//**************************************************************************************
// HTML Generation
//**************************************************************************************

//$ThePage->BodyStart();

//#dialog_addreply

//DebugWriteln("ModelID=$ModelID");

//DebugWriteln("ParentID=$ParentID");

?>
 
<div id="dialog_addreply" title="Add Reply" style="display:none;">
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
                                                    
                        echo("<label style='display:inline-block; width:110px;'>Reply Title:</label>");
                        $TheTable->FormTextEntry("ReplyTitle",$Title,200,"id='ReplyTitle'"); 
                        echo("<br/><br/>");
                        echo("<label style='display:inline-block; float:left; width:110px;'>Reply Text:</label>");
                        $TheTable->FormTextAreaEntry("ReplyText",$Text,2,1,"style='width:70%' id='ReplyText'"); 
                        echo("<br/><br/>");
                        
                        echo("<div>");
                        

                        $TheTable->TableEnd();
                        
                        echo("<br>");
                        echo("<center>");
                        //echo("<input type='submit' id='submit' value='Add Reply' />"); 
                        echo("<input type='button' id='submit' value='Add Reply' onclick='DoInsertReply(255,$RecordID);' />"); // DoInsertComment() function located in this file locally...
                        echo("</center>");
                        echo("</form>");
                        
                        //DebugWriteln("ChildID=$ChildID");
                        
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
		
