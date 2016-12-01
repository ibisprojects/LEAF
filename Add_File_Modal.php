<?php
//**************************************************************************************
// FileName: Add_File_Modal.php
// Author: GN, RS
// Owner:
// Purpose: 
//	Allows the user to upload a plain text file of LEAF plant observations
// 
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/Classes/Upload.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");
require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");

require_once("C:/Inetpub/wwwroot/cwis438/utilities/GodmUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/SecurityUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/StringUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/FileUtil.php");

//**************************************************************************************
// Database Connection
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

  


//**************************************************************************************
// Security
//**************************************************************************************

//CheckLogin2($Database,PERMISSION_USER);

//**************************************************************************************
// Parameters
//**************************************************************************************

$CallingPage=GetStringParameter("CallingPage","");
$CallingLabel=GetStringParameter("CallingLabel","");
$RelativePath=GetStringParameter("RelativePath","OrganismData/");

//**************************************************************************************
// Server-side code
//**************************************************************************************

$DestinPath="D:/Inetpub/UserUploads/".GetUserID()."/".$RelativePath;

//$DestinPath="D:/Inetpub/UserUploads/".GetUserID()."/OrganismData";

MakeSurePathExists($DestinPath);

if ($CallingPage=="") $CallingPage="/cwis438/Websites/LEAF/Add_Data.php?TakeAction=AddFile";

//**************************************************************************************
// HTML Header block and client-side includes
//**************************************************************************************

//**************************************************************************************
// HTML Generation
//**************************************************************************************
?>

<div id="dialog_uploadfile" title="Add File" style="display:none;">
			<div  style="text-align:left;">
				<div class="profileoverlay_bdy p_signin">
					<div class="profileoverlay_bdy2">
					    <p></p>
					    
						<!--<form action="Add_Data.php?TakeAction=AddFile" method="post" name="loginform" id="loginform">-->
						    
							<div style="margin: 0px auto 0px auto; width:400px;">
							
								<div style="width:400px;">
								
								<?php
                                                                
                                Upload::WriteUploadForm("Browse to a file and then click 'Upload'",array("Text file ('txt'):"),
                                                        $CallingPage,$CallingLabel,$DestinPath,1,10000000,UPLOAD_TEXT_FILES); 
                                
                                //WriteUploadForm($Title,$Labels,$CallingPage,$CallingLabel,$DestinPath,$NumFiles=1,
                                //$MaxFileSize=90000000,$Type=UPLOAD_UNKONWN)-->
                                ?>
                                                                
								<br>								
								
								<div id="Error" Style='margin-top:20px;color:red;'></div>
								
								</div>
							</div>
							
							<div class="clear"></div>
						<!--</form>-->
					</div>
				</div>

			</div>
		</div>