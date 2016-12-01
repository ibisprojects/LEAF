<?php
//**************************************************************************************
// FileName: Species_List.php
// Purpose: Lists the species we currently have related to the LEAF site
// NOTE: makes webservice calls to /cwis438/Websites/LEAF/webservices/GetSpeciesList.php 
//       and /cwis438/Websites/LEAF/webservices/GetSpeciesProfiles.php to update content
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");

require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");


//**************************************************************************************
// Database connection
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Parameters
//**************************************************************************************

$WebsiteID=20;

//**************************************************************************************
// Server-Side code
//**************************************************************************************


// calling pages

//**************************************************************************************
// HTML Header block, client-side includes, and client-side functions
//**************************************************************************************

$ThePage=new PageSettings;

$ThePage->HeaderStart("LEAF Species Profiles");

?>
<!--  TABS  -->
<script type="text/javascript" src="/cwis438/Includes/FormUtil.js"></script>
<script type="text/javascript" src="/cwis438/Includes/JQuery/jquery-1.9.1.code.jquery.js"></script>
<script type="text/javascript" src="/cwis438/Includes/JQuery/jquery-ui-1.10.3.code.jquery.js"></script>

<!-- DataTables -->
<script type="text/javascript" src="/cwis438/Includes/DataTables/jquery.dataTables.js"></script>

<!-- DataTable TableTools -->
<script type="text/javascript" src="/cwis438/Includes/DataTables/TableTools-2.1.5/media/js/TableTools.js"></script>
<script type="text/javascript" src="/cwis438/Includes/DataTables/TableTools-2.1.5/media/js/ZeroClipboard.js"></script>

<script>
    
$(function() 
{
    $( "#tabs" ).tabs();
});

$(document).ready(function()
{    
    // new way server side...

	$('#tab1_content').dataTable( {
        "sDom": 'T<"clear">lfrtip',
            "oTableTools": {
                "sSwfPath": "/cwis438/Includes/DataTables/TableTools-2.1.5/media/swf/copy_csv_xls.swf",
                "aButtons": ["copy","csv","xls"] },
        "iDisplayLength": 15,
		"bProcessing": true, // true
		"bServerSide": true,
		"sAjaxSource": "/cwis438/Websites/LEAF/webservices/GetLEAFSpeciesList.php",
        "aoColumns": [ 
			/* SciName */       null,
			/* Name */          null,
            /* Vernaculars */   {"bVisible": false},
            /* Family */        null,
			/* OrganismInfo */ {"bVisible": false},
            /* Rank */          null,
            /* Options  */     {"bSortable": false}
		]
	} );
    
    $("#tab1_content").width("100%");
    
});

</script>

<style type="text/css" title="currentStyle">
    @import "/cwis438/Websites/LEAF/stylesheets/demo_table.css";
    @import "/cwis438/Websites/LEAF/stylesheets/jquery.dataTables_themeroller.css";
    @import "/cwis438/Websites/LEAF/stylesheets/jquery-ui-1.8.4.custom.css";
    @import "/cwis438/Websites/LEAF/stylesheets/jquery-ui.css"; /* why this as well as the one above? */
    @import "/cwis438/Includes/DataTables/TableTools-2.1.5/media/css/TableTools.css";  
</style>

<style>
th 
{
    background-color: white;
}

.city_name
{
    color:#8C8F3D; 
    font-family: Arial,Times,serif; 
    font-size:30px; 
    font-weight:bold;
    padding:20px;
}

.ui-widget-header 
{
    /*background-color:#8C8F3D;*/
	/*background-color:red; tabs background */
}        

.fg-toolbar
{
    background-color:#FFFFFF;
	/*background-color:red;*/
}

div.img
{
    margin:2px;
    border:1px solid #0000ff;
    height:auto;
    width:auto;
    float:left;
    text-align:center;
}
div.img img
{
    display:inline;
    margin:3px;
    border:1px solid #ffffff;
}
div.img a:hover img
{
    border:1px solid #0000ff;
}
div.desc
{
    text-align:center;
    font-weight:normal;
    width:120px;
    margin:2px;
    font-size:12px;
}

</style>
<?php

$ThePage->HeaderEnd();

$ThePage->BodyStart(); 

$ThePage->LineBreak();

echo("<div style='text-align: center;'><span class='city_name'>East Africa's Plants</span></div>");

$ThePage->LineBreak();
$ThePage->LineBreak();

//-----------------------------------TABS---------------------------------------//

echo("<div id='tabs'>");

    echo("<ul>");
        echo("<li><a href='#tabs-1'>Species List</a></li>");
    echo("</ul>");
    
//----------------------- TAB 1 Species List  --------------------------//
    
    echo("<div id='tabs-1'>");
        
        echo("<table cellpadding='0' cellspacing='0' border='0' class='display' id='tab1_content'>
					<thead>
						<tr>
							<th width='25%'>Scientific Name</th>
							<th width='25%'>Common Names</th>
                            <th width='0%'>Vernaculars</th>
                            <th width='25%'>Family</th>
                            <th width='0%'>OrgInfoID</th>
                            <th width='10%'>IUCN rank</th>
                            <th width='204'>Options</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan='5' class='dataTables_empty'>Loading data from server</td>
						</tr>
					</tbody>
				</table>");

    echo("</div>");  // End div tabs-1


echo("</div>");  // End Tabs div

//-------------------------------   End Tabs   -----------------------------------//


$ThePage->BodyEnd();

?>
