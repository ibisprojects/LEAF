<?php
//**************************************************************************************
// FileName: GetMyProjects.php
// Author: gjn
// Owner: gjn
// Purpose: gets HTML for list of projects associated with specified user
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

$MyProjectsQuery="SELECT DISTINCT TBL_Projects.ProjName, TBL_Projects.ID AS ProjectID, TBL_Projects.CitSciFlag, TBL_Projects.InstigatorID
                    FROM REL_PersonToProject INNER JOIN
                      TBL_Projects ON REL_PersonToProject.ProjectID = TBL_Projects.ID INNER JOIN
                      REL_WebsiteToProject ON TBL_Projects.ID = REL_WebsiteToProject.ProjectID
                    WHERE (REL_PersonToProject.PersonID = $UserID) AND (REL_WebsiteToProject.WebsiteID = 20)
                    ORDER BY ProjName";

//$MyProjectsSet=TBL_Perojects::GetSetForPersonID($Database,$UserID,$Role = null);

$MyProjectsSet=$Database->Execute($MyProjectsQuery);

//**************************************************************************************
// HTML
//**************************************************************************************

//$HTML="Hi this is some html for dynamic tab loading...for person $UserID";

$HTML="<h4>My Projects</h4>";

while($MyProjectsSet->FetchRow())
{
    $ProjName=$MyProjectsSet->Field("ProjName");
    $ProjectID=$MyProjectsSet->Field("ProjectID");
    
    $NumObservationsSet=TBL_Projects::GetNumVisitsForID($Database,$ProjectID);
    $NumObservations=$NumObservationsSet->Field("NumVisits");
    
    $ProjectManagerID=$MyProjectsSet->Field("InstigatorID");
    $ProjectManagerFirstName=TBL_People::GetFieldValue($Database,"FirstName",$ProjectManagerID);
    $ManagerAvatarString='<i class="fa fa-user" aria-hidden="true" class="avatar" alt="Project Manager: '.$ProjectManagerFirstName.'" title="Project Manager: '.$ProjectManagerFirstName.'"></i>';
    $HasMediaQuery="SELECT MediaID FROM TBL_People WHERE (ID=$ProjectManagerID)";
    $HasMediaSet=$Database->Execute($HasMediaQuery);
    $ManagerMediaID=$HasMediaSet->Field("MediaID");
    
    if ($ManagerMediaID>0)
    {
        $UserImagePath=TBL_Media::GetPathFromID($Database,$ManagerMediaID,$Version=0,$UseRoot=false);
        
        if (file_exists($UserImagePath));
        {
            $ManagerAvatarString='<img src="'.$UserImagePath.'" alt="Project Manager: '.$ProjectManagerFirstName.'" title="Project Manager: '.$ProjectManagerFirstName.'" class="avatar"/>';
        }    
    }
    
    $BackgroundImagePath="/cwis438/websites/citsci/images/thumb.png"; // cool cheetah: /UserUploads/1747/Media/_display/B4438med.jpg 

    $ProjectImageQuery="SELECT TOP (1) TBL_Projects.ID, TBL_Projects.ProjName, REL_MediaToProject.ID AS Expr1, REL_MediaToProject.MediaID, REL_MediaToProject.OrderNumber
        FROM TBL_Projects INNER JOIN
                          REL_MediaToProject ON TBL_Projects.ID = REL_MediaToProject.ProjectID
        WHERE (TBL_Projects.ID = $ProjectID)
        ORDER BY REL_MediaToProject.OrderNumber";

    $ProjectImageSet=$Database->Execute($ProjectImageQuery);

    while($ProjectImageSet->FetchRow())
    {
        $MediaID=$ProjectImageSet->Field("MediaID");
        $BackgroundImagePath=TBL_Media::GetPathFromID($Database,$MediaID,$Version=0,$UseRoot=false);
    }

    $HTML.='<div class="card card-background pull-left" style="width:48%; margin-right:8px;">

            <div class="image">
                <img src="'.$BackgroundImagePath.'" alt="..."/><!-- /cwis438/websites/citsci/images/thumb.png -->
                <div class="filter"></div>
            </div>

             <div class="content">
                <h5 class="price" style="width:93%;">&nbsp;
                     <a href="#" class="pull-right">
                        <!--<i class="fa fa-heart"></i>-->
                     </a>
                 </h5>

                <a href="/cwis438/browse/project/Project_Info.php?ProjectID='.$ProjectID.'&WebSiteID=20"><br/>
                    <h4 class="title">'.$ProjName.'</h4>
                </a>
            </div>

            <div class="footer" style="width:93%;">
               <div class="author">
                    <a href="#">
                       '.$ManagerAvatarString.'
                       <span>'.$ProjectManagerFirstName.'</span>
                    </a>
                </div>

               <div class="stats pull-right">
                    <i class="fa fa-comment"></i> '.$NumObservations.' Observations <!-- </i><i class="fa fa-eye">-->
               </div>
            </div>

        </div> <!-- end card -->';
    
}

echo json_encode($HTML);