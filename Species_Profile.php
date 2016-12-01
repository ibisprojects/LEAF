<?php
//**************************************************************************************
// FileName: OrganismInfo_Info.php
// Author: 
// Owner:
// Purpose: Provides a search facility for the db's species profiles
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");

require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/SecurityUtil.php");

require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Areas.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Visits.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Projects.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_AttributeData.php");

require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Media.php");

require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/REL_MediaToOrganismInfo.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_OrganismInfos.php");

//**************************************************************************************
// Database Connection
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Security
//**************************************************************************************

//**************************************************************************************
// Server-side functions
//**************************************************************************************

//**************************************************************************************
// Parameters
//**************************************************************************************

$OrganismInfoID=GetIntParameter("OrganismInfoID");

//**************************************************************************************
// Server-side code
//**************************************************************************************

$Set=TBL_OrganismInfos::GetSetFromID($Database,$OrganismInfoID);
$CommonName=$Set->Field("Name");

$TSNSet=REL_OrganismInfoToTSN::GetSet($Database,$OrganismInfoID);
$TSN=$TSNSet->Field("TSN");

$SciName=TBL_TaxonUnits::GetScientificNameFromTSN($Database,$TSN);

$WebSiteID=7; // citsci needed for demo?

$NumVisitsQuery="SELECT COUNT(DISTINCT VisitID) AS NumVisits FROM TBL_OrganismData WHERE (OrganismInfoID = $OrganismInfoID)";

$NumVisitsSet=$Database->Execute($NumVisitsQuery);

$NumVisits=$NumVisitsSet->Field("NumVisits");

$NumAreasQuery="SELECT COUNT(DISTINCT TBL_Areas.ID) AS NumAreas
    FROM TBL_OrganismData INNER JOIN
        TBL_Visits ON TBL_OrganismData.VisitID = TBL_Visits.ID INNER JOIN
        TBL_Areas ON TBL_Visits.AreaID = TBL_Areas.ID
    WHERE (TBL_OrganismData.OrganismInfoID = $OrganismInfoID)";

$NumAreasSet=$Database->Execute($NumAreasQuery);

$NumAreas=$NumAreasSet->Field("NumAreas");

$InitialLatitude=2.70; // 9.0300 is addis
$InitialLongitude=38.8500;
$InitialZoom=4;

if ($InitialLatitude==null)
{
    $InitialLatitude="9.0300";
    $InitialZoom=4;
}

if ($InitialLongitude==null)
{
    $InitialLongitude="38.7400";
    $InitialZoom=4;
}

// Get Common and Vernacular names

$SelectString="SELECT Name
			FROM TBL_Venaculars
			WHERE TSN=$TSN";

$NameSet=$Database->Execute($SelectString);
    


//**************************************************************************************
// Client-side
//**************************************************************************************

$ThePage=new PageSettings();

$ThePage->HeaderStart("Living Atlas of East African Flora - Species Profile");

?>
<!--  TABS  -->

<link rel="stylesheet" href="/cwis438/Websites/LEAF/stylesheets/jquery-ui.css" />

<script type="text/javascript" SRC="/cwis438/includes/FormUtil.js"></script>
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyD9RHlLKYGsbelja9DzYOHq1YpNMFFbMDE&sensor=false"></script>
<script src="http://www.google.com/jsapi"></script>

<script>

//------------------------------------------------------------------
// globals
//------------------------------------------------------------------

var infowindow = null;
var map;
var geocoder;
var marker = null;
var markersArray = [];
var myLatLng = null;
var myLat;
var myLng;

//var NumVisits=parseInt("<?php //echo $NumVisits;?>//");

var NumAreas=parseInt("<?php echo $NumAreas;?>");

var OrganismInfoID="<?php echo $OrganismInfoID; ?>";

var NumAreasReturned=null;

var RealData=0;

var markers=[];

var UserZoom=true;

var WebSiteID="<?php echo $WebSiteID; ?>";

$(document).ready(function()
{
	$("#tabs").tabs();

	// ------------------------------------------------------------------
    // IBIS Internal Web Service Calls
	// -----------------------------------------------------------------

	GetIBISTaxonomy();

    GetOverview();

    GetResources();

    GetGallery();
    
    // ------------------------------------------------------------------
    // adjust page height as needed?
	// ------------------------------------------------------------------
	
    // get the initial css height of the tabs <div>
    var TabsString = $("#tabs").css('height');
    //alert(TabsString);

    // strip the px off of the TabsString and convert the number to an integer
    var TabsHeight=parseInt(TabsString.replace("px","")); 
    //alert(TabsHeight);

    // add a buffer big enough to accomodate the text above the tabs <div>
    var NewTabsHeight=TabsHeight+200;
    //alert(NewTabsHeight);

    // re-create a new css string
    var TabsHeightStyleString="min-height:"+NewTabsHeight+"px;";
    
    // override initial css of the content <div> with newly calculated css based on height of tabs <div> 
    document.getElementById("content").setAttribute("style",TabsHeightStyleString);
});

function GetIBISTaxonomy() //Get Taxonomy for Classification Tab
{
    var url='/cwis438/websites/LEAF/Webservices/GetTaxonomy.php';
    
	$.post(url,{'OrganismInfoID':OrganismInfoID},function(html) {$('#classification_tab').html(html);}); 
}

function GetOverview() // Get Overview material
{
    var url='/cwis438/websites/LEAF/Webservices/GetOverview.php';
    
	$.post(url,{'OrganismInfoID':OrganismInfoID},function(html) {$('#overview_tab').html(html);}); 
}

function GetResources() // Get Resources content
{
    var url='/cwis438/websites/LEAF/Webservices/GetResources.php';
    
	$.post(url,{'OrganismInfoID':OrganismInfoID},function(html) {$('#resources_tab').html(html);}); 
}

function GetGallery() // Get Resources content
{
    var url='/cwis438/websites/LEAF/Webservices/GetGallery.php';
    
	$.post(url,{'OrganismInfoID':OrganismInfoID},function(html) {$('#gallery_tab').html(html);}); 
}

// ---------------------------------------------------------------------------------------------------
// Google Maps Code
//---------------------------------------------------------------------------------------------------

function initialize()
{
    //alert("Initializing...");

	infowindow = new google.maps.InfoWindow({content: "loading..."});
    
    var MapCenter = new google.maps.LatLng(<?php echo $InitialLatitude; ?>, <?php echo $InitialLongitude; ?>);

    var myOptions =
    {
        zoom: <?php echo $InitialZoom;?>,
        center: MapCenter,
        mapTypeId: google.maps.MapTypeId.TERRAIN,
        streetViewControl: false,
        zoomControlOptions:
		{
			style: google.maps.ZoomControlStyle.SMALL
		}
    }

    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions); // map

    var bounds = new google.maps.LatLngBounds();
    
    var InitialBounds="((-90.0, -180.0), (90.0, 180.0))"; // Initial Bounds for entire world; get all project data for initial load
    
    var url='/cwis438/webservices/GetOrganismData_Scalable.php?OrganismInfoID='+OrganismInfoID+'&ZoomLevel=8&Bounds='+InitialBounds;
    
    var Project_ImageURL='/cwis438/includes/GoogleMapsJS_API_V3/images/YellowDot75Percent.png';
    
    if (NumAreas>5000)
    {
        url='/cwis438/webservices/GetOrganismData_Scalable.php?OrganismInfoID='+OrganismInfoID+'&ZoomLevel=8&Bounds='+InitialBounds;
        Project_ImageURL='/cwis438/includes/GoogleMapsJS_API_V3/images/YellowDot75Percent.png';
    }
    else
    {
    	url='/cwis438/webservices/GetOrganismData.php?OrganismInfoID='+OrganismInfoID+'&WebSiteID='+WebSiteID;
    	Project_ImageURL='/cwis438/websites/CitSci/Map/images/OrangeBlackOutlineDot.png';        
    }
    
    var data = jQuery.parseJSON(
        jQuery.ajax({
            url: url, 
            async: false, // false
            dataType: 'json'
        }).responseText
    );
    
    var DataPoints = data['DataPoints']; 

    var NumDataPoints = data['NumDataPoints'];
    
    for (var i=0; i<NumDataPoints.length; i++)
    { 
        var object=NumDataPoints[i];
        RealData=object['RealData']; // RealData variable initialized globally; var NumRecordsWithinBounds=object['NumRecordsWithinBounds'];
    }

    if (RealData>0) // we have real data coming back from our json request
    {
    	Project_ImageURL='/cwis438/websites/CitSci/Map/images/OrangeBlackOutlineDot.png';    
    }

    var ProjectMarkerImage=new google.maps.MarkerImage(Project_ImageURL,new google.maps.Size(12,12));

    var Title="Click to zoom in...";
    
    for (var i=0; i<DataPoints.length; i++)
    { 
        var object = DataPoints[i]; 
        
        var X=object['X'];
    
        var Y=object['Y'];

        var AreaName=object['AreaName'];

        if (RealData>0) // we have real data coming back from our json request
        {
            Title=AreaName;
        }
        
        var HTMLString=object['BalloonContent'];
        
        var latLng = new google.maps.LatLng(Y,X);

    	var ProjectMarker = new google.maps.Marker({
          position: latLng,
          icon: ProjectMarkerImage,
          map: map,
          title: Title,
          zIndex: 999,
          html: HTMLString
          });

    	google.maps.event.addListener(ProjectMarker,"click",ShowInfoWindow);
    	
    	markers.push(ProjectMarker);
        
        bounds.extend(ProjectMarker.position);
    }
    
    UserZoom=false;
    
    map.fitBounds(bounds);

    if (NumAreas>5000)
    {
        // ---------------------------------------------------------------------
        // Event Listener to detect change in drag event when ending
        // ---------------------------------------------------------------------
    	
        google.maps.event.addListener(map,'dragend',UpdateMap);
    
        // ---------------------------------------------------------------------
        // Event Listener to detect change in zoom level
        // ---------------------------------------------------------------------
    
        google.maps.event.addListener(map,'zoom_changed',UpdateMap);
    }
    
    // show loading icon...?

    //console.log($("#Wikipedia_Description").height());

    setTimeout(function()
    {
        console.log($("#Wikipedia_Description").height());
        console.log($("#EOL_Description").height());
        console.log($("#LEAF_Description").height());

        //$("#overview_tab").animate({height:$("#Wikipedia_Description").height()+580},600); // 330; was 242; 220 (ht of map) + 12 buffer for bottom of div

        $("#overview_tab").animate({height:$("#Wikipedia_Description").height()+$("#Wikipedia_Description").height()+$("#EOL_Description").height()+$("#LEAF_Description").height()+410},600); // 330; was 242; 220 (ht of map) + 12 buffer for bottom of div
        
    },2000); // 3000
}

function ShowInfoWindow() // RealData,map
{
	if (RealData>0)
    {
        infowindow.setContent(this.html);
        infowindow.open(map, this);
    }
    else
    {   
        map.panTo(this.getPosition());

    	var ZoomLevel=map.getZoom();
    	
    	var NewZoomLevel=ZoomLevel+3;
    	
        map.setZoom(NewZoomLevel);

        // clear overlays, delete markers, and re-query for new markers based on new map properties such as new zoom and new bounds...

        UpdateMap();
	}
}

function UpdateMap()
{
	// zoom changed... or bounds changed or drag ended... etc...
	
    if (UserZoom)
    {
    	$("#preloader").show();

        var NewZoomLevel=map.getZoom();
        
        //alert("zoon changed to..."+ZoomLevel);

        var NewBounds=map.getBounds();

        // delete all existing markers and clear overlays... (may be able to just call clearOverlays() function here...)

        //alert("zoom changed... num markers="+markers.length);
        
        for (var i=0; i<markers.length; i++)
        {
            markers[i].setMap(null);
        }

        markers = [];

        // go get new markers based on new zoom and bounds...
        
        var newurl='/cwis438/webservices/GetOrganismData_Scalable.php?OrganismInfoID='+OrganismInfoID+'&ZoomLevel='+NewZoomLevel+'&Bounds='+NewBounds; //&ZoomLevel='+ZoomLevel+'&Bounds='+Bounds

        if (NumAreas>5000) // was NumAreas>5000 but that was remembering total project num areas always...
        {
            newurl='/cwis438/webservices/GetOrganismData_Scalable.php?OrganismInfoID='+OrganismInfoID+'&ZoomLevel='+NewZoomLevel+'&Bounds='+NewBounds; //&ZoomLevel='+ZoomLevel+'&Bounds='+Bounds
            NewProject_ImageURL='/cwis438/includes/GoogleMapsJS_API_V3/images/YellowDot75Percent.png';
        }
        else
        {
        	newurl='/cwis438/webservices/GetOrganismData.php?OrganismInfoID='+OrganismInfoID+'&WebSiteID='+WebSiteID;
        	NewProject_ImageURL='/cwis438/websites/CitSci/Map/images/OrangeBlackOutlineDot.png';        
        }
        
        var NewData=jQuery.parseJSON(
            jQuery.ajax({
                url: newurl, 
                async: false,
                dataType: 'json'
            }).responseText
        );
        
        var NewDataPoints=NewData['DataPoints'];

        var NewNumDataPoints=NewData['NumDataPoints'];

        //var NewRealData=0;
        
        for (var i=0; i<NewNumDataPoints.length; i++)
        { 
            var Object2=NewNumDataPoints[i]; 
            //var NumRecordsWithinBounds=object['NumRecordsWithinBounds'];
            RealData=Object2['RealData'];
        }
        
        if (RealData>0) // we have real data coming back from our json request
        {
        	//alert("NewRealData="+NewRealData);
        	NewProject_ImageURL='/cwis438/websites/CitSci/Map/images/OrangeBlackOutlineDot.png';    
        }

        var NewProjectMarkerImage=new google.maps.MarkerImage(NewProject_ImageURL,new google.maps.Size(12,12));

        var NewTitle="Click to zoom in...";
        
        for (var i=0; i<NewDataPoints.length; i++)
        { 
            var NewObject = NewDataPoints[i]; 
            
            var NewX=NewObject['X'];
        
            var NewY=NewObject['Y'];

            var NewAreaName=NewObject['AreaName'];
            
            if (RealData>0)
            {
                NewTitle=NewAreaName;
            }
            
            var NewHTMLString=NewObject['BalloonContent'];
            
            var NewLatLng = new google.maps.LatLng(NewY,NewX);

        	var NewProjectMarker = new google.maps.Marker({
                position: NewLatLng,
                icon: NewProjectMarkerImage,
                map: map,
                title: NewTitle,
                zIndex: 999,
                html: NewHTMLString // change this eventually
                });
            
        	google.maps.event.addListener(NewProjectMarker,"click",ShowInfoWindow);
            
        	markers.push(NewProjectMarker);
            
        	NewBounds.extend(NewProjectMarker.position);
        }

        $("#preloader").hide();
    }
    else
    {
        // do nothing...
        //alert("not user zoom...");
    }
    
    UserZoom=true;
}

//$(window).load(function()
//{ 
	/*code here*/
	//console.log($("#Wikipedia_Description").height());
	//alert("done loading page");
//});

</script>

<style>

.sci_name
{
    color: #8C8F3D;
    font-family: Arial,Times,serif;
    font-size: 30px;
    font-weight: bold;
    font-style:italic; 
    width:50%;
}

#titlebar
{
    height:70px;
}

.common_name
{
    font-family: Arial,Times,serif;
    font-size: 20px;
    font-weight: bold;
    color:black;
    width:50%;
}

#tabs
{
    /*display:inline-block;*/
    width:756px;/* 752 */
    float:left;
	margin-right:10px;
}

#content
{
    padding:10px;
}

#controls
{
    background-color:#515E66; /* #E8E8C6 #E8E8C6 #FFFFCA #E8E8C6  #384046 #384046      #898C38 #ECEA9D #939648 #939648 #F5F5E7 #ECEA9D #E6E6E6 */
    min-height:150px; /* 283 */
    width:168px;
    
    display:inline-block;
	float:right;
	margin-top:38px;
    padding:5px;
	
	border:1px solid #B1BE5A;
	
	-moz-border-radius: 4px;
    -webkit-border-radius: 4px;
    -khtml-border-radius: 4px;
	border-radius: 4px 4px 4px 4px;
	behavior: url(/cwis438/websites/LEAF/stylesheets/PIE.htc); /*behavior: url(/CCEP/stylesheets/PIE.htc);*/
}

#controls ul
{
    margin-left:auto;
    margin-right:auto;
    list-style-type: none;
    padding: 0;
}

#controls li
{
    margin-top:5px;
    text-align:center;
}

ul .registerbutton
{
    height:20px;
    line-height:20px;
    width:150px;
}

#overview_tab
{
	position: relative; overflow: hidden;
}

#photo
{
	overflow: hidden;
	
	-moz-border-radius: 4px;
    -webkit-border-radius: 4px;
    -khtml-border-radius: 4px;
	border-radius: 4px 4px 4px 4px;
	behavior: url(/cwis438/websites/LEAF/stylesheets/PIE.htc);
}

#map_container
{
	display:inline-block;
	
	float:right;
	width:497px;
	height:320px;
	background-color:#E8E8C6;
	margin-bottom:6px;
	
	-moz-border-radius: 4px;
    -webkit-border-radius: 4px;
    -khtml-border-radius: 4px;
	border-radius: 4px 4px 4px 4px;
	behavior: url(/cwis438/websites/LEAF/stylesheets/PIE.htc); /*behavior: url(/CCEP/stylesheets/PIE.htc);*/
}

#map_canvas
{
	position:relative;
	display:block;
	float:left;
	width:473px;
	height:301px;
	
	margin-left:12px;
	margin-right:12px;
	margin-top:9px;
	
	border:solid 1px #8C8F3D;
	
	-moz-border-radius: 4px;
    -webkit-border-radius: 4px;
    -khtml-border-radius: 4px;
	border-radius: 4px 4px 4px 4px;
	behavior: url(/cwis438/websites/LEAF/stylesheets/PIE.htc); /*behavior: url(/CCEP/stylesheets/PIE.htc);*/
}

#content
{
    min-height:800px !important;
}

</style>

<?php

$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart();

//---------------------------- TITLE BAR  ----------------------------------//

echo("<div id='titlebar'>");

    echo("<div class='sci_name'>");
        echo("<span>$SciName</span>");
    echo("</div>");  // End organism_name
    
    $CommonName=ucfirst($CommonName);
    
    echo("<div class='common_name'>");
        echo("<span>$CommonName</span>");
    echo("</div>");  // End common_name
    
echo("</div>");  // End titlebar

//----------------------------  Controls  ----------------------------------//

echo("<div id='controls'>");

echo("<ul>");
    echo("<li><a href='/cwis438/Websites/LEAF/Submit_Observation.php?OrganismInfoID=$OrganismInfoID&WebSiteID=20' class='registerbutton'>Add Observations</a></li>"); ///cwis438/websites/LEAF/Submit_Observation.php?WebSiteID=20
    echo("<li><a href='' style='pointer-events:none; cursor:default;' title='Coming Soon' class='registerbutton'>Add Stories</a></li>");
    
    $CallingPage=urlencode("/cwis438/Websites/LEAF/Species_Profile.php?OrganismInfoID=$OrganismInfoID");
    $CallingLabel=urlencode("To Species Profile<br/><br/>");
    
    $URL="/cwis438/Analysis/DataTable/DataTable_Download_FileOptions.php?ProjectID=237&WebSiteID=20&CallingPage=$CallingPage&CallingLabel=$CallingLabel&DataTableRowTypeID=7";
    
    echo("<li><a href='$URL' title='Doanload Data' class='registerbutton'>Download Data</a></li>"); //style='pointer-events:none; cursor:default;' 
    echo("<li><a href='' style='pointer-events:none; cursor:default;' title='Coming Soon' class='registerbutton'>Print Maps</a></li>");
echo("</ul>");

echo("</div>"); // End controls


//-------------------------------- TABS  -----------------------------------//

echo("<div id='tabs'>");

echo("<ul>");
    echo("<li><a href='#overview_tab' alt='Overview' title='Overview'>Overview</a></li>");
    echo("<li><a href='#gallery_tab' alt='Gallery' title='Gallery'>Gallery</a></li>");
    echo("<li><a href='#names_tab' alt='Names' title='Names'>Common Names</a></li>");
    echo("<li><a href='#classification_tab' alt='Classification' title='Classification'>Classification</a></li>");
    echo("<li><a href='#resources_tab' alt='Resources' title='Resources'>Resources</a></li>");
echo("</ul>");
    
//---------------------------- TAB 1 Overview  ----------------------------//
    
echo("<div id='overview_tab'></div>");

//---------------------------   TAB 2 Gallery   ---------------------------//

echo("<div id='gallery_tab'>");

        echo("<h2>Gallery</h2>");
        
        //echo("<iframe src='http://www.citsci.org' width='700' height='450'></iframe>");
        
        //echo("<iframe src='http://www.flickr.com/search/?q=Wollemia%20nobilis' width='700' height='450'></iframe>");

echo("</div>");  // End gallery_tab
    
//--------------------------   TAB 3 Names --------------------------------//

echo("<div id='names_tab'>");

        echo("<h2>Popular name</h2>");
        
        if($SciName==$CommonName)
        {
            echo("Popular name not yet recommended.<br/>");
        }    
        else
        {
            echo("$CommonName<br/>");
        }    
        
        echo("<br/>");
        
        echo("<h2>Common names</h2>");
        
        if(!$NameSet->FetchRow())
        {
            echo("Common names not found.");
        }
        else
        {
            $NameSet=$Database->Execute($SelectString);
            while ($NameSet->FetchRow())
            {
                $Name=$NameSet->Field("Name");
                $Name=ucfirst($Name);
                echo("$Name<br/>");
            } 
        }
           
echo("</div>");  // End names_tab  

//--------------------   TAB 4 Classification   ---------------------------//

echo("<div id='classification_tab'></div>");

//------------------------   TAB 5 Resources  -----------------------------//

echo("<div id='resources_tab'>");

    //echo("<div id='resources'>");

        //echo("Resources<br>");
        //$eol_id="2";
        //echo("EOL ID: <a href='http://www.eol.org/pages/$eol_id/overview' target='NewWindow'>EOL Page</a>");
        //echo("<br/>");
        //echo("<a href='http://eol.org/api/pages/1.0/$eol_id.json?images=2&videos=0&sounds=0&maps=0&text=2&iucn=true&subjects=overview&licenses=all&details=true&common_names=true'>eol json feed</a>");

    //echo("</div>");  // End resources

echo("</div>");  // End resources_tab 
    
//------------------------------------------------------------------------//    
    
echo("</div>");  // End Tabs div

echo("&nbsp;");

$ThePage->LineBreak();

$ThePage->BodyEnd();

?>
