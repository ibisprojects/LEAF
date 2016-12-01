<?php

//**************************************************************************************
// FileName: InsertComment.php
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
// Author: gjn
// Owner: gjn
// This page inserts a comment
//**************************************************************************************

//**************************************************************************************
// Includes
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Blogs.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/REL_PersonToProject.php");

require_once("C:/Inetpub/wwwroot/utilities/StringUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/SecurityUtil.php");

//**************************************************************************************
// Database connections
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Security
//**************************************************************************************

CheckLogin2($Database,PERMISSION_USER);

//**************************************************************************************
// Variables
//**************************************************************************************

//$ProjectID==GetIntParameter("ProjectID");
$VisitDateObject=GetDateParameter("Date");
$Title=GetStringParameter("Title");
$Text=GetStringParameter("BlogText");
$DatabaseTableID=GetIntParameter("DatabaseTableID",83); // id for TBL_Websites
$RecordID=GetIntParameter("RecordID",20); // id for LEAF record
$ParentID=GetIntParameter("ParentID");

//DebugWriteln("Title=$Title");
//DebugWriteln("Text=$Text");

//**************************************************************************************
// Server-side code
//**************************************************************************************

$PersonID=0;
$PersonID=GetUserID();

//*********************************************************************************
// (1) insert new record into TBL_Blogs
//*********************************************************************************

if ($PersonID>0)
{    
    $CommentID=TBL_Blogs::Insert($Database,$Text);

    //*********************************************************************************
    // (2) update new blog record
    //*********************************************************************************

    if ($ParentID>0)
    {
        TBL_Blogs::Update($Database,$CommentID,$DatabaseTableID,$RecordID,$ParentID,$Title,$PersonID,null);
    }
    else
    {
        TBL_Blogs::Update($Database,$CommentID,$DatabaseTableID,$RecordID,NULL,$Title,$PersonID,null);
    }
}
//*********************************************************************************
// (2) encode the return value and echo JSON data for return
//*********************************************************************************

$ReturnArray=array();

array_push($ReturnArray,"Success");

$JSON=json_encode($ReturnArray);

echo('{"InsertCommentResponse":'.$JSON.'}');

?>