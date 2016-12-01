<?php
           
class Page
{
	// Header functions
	
	function Header($Title)
	{
		$this->HeaderStart($Title);
		$this->HeaderEnd();
	}
	
	function HeaderStart($Title)
	{
		echo("<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN' 'http://www.w3.org/TR/html4/strict.dtd'>
				<html xmlns='http://www.w3.org/1999/xhtml'>
				<head>
				<meta http-equiv='content-Type' content='text/html; charset=utf-8' />
				<title>$Title</title>
				<link rel='stylesheet' href='stylesheets/LEAF.css'>
				<link rel='shortcut icon' type='image/x-icon' href='/CCEP/Images/icon.ico'>");
	}
	
	function HeaderEnd()
	{
		echo("</head>");
	}
	
	// Body functions
	
	function BodyStart()
	{
		echo("<body>"); // scroll='auto'
		
		//require_once("C:/Inetpub/wwwroot/CCEP/Login_Include.php");
		
		echo("<div id='wrapper'>");

        echo("<div id='header'>"); // start header section
        
        echo("<div id='banner'>
                <div style='position:relative; margin-left:-98px; width:98px; height:73px; background-color:black; display:inline-block; float:left;'><img src='images/BannerTree.png'></div>
                <img src='images/Banner.png' />
              </div>");
        
        echo("</div>"); // end header section
        
        echo("<div id='middle'>");
        
        echo("<div id='middle_container'>");
        
        echo("<div id='navigation'>
                <a class='NavigationLink' href='Index.php'>Home</a> | 
                <a class='NavigationLink' href='About.php'>About</a> | 
                <a class='NavigationLink' href='Partners.php'>Partners</a> |
                <a class='NavigationLink' href='Add_Data.php'>Add Data</a>
              </div>");
        
        echo("<div id='content'>");
		
		// place content here...
	}
	
	function BodyEnd()
	{    
		echo("</div>"); // content end

        echo("</div>"); // middelcontainer end
        
        echo("</div>"); // middle end
        
        echo("<div id='footer'></div>");
        
        echo("</div>"); // wrapper end
		
		echo("</body>");
		
		echo("</html>");
	}
	
}

?>