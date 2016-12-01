<?php
//**************************************************************************************
// FileName: Species_List_Modal.php
// Purpose: Lists species so managers can select one to add to their datasheet
// NOTE: makes webservice calls to /cwis438/Websites/LEAF/webservices/GetSpeciesList.php 
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/SecurityUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/GodmUtil.php");

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/TaxonSearch.php");

require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Venaculars.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_TaxonUnits.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_OrganismInfos.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Media.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_UserSettings.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Permissions.php");

//**************************************************************************************
// Database connection
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Parameters
//**************************************************************************************

//**************************************************************************************
// Server-side functions
//**************************************************************************************

//**************************************************************************************
// Server-Side code
//**************************************************************************************

//**************************************************************************************
// HTML Header block, client-side includes, and client-side functions
//**************************************************************************************

//$ThePage=new PageSettings;

//$ThePage->HeaderStart("Select a species");

?>

<!-- TABS --> 

<script type="text/javascript" SRC="/cwis438/includes/FormUtil.js"></script>
<script type="text/javascript" src="/cwis438/Includes/JQuery/jquery-1.9.1.code.jquery.js"></script> <!-- original source: http://code.jquery.com/jquery-1.9.1.js -->
<script type="text/javascript" src="/cwis438/Includes/JQuery/jquery-ui-1.10.3.code.jquery.js"></script> <!-- original source: http://code.jquery.com/ui/1.10.3/jquery-ui.js -->
<script type="text/javascript" src="/cwis438/Includes/DataTables/jquery.dataTables.js"></script>



<style type="text/css" title="currentStyle">
    @import "/cwis438/Websites/CitSci/stylesheets/CitSci_DataTables.css";
    @import "/cwis438/Websites/CitSci/stylesheets/jquery.dataTables_themeroller.css";
    @import "/cwis438/Websites/CitSci/stylesheets/jquery-ui-1.8.4.custom.css";
    @import "/cwis438/Websites/CitSci/stylesheets/jquery-ui.css";
</style>

<style>       

.fg-toolbar
{
    background-color:#FFFFFF;
}

</style>


<div id="dialog_add_species" title="Select a species" style="display:none;">
			<div style="text-align:left;">
				<div class="profileoverlay_bdy p_signin">
					<div class="profileoverlay_bdy2">
					    <p></p>
						    
							<div style="margin: 0px auto 0px auto; width:780px;">
							
								<div style="width:780px;">
								
								<?php

                                echo("<h1>Select a species</h1>");
                                
                                echo("<br>");
                                
                                            ?>
                                            
                                            <table class="display" id="example" style="width:100%;">
                            					<thead>
                            						<tr>
                            							<th width="40%">Scientific Name</th>
                            							<th width="45%">Common Names</th>
                                                        <th width='0%'>Vernaculars</th>
                            							<th width="0%">Family</th> 
                            							<th width="0%">OrganismInfoID</th> <!-- hidden anyway but needed so it can be hidden -->
                                                        <th width='10%'>IUCN rank</th>
                            							<th width="15%">Options</th>
                            						
                            						</tr>
                            					</thead>
                            					<tbody>
                            						<tr>
                            							<td colspan="5" class="dataTables_empty">Loading data from server</td>
                            						</tr>
                            					</tbody>
                            				</table>
                               
								<br>								
								
								<div id="Error" Style='margin-top:20px;color:red;'></div>
								
								</div>
							</div>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		</div>
		
		
