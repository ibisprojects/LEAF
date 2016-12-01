<?php
//**************************************************************************************
// FileName: GetMyObservations.php
// Author: gjn
// Owner: gjn
// Purpose: gets HTML for list of observations associated with specified user
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");
require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");
require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_UserSessions.php");

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");

require_once("C:/Inetpub/wwwroot/utilities/ServerUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/StringUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");

require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_People.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/REL_PersonToProject.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/REL_PersonToWebsite.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Media.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Projects.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Visits.php");

//**************************************************************************************
// Security
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

$UserID=GetUserID();

//CheckLogin2($Database,PERMISSION_USER);

//**************************************************************************************
// Parameters
//**************************************************************************************

//**************************************************************************************
// Server-side code
//**************************************************************************************

$SelectString="SELECT ProjName, AreaName, Latitude, Longitude, VisitDate, Species, AttributeName, FloatValue, IntValue, LookupName, VisitID 
                        FROM VIEW_MySiteCharacteristicData 
                        WHERE RecorderID=$UserID
                        UNION
              SELECT ProjName, AreaName, Latitude, Longitude, VisitDate, Species, AttributeName, FloatValue, IntValue, LookupName, VisitID 
                        FROM VIEW_MyOrganismAttributeData 
                        WHERE RecorderID=$UserID ORDER BY VisitDate" ;
			
$MyObservationsSet=$Database->Execute($SelectString);

//**************************************************************************************
// HTML
//**************************************************************************************

$HTML="<h4>My Observations</h4>";

$HTML.='<table class="table table-condensed table-striped">
            <thead>
            <tr>
               <th>Date</th>
               <th>Project</th>
               <th>Location</th>
               <th>Lat</th>
               <th>Long</th>
               <th>Species</th>
               <th>Attribute</th>
               <th>Value</th>
            </tr>
            </thead>

            <tbody>';

while($MyObservationsSet->FetchRow())
{
    $VisitDate = $MyObservationsSet->Field("VisitDate");
    $Dated=date('m/d/Y',strtotime($VisitDate));
    $AreaName = $MyObservationsSet->Field("AreaName");
    $Latitude = round(($MyObservationsSet->Field(3)),3);
    $Longitude = round(($MyObservationsSet->Field(4)),3);      
    $Species = $MyObservationsSet->Field("Species");
    $ProjName = $MyObservationsSet->Field("ProjName");
    $AttributeName = $MyObservationsSet->Field("AttributeName");
    $FloatValue = $MyObservationsSet->Field("FloatValue");
    $IntValue = $MyObservationsSet->Field("IntValue");
    $LookupName = $MyObservationsSet->Field("LookupName");   
    $VisitID = $MyObservationsSet->Field("VisitID");
    /*
    if ($Species!=null)
    {   
         $Species=TBL_OrganismData::GetSciNameFromOrgInfoName($Database,$Species);
    } 
    */
    if ($FloatValue!=null) $Value=$FloatValue;
    if ($IntValue!=null) $Value=$IntValue;
    if ($LookupName!=null) $Value=$LookupName;

    //$TheTable->TableRow(array($i, $Dated, $ProjName, $AreaName, $Latitude, $Longitude, $Species, $AttributeName, $Value));
    
    $HTML.='<tr>
                <td><a href="/cwis438/browse/project/visit_info.php?WebSiteID=20&VisitID='.$VisitID.'">'.$Dated.'</a></td>
                <td>'.$ProjName.'</td>
                <td>'.$AreaName.'</td>
                <td>'.$Latitude.'</td>
                <td>'.$Longitude.'</td>
                <td>'.$Species.'</td>
                <td>'.$AttributeName.'</td>
                <td>'.$Value.'</td>
            </tr>';
}

$HTML.='    </tbody>
        </table>';

echo json_encode($HTML);