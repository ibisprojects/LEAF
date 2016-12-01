<?php
//**************************************************************************************
// FileName: Mission.php
// Author: GN, RS, BF, NK
// The main Mission page for the LEAF website (Living Atlas of East African Flora)
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

//**************************************************************************************
// HTML Header block, client-side includes, and client-side functions
//**************************************************************************************

$ThePage=new PageSettings();

$ThePage->HeaderStart("Living Atlas of East African Flora");

$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart();

echo("<h2>Mission</h2>");

echo("Our misison is to provide a rich suite of tools to help researhers, scientists, land managers, and community based monitoring organizations concerned about the preservation of palnt biodiversity across East Africa 
        by making it easy for them to collect, organize, manage, share, integrate, synthesize, visulualize, and curate biodiversity information.");

echo("<h2>Introduction</h2>");

echo("East Africa is home to unique and diverse plant communities. Most notable is Ethiopia's rich plant diversity, consisting of 6,000+ documented species, almost 20% endemic. However, no centralized, publicly available portal exists to collect, aggregate, update, synthesize, disseminate, and visualize plant species occurrence and diversity data for the region. This project aims to develop an online living atlas of East African floral diversity that integrates new information (e.g., species occurrences, plot data, and traditional ecological knowledge) with legacy data. We will pre-populate the atlas with Ethiopian data as an initial beginning and train the next generation of students on its use to foster regional ownership, system investment, growing data contributions, and continued participation. The atlas will summarize data using maps of diversity, sampling effort, endemism, and species distribution to guide conservation actions and outcomes. This globally accessible system will 'live' off of continued participation and grow as new contributions are added.");

echo("<h2>Project Goals</h2>");

echo("The goals of this project are to:<br/><br/>
        <ol style='font-family: Arial,Helvetica,sans-serif; font-size: 14px;'>
        <li>develop a centralized and accessible online living atlas (website, searchable database, and interactive map application) that integrates plant species distribution and diversity information in East Africa</li> 
        <li>collect, aggregate, synthesize, visualize, and disseminate vascular plant data (including species occurrence data, profile information, and local knowledge) for Ethiopia</li>
        <li>use this initial information to pre-populate and jumpstart the atlas to foster continual contributions and updates as new information becomes available</li>
        <li>provide training to students at Wondo-Genet College of Forestry, Hawassa University, and Addis-Ababa University on the use of the atlas to ensure future contributions and help inform conservation decisions guided by best available data.
        </ol>
        ");

$ThePage->BodyEnd();

?>
