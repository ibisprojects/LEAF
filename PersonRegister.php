<?php

//**************************************************************************************
// FileName: PersonRegister.php for LEAF
//
// Copyright (c) 2006, 
//
// Permission is hereby granted, free of charge, to any person obtaining a
// copy of this software and associated documentation files (the "Software"),
// to deal in the Software without restriction, including without limitation
// the rights to use, copy, modify, merge, publish, distribute, sublicense,
// and/or sell copies of the Software, and to permit persons to whom the
// Software is furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included
// in all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
// THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
// FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
// DEALINGS IN THE SOFTWARE.
//**************************************************************************************

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

require_once('C:/Inetpub/wwwroot/utilities/recaptcha-php-1.11/recaptchalib.php');

$publickey=GetReCaptchaCode();

//**************************************************************************************
// Definitions
//**************************************************************************************

//**************************************************************************************
// Server-side functions
//**************************************************************************************

//**************************************************************************************
// Parameters
//**************************************************************************************

$CallingPage=GetStringParameter("CallingPage","");

$Submitted=GetIntParameter("Submitted",0);

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

$PageMsg="Please enter the following information to become a registered LEAF user";

$Error=null;

if ($Submitted) // Form was filled in, process entries
{		
	
	if ((ValidateString($FirstName,2,100)!=null)||
		(ValidateString($LastName,2,100)!=null)||
		($CheckedUseAgreement==false)||
		(ValidateLogin($Database,$Login)!=null)||
		(ValidateEmail($Email)!=null)||
		(ValidatePassword($Password)!=null))
	{
		$Error="There is a problem on the page, please correct it and click 'Submit'";
		$PageMsg=$Error;
	}
	
	if ($Error==null) // go ahead and register them
	{
		// insert a blank person record to work from
		
		$PersonID=TBL_People::Insert($Database);
		
		$StateID=null;
		
		$HashPassword=sha1($Password);
		
		$UpdateString="UPDATE TBL_People ".
			"SET FirstName='$FirstName', ".
				"LastName='$LastName', ".
				"AgreedToDataUse='".(int)$CheckedUseAgreement."', ".
				"Email='$Email', ".
				"Login='$Login', ".
				"Password='$HashPassword', ".
				"WebsiteID='$WebsiteID' ".
			"WHERE ID=$PersonID";
		
		$Database->Execute($UpdateString);
                   
		TBL_Permissions::Insert($Database,$PersonID,PERMISSION_USER);
                
		DoLogIn($PersonID);

		$CallingPage=urlencode($CallingPage);
		
		$WebsiteSet=TBL_Websites::GetSetFromID($Database,$WebsiteID);
		$Description=$WebsiteSet->Field("Description");
		
		if (strripos($Description,"website")===false) // website not found
		{
			$Description.=" (LEAF) website";
		}
		
        $Link1="http://$ServerName/cwis438/UserManagement/Login.php?WebSiteID=20";
		$Link2="http://$ServerName/cwis438/Browse/Project/Project_List.php?WebSiteID=20";      
        $Link3="http://$ServerName/cwis438/websites/LEAF/Add_Data.php?WebSiteID=20";
        $Link4="http://$ServerName/cwis438/websites/LEAF/UserProfile.php?WebSiteID=20";
        $Link5="http://$ServerName/cwis438/websites/LEAF/Help.php?WebSiteID=20";
        $Link6="http://$ServerName/cwis438/websites/LEAF/Contact_Us.php?WebSiteID=20";
			
		$Message="Dear $FirstName,<p><br/>".
                
				"Thank you for registering with the <b>$Description</b>.<br/><br/> ".
                
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
                
				"The $Description Support Team \n";
        
        SendHTMLMsg($Email,"Welcome to the Living Atlas of East African Flora (LEAF)!",$Message,$WebsiteID);
               
        RedirectFromRoot("/cwis438/websites/LEAF/PersonRegisterConfirmation.php?".
                    "HasData=Yes&WebSiteID=$WebsiteID");                                         
            
	}
}

//**************************************************************************************
// HTML Header block and client-side includes
//**************************************************************************************

$ThePage=new PageSettings;

$ThePage->HeaderStart("Registration Information");

?>
<SCRIPT TYPE="text/javascript" LANGUAGE="JavaScript" SRC="/cwis438/includes/FormUtil.js"></SCRIPT>
<SCRIPT TYPE="text/javascript" LANGUAGE="JavaScript" SRC="/cwis438/includes/JQuery/jquery-1.5.1.js"></SCRIPT>
<SCRIPT>

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
        //var vpcontent = validatePContent();
        if((vfname == true) && (vlname == true) && (vemail == true) && (vvalidatelogin == true) && (vpassword == true) && (vcpassword == true) && (vagreement == true))
            {
                document.register.Submitted.value=1;
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

});

</SCRIPT>

<?

$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart();

$TheHost=GetComputerName();

$TheTable=new TableSettings(TABLE_LAYOUT); //Table Form

$TheTable->FormStart("/cwis438/websites/LEAF/PersonRegisterConfirmation.php?WebSiteID=$WebsiteID","register","post","register");

    $ThePage->DivStart("id=container");
    	
    	$ThePage->LineBreak();
    
        $ThePage->Heading(0,"Join LEAF"); //." <a href='/DH.php?WC=/WS/CitSci/Join_Us.html&WebSiteID=7'><img src='/cwis438/websites/CitSci/images/Help_Question_Mark_1_00.png' border='0' /></a>"
        
        $ThePage->LineBreak();
        
        $ThePage->DivStart("id='container1'"); //border:solid 1px red;    
    
            $ThePage->DivStart("id='container2'"); // width:320px;  border:solid 1px green;
	            
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
                
                $ThePage->DivStart("class='inputclass'");
		            $ThePage->WriteLabel("Terms & Conditions","Agreement","class='labelclass'"); // onClick=\"OpenCloseDiv('TermsAgreement','wow','SEARCHSTRING')\" id='wow'
		            
		            $TheTable->FormCheckBox("Agreement","","","id='Agreement'");
                    
                        $ThePage->SpanStart("id='AgreementInfo'");
                            echo "Please Click On terms and conditions";
                        $ThePage->SpanEnd();
                   
		            $ThePage->DivStart("style='margin-left:20px; margin-top:12px; margin-right:12px;'"); // id='TermsAgreement'     border:solid 1px red;
			        	echo("<span style='font-size:11px;'>(1) The quality and completeness of data cannot be guaranteed. Users employ these data at their own risk.<br><br>
		                            (2) Users must publicly acknowledge, in conjunction with the use of the data, the data providers whose 
		                                data they have used. Data providers may require additional attribution of specific 
		                                collections within their institution.</span>"
									);
			        	echo("<br/><br/>");
			        	echo("<div style='width:132px; height:180px; float:left;'><b>Enter what's seen</b></div>");
			        	
			        	echo("<div style='width:340px; height:180px; float:left;'>"); //float:right;
			        	$publickey=GetReCaptchaCode();
			        	echo recaptcha_get_html($publickey);
						echo("<span style='font-size:11px;'>Case sensitive. Added for security reasons. Use the <img src='/cwis438/images/ReCaptchaRecycleImage.png' height='16' /> button to generate a new pattern to match if needed.</span>");
						echo("</div>");
						
						echo("<br/><br/ style='clear:all;'>");
						
						echo("<div style='width:100%; height:50px; display:inline-block;'>");
						
						echo("<a rel='license' href='http://creativecommons.org/licenses/by/3.0/deed.en_US'><img alt='Creative Commons License' style='border-width:0' 
						        src='http://i.creativecommons.org/l/by/3.0/88x31.png' /></a>&nbsp;
						        <div style='display:inline;' xmlns:dct='http://purl.org/dc/terms/' property='dct:title'><span style='font-size:11px; text-decoration:none; text-align:bottom'>LEAF</span> </div> 
						         <span style='font-size:11px; text-decoration:none;'>is licensed under a </span><a style='font-weight:normal; text-decoration:none; font-size:11px;' rel='license' href='http://creativecommons.org/licenses/by/3.0/deed.en_US'>Creative Commons Attribution 3.0 Unported License</a>.");
						
						echo("</div>");
						
                    $ThePage->DivEnd();
		        $ThePage->DivEnd();
		        
            $ThePage->DivEnd();
        $ThePage->DivEnd();
    $ThePage->DivEnd();
	$ThePage->LineBreak();
    
    $ThePage->DivStart("id='regbutton'");
        $TheTable->FormButton("Submit","Submit","id='buttonnone' class='btn btn-default'"); // class='ButtonStyle'
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