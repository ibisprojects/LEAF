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

$UserID=GetUserID();

if ($UserID>0)
{
    if (TBL_Permissions::HasPermission($Database,PERMISSION_INSTIGATOR)==true)  // if logged in user is a project coordinator,
                                                                                // redirect to Project_Edit.php
    {
        RedirectFromRoot("/cwis438/websites/LEAF/Project_Edit.php?WebSiteID=20");
        exit();
    }
    else //(TBL_Permissions::HasPermission($Database,PERMISSION_USER)==true)  // if logged in user is only a user
    {
        RedirectFromRoot("/cwis438/websites/LEAF/Create_Project2.php?WebSiteID=20");
        exit();
    }        
}


// if logged in, 


//**************************************************************************************
// Definitions
//**************************************************************************************

//**************************************************************************************
// Server-side functions
//**************************************************************************************

//**************************************************************************************
// Parameters
//**************************************************************************************

$CallingPage=GetStringParameter("CallingPage","/cwis438/websites/LEAF/Create_Project.php");

$Submitted=GetIntParameter("Submitted",0);
//$ProjectCoordinator=GetIntParameter("ProjectCoordinator",0);
$ProjectCoordinatorMessage=GetStringParameter("ProjectCoordinatorMessage","");

$FirstName=GetStringParameter("FName","");
$LastName=GetStringParameter("LName","");
$Email=GetStringParameter("Email","");
$Login=GetStringParameter("Login","");
$Password=GetStringParameter("Password","");
$PasswordConfirm=GetStringParameter("CPassword","");
$CheckedUseAgreement=GetCheckBoxParameter("Agreement");

//**************************************************************************************
// Server-side code
//**************************************************************************************

$WebsiteID=GetWebSiteID();

$ServerName=$_SERVER["HTTP_HOST"];

$PageMsg="Register and create a LEAF Project <b>OR</b> <a href='/cwis438/UserManagement/Login.php?WebSiteID=20&CallingPage=$CallingPage'>LOGIN<a/>";

$Error=null;

if ($Submitted) // Form was filled in, process entries
{
	// insert a blank person record to work from
	
	$PersonID=TBL_People::Insert($Database);
	
	$HashPassword=sha1($Password);
	
	$UpdateString="UPDATE TBL_People ".
		"SET FirstName='$FirstName', ".
			"LastName='$LastName', ".
			"AgreedToDataUse='".(int)$CheckedUseAgreement."', ".
			"Email='$Email', ".
			"Login='$Login', ".
			"Password='$HashPassword', ".
			"WebsiteID='$WebsiteID', ".
            "ValidatedEmail= 1 ".
		"WHERE ID=$PersonID";
	
	$Database->Execute($UpdateString);

	TBL_Permissions::Insert($Database,$PersonID,PERMISSION_USER);
	
    // email Russ a request to become an instigator (project coordinator)	
    
    SendHTMLMsg("russellscarpino@yahoo.com",			
        "LEAF Project Coordinator Request", "Dear LEAF Administrator,<br><br/> $FirstName $LastName ($Email)".
        " has requested to become a LEAF PROJECT COORDINATOR. <br><br>Please ".
        "<a href='http://$ServerName/cwis438/admin/Management/People/People_Edit_UserLevel.php?PersonID=$PersonID&WebSiteID=$WebsiteID'>".
        "click here</a> to approve/deny their request. <br><br>Project Goals: $ProjectCoordinatorMessage",$WebsiteID);
    
    $Link1="http://$ServerName/cwis438/UserManagement/Login.php?WebSiteID=20";
    $Link2="http://$ServerName/cwis438/Browse/Project/Project_List.php?WebSiteID=20";      
    $Link3="http://$ServerName/cwis438/websites/LEAF/Add_Data.php?WebSiteID=20";
    $Link4="http://$ServerName/cwis438/UserManagement/UserProfile.php?WebSiteID=20";
    $Link5="http://$ServerName/cwis438/websites/LEAF/Help.php?WebSiteID=20";
    $Link6="http://$ServerName/cwis438/websites/LEAF/Contact_Us.php?WebSiteID=20";
			
    $Message="Dear $FirstName,<p><br/>".

            "Thank you for registering with the Living Atlas of East African Flora!</b>.<br/><br/> ".

            "You login information is:<br/><br/> ".

            "Login:&nbsp;&nbsp;<b>$Login</b><br/>".
            "Password:&nbsp;&nbsp;<b>$Password</b><br/><br/> ".

                "&nbsp;&nbsp;&nbsp;<a href='$Link1'>LOGIN</a> to the LEAF.<br/><br/> ".
                "&nbsp;&nbsp;&nbsp;<a href='$Link4'>GO TO</a> my LEAF profile.<br/><br/> ".
                "&nbsp;&nbsp;&nbsp;<a href='$Link2'>EXPLORE</a> the LEAF projects.<br/><br/> ".
                "&nbsp;&nbsp;&nbsp;<a href='$Link3'>CONTRIBUTE</a> data to LEAF.<br/><br/> ".
                "&nbsp;&nbsp;&nbsp;<a href='$Link5'>GET HELP</a> using the LEAF.<br/><br/><br/> ".
                "&nbsp;&nbsp;&nbsp;<a href='$Link6'>CONTACT US</a> for any questions you have.<br/><br/> ".

            "Thank you,<p><br/>".

            "The LEAF Support Team \n";

    SendHTMLMsg($Email,"Welcome to the Living Atlas of East African Flora (LEAF)!",$Message,$WebsiteID); 

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
var fname = $("#Fname");  
var fnameInfo = $("#FnameInfo");
var lname = $("#Lname");  
var lnameInfo = $("#LnameInfo"); 
var email = $("#Email");  
var emailInfo = $("#EmailInfo"); 

var login = $("#Login");  
var loginInfo = $("#LoginInfo"); 

var password = $("#Password");  
var passwordInfo = $("#PasswordInfo"); 
var cpassword = $("#CPassword");  
var cpasswordInfo = $("#CPasswordInfo"); 

var agreement = $("#Agreement"); 
var agreementInfo = $("#AgreementInfo"); 

var pcontent = $("#PContent");
var pcontentInfo = $("#PContentInfo");


var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;

    $(function() {  
    $('input[name*="Submit"]').click(function() { 
        var vfname = validateFName();
        var vlname = validateLName();
        var vemail = validateEmail();
        var vvalidatelogin = validateLogin();
        var vpassword = validatePassword();
        var vcpassword = validateCPassword();
        var vagreement = validateAgreement();
        var vpcontent = validatePContent();
        if((vfname == true) && (vlname == true) && (vemail == true) && (vvalidatelogin == true) && (vpassword == true) && (vcpassword == true) && (vagreement == true)
            && (vpcontent == true))
            {
                document.register.Submitted.value=1;
                //alert('document.register.Submitted.value='+document.register.Submitted.value);
                document.register.submit();
            }
    });  
});

function validateFName()
{
    if(fname.val().length <= 2){  
        fname.addClass("error");  
        fnameInfo.text("Please provide a first name with more than 2 letters!");  
        fnameInfo.addClass("error");  
        return false;  
    }else{  
        fname.removeClass("error");  
        fnameInfo.text("What's your first name?");  
        fnameInfo.removeClass("error");  
        return true;  
    }  
}

function validateLName()
{
    if(lname.val().length <= 2){  
        lname.addClass("error");  
        lnameInfo.text("Please provide a last name with more than 2 letters!");  
        lnameInfo.addClass("error");  
        return false;  
    }else{  
        lname.removeClass("error");  
        lnameInfo.text("What's your last name?");  
        lnameInfo.removeClass("error");  
        return true;  
    }  
}

function validateEmail()
{
    if(email.val().length < 4){  
        email.addClass("error");  
        emailInfo.text("Please type a valid email address!");  
        emailInfo.addClass("error");  
        return false;  
    }else if(!emailReg.test(email.val())){
        email.addClass("error");  
        emailInfo.text("Type Valid Email Address!");  
        emailInfo.addClass("error");  
        return false;      
    }else{  
        email.removeClass("error");  
        emailInfo.text("Valid e-mail please!");  
        emailInfo.removeClass("error");  
        return true;  
    }  
}


function validateLogin()
{
    if(login.val().length <= 2){  
        login.addClass("error");  
        loginInfo.text("We want login with more than 2 letters!");  
        loginInfo.addClass("error");  
        return false;  
    }else{  
        login.removeClass("error");  
        loginInfo.text("Your login Name");  
        loginInfo.removeClass("error");  
        return true;  
    }  
}


function validatePassword()
{
    if(password.val().length <= 5){  
        password.addClass("error");  
        passwordInfo.text("Remember: At least 5 characters: letters, numbers and '_'");  
        passwordInfo.addClass("error");  
        return false;  
    }else{  
        password.removeClass("error");  
        passwordInfo.text("At least 5 characters: letters, numbers and '_' ");  
        passwordInfo.removeClass("error");  
        return true;  
    }  
}

function validateCPassword()
{
    if((cpassword.val().length <= 5) ){  
        cpassword.addClass("error");  
        cpasswordInfo.text("Remember: Confirm password should match password!");  
        cpasswordInfo.addClass("error");  
        return false;  
    }else if((password.val() != cpassword.val() )){
        cpassword.addClass("error");  
        cpasswordInfo.text("Remember: Confirm password should match password!");  
        cpasswordInfo.addClass("error");  
        return false;  
    }else{  
        cpassword.removeClass("error");  
        cpasswordInfo.text("Confirm password! ");  
        cpasswordInfo.removeClass("error");  
        return true;  
    }  
}

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
    //if(document.getElementById("PContent").style.display=="block")

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
                    echo "Fill out the information, Click Submit, and you will receive an email from the administrator when approved.<br/><br/>";
                    $ThePage->DivEnd();
	            
            	$ThePage->DivStart("id='firstinputclass'");
	            	$ThePage->WriteLabel("First Name","Fname","class='labelclass'");
		            $TheTable->FormTextEntry('FName','',50,"id='Fname'"); // border:solid 1px red; 
		            $ThePage->SpanStart("id='FnameInfo'");
		                echo "What's your first name?";
		            $ThePage->SpanEnd();
		            echo("<br>");
	            $ThePage->DivEnd();
	            
	            $ThePage->DivStart("class='inputclass'");
		            $ThePage->WriteLabel("Last Name","Lname","class='labelclass'");
		            $TheTable->FormTextEntry('LName','',50,"id='Lname'");
		            $ThePage->SpanStart("id='LnameInfo'");
		                echo "What's your last name?";
		            $ThePage->SpanEnd();
		            echo("<br>");
	            $ThePage->DivEnd();
	            
	            $ThePage->DivStart("class='inputclass'");
		            $ThePage->WriteLabel("Email","Email","class='labelclass'");
		            $TheTable->FormTextEntry('Email','',50,"id='Email'");
		            $ThePage->SpanStart("id='EmailInfo'");
		                echo "Valid e-mail please.";
		            $ThePage->SpanEnd();
		            echo("<br>");
	            $ThePage->DivEnd();
                
                $ThePage->DivStart("class='inputclass'");
		            $ThePage->WriteLabel("Login","Login","class='labelclass'");
		            $TheTable->FormTextEntry('Login','',50,"id='Login'");
		            $ThePage->SpanStart("id='LoginInfo'");
		                echo "Your login.";
		            $ThePage->SpanEnd();
		            echo("<br>");
	            $ThePage->DivEnd();
	            
	            $ThePage->DivStart("class='inputclass'");
		            $ThePage->WriteLabel("Password","Password","class='labelclass'");
		            $TheTable->FormPasswordEntry('Password','',50,"id='Password'");
		            $ThePage->SpanStart("id='PasswordInfo'");
		                echo "At least 5 characters: letters, numbers and '_'";
		            $ThePage->SpanEnd();
		            echo("<br>");
	            $ThePage->DivEnd();
	            
	            $ThePage->DivStart("class='inputclass'");
		            $ThePage->WriteLabel("Confirm Password","CPassword","class='labelclass'");
		            $TheTable->FormPasswordEntry('CPassword','',50,"id='CPassword'");
		            $ThePage->SpanStart("id='CPasswordInfo'");
		                echo "Confirm password";
		            $ThePage->SpanEnd();
		            echo("<br>");
	            $ThePage->DivEnd();
	            
                //$ThePage->DivStart("id='ProjectCoordinatorContent'"); 
                    $ThePage->DivStart("id='PCtextContainer'");
                    $ThePage->SpanStart("id='PContentInfo' style='margin-left: 0px;'");
                    echo "Tell us a little about your project...";
                    $ThePage->SpanEnd();
                    $ThePage->DivEnd();

                    //$ThePage->DivStart("id='ProjectCoordinatorContent'"); 
                    $ThePage->DivStart("class='inputclass'");
                    $ThePage->WriteLabel("Project Goals","Goals","class='labelclass'");
                    $TheTable->FormTextAreaEntry('ProjectCoordinatorMessage','',4,55,"id='PContent'"); // ,4,55, border:solid 1px red; 33
                    $ThePage->DivEnd();
                 // $ThePage->DivEnd();
	      /*
                $ThePage->DivStart("id='ProjectCoordinatorContent'"); // border:solid 1px red;
                    $ThePage->WriteLabel("Project Goals","Goals","class='labelclass'");
                    
                    $ThePage->DivStart("id='PCtextContainer'");
                        $ThePage->SpanStart("id='PContentInfo' style='margin-left: 0px;'");
                            echo "Tell us about yourself, your project, and your goals... What is your research question?";
                        $ThePage->SpanEnd();
                    $ThePage->DivEnd();
                    
                    $TheTable->FormTextAreaEntry('PContent','',4,55,"id=PContent"); // border:solid 1px red; 33
                $ThePage->DivEnd();
                */
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
//$TheTable->FormHidden("ProjectCoordinator",$ProjectCoordinator);    

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