<?php
//**************************************************************************************
// FileName: GetMyLocations.php
// Author: gjn
// Owner: gjn
// Purpose: gets HTML for list of locations associated with specified user
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

$SelectString="SELECT TBL_Areas.ID AS AreaID, TBL_Areas.AreaName AS LocationName, TBL_SpatialLayerData.RefY AS Latitude, TBL_SpatialLayerData.RefX AS Longitude, 
                      COUNT(TBL_Visits.ID) AS Observations
                FROM TBL_Areas INNER JOIN
                      TBL_SpatialLayerData ON TBL_Areas.ID = TBL_SpatialLayerData.AreaID LEFT OUTER JOIN
                      TBL_Visits ON TBL_Areas.ID = TBL_Visits.AreaID
                GROUP BY TBL_Areas.ID, TBL_Areas.AreaName, TBL_SpatialLayerData.RefY, TBL_SpatialLayerData.RefX, TBL_Visits.RecorderID
                HAVING (TBL_Visits.RecorderID = $UserID) ORDER BY LocationName" ;
			
$MyLocationsSet=$Database->Execute($SelectString);

$MyAssignedLocationsQuery="SELECT REL_PersonToArea.AreaID, TBL_Areas.AreaName, TBL_SpatialLayerData.RefY AS Lat, TBL_SpatialLayerData.RefX AS Long
        FROM REL_PersonToArea INNER JOIN
                      TBL_Areas ON REL_PersonToArea.AreaID = TBL_Areas.ID INNER JOIN
                      TBL_SpatialLayerData ON TBL_Areas.ID = TBL_SpatialLayerData.AreaID
        WHERE (REL_PersonToArea.PersonID = $UserID)";

$MyAssignedLocationsSet=$Database->Execute($MyAssignedLocationsQuery);

//**************************************************************************************
// HTML
//**************************************************************************************

$HTML="";

if ($MyAssignedLocationsSet->FetchRow())
{   
    $HTML.="<h4>My Assigned Locations</h4>";
    
    $HTML.='<table class="table table-condensed table-striped">
            <thead>
            <tr>
               <th>Location Name</th>
               <th>Latitude</th>
               <th>Longitude</th>
            </tr>
            </thead>

            <tbody>';
    
    $MyAssignedLocationsSet=$Database->Execute($MyAssignedLocationsQuery);
    
    while($MyAssignedLocationsSet->FetchRow())
    {
        $AreaID = $MyAssignedLocationsSet->Field("AreaID");
        $LocationName = $MyAssignedLocationsSet->Field("AreaName");
        $Latitude = round(($MyAssignedLocationsSet->Field("Lat")),3);
        $Longitude = round(($MyAssignedLocationsSet->Field("Long")),3);      
        
        $AreaLink="/cwis438/websites/citsci/map/Location_Info.php?AreaID=$AreaID&WebSiteID=20";
        
        $HTML.='<tr>
                    <td><a href="'.$AreaLink.'">'.$LocationName.'</a></td>
                    <td>'.$Latitude.'</td>
                    <td>'.$Longitude.'</td>
                </tr>';
    }

    $HTML.='</tbody>
        </table>';
}

$HTML.="<h4>My Locations With Data</h4>";

$HTML.='<table class="table table-condensed table-striped">
            <thead>
            <tr>
               <th>Location Name</th>
               <th>Latitude</th>
               <th>Longitude</th>
               <th># Observations</th>
            </tr>
            </thead>

            <tbody>';

while($MyLocationsSet->FetchRow())
{
    $AreaID = $MyLocationsSet->Field("AreaID");
    $LocationName = $MyLocationsSet->Field("LocationName");
    $Latitude = round(($MyLocationsSet->Field("Latitude")),3);
    $Longitude = round(($MyLocationsSet->Field("Longitude")),3);      
    $NumObservations = $MyLocationsSet->Field("Observations");
    
    $AreaLink="/cwis438/websites/citsci/map/Location_Info.php?AreaID=$AreaID&WebSiteID=20";
    
    $HTML.='<tr>
                <td><a href="'.$AreaLink.'">'.$LocationName.'</a></td>
                <td>'.$Latitude.'</td>
                <td>'.$Longitude.'</td>
                <td>'.$NumObservations.'</td>
            </tr>';
}

$HTML.='    </tbody>
        </table>';

echo json_encode($HTML);