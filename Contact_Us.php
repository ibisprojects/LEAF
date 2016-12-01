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

require_once("C:/Inetpub/wwwroot/cwis438/utilities/GodmUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/EmailUtil.php");


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
$TakeAction=GetStringParameter("TakeAction","");
$MsgToManager=GetStringParameter("MsgToManager","");
//**************************************************************************************
// Server-Side code
//**************************************************************************************
$UserID=GetUserID();

$WebsiteID=GetWebSiteID();

$WebmasterEmailAddress="gregory.newman@colostate.edu";

$PersonSet=TBL_People::GetSetFromID($Database,$UserID);

$PersonEmail=$PersonSet->Field("Email");

$PersonSet=TBL_People::GetSetFromID($Database,$UserID);
$PersonName=TBL_People::GetPersonsName($Database,$UserID);

$IPAddress=GetServerIPAddress();

if ($TakeAction=="SendEmail")
{	
	SendTextMsg($WebmasterEmailAddress,"CitSci.org Contact Question","Message from $PersonEmail:\n\n".$MsgToManager,$WebsiteID);
	
	RedirectFromRoot("/cwis438/UserManagement/ContactUsConfirmation.php?WebSiteID=$WebsiteID");
}

//**************************************************************************************
// HTML Header block, client-side includes, and client-side functions
//**************************************************************************************

$ThePage=new PageSettings();

$TheTable=new TableSettings(TABLE_FORM);

$ThePage->HeaderStart("Contact Us");

$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart();

$ThePage->Heading(1,"Contact Us");

$ThePage->BodyText
        ("Please let us know of any comments or concerns you have. <p>
        <br><p> Mailing address: <p>
        Living Atlas of East African Flora <p>
        Natural Resource Ecology Laboratory <p>
        Colorado State University <p>
        Fort Collins, Colorado 80523-1499 <p>
        <br> If you are having trouble with the website, please try looking at our <a href='/cwis438/websites/LEAF/FAQ.php'>Frequently Asked Questions</a>. (FAQs) These should answer many common questions. 
        <br> If you cannot find what you are looking for on the website, then please email us at gregory.newman@colostate.edu, or fill out the form below. 
        ");
$ThePage->LineBreak();

if ($UserID<=0)
{
	$ThePage->BodyText("Please provide your email address in your message below.");
}
$ThePage->LineBreak();
	
$TheTable->FormStart("/cwis438/UserManagement/ContactUs.php");

$TheTable->TableStart();

$TheTable->Columns[0]->VAlign="Top";
$TheTable->Columns[0]->Width="45";
$TheTable->Columns[1]->Width="680";

$TheTable->ColumnHeading->ColumnSpan=2;


$TheTable->TableRow(array("To:","$WebmasterEmailAddress"));

if ($UserID>0)
{
	$TheTable->TableRow(array("From:",$PersonName));
}
else 
{
	$TheTable->TableRow(array("From:","Website guest"));
}

$TheTable->TableRowStart();
	$TheTable->TableCell(0,"Message:");

	$TheTable->TableCellStart(1);
		$TheTable->FormTextAreaEntry("MsgToManager","",6,54); // Num Rows, Num Cols: 6,54
	$TheTable->TableCellEnd();
	
$TheTable->TableRowEnd();

$TheTable->TableEnd();

$TheTable->FormSubmit("Submit","Submit","onclick='DoSubmit()'");
$TheTable->FormHidden("TakeAction","SendEmail");
$TheTable->FormEnd();



$ThePage->BodyEnd();

?>
