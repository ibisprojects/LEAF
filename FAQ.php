<?php
//**************************************************************************************
// FileName: AboutUs.php
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

echo("<h2>FAQ</h2>");
    
?>
    <ol>
    <li><strong><a href='#0'>Why should I contribute to the LEAF?</a></strong><br /></li>
    <li><strong><a href='#1'>How can I contribute to LEAF?</a></strong><br /></li>
    <li><strong><a href='#2'>What is the LEAF?</a></strong><br /></li>
    <li><strong><a href='#3'></a></strong><br /></li>
    <li><strong><a href='#4'></a></strong><br /></li>
    <li><strong><a href='#5'></a></strong><br /></li>
    <li><strong><a href='#6'></a></strong><br /></li>
    <li><strong><a href='#7'></a></strong><br /></li>
    <li><strong><a href='#8'></a></strong><br /></li>
</ol><br/>    

<ol>
    
<p id="0"><strong><li>Why should I contribute to the LEAF?</li></strong><br /></p>
<p>Answer</p><br/><br/>

<p id="1"><strong><li>How can I contribute to LEAF?</li></strong><br />
<p>Answer</p><br/><br/>

<p id="2"><strong><li>What is the LEAF?</li></strong><br/>
<p>Answer</p><br/><br/>
 
<h2>Technical Questions/Difficulties?</h2>

</ol>


<?php                
$ThePage->BodyEnd();

?>
