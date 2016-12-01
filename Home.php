<?php
//**************************************************************************************
// FileName: Index.php
// Author: GN, RS, BF, NK
// The main Index page for the LEAF website (Living Atlas of East African Flora)
//**************************************************************************************

//**************************************************************************************
// Includes
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");

require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");
require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");
require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/classes/DBTable/TBL_Projects.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/SecurityUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Media.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Collections.php");

//**************************************************************************************
// Security
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Security
//**************************************************************************************

// No security needed to view this page.

//**************************************************************************************
// Server-side functions
//**************************************************************************************

//**************************************************************************************
// Parameters
//**************************************************************************************

//**************************************************************************************
// Server-Side code
//**************************************************************************************

// get count of organismdata records in all leaf projects for STATS
$SelectString="SELECT COUNT(TBL_OrganismData.ID) AS Count
        FROM  TBL_OrganismData INNER JOIN
              TBL_Visits ON TBL_OrganismData.VisitID = TBL_Visits.ID INNER JOIN
              REL_WebsiteToProject ON TBL_Visits.ProjectID = REL_WebsiteToProject.ProjectID
        WHERE (REL_WebsiteToProject.WebsiteID = 20)";

$Set=$Database->Execute($SelectString);
$Count=$Set->Field("Count");
$Count=number_format($Count);

// get count of collections for STATS
$SelectString="SELECT COUNT(ID) AS Count
                FROM TBL_Collections
                WHERE (WebsiteID = 20)";

$Set=$Database->Execute($SelectString);
$Collections=$Set->Field("Count");

// get count of projects for STATS
$SelectString="SELECT COUNT(ProjectID) AS Count
                FROM REL_WebsiteToProject
                WHERE (WebsiteID = 20)";

$Set=$Database->Execute($SelectString);
$Projects=$Set->Field("Count");


//**************************************************************************************
// HTML Header block, client-side includes, and client-side functions
//**************************************************************************************

$ThePage=new PageSettings(); // $ThePage=new Page();
$TheTable=new TableSettings();

$ThePage->HeaderStart("Living Atlas of East African Flora");

?>

<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro' rel='stylesheet' type='text/css'>

<style>

#middle
{
	width:100%; height:auto; min-height:500px; background-image:url('/cwis438/websites/LEAF/images/ContentBackground_Home.png'); background-repeat:repeat-x; background-color:#E8E8C6;
	/*width:100%; height:auto; min-height:500px; background-image:url('/cwis438/websites/LEAF/images/ContentBackground.png'); background-repeat:repeat-x; background-color:#E8E8C6;*/
}

#content
{
	padding-left:0px;
	padding-right:0px;
	padding-top:0px;
	background-image:url('/cwis438/websites/LEAF/images/Home_Background_1_00.png'); background-repeat:repeat-x; background-color:#ffffff;
}

.HomeContainer
{
	width:980px; height:18px; background-color:#ffffff; 
    -moz-border-radius: 6px;
    -webkit-border-radius: 6px;
    -khtml-border-radius: 6px;
	border-radius: 6px 6px 0px 0px;
	behavior: url(/cwis438/websites/LEAF/stylesheets/PIE.htc);
	position:relative; display:block;
}

a.homelink
{
	color: #ffcc00;
}

a.homelink:hover
{
	color: #DDE446; /* #C0CB41 #5e7630 */
}

.map
{ 
    display:inline-block; 
    *display: inline;  /*internet explorer hack*/
    zoom: 1;
    background-image:url(images/EastAfrica.png); 
    height:150px; 
    width:124px; 
    z-index:-99;
}

p
{
    font-family: Source Sans Pro, sans-serif; 
    font-size: 18px; 
    font-weight:300;
}

a
{
    text-decoration:none;
    color:#7D777D;
}

#push {
    height: 0px;
}

#container_leaf
{
    height:300px;
}

</style>

<?php

$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart();

$UserID=GetUserID();

echo("<div style='width:980px; height:213px; margin-bottom:17px;'>"); // margin-bottom:16px; margin-top:26px;  display:inline-block;

echo ("<div class='bigbuttons' style='background-image:url(images/Home_1.png)'>"); // 4.jpg
    echo ("<div style='margin-top:156px; text-align:center;'>");
        echo("<a href='/cwis438/websites/LEAF/Explore.php?WebSiteID=20' class='button orange'>Explore LEAF</a>");
    echo ("</div>");
echo ("</div>");

echo ("<div class='bigbuttons' style='background-image:url(images/Home_3.png)'>"); // 5.jpg
    echo ("<div style='margin-top:156px; text-align:center;'>");
        echo("<a href='/cwis438/websites/LEAF/Create_Project.php?WebSiteID=20' class='button red'>Create Projects</a>");
    echo ("</div>");
echo ("</div>");

echo ("<div class='bigbuttons' style='float:right; margin-right:0px; background-image:url(images/Home_2.png)'>"); // 7.jpg
    echo ("<div style='margin-top:156px; text-align:center;'>");
        echo("<a href='/cwis438/websites/LEAF/Submit_Observation.php?WebSiteID=20' class='button green'>Add Observations</a>");
    echo ("</div>");
echo ("</div>");

echo("</div>");

echo("<div class='HomeContainer' style='margin-left:0px;'></div>");

echo("<div style='height:200px; width:100%; margin-top:-20px; position:relative;'>");

    echo("<div class='leaftext'>");

        echo("<h2 style='font-family: Source Sans Pro, sans-serif;'>Welcome to LEAF</h2>"); // p style='font:bold 18px trebuchet ms; color:#3A5C83;'

        echo("<p style='line-height:130%; margin-bottom:20px;'>East Africa is home to unique and diverse plant communities.
            Most notable is Ethiopia's rich plant diversity, consisting of 6,000+ documented species, almost 20% endemic.
            The <em>Living Atlas of East African Flora (LEAF)</em>  integrates new plant information (e.g., species occurrences, 
            plot data, and local ecological knowledge) with legacy data. These integrated datasets are then summarized using 
            maps of diversity, sampling effort, endemism, and species distribution to guide conservation actions and outcomes.<br/><br/></p>");
    
    echo("</div>");  // end leaftext div

    echo("<div class='map'></div>");
    
echo("</div>");   

/////----------  Statistics  --------------////////

echo("<div id='stats_container'>");  

    echo("<div class='stat_box' style=''>");
    
        echo("<div class='stat_number'>");
        
            echo("$Count");
        
        echo("</div>");  // end stat_number
        
        echo("<div class='stat_name'>");
        
            echo("OCCURRENCES");
        
        echo("</div>");  // end stat_name
    
    echo("</div>"); // end stats
    
    echo("<div class='stat_box' style=''>");
    
        echo("<div class='stat_number'>");
        
            echo("$Collections");
        
        echo("</div>");  // end stat_number
        
        echo("<div class='stat_name'>");
        
            echo("COLLECTIONS");
        
        echo("</div>");  // end stat_name
    
    echo("</div>"); // end stats
    
    echo("<div class='stat_box' style='border-right:solid 1px #C0C0C0;'>");
    
        echo("<div class='stat_number'>");
        
            echo("$Projects");
        
        echo("</div>");  // end stat_number
        
        echo("<div class='stat_name' style=''>");
        
            echo("PROJECTS");
        
        echo("</div>");  // end stat_name
    
    echo("</div>"); // end stats

echo("</div>");  // end stats_container


echo("<div id='container_leaf'>"); 

// ----------------------------------------------------------------------------------

echo("<div style='width:100%; height:202px; margin-top: 10px;'>");

/////----------  OBSERVATIONS  --------------////////

echo("<div class='feature observation'>");

    echo("<p style='font-weight:bold; color:#0099CC;'>Most Recent Observation:</p>");
    
    echo("<img style='margin-top:10px; width:180px; height:100px;' src='/cwis438/Websites/LEAF/images/Home_7.jpg'/><br/>");
    
    $SelectString="SELECT  TOP (1) *
    FROM TBL_Visits INNER JOIN
    REL_WebsiteToProject ON TBL_Visits.ProjectID = REL_WebsiteToProject.ProjectID INNER JOIN
    TBL_Projects ON REL_WebsiteToProject.ProjectID = TBL_Projects.ID
    WHERE (REL_WebsiteToProject.WebsiteID = 20)
    ORDER BY TBL_Visits.VisitDate DESC";
    
    $Set=$Database->Execute($SelectString);
    
    $ProjectName=$Set->Field("ProjName");
    $ProjectID=$Set->Field("ProjectID");

    $VisitID=$Set->Field("ID");
    $RecorderID=$Set->Field("RecorderID");
    $PersonName=TBL_People::GetPersonsName($Database, $RecorderID);
    
    $AreaID=$Set->Field("AreaID");
    $AreaName=TBL_Areas::GetFieldValue($Database,"AreaName",$AreaID);
    
    $VisitDate=GetPrintDateFromSQLServerDate($Set->Field("VisitDate"));
    
    $OrganismDataSet=TBL_OrganismData::GetSet($Database,$VisitID);
    
    $OrganismDataID=$OrganismDataSet->Field("ID");
    $OrganismDataOrgInfoID=$OrganismDataSet->Field("OrganismInfoID");
    $OrgName=TBL_OrganismInfos::GetName($Database,$OrganismDataOrgInfoID,true,false);
    $OrgNameLimited=WordLimiter($OrgName,4);
    
    $OrgNameLimited=ucfirst($OrgNameLimited);
    
    if ($OrganismDataID!=null)
    {
        $OrgDataMediaSet=TBL_Media::GetSetFromOrganismDataID($Database,$OrganismDataID);
    
        $OrganismDataLink="<a href='/cwis438/Browse/Project/Visit_Info.php?VisitID=$VisitID'>$VisitDate</a>";
    
        $OrganismDataImage="";
    
        if ($OrgDataMediaSet->FetchRow())
        {
            $MediaID=$OrgDataMediaSet->Field("ID");
    
            $OrganismInfoImage=TBL_Media::GetImgTagFromID($Database,$MediaID,"Most Recent Observation Picture",80,94,TBL_MEDIA_VERSION_THUMBNAIL,null); // could pass in last param as a calling page
            $OrganismDataImage=TBL_Media::GetImgTagFromID($Database,$MediaID,"Most Recent Observation Picture",80,94,TBL_MEDIA_VERSION_THUMBNAIL,null); // could pass in last param as a calling page
    
            $Name=FixStringForHTML($OrgNameLimited);
            
            $Name=ucfirst($Name);
    
            $OrganismDataImage=GetLink("/cwis438/Browse/Project/OrganismData_Info.php",$OrganismDataImage,"OrganismDataID=$OrganismDataID&WebSiteID=20");
    
            $OrganismDataLink=$OrganismDataImage."<span style='width:392px; margin-top:6px;'>".$OrganismDataLink."</span>";
        }
        else
        {
            $AreaNameText="";
    
            if (($AreaName!="Untitled")AND($AreaName!=null))
            {
                $AreaNameText="found in $AreaName ";
            }
            
            $OrgName=ucfirst($OrgName);
    
            $StateNameText="";
            $OrganismDataImage="<br><br>$OrgName<br>submitted by $PersonName<br><br><br><br><br>"; // $StateNameText eventually
            $OrganismDataLink="<div style='width:224px; margin-top:8px; margin-bottom:8px;'>".$OrganismDataImage."</div>".$OrganismDataLink;
        }
    }
    
    $StatsLink=GetLink("/cwis438/Browse/Taxon_Pareto.php","All Sightings");
    
    // print out recent observation stuff
    
    $MyCallingPage="/cwis438/websites/LEAF/Home.php?WebSiteID=20";
    $MyCallingPage=urlencode($MyCallingPage);
    $MyCallingLabel="To Home Page";
    $MyCallingLabel=urlencode($MyCallingLabel);
    
    if ($OrgNameLimited==="Untitled")
    {
        $AreaNameLimited=WordLimiter($AreaName,3);
        echo("<a href='/cwis438/Browse/Project/Visit_Info.php?VisitID=$VisitID&CallingPage=$MyCallingPage&CallingLabel=$MyCallingLabel&WebSiteID=20'>Sighting in $AreaNameLimited</a><br />");
    }
    else
    {
        echo("<a href='/cwis438/Browse/Project/Visit_Info.php?VisitID=$VisitID&CallingPage=$MyCallingPage&CallingLabel=$MyCallingLabel&WebSiteID=20'>$OrgNameLimited</a><br />");
    }
    
    echo("<p style='font-size:15px;'> was reported by $PersonName<br/> on $VisitDate</p>");
    
echo("</div>");

/////----------  FEATURED PROJECTS    --------------////////

echo("<div class='feature project'>");
    
    echo("<p style='font-weight:bold; color:#0099CC;'>Featured Project:</p>");
    
    echo("<br />");
    
    $SelectString2="SELECT REL_PersonToProject.ProjectID, REL_PersonToProject.Role, TBL_People.FirstName, TBL_People.LastName
    FROM REL_PersonToProject INNER JOIN
    TBL_People ON REL_PersonToProject.PersonID = TBL_People.ID
    WHERE (REL_PersonToProject.Role = 5) AND (REL_PersonToProject.ProjectID = $ProjectID)";
    
    $Set2=$Database->Execute($SelectString2);
    
    $FirstName=$Set2->Field("FirstName");
    $LastName=$Set2->Field("LastName");
    
    echo("<img style='margin-top:-12px; margin-bottom:5px; display:block; width:180px; height:100px;' src='/cwis438/Websites/LEAF/images/Home_4.jpg'/>");
    
    echo ("<a href='/cwis438/Browse/Project/Project_Info.php?ProjectID=$ProjectID&WebSiteID=20'>$ProjectName</a><br/>");
    echo ("<p style='font-size:15px;'>Coordinator: $FirstName $LastName</p>");
    
    //$ProjectLink="<a href='/cwis438/Browse/Project/Project_info.php?ProjectID=$ProjectID&WebSiteID=20' class=''>$ProjectName</a>";
    
    $MediaSet=TBL_Media::GetSetFromProjectID($Database,$ProjectID);
    
    if ($MediaSet->FetchRow())
    {
        $MediaID=$MediaSet->Field("ID");
    
        $ProjectImage=TBL_Media::GetImgTagFromID($Database,$MediaID,"$ProjectName",80,94,TBL_MEDIA_VERSION_THUMBNAIL,null); // could pass in last param as a calling page
        //$ProjectImage=GetLink("/cwis438/Browse/Project/Project_info.php",$ProjectImage,"ProjectID=".$ProjectSet->Field("ID"));
        //$ProjectLink="<span style='width:224px; margin-top:6px;'>".$ProjectImage."</span>";
    }
    else
    {
        $ProjectImage="<div class='ImageThumbnail'><img border='0' height='100%' src='/cwis438/websites/CitSci/images/NoProjectPhoto.jpg' alt='No project photo is available yet' title='No project photo is available yet' border='0'></img></div>";
        //$ProjectLink="<span style='width:224px; margin-top:6px;'>".$ProjectImage."</span>";
    }
    
echo("</div>");

/////----------  NEWS  --------------////////

echo("<div class='feature news'>");

    echo("<p style='font-weight:bold; color:#0099CC;'>News:</p>");
    
    echo("<br />");
    
    echo("<div class='featured'>"); // style='clear:both;'
    
    $ThePage->DivStart("class='recentobservation'");
    
    echo("<div class='news' style='font:11px trebuchet ms; height:100px;'>");
        echo("<div class='news_container'><u>May 5 2013</u>: <div class='news_text'>New projects have been added to LEAF!!!</div></div><br/>");
        echo("<div class='news_container'><u>Dec 19 2013</u>: <div class='news_text'>The first species collections are added to LEAF.</div></div><br/>");
        echo("<div class='news_container'><u>Jan 15 2014</u>: <div class='news_text'>Colorado State University staff launches <a href='http://www.nrel.colostate.edu/projects/csu-ethiopia/index.html'>WCNR-Ethiopia Strategic Alliance</a> website.</div></div>");
    echo("</div>");
     
    $ThePage->DivEnd();
    
    echo("</div>");
    
echo("</div>");

echo("<br style='clear:left;'>");

echo("</div>");

$ThePage->BodyEnd();

?>
