<?php
//**************************************************************************************
// FileName: PersonRegister.php
// Author: jjg
// Owner: jjg
// Purpose: Allows the user to register on the NIISS/TMAP websites
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");

require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Areas.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_People.php");
require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_UserSessions.php");
require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");

require_once("C:/Inetpub/wwwroot/cwis438/utilities/GodmUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/SecurityUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/StringUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/ValidUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/EmailUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Permissions.php");

//**************************************************************************************
// Database connection
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Security
//**************************************************************************************

//**************************************************************************************
// Definitions
//**************************************************************************************

//**************************************************************************************
// Server-side functions
//**************************************************************************************

//**************************************************************************************
// Parameters
//**************************************************************************************

$CallingPage=GetStringParameter("CallingPage","/cwis438/websites/LEAF/Create_Project2.php");

$Submitted=GetIntParameter("Submitted",0);
$ProjectCoordinatorMessage=GetStringParameter("PContent","");
$CheckedUseAgreement=GetCheckBoxParameter("Agreement");

//**************************************************************************************
// Server-side code
//**************************************************************************************

$UserID=GetUserID();

$UserSet=TBL_People::GetSetFromID($Database,$UserID,NOT_SPECIFIED);
$FirstName=$UserSet->Field('FirstName');
$LastName=$UserSet->Field('LastName');
$Email=$UserSet->Field('Email');

$WebsiteID=GetWebSiteID();

$ServerName=$_SERVER["HTTP_HOST"];

$PageMsg="Create a LEAF Project";

$Error=null;

if ($Submitted) // Form was filled in, process entries
{
	// insert a blank person record to work from
	
	$PersonID=TBL_People::Insert($Database);
	
	$HashPassword=sha1($Password);
	
    // email admin request to become an instigator (project coordinator)	
    
    SendHTMLMsg("webmaster@citsci.org",			
        "LEAF Project Coordinator Request", "Dear LEAF Administrator,<br><br> $FirstName $LastName ($Email)".
        " has requested to become a LEAF PROJECT COORDINATOR. <br><br>Please ".
        "<a href='http://$ServerName/cwis438/admin/Management/People/People_Edit_UserLevel.php?PersonID=$UserID&WebSiteID=$WebsiteID'>".
        "click here</a> to approve/deny their request. <br><br>Project Goals: $ProjectCoordinatorMessage",$WebsiteID);

	DoLogIn($PersonID);

	$CallingPage=urlencode($CallingPage);
	
	RedirectFromRoot("/cwis438/websites/LEAF/PersonRegisterConfirmation.php?".
		"HasData=No&CallingPage=$CallingPage&WebSiteID=$WebsiteID");
}

//**************************************************************************************
// HTML Header block and client-side includes
//**************************************************************************************

$ThePage=new PageSettings;

$ThePage->HeaderStart("Create a LEAF project!");

?>
<SCRIPT TYPE="text/javascript" LANGUAGE="JavaScript" SRC="/cwis438/includes/FormUtil.js"></SCRIPT>
<SCRIPT TYPE="text/javascript" LANGUAGE="JavaScript" SRC="/cwis438/includes/JQuery/jquery-1.5.1.js"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">

document.onkeypress=function(e) { return(SubmitOnEnter(e)); };

$(document).ready(function()
{
var agreement = $("#Agreement"); 
var agreementInfo = $("#AgreementInfo"); 

var pcontent = $("#PContent");
var pcontentInfo = $("#PContentInfo");


var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;

    $(function() {  
    $('input[name*="Submit"]').click(function() { 
        var vagreement = validateAgreement();
        var vpcontent = validatePContent();
        if((vagreement == true) && (vpcontent == true))
            {
                document.register.Submitted.value=1;
                document.register.submit();
            }
    });  
});


function validateAgreement(){
    if($("#Agreement").attr('checked') == false){  
        agreement.addClass("error");  
        agreementInfo.text("Remember: Accept the Terms & Conditions");  
        agreementInfo.addClass("error");  
        return false;  
    }else{  
        agreement.removeClass("error");  
        agreementInfo.text("Please Click On Terms & Conditions");  
        agreementInfo.removeClass("error");  
        return true;  
    }  
}

function validatePContent()
{
        if(pcontent.val().length <= 10 )
        {  
            pcontent.addClass("error");  
            pcontentInfo.text("Please provide at least 10 characters in your description of your project goals.");  
            pcontentInfo.addClass("error");  
            return false;  
        }
        else
        {  
            pcontent.removeClass("error");  
            pcontentInfo.text("Tell us about yourself, your project, and your goals... What is your research question? ");  
            pcontentInfo.removeClass("error");  
            return true;  
        }

};
});

</SCRIPT>


<?
$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart();

$TheHost=GetComputerName();

$HelpLink="http://ibis.colostate.edu/DH.php?WC=/cwis438/help/UserManagement/personregister.htm?WebSiteID=$WebsiteID";

$TheTable=new TableSettings(TABLE_LAYOUT); //Table Form

$TheTable->FormStart(null,"register","post","register"); 
             //FormStart($Action=null,$Name="SelectForm",$Method="POST",$Id=null)

    $ThePage->DivStart("id=container");
    	
    	$ThePage->LineBreak();
    
        // NEED TO ADD A HELP PAGE TO LINK FROM THE ? img
        
        $ThePage->Heading(0,$PageMsg." <a href='/DH.php?WC=/WS/LEAF/Join_Us.html&WebSiteID=20'><img src='/cwis438/websites/CitSci/images/Help_Question_Mark_1_00.png' border='0' /></a>");
        
        $ThePage->LineBreak();
        
        $ThePage->DivStart("id='container1'"); //border:solid 1px red;    
    
            $ThePage->DivStart("id='container2'"); // width:320px;  border:solid 1px green;
	            
                    $ThePage->DivStart("style='color:red; text-align:center;'");
                    echo "Tell us about your project's goals, click Submit, and you will receive an email from the administrator when approved.<br/><br/>";
                    $ThePage->DivEnd();
            
                    $ThePage->DivStart("id='PCtextContainer'");
                    $ThePage->SpanStart("id='PContentInfo' style='margin-left: 0px;'");
                    echo "Tell us a little about your project...";
                    $ThePage->SpanEnd();
                    $ThePage->DivEnd();

                    $ThePage->DivStart("class='inputclass'");
                    $ThePage->WriteLabel("Project Goals","Goals","class='labelclass'");
                    $TheTable->FormTextAreaEntry('PContent','',4,55,"id='PContent'"); // ,4,55, border:solid 1px red; 33
                    $ThePage->DivEnd();
              
                    $ThePage->DivStart("class='inputclass'");
		            $ThePage->WriteLabel("Terms & Conditions","Agreement","class='labelclass'"); // onClick=\"OpenCloseDiv('TermsAgreement','wow','SEARCHSTRING')\" id='wow'
		            
		            $TheTable->FormCheckBox("Agreement","","","id='Agreement'");
                    
                        $ThePage->SpanStart("id='AgreementInfo'");
                            echo "Please click on terms and conditions";
                        $ThePage->SpanEnd();
                    
		            $ThePage->DivStart("style='margin-left:20px; margin-top:12px; margin-right:12px;'"); // id='TermsAgreement'     border:solid 1px red;
		                $ThePage->SpanStart("id='AgreementInfo'");
			        	echo("(1) The quality and completeness of data cannot be guaranteed. Users employ these data at their own risk.<br>
			        	        (2) Users must publicly acknowledge, in conjunction with the use of the data, the data providers whose 
		                        data they have used. Data providers may require additional attribution of specific 
		                        collections within their institution.");
			        	$ThePage->SpanEnd();
						echo("<br><br>");
						echo("<a rel='license' href='http://creativecommons.org/licenses/by/3.0/deed.en_US'><img alt='Creative Commons License' style='border-width:0' 
						        src='http://i.creativecommons.org/l/by/3.0/88x31.png' /></a>&nbsp;
						        <div style='display:inline;' xmlns:dct='http://purl.org/dc/terms/' property='dct:title'>CitSci.org </div> 
						         is licensed under a <a style='font-weight:bold; text-decoration:none; font-size:11px;' rel='license' href='http://creativecommons.org/licenses/by/3.0/deed.en_US'>Creative Commons Attribution 3.0 Unported License</a>.");
                    $ThePage->DivEnd();
		        $ThePage->DivEnd();
		        
            $ThePage->DivEnd();
        $ThePage->DivEnd();
    $ThePage->DivEnd();
	$ThePage->LineBreak();
    
    $ThePage->DivStart("id='regbutton'");
        $TheTable->FormButton("Submit","Submit","id='buttonnone' class='ButtonStyle'"); // class='ButtonStyle'
    $ThePage->DivEnd();
    
$TheTable->FormHidden("Submitted",$Submitted);

$TheTable->FormEnd();

$ThePage->LineBreak();
$ThePage->LineBreak();
$ThePage->LineBreak();
$ThePage->LineBreak();
$ThePage->LineBreak();
$ThePage->LineBreak();
$ThePage->LineBreak();
$ThePage->LineBreak();
$ThePage->LineBreak();

$ThePage->BodyEnd();

?>