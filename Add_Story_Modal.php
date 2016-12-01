<?php

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/utilities/StringUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");

$Database=NewConnection(INVASIVE_DATABASE);

?>

<div id="dialog_addstory" title="Add Story" style="display:none;">
			<div  style="text-align:left;">
				<div class="profileoverlay_bdy p_signin">
					<div class="profileoverlay_bdy2">
					    <p></p>
					    
						<form action="Add_Data.php?TakeAction=AddStory" method="post" name="loginform" id="loginform">
						    
							<div
								style="float: left; margin: 0 0 0 0; display: inline; width: 260px;">
								<p style="font-size: 14px; font-weight: bold; line-height: 16px; padding: 0 0 10px 0;">
									Choose a plant species and include a story:
								</p>
                                                                
                                                                <?php
                                                                
                                                                $ThePage=new PageSettings;
                                                                
                                                                $TheTable=new TableSettings(TABLE_FORM);
	
                                                                $TheTable->FormStart();
								
                                                                $TheTable->TableStart();
                                                                
                                                                $PlantStory="";
                                                                $Species="";
                                                                
                                                                $PlantNames=array("Parthenium hysterophourus","prosopis juliflora", "Eichornia crassipes", "Lantana camara");
                                                               
                                                                $TheTable->FormRowArray("Species:","Species",$Species,$PlantNames,0,3,true," --- Select One --- ",-1,true,1,false,null,"Required");
		
                                                                $TheTable->TableRowStart();
                                                                        $TheTable->TableCell(0,"Plant Story:");                                                              
                                                                        $TheTable->FormCellTextAreaEntry(2,"PlantStory",$PlantStory,3,1,"style='width:490px;' width='100%'"); // max length in db is 8000
                                                                $TheTable->TableRowEnd();

                                                                $TheTable->TableEnd();
                                                                
								?>
								
								<input type="submit" value="Submit" name="Submit" id="login-btn" name="login-btn">
								
								<div id="Error" Style='margin-top:20px;color:red;'></div>
								
							</div>
							
							<div class="clear"></div>
						</form>
					</div>
				</div>

			</div>
		</div>