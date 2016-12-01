<?php
//**************************************************************************************
// FileName: Collections_Modal.php
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/SecurityUtil.php");

require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Collections.php");

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

?>

<script type="text/javascript" SRC="/cwis438/includes/FormUtil.js"></script>
<script type="text/javascript" src="/cwis438/Includes/JQuery/jquery-1.9.1.code.jquery.js"></script> <!-- original source: http://code.jquery.com/jquery-1.9.1.js -->
<script type="text/javascript" src="/cwis438/Includes/JQuery/jquery-ui-1.10.3.code.jquery.js"></script> <!-- original source: http://code.jquery.com/ui/1.10.3/jquery-ui.js -->
<script type="text/javascript" src="/cwis438/Includes/DataTables/jquery.dataTables.js"></script>

<link rel="stylesheet" href="/cwis438/Websites/LEAF/stylesheets/jquery-ui.css" />

<style type="text/css" title="currentStyle">
    @import "/cwis438/Websites/CitSci/stylesheets/CitSci_DataTables.css";
    @import "/cwis438/Websites/CitSci/stylesheets/jquery.dataTables_themeroller.css";
    @import "/cwis438/Websites/CitSci/stylesheets/jquery-ui-1.8.4.custom.css";
    @import "/cwis438/Websites/CitSci/stylesheets/jquery-ui.css";
</style>

<style>
table.display thead th 
{
    background-color: white;
}
.ui-widget input, .ui-widget select, .ui-widget textarea, .ui-widget button 
{
    background-color: #BFF2F0;
    font-family: Arial,sans-serif;
    font-size: 16px;
    height: 25px;
    margin-bottom: 20px;
    width: 200px;
}
</style> 
   
<script>

//var RetrievedCollectionName = $( "#dialog_view_collection" ).data('CollectionName');
//alert(RetrievedCollectionName);

</script>

<div id="dialog_view_collection" title="Collection" style="display:none;" >
        <div style="text-align:left;">
            <div class="profileoverlay_bdy p_signin">
                <div class="profileoverlay_bdy2">
                    <p></p>
                        <div style="margin: 0px auto 0px auto; width:780px;">

                            <div style="width:780px;">

                                <table class="display" id="example" style="width:100%;">
                                    <thead>
                                        <tr>
                                            <th>Scientific Name</th>
                                            <th>Common Name</th>
                                            <th>Family</th>
                                            <th>OrganismInfoID</th>
                                            <th>CollectionID</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="5" class="dataTables_empty">Loading data from server</td>
                                        </tr>
                                    </tbody>
                                </table>

                            <div id="Error" Style='margin-top:20px;color:red;'></div>

                            </div>
                        </div>
                        <div class="clear"></div>
                </div>
            </div>

        </div>
    
</div>
