<?php
//**************************************************************************************
// FileName: Collections.php
// Author: RS, GN
// The Collections page for the LEAF website (Living Atlas of East African Flora)
//**************************************************************************************

//**************************************************************************************
// Includes
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");
require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");
require_once("C:/Inetpub/wwwroot/cwis438/classes/DBTable/TBL_Collections.php");

//**************************************************************************************
// Security
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Parameters
//**************************************************************************************

//**************************************************************************************
// Server-Side code
//**************************************************************************************

//**************************************************************************************
// HTML Header block, client-side includes, and client-side functions
//**************************************************************************************

$ThePage=new PageSettings();

$ThePage->HeaderStart("Living Atlas of East African Flora");

?>

<script type="text/javascript" SRC="/cwis438/includes/FormUtil.js"></script>
<link rel="stylesheet" href="/cwis438/Websites/LEAF/stylesheets/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

<script type="text/javascript" src="/cwis438/Includes/DataTables/jquery.dataTables.js"></script>

<!-- Table Tools -->
<script type="text/javascript" src="/cwis438/Includes/DataTables/TableTools-2.1.5/media/js/TableTools.js"></script>
<script type="text/javascript" src="/cwis438/Includes/DataTables/TableTools-2.1.5/media/js/ZeroClipboard.js"></script>


<script>

var SelectedCollectionID=null;
//var PlantTableInitialized=false;

$(document).ready(function()
{
        $('#collections').dataTable( {
            "sDom": 'T<"clear">lfrtip',
            "oTableTools": {
                "sSwfPath": "/cwis438/Includes/DataTables/TableTools-2.1.5/media/swf/copy_csv_xls.swf",
                "aButtons": ["copy","csv","xls"] },
            "bProcessing": true, 
            "bServerSide": true,       
            "sAjaxSource": "/cwis438/Websites/LEAF/webservices/GetCollectionsList.php",
            "aaSorting": [[ 4, "desc" ]],
            "aoColumns": [ 
                /* ID */           {"bVisible": false},
                /* Name */         {"sWidth":"25%"},
                /* Citation */     {"sWidth":"50%"},
                /* WebsiteID */    {"bVisible": false},
                /* DateAdded */    null,
                /* DateModified*/  {"bVisible": false},
                /* Options */      {"sWidth":"25%", "bSortable": false}
                        ]
        } );

});

function DoViewCollection(CollectionID) 
{    
    var SelectedCollectionID=CollectionID;
    
    $.ajax({
        url: "/cwis438/websites/LEAF/webservices/GetCollectionName.php",
        type: "POST",
        data: 'SelectedCollectionID='+SelectedCollectionID,
        success: function(resp){
            var CollectionName = resp
            $( "#dialog_view_collection").dialog({ title: CollectionName });  // dynamically set Title of Modal dialog
        }
        });
        
        //alert(CollectionName)
  
	//open view collection dialog...
    
    $( "#dialog_view_collection").dialog({ modal: true }); 
    $( "#dialog_view_collection").dialog({ minHeight: 600 });
    $( "#dialog_view_collection").dialog({ width: 800 });
    $( "#dialog_view_collection").dialog({ resizable: true });
    $( "#dialog_view_collection").dialog('open');

    var overlay = $(".ui-widget-overlay");
    baseBackground = overlay.css("background");
    baseOpacity = overlay.css("opacity");
    overlay.css("background", "black").css("opacity", "1");
    $(".ui-widget-overlay").css("z-index", "1").css("backgroundColor", baseBackground).css("opacity", baseOpacity).css("filter","Alpha(Opacity=80)");

    var dialog = $(".ui-dialog-titlebar"); 
    dialog.css("background","#E9E8B8");
             
	$('#example').dataTable({
        "iDisplayLength": 15,
		"bProcessing": true, 
		"bServerSide": true,
        "bDestroy": true,
		"sAjaxSource": "/cwis438/websites/LEAF/webservices/GetSpeciesByCollection.php?CollectionID="+CollectionID,
		"aoColumns": [ 
                        null, /* SciName */ 
                        null, /* Name */
                        null,  /* Family */ 
                        {"bVisible": false}, /* OrgInfoID */ 
                        {"bVisible": false} /* CollectionID */
			  		 ]
	} );
    
    $("#example").width("100%");
 


}  // end function

</script>

<style type="text/css" title="currentStyle">
    @import "/cwis438/Websites/LEAF/stylesheets/demo_table.css";
    @import "/cwis438/Websites/LEAF/stylesheets/jquery.dataTables_themeroller.css";
    @import "/cwis438/Websites/LEAF/stylesheets/jquery-ui-1.8.4.custom.css";  
    @import "/cwis438/Includes/DataTables/TableTools-2.1.5/media/css/TableTools.css";
</style>

<style>

.title
{
    color:#8C8F3D; 
    font-family: Arial,Times,serif; 
    font-size:30px; 
    font-weight:bold;
    padding:20px;
}
#collections_filter
{
    margin-bottom:20px;
}

input
{
    background-color: #FFFFCA;
    font-family: Arial,sans-serif;
    font-size: 16px;
    height: 25px;
    margin-bottom: 20px;
    width: 200px;
}

#collections_info, #collections_paginate
{
    margin-top:20px;
}
.ui-dialog-content
{
    padding-top:20px;
}

</style>

<?php

$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart();

$ThePage->LineBreak();

echo("<div style='text-align: center;'><span class='title'>Collections</span></div>");

$ThePage->LineBreak();
$ThePage->LineBreak();

//----------------------- Collection List DataTable  --------------------------//

//echo("<div>");

echo("<table id='collections' width='100%' cellpadding='0' cellspacing='0' border='0' class='display'>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Citation</th>
                <th>WebsiteID</th>
                <th>Date Added</th>
                <th>Date Modified</th>
                <th>Options</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan='6' class='dataTables_empty'>Loading data from server</td>
            </tr>
        </tbody>
    </table>");

//echo("</div>");

require_once("C:/Inetpub/wwwroot/cwis438/Websites/LEAF/Collections_Modal.php");

$ThePage->LineBreak();
$ThePage->LineBreak();

echo("&nbsp;");

$ThePage->BodyEnd();

?>
