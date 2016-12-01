<?php

//**************************************************************************************
// FileName: PersonRegisterConfirmation.php
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
// Author:  gjn
// Owner:  Ray Franklin
// Purpose: Displays a welcome to a newly registered user with an advisory that they need to
// check their email for a validation URL
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

//**************************************************************************************
// Database connection
//**************************************************************************************

$Database = NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Security
//**************************************************************************************

require_once('C:/Inetpub/wwwroot/utilities/recaptcha-php-1.11/recaptchalib.php');

$privatekey = GetReCaptchaPrivateCode();

//**************************************************************************************
// Definitions
//**************************************************************************************
//**************************************************************************************
// Server-side functions
//**************************************************************************************
//**************************************************************************************
// Parameters
//**************************************************************************************

$CallingPage = GetStringParameter("CallingPage", "");

$TakeAction = GetStringParameter("TakeAction", "");

// From PersonRegister............................................

$FirstName = GetStringParameter("FName", "");
$LastName = GetStringParameter("LName", "");
//$SpecificOrganization=GetStringParameter("SpecificOrganization","");
$Email = GetStringParameter("Email", "");
$Login = GetStringParameter("Login", "");
$Password = GetStringParameter("Password", "");
$PasswordConfirm = GetStringParameter("CPassword", "");
$CheckedUseAgreement = GetCheckBoxParameter("Agreement");

$WebSiteID = GetWebsiteID();

//**************************************************************************************
// Server-side code
//**************************************************************************************

$resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

if (!$resp->is_valid) {
    // What happens when the CAPTCHA was entered incorrectly
    die("The reCAPTCHA wasn't entered correctly. Go back and try it again." . "(reCAPTCHA said: " . $resp->error . ")");
} else { // response from recaptcha is valuid so go insert them and log them in...
    $ServerName = $_SERVER["HTTP_HOST"];

    $Error = null;

    //if ($Submitted) // Form was filled in, process entries
    {

        if ((ValidateString($FirstName, 2, 100) != null) ||
                (ValidateString($LastName, 2, 100) != null) ||
                ($CheckedUseAgreement == false) ||
                (ValidateLogin($Database, $Login) != null) ||
                (ValidateEmail($Email) != null) ||
                (ValidatePassword($Password) != null)) {
            $Error = "There is a problem on the page, please correct it and click 'Submit'";
            $PageMsg = $Error;
        }

        if ($Error == null) { // go ahead and register them
            // insert a blank person record to work from

            $PersonID = TBL_People::Insert($Database);

            $StateID = null;

            $HashPassword = sha1($Password);

            $UpdateString = "UPDATE TBL_People " .
                    "SET FirstName='$FirstName', " .
                    "LastName='$LastName', " .
                    "AgreedToDataUse='" . (int) $CheckedUseAgreement . "', " .
                    "Email='$Email', " .
                    "Login='$Login', " .
                    "Password='$HashPassword', " .
                    "WebsiteID='$WebsiteID' " .
                    "WHERE ID=$PersonID";

            $Database->Execute($UpdateString);

            $length = 10;
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, strlen($characters) - 1)];
            }
            // DebugWriteln("$UpdateString=".$UpdateString);
            $VerificationUpdateString = "UPDATE TBL_People " .
                    "SET VerificationCode='$randomString' " .
                    "WHERE ID=$PersonID";
            $Database->Execute($VerificationUpdateString);


            TBL_Permissions::Insert($Database, $PersonID, PERMISSION_USER);

            DoLogIn($PersonID);

            $CallingPage = urlencode($CallingPage);

            $WebsiteSet = TBL_Websites::GetSetFromID($Database, $WebsiteID);
            $Description = $WebsiteSet->Field("Description");

            if (strripos($Description, "website") === false) { // website not found
                $Description.=" (LEAF) website";
            }

            $Link1 = "http://$ServerName/cwis438/UserManagement/Login.php?WebSiteID=20";
            $Link2 = "http://$ServerName/cwis438/Browse/Project/Project_List.php?WebSiteID=20";
            $Link3 = "http://$ServerName/cwis438/websites/LEAF/Add_Data.php?WebSiteID=20";
            $Link4 = "http://$ServerName/cwis438/websites/LEAF/UserProfile.php?WebSiteID=20";
            $Link5 = "http://$ServerName/cwis438/websites/LEAF/Help.php?WebSiteID=20";
            $Link6 = "http://$ServerName/cwis438/websites/LEAF/Contact_Us.php?WebSiteID=20";

            $Message = "Dear $FirstName,<p><br/>" .
                    "Thank you for registering with the <b>$Description</b>.<br/><br/> " .
                    "You login information is:<br/><br/> " .
                    "Login:&nbsp;&nbsp;<b>$Login</b><br/>" .
                    "Password:&nbsp;&nbsp;<b>$Password</b><br/><br/> " .
                    "&nbsp;&nbsp;&nbsp;<a href='$Link1'>LOGIN</a> to the LEAF.<br/><br/> " .
                    "&nbsp;&nbsp;&nbsp;<a href='$Link4'>GO TO</a> my LEAF profile.<br/><br/> " .
                    "&nbsp;&nbsp;&nbsp;<a href='$Link2'>EXPLORE</a> the LEAF projects.<br/><br/> " .
                    "&nbsp;&nbsp;&nbsp;<a href='$Link3'>CONTRIBUTE</a> data to LEAF.<br/><br/> " .
                    "&nbsp;&nbsp;&nbsp;<a href='$Link5'>GET HELP</a> using the LEAF.<br/><br/><br/> " .
                    "&nbsp;&nbsp;&nbsp;<a href='$Link6'>CONTACT US</a> for any questions you have.<br/><br/> " .
                    "Thank you,<p><br/>" .
                    "The $Description Support Team \n";

            SendHTMLMsg($Email, "Welcome to the Living Atlas of East African Flora (LEAF)!", $Message, $WebsiteID);

            RedirectFromRoot("/cwis438/websites/LEAF/UserProfile.php?WebSiteID=$WebsiteID");
        }
    }
}

$Set = TBL_People::GetSetFromID($Database, $PersonID);

//**************************************************************************************
// HTML Header block and client-side includes
//**************************************************************************************

$ThePage = new PageSettings;

$ThePage->HeaderStart("Login Confirmation");

$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart();

$ThePage->Heading(0, "Welcome " . $Set->Field("FirstName") . "!");
echo ("<br>");
$ThePage->ParagraphBreak();

$ThePage->Heading(2, "Thank you for registering with the $Description!");
echo ("<br>");
$ThePage->ParagraphBreak();

echo "<br/>";
$ThePage->BodyText("If you registered to create a project, an email will be sent to you upon approval by the administrator.");
echo "<br/>";
$ThePage->BodyText("Go to <a href='/cwis438/websites/LEAF/UserProfile.php?WebSiteID=20'>MY PROFILE</a>.");
echo "<br/>";
$ThePage->BodyText("<a href='/cwis438/websites/LEAF/Submit_Observation.php?WebSiteID=20'>CONTRIBUTE</a> to the LEAF.");
echo "<br/>";
$ThePage->BodyText("Read the LEAF <a href='/cwis438/websites/LEAF/FAQ.php?WebSiteID=20'>FAQ</a>.");
echo "<br/>";
$ThePage->BodyText("Need <a href='/cwis438/websites/LEAF/Help.php?WebSiteID=20'>HELP</a>?");



$ThePage->BodyEnd();
?>
