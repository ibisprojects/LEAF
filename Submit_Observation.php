<?php
//**************************************************************************************
// FileName: Submit_Observation.php
// Author: gjn
// Owner: gjn
// Purpose: Allows users to enter x,y coordinates for a single point to add data to GODM
// 	database. The page now may be entered via new Sightings link on NIISS or via single
//	click on our map application (in which case a CallingPage should be passed in). The
//	page rememvbers user input a few different ways: (1) by updating prior to looping
// 	through loop pages to go search for a species for the sighting or search for a
//	project for the sighting, and (2) by using UserSettings() so that upon return to this
//	page, it makes it easier for the user to add the new sighting b/c it remembers certain
// 	information from the last time they used the page.  
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/Classes/Date.php");
require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");

require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/SecurityUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/GodmUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/StringUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/ServerUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/ProjectionUtil.php");

require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/LKU_CoordinateSystems.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/LKU_AreaSubtypes.php");

require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");
require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_UserSessions.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_SpatialLayerData.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_UserSettings.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_InsertLogs.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Projects.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_People.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_TaxonUnits.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_SceneLayers.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/REL_MediaToVisit.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/REL_MediaToOrganismData.php");

//require_once("C:\inetpub\wwwroot\cwis438\Classes\BlueSpray\STVectors.php");

//**************************************************************************************
// Database connection
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Security
//**************************************************************************************

//CheckCurrentProjectRole("/cwis438/websites/LEAF/Submit_Observation.php",PROJECT_CONTRIBUTOR);

// Make the user log in if they are not currently
CheckLogin2($Database,PERMISSION_USER);

$UserID=GetUserID();
$UserSessionID=TBL_UserSessions::GetID($Database,GetUserID());

//**************************************************************************************
// Server-side functions
//**************************************************************************************

$ComputerName=GetComputerName();

//**************************************************************************************
// Parameters
//**************************************************************************************

$TakeAction=GetStringParameter("TakeAction","New"); // default to New

$AreaName=GetStringParameter("AreaName",null); // default to null
$AreaComments=GetStringParameter("AreaComments",null); // default to null
$East=GetIntParameter("East",1); // default western (0)

$South=GetIntParameter("South",0); // default northern (1)
$Datum=GetIntParameter("Datum",STDATUM_WGS_84); // was default set to 1; use STDATUM_WGS_84 which is a 2.
$ZoneIndex=GetIntParameter("ZoneIndex",-1);
$X=GetFloatParameter("lon"); // X
$Y=GetFloatParameter("lat"); // Y
$Accuracy=GetFloatParameter("Accuracy",null);
$Projection=GetIntParameter("Projection",STPROJECTION_GEOGRAPHIC); // jjg - converted all code as an STPROJECTION definition
$OrganismInfoID=GetIntParameter("OrganismInfoID",-1);

$ProjectCode="LEAF";
$ProjectSet=TBL_Projects::GetSet($Database,$ProjectCode); 
$DefaultProjectID=$ProjectSet->Field("ID");

$SelectedProject=GetIntParameter("SelectedProject",-1);

if ($SelectedProject>-1)
{
    $ProjectID=$SelectedProject;
}

$Present=GetIntParameter("Present",1); // default to present

$SceneLayerID=GetIntParameter("SceneLayerID");
$OrganismDataID=GetIntParameter("OrganismDataID",0);

$ReporterName=GetStringParameter("ReporterName",null);
$Email=GetStringParameter("Email",null);
$Phone=GetStringParameter("Phone",null);

$MediaID=GetIntParameter("MediaID");
$Media2ID=GetIntParameter("Media2ID",0);
$Media3ID=GetIntParameter("Media3ID",0);
$Media4ID=GetIntParameter("Media4ID",0);

$CallingPage=GetStringParameter("CallingPage",null);

// keep user defined date in form

$VisitDateObject=GetDateParameter("Date");
$Month=GetIntParameter("Month",$VisitDateObject->Month);  // get the user selected date value if they selected one, otherwise use today's date
$Day=GetIntParameter("Day",$VisitDateObject->Day);
$Year=GetIntParameter("Year",$VisitDateObject->Year);

//**************************************************************************************
// Server-side code
//**************************************************************************************

if ($SceneLayerID>0)
{
	$Set=TBL_SceneLayers::GetSetFromID($Database,$SceneLayerID);
	
	$OrganismInfoID=$Set->Field("OrganismInfoID");
}

$UserSessionID=TBL_UserSessions::GetID($Database,$UserID);

// assume default west longitudes always negative (if not; code fixes to be so)

if ($Projection==STPROJECTION_GEOGRAPHIC)
{
	$X_Label="Latitude";
	$Y_Label="Longitude";
}
else if ($Projection==STPROJECTION_UTM)
{
	$X_Label="Easting";
	$Y_Label="Northing";
}


$MyDefaultCallingPage="/cwis438/websites/LEAF/Submit_Observation.php".
	"?OrganismDataID=$OrganismDataID".
	"&AreaName=$AreaName".
	"&AreaComments=$AreaComments".
	"&Datum=$Datum".
	"&ZoneIndex=$ZoneIndex".
	"&Projection=$Projection".
	"&X=$X".
	"&Y=$Y".
	"&Accuracy=$Accuracy".
    "&SelectedProject=$SelectedProject".
	"&Present=$Present".
	"&ReporterName=$ReporterName".
	"&Email=$Email".
	"&Phone=$Phone".
	"&SceneLayerID=$SceneLayerID".
	"&OrganismInfoID=$OrganismInfoID".
	"&MediaID=$MediaID".
	"&Media2ID=$Media2ID".
	"&Media3ID=$Media3ID".
	"&Media4ID=$Media4ID".
	//"&RunAsGuest=17".
    "&Month=$Month".
    "&Day=$Day". 
    "&Year=$Year".
	"&TakeAction=Returned";

// Take Actions

if (($TakeAction=="New")||($TakeAction=="NewSpecified")) // called from the AddPoint on map (or someone else who wants to pass in data) with TakeAction=NewSpecified
{
	if ($TakeAction!="NewSpecified")
	{
		$Datum=TBL_UserSettings::Get($Database,"Datum",STDATUM_WGS_84);
		$ZoneIndex=TBL_UserSettings::Get($Database,"ZoneIndex",-1);
		$South=TBL_UserSettings::Get($Database,"South",0);
	}
	
	$TakeAction="Create";
}
else if (($TakeAction=="Update")||($TakeAction=="Create"))
{
	// update data
	
	$Zone=$ZoneIndex+1;
	
	$CoordinateSystemID=LKU_CoordinateSystems::GetIDFromProjection($Database,$Projection,$Zone,$South,$Datum);
	
	$VisitComments="";
	if ($ReporterName!==null) $VisitComments.=$ReporterName."; ";
	if ($Email!==null) $VisitComments.=$Email."; ";
	if ($Phone!==null) $VisitComments.=$Phone."; ";
	
	if ($OrganismDataID<=0)
	{
		
		$OrganismDataID=TBL_OrganismData::AddPoint($Database,$UserID,$ProjectID,$X,$Y,
			$CoordinateSystemID,$AreaName,$VisitDateObject,$Present,null,$OrganismInfoID,$Accuracy,true,AREA_SUBTYPE_POINT,null,$AreaComments,$VisitComments);
	}
	else 
	{
		// we may need to eventually add $AreaComments and $VisitComments to the update function as well; gjn
		
		TBL_OrganismData::UpdatePoint($Database,$OrganismDataID,$ProjectID,$X,$Y,
			$CoordinateSystemID,$AreaName,$VisitDateObject,$Present,null,$OrganismInfoID,$Accuracy);	
	}

	if ($CallingPage!=null) // have a calling page (this is not currently used)
	{
		//RedirectFromRoot($CallingPage);
	}
	else // no calling page specified
	{
		//DebugWriteln("OrganismDataID=$OrganismDataID");
		
		if ($OrganismDataID!=0) // have a survey, go to the visit info page
		{ 
			$VisitID=TBL_OrganismData::GetFieldValue($Database,"VisitID",$OrganismDataID,0);
			
			// if we have optional MediaID insert a REL between the MediaID and the VisitID
			
			if ($MediaID>0)
			{
				REL_MediaToVisit::Insert($Database,$MediaID,$VisitID,$UserID);
				REL_MediaToOrganismData::Insert($Database,$MediaID,$OrganismDataID);
			}
			
			// if we have optional Media2ID insert a REL between the Media2ID and the VisitID
			
			if ($Media2ID>0)
			{
			    REL_MediaToVisit::Insert($Database,$Media2ID,$VisitID,$UserID);
			    REL_MediaToOrganismData::Insert($Database,$Media2ID,$OrganismDataID);
			}
			
			// if we have optional Media3ID insert a REL between the Media3ID and the VisitID
				
			if ($Media3ID>0)
			{
			    REL_MediaToVisit::Insert($Database,$Media3ID,$VisitID,$UserID);
			    REL_MediaToOrganismData::Insert($Database,$Media3ID,$OrganismDataID);
			}
			
			// if we have optional Media4ID insert a REL between the Media4ID and the VisitID
			
			if ($Media4ID>0)
			{
			    REL_MediaToVisit::Insert($Database,$Media4ID,$VisitID,$UserID);
			    REL_MediaToOrganismData::Insert($Database,$Media4ID,$OrganismDataID);
			}
			
			RedirectFromRoot("/cwis438/Websites/LEAF/Submit_Confirmation.php?VisitID=$VisitID");
		}
		else // show the area on the map?
		{
			RedirectFromRoot("/cwis438/Browse/TiledMap/Scene_Basic.php?AreaID=$AreaID");
		}
	}
}
else if (($TakeAction=="")||($TakeAction=="Edit"))
{	
	TBL_OrganismData::GetPoint($Database,$OrganismDataID,$ProjectID,$X,$Y,
		$CoordinateSystemID,$AreaName,$VisitDateObject,$Present,$SubplotID,$OrganismInfoID,$Accuracy);
}

else if ($TakeAction=="SelectPhoto")
{	
	// Redirect to go find a Photo
	
	$PhotoCallingPage=$MyDefaultCallingPage."&MediaID=";
	
	$PhotoCallingPage=urlencode($PhotoCallingPage);	
	
	$MyCallingLabel="To Data Entry Form";
	
	RedirectFromRoot("/cwis438/contribute/Upload/upload_media.php?TakeAction=NewMedia&CallingPage=$PhotoCallingPage&CallingLabel=$MyCallingLabel");
}
else if ($TakeAction=="SelectPhoto2")
{
    // Redirect to go find a Photo2

    $PhotoCallingPage=$MyDefaultCallingPage."Media2ID=";

    //DebugWriteln("PhotoCallingPage=$PhotoCallingPage");

    $PhotoCallingPage=urlencode($PhotoCallingPage);

    $MyCallingLabel="To Data Entry Form";

    RedirectFromRoot("/cwis438/contribute/Upload/upload_media.php?TakeAction=NewMedia&CallingPage=$PhotoCallingPage&CallingLabel=$MyCallingLabel");
}
else if ($TakeAction=="SelectPhoto3")
{
    // Redirect to go find a Photo3

    $PhotoCallingPage=$MyDefaultCallingPage."&Media3ID=";

    //DebugWriteln("PhotoCallingPage=$PhotoCallingPage");

    $PhotoCallingPage=urlencode($PhotoCallingPage);

    $MyCallingLabel="To Data Entry Form";

    RedirectFromRoot("/cwis438/contribute/Upload/upload_media.php?TakeAction=NewMedia&CallingPage=$PhotoCallingPage&CallingLabel=$MyCallingLabel");
}
else if ($TakeAction=="SelectPhoto4")
{
    // Redirect to go find a Photo4

    $PhotoCallingPage=$MyDefaultCallingPage."&Media4ID=";

    //DebugWriteln("PhotoCallingPage=$PhotoCallingPage");

    $PhotoCallingPage=urlencode($PhotoCallingPage);

    $MyCallingLabel="To Data Entry Form";

    RedirectFromRoot("/cwis438/contribute/Upload/upload_media.php?TakeAction=NewMedia&CallingPage=$PhotoCallingPage&CallingLabel=$MyCallingLabel");
}
else if ($TakeAction=="Returned") // we are coming back from a species search loop
{
	// fetch existing information from URL (not from db, maybe from UserSettings I suppose?)
	
	$OrganismDataID=GetIntParameter("OrganismDataID");
	$AreaName=GetStringParameter("AreaName",null); // default to null
	//$East=GetIntParameter("East",1); // default western (0)
	$South=GetIntParameter("South",0); // default northern (1)
	$Datum=GetIntParameter("Datum",STDATUM_WGS_84); // was default to 1
	$ZoneIndex=GetIntParameter("ZoneIndex",-1);
	$Projection=GetIntParameter("Projection",STPROJECTION_GEOGRAPHIC); // jjg - converted all code as an STPROJECTION definition
	$X=GetFloatParameter("X");
	$Y=GetFloatParameter("Y");
	$Accuracy=GetFloatParameter("Accuracy",null);
    $SelectedProject=GetIntParameter("SelectedProject",-1);
	$Present=GetIntParameter("Present",1);
	$OrganismInfoID=GetIntParameter("OrganismInfoID",-1);
      $VisitDateObject=GetDateParameter("Date");
      $Month=GetIntParameter("Month");
      $Day=GetIntParameter("Day");
      $Year=GetIntParameter("Year");
	
	if (stripos($CallingPage,"?OrganismInfoID=")!==false)
	{
		$CallingPage.=$OrganismInfoID;
		//DebugWriteln("CallingPageRevised=$CallingPage");
	}
	
	$TakeAction="Update";
}

$Zones=GetUTMZones();

$Code=GetGoogleMapsAuthorizationCode();

//**************************************************************************************
// HTML Header block and client-side includes
//**************************************************************************************

$ThePage=new PageSettings;

$TheTable=new TableSettings(TABLE_FORM);

$Title="Add a Single Plant";

$ThePage->HeaderStart($Title);

?>

<!-- <script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?//echo($Code)?>"></script> -->

<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyD9RHlLKYGsbelja9DzYOHq1YpNMFFbMDE&sensor=false"></script>

<SCRIPT TYPE="text/javascript" SRC="/cwis438/includes/FormUtil.js"></SCRIPT>
<SCRIPT TYPE="text/javascript" SRC="/cwis438/includes/ProjectionUtil.js"></SCRIPT>
<SCRIPT TYPE="text/javascript" SRC="/cwis438/includes/DOMElement.js"></SCRIPT>
<SCRIPT TYPE="text/javascript" SRC="/cwis438/includes/DOMStyle.js"></SCRIPT>

<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js'></script>
<!-- <link href='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' rel='stylesheet' type='text/css' />-->
<script src='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js'></script>

<SCRIPT>

function DoSubmit() // TakeAction
{	
	// ************************************************************************************
	
	var ErrorString=null;
	
	// first ensure they have selected a species

	var OrganismInfoID=document.forms.geoForm.OrganismInfoID.value; // OrganismInfoID
    
	if (OrganismInfoID<=0)
	{	
        alert("Sorry, you must select a species");
        ErrorString="Sorry, you must select a species";
	}

    // next check for location name

    if (ErrorString==null)
    {
    	var AreaName=document.forms.geoForm.AreaName.value;
    
    	if (AreaName=="")
        {
    		alert("Sorry, you must enter a location name");
        	ErrorString="Sorry, you must enter a location name";
        }
    }
    
	// next check coordinates for errors
	
	if (ErrorString==null) // check coordinate values
	{
		var X=parseFloat(document.forms.geoForm.lon.value); // X
		var Y=parseFloat(document.forms.geoForm.lat.value); // Y
		
		var Projection=STPROJECTION_GEOGRAPHIC;
		//if (document.geoForm.Projection[1].checked)
		//{
			//Projection=STPROJECTION_UTM;
		//}
		//alert("Projection="+Projection);
		
		if (Projection==STPROJECTION_GEOGRAPHIC) // geographic
		{
			if ((isNaN(X))||(X<-180)||(X>180))
			{
			    alert("Your longitude coordinate must be between -180 and +180");
				ErrorString="Your longitude coordinate must be between -180 and +180";
			}
			else if ((isNaN(Y))||(Y<-90)||(Y>90)) // geographic
			{
				alert("Your latitude coordinate must be between -90 and +90");
				ErrorString="Your latitude coordinate must be between -90 and +90";
			}
		}
		else // UTM
		{
			// was old utm code - see file history in soyrce safe to get it back; GJN
		}
	}
	
	// either report an error or submit the form
	
	if (ErrorString!=null)
	{
		//window.alert(ErrorString);
	}
	else
	{	
		document.forms.geoForm.submit();
	}
}



function DoSearchOrganismInfo()
{
	document.geoForm.TakeAction.value="SearchOrganismInfo";
	
	document.geoForm.submit();
}

function DoSelectPhoto()
{
	document.geoForm.TakeAction.value="SelectPhoto";
	
	document.geoForm.submit();
}

function DoSelectPhoto2()
{
	document.geoForm.TakeAction.value="SelectPhoto2";
	
	document.geoForm.submit();
}

function DoSelectPhoto3()
{
	document.geoForm.TakeAction.value="SelectPhoto3";
	
	document.geoForm.submit();
}

function DoSelectPhoto4()
{
	document.geoForm.TakeAction.value="SelectPhoto4";
	
	document.geoForm.submit();
}

//************************************************************************************************************
// Google Maps Code
//************************************************************************************************************

$(function()
{
    //call function to load Google Map
    
    loadMap();

    //function to display help text pop up window
    $("#btnhelp").click(function()
    {
        $("#helptext").css('display', 'block');
    });//end btnhelp click function

});

//close help div
			
function closeWindow()
{
    $(".popupmaphelp").css('display', 'none');

}

// -------------------------------------------------------------------------------------------------------------
// Google Maps
// -------------------------------------------------------------------------------------------------------------

//initialize globalvariables

var map;
var geocoder;
var marker = null;
var markersArray = [];
var myLatLng = null;
var myLat;
var myLng;
var usCenter = new google.maps.LatLng(9.03,38.74); // Addis Ababa Ethiopia as center for now...
var MetersArray=[20088000,10044000,5022000,2511000,1255500,627750,313875,156937,156937,78468,39234,19617,9808,4909,2452,1226,613,306,153,76,38];

function loadMap()
{
	//if (GBrowserIsCompatible()) { -- commented out - no GBrowserIsCompatible function in Google Maps v3

	//var usCenter = new google.maps.LatLng(+40, -100);
	geocoder = new google.maps.Geocoder();
	
	//set map options - zoom level, center, scale control, large zoom control, horizontal type control, terrain style, 
	var mapOptions =
	{
		zoom: 4, // 5
		center: usCenter,
		streetViewControl: false,
		scaleControl: true,
		zoomControl: true,
		zoomControlOptions:
		{
			style: google.maps.ZoomControlStyle.LARGE
		},
		mapTypeControl: true,
		mapTypeControlOptions:
		{
			style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR
		},
		mapTypeId: google.maps.MapTypeId.TERRAIN // ROADMAP
	}; // end mapOptions

	//create new map object
	map = new google.maps.Map(document.getElementById('map'), mapOptions);
	
	var zoomMark = 5

	// ====== Create new EventListener to add marker on click ========
	google.maps.event.addListener(map, "click", function(event)
	{
		//capture lat/lng of click location
		myLatLng = event.latLng;
		
		//check to see if visitor is zoomed out to a level not conducive to setting an accurate marker
		if (map.getZoom() < zoomMark)
		{
			//alert ('Zoom in before setting your location');
			//center map on click location
			map.setCenter(myLatLng);
			//zoom in
			map.setZoom(zoomMark);
			//bold zoom label
			$("#zoom").css("font-weight","normal");
			//remove bold for mark label
			$("#mark").css("font-weight","bold");
		}
		else
		{
			//call fx to add marker to markersArray
			addMarker(myLatLng);
		}
	});//end click listener
	
	//event listener to check for zoom level change
	google.maps.event.addListener(map, 'zoom_changed', function()
	{
		//fetch current zoom level
		var zoomLevel = map.getZoom();

		//check for zoom level <6 and toggle mouse function text from zoom to mark
		if(zoomLevel<zoomMark)
		{
			//change text to zoom
			$("#zoom").css("font-weight","bold");				
			$("#mark").css("font-weight","normal");
			//clear map marker
			clearOverlays();
		}
		
		if (zoomLevel>=zoomMark)
		{
			//change text to mark
			$("#zoom").css("font-weight","normal");
			$("#mark").css("font-weight","bold");
		}
	});//end zoom level change listener

} // end loadMap()

function zoomMap(lat,lng)
{
	//create map latlng object
	myLatLng = new google.maps.LatLng(lat, lng);
	//center map on latlng object location
	map.setCenter(myLatLng);
	//zoom in
	map.setZoom(5);
	//set marker
	addMarker(myLatLng);
}
	
//add marker to markersArray and set lat/lng vars to marker location

function addMarker(location)
{
    //create new marker object
	marker = new google.maps.Marker(
	{
		position: location,
		map: map
	});
	//clear previous marker
	clearOverlays();
	
	//add new marker object to markers array
	markersArray.push(marker);

	
	
	//capture lat/lng of clicked location
	var myLat = Math.round(location.lat()*1000000)/1000000;
	var myLng = Math.round(location.lng()*1000000)/1000000;

	// capture accuracy of clicked point
	var zoomlevel = map.getZoom();
	var Accuracy=MetersArray[zoomlevel];
    
	//copy lat/lng in html fields for population of displayed lat/lng html fields
	copygeopoint(myLat, myLng, Accuracy);
}	// end addMarker()

//remove map markers from markersArray[] and destroy array

function clearOverlays()
{
	if(markersArray)
	{
		for (i in markersArray)
		{
			markersArray[i].setMap(null);
		}
		markersArray.length = 0;
	}
}
	
// ====== Geocoding ======

function showAddress()
{
    var search = document.getElementById("search").value;
	var searchresult;

    // ====== Perform the Geocoding ======        
    //geo.getLocations(search, function (result)
	geocoder.geocode( {'address': search}, function(results, status)
    { 
        // If geocode was successful - status OK
        if (status == google.maps.GeocoderStatus.OK)
		{				
			//clear previuos Google Maps overlays to remove map marker
			clearOverlays();
			//set map center to geocoded results
			map.setCenter(results[0].geometry.location);
			//zoom in
			map.setZoom(15);
			//display marker at geocoded results
			var marker = new google.maps.Marker(
			{
				map: map,
				position: results[0].geometry.location
			});
			//add new marker object to markers array so marker can be cleared if map is clicked upon
			markersArray.push(marker);

			//set lat/lng from geocode results
			var myLat = Math.round(results[0].geometry.location.lat()*100000)/100000;
			var myLng = Math.round(results[0].geometry.location.lng()*100000)/100000;		

			// capture accuracy of clicked point
			var zoomlevel = map.getZoom();
			var Accuracy=MetersArray[zoomlevel];
			
			//copy geocode results to form input fields
			copygeopoint(myLat, myLng, Accuracy);
        }
        // ====== Decode the error status ======
        else
		{
			//var reason="Code "+status;
			//if (reasons[status])
			//{
			//  reason = reasons[status]
			//} 
			//set var to default so if no location entered, it displays text
			if(!search)
			{
				searchresult="Please enter a location.";
			}
			else
			{
				searchresult = 'We couldn\'t find "' + search + '".  Please give us a little more detail about your location.';
				//reset search field to null
				document.getElementById("search").value = "";
			}
			//display error div
			//display red x button
			$("#searcherror").html('<img src="images/red_close_button.png" class="btnclosewindow" onclick="closeWindow()"/>');
			//append search error text w/o deleting red x img
			$("#searcherror").append(searchresult);
			//display search error div
			$("#searcherror").css('display', 'block');
        }
    });
}

//Copy geopoint to hidden html inputs for future use

function copygeopoint(myLat, myLng, Accuracy)
{
	//check to see if geocoding has produced coordinates
	if(myLat && myLng)
	{
		//assign previous lat/lng values to visible input fields
		document.getElementById("lat").value = myLat; // was Y
		document.getElementById("lon").value = myLng; // was X

		document.getElementById("Accuracy").value = Accuracy; // Accuracy
	}
	else
	{
		//build error message string
		searchresult="We didn't get your coordinates.  Please check the geocoding and try your copy again.";
		//display error div
		//display red x button
		$("#searcherror").html('<img src="images/red_close_button.png" class="btnclosewindow" onclick="closeWindow()"/>');
		//append search error text w/o deleting red x img
		$("#searcherror").append(searchresult);
		//display search error div
		$("#searcherror").css('display', 'block');
	}
}//end copygeopoint()

//Clear map marker, current marker text and hidden inputs holding copied geopoints

function cleargeopoint()
{
	//clear Google Maps overlays to remove map marker
	clearOverlays();
	//clear user entered address from input box
	document.getElementById("search").value = "";
	//clear any previously saved lat/lng in hidden inputs
	document.getElementById("lat").val="";
	document.getElementById("lng").val="";
}

//clear all input form fields

function clearForm()
{
	document.getElementById('sitename').value = "";
	document.getElementById('lat').value = "";
	document.getElementById('lon').value = "";
	document.getElementById('city').value = "";
	document.getElementById('state').value = "";
	document.getElementById('zip').value = "";
	document.getElementById('station_irrigation').value = "";
	document.getElementById('station_shading').value = "";
	document.getElementById('station_concrete').value = "";
	document.getElementById('station_habitat').value = "";
									
	//clear Google Maps overlays to remove map marker
	clearOverlays();
	map.setZoom(3);
	map.setCenter(usCenter);
}

// *********************************************************************************************

function DoSelectSpecies() // Select a species
{
	//open add species dialog...
	
    $( "#dialog_add_species").dialog({ modal: true }); 
    $( "#dialog_add_species").dialog({ minHeight: 600 });
    $( "#dialog_add_species").dialog({ width: 800 });
    $( "#dialog_add_species").dialog({ resizable: true });
    $( "#dialog_add_species").dialog({ stack: true });
    //$( "#dialog_add_species").dialog({ z-index: 1000 !important });
    $( "#dialog_add_species").dialog('open');

    $('#dialog_add_species').css('z-index','1000000');
    
    var overlay = $(".ui-widget-overlay");
    baseBackground = overlay.css("background");
    baseOpacity = overlay.css("opacity");
    overlay.css("background", "black").css("opacity", "1"); // 1
    
    $(".ui-widget-overlay").css("backgroundColor", baseBackground).css("opacity", baseOpacity).css("filter","Alpha(Opacity=80)");

    var dialog = $(".ui-dialog-titlebar"); //ui-dialog, ui-dialog-title, ui-dialog-titlebar, ui-dialog-content, ui-resizable-handle, ui-dialog-buttonpane, ui-dialog-buttonset
    dialog.css("background","#E9E8B8"); //red

        // post a request to get data for the add a species table in the modal dialog...
    
    	$('#example').dataTable( {
    		"bProcessing": true, // true
    		"bServerSide": true,
    		"sAjaxSource": "/cwis438/websites/LEAF/webservices/GetLEAFSpeciesListForModal.php",
            "bDestroy": true,
    		"aoColumns": [ 
    	    /* SciName */       null,
			/* Name */          null,
            /* Vernaculars */   {"bVisible": false},
            /* Family */        null,
			/* OrganismInfo */ {"bVisible": false},
            /* Rank */          null,
            /* Options  */     {"bSortable": false}
    			  		 ]
    	} ); 
}

function DoSelectOrganismInfoID(OrganismInfoID,SciName,Name)
{
      $("#OrganismInfoID").val(OrganismInfoID);  // set the OrgInfoID

      $("#speciesdiv").width(300); 
      
      $("#speciesdiv").html(Name + ' (' + SciName + ')');  // Insert the SciName into the DIV
    
     // close this select a species modal dialog 
    
     $("#dialog_add_species").dialog('close');
}

</SCRIPT>

<?php

$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart(); 

echo("<form action='/cwis438/websites/LEAF/Submit_Observation.php' method='POST' id='geoForm' name='geoForm'>");

require_once("C:/Inetpub/wwwroot/cwis438/websites/LEAF/Species_List_Modal.php");

echo("<c><h1>Add An Observation</h1></c>");  

$ThePage->LineBreak();

// **************************** Species ****************************


$TheTable->TableStart();

$TheTable->Columns[0]->Width="25%";

$TheTable->ColumnHeading->ColumnSpan=2;
$TheTable->TableColumnHeading("Species"); // Enter coordinates for the point location

$TheTable->Columns[1]->FormElementWidth=null;

$TheTable->TableRowStart();

    if ($OrganismInfoID>0)
    {
        $OrganismName=TBL_OrganismInfos::GetName($Database,$OrganismInfoID);

        $TheTable->TableCell(0,"Species: <div id='speciesdiv' style='margin-left:80px; color:#F47C20; font-size:16px; width:300px;'>$OrganismName</div>");      
    }
    else
    {
        $TheTable->TableCell(0,"Species: <div id='speciesdiv' style='margin-left:80px; color:#F47C20; font-size:16px; width:0px;'></div>");
    }    
    
    $TheTable->TableCellStart(1); // 1
        echo("<div style='float:left; height:24px;'>");
            $TheTable->FormButton("Change","Select a Species","class='registerbutton' id='SelectSpecies' alt='Choose a species' title='Choose a species' style='width:125px; text-align:center;' onclick='DoSelectSpecies()'"); 
        echo("</div>");
    $TheTable->TableCellEnd();

$TheTable->TableRowEnd();

//DebugWriteln("OrgID=$OrganismInfoID");

$TheTable->TableEnd();

$ThePage->LineBreak();


// **************************** Date ****************************

$TheTable->TableStart();
$TheTable->Columns[0]->Width="25%";

$TheTable->ColumnHeading->ColumnSpan=2;
$TheTable->TableColumnHeading("Date"); // Enter coordinates for the point location

$TheTable->TableRowStart();
	$TheTable->TableCell(0,"Date of observation:");
	$TheTable->TableCellStart(1);
    $TheTable->FormDate("Date",$Year,$Month,$Day);
	$TheTable->TableCellEnd();
$TheTable->TableRowEnd();

$TheTable->TableEnd();

$ThePage->LineBreak();

// **************************** Project List ****************************

$SelectString="SELECT DISTINCT REL_PersonToProject.ProjectID, TBL_Projects.ProjName
               FROM   REL_PersonToProject INNER JOIN
                      REL_WebsiteToProject ON REL_PersonToProject.ProjectID = REL_WebsiteToProject.ProjectID LEFT OUTER JOIN
                      TBL_Projects ON REL_PersonToProject.ProjectID = TBL_Projects.ID
               WHERE  (REL_PersonToProject.PersonID = $UserID) AND (REL_PersonToProject.Role >= 2) AND (REL_WebsiteToProject.WebsiteID = 20)
               ORDER BY TBL_Projects.ProjName";

$ProjectNameSet=$Database->Execute($SelectString);

$ProjectCode="LEAF";
$ProjectSet=TBL_Projects::GetSet($Database,$ProjectCode); 
$DefaultProjectID=$ProjectSet->Field("ID");

if ($ProjectNameSet->FetchRow())
{
    $ProjectNameSet=$Database->Execute($SelectString);
    
    $TheTable->TableStart();
    
        $TheTable->TableRowStart();

            $TheTable->TableCell(0,"Project:");

            $TheTable->FormCellRecordSet(1,"SelectedProject",$SelectedProject,$ProjectNameSet,
                array("ProjName"),"ProjectID",true,"-- Select a project --",$DefaultProjectID,1,false,null,false,"95%",null);

        $TheTable->TableRowEnd();

    $TheTable->TableEnd();
}
else
{
    $TheTable->FormHidden("SelectedProject",$DefaultProjectID);
}

$ThePage->LineBreak();

// **************************** Location ****************************

if ($X==0) $X="";
if ($Y==0) $Y="";

$TheTable->TableStart();
$TheTable->TableColumnHeading("Location");

$TheTable->TableRowStart();
    $TheTable->TableCellStart();
        $ThePage->DivStart();
            
            echo("<div style='font-size:10pt; color:#919344; margin-top:10px; margin-bottom:12px; width:100%;'>Please either (a) enter latitude / longitude coordinates below from your GPS unit, or (b) 
                        click on the map to the right and/or enter an address in the search box to determine and provide an accurate location.</div>");
            
            $ThePage->DivStart("style='float:left; width:325px; height:255px;'"); // border: solid 1px red;
                
                $ThePage->DivStart("style='width:322px; margin-bottom:12px; margin-top:12px;'");
                    echo("<label style='display:inline-block; width:110px;'>Location Name:</label>");
                    $TheTable->FormTextEntry("AreaName",$AreaName,100); // need to change lat to Y ---- id='lat' type='text' size='20' name='lat' value='' style="background-color: #eee"
                    echo("<span style='color:#F47C20; font-size:10pt;'>required</span>");
                $ThePage->DivEnd();
            
                $ThePage->DivStart("style='width:322px; margin-bottom:12px;'");
                    echo("<label style='display:inline-block; width:110px;'>Latitude:</label>"); 
                    $TheTable->FormTextEntry("lat",$Y,100,"id='lat'"); // need to change lat to Y ---- id='lat' type='text' size='20' name='lat' value='' style="background-color: #eee"
                    echo("<span style='color:#F47C20; font-size:10pt;'>required</span>");
                $ThePage->DivEnd();
                
                $ThePage->DivStart("style='width:322px; margin-bottom:12px;'");
                    echo("<label style='display:inline-block; width:110px;'>Longitude:</label>");
                    $TheTable->FormTextEntry("lon",$X,100,"id='lon'"); // set max length to 100
                    echo("<span style='color:#F47C20; font-size:10pt;'>required</span>");
                $ThePage->DivEnd();
                
                $ThePage->DivStart("style='width:322px; margin-bottom:12px;'");
                    echo("<label style='display:inline-block; width:110px;'>Accuracy:</label>");
                    $TheTable->FormTextEntry("Accuracy",$Accuracy,100,"id='Accuracy'"); // set max length to 100
                    echo("<span style='color:#919344; font-size:10pt;'>meters</span>");
                $ThePage->DivEnd();
                
                $ThePage->DivStart("style='width:322px; margin-bottom:12px;'");
                    echo("<label style='display:inline-block; width:110px;'>Comments:</label>");
                    $TheTable->FormTextEntry("AreaComments",$AreaComments,100); // need to change lat to Y ---- id='lat' type='text' size='20' name='lat' value='' style="background-color: #eee"
                $ThePage->DivEnd();
                
            $ThePage->DivEnd();
            
            $ThePage->DivStart("style='float:right; width:455px; height:310px; margin-right:10px;'"); // 292
            
            echo("<div id='content' style='min-height: 255px; background-color:#F5F5E7;'>"); //style='min-height: 255px;' border: solid 1px red;
            
            echo("<!--display search box-->");
            
            echo("<div id='searchbox'>");
                echo("<label for='search' style='margin-bottom:0px;'>Search:&nbsp;</label>");
                echo("<input type='text' id='search' placeholder='Address, City, Landmark' tabindex ='10' style='margin-bottom:0px; width:225px;'/>");
                echo("<input type='button' class='registerbutton' onclick='showAddress();' value='Locate' tabindex ='11' style='vertical-align:middle; margin-left:6px;'/>"); // margin-bottom:0px; 
                //echo("<!--<img src='images/help.png' style='vertical-align:bottom' id='btnhelp' />-->");
            echo("</div>");
            
            echo("<!--end search box div-->");
            
            echo("<!--display site selection map-->");
                    
            echo("<div id='map' style='height: 255px; width: 432px; margin-top:6px; border: 1px solid #CCCCCC'></div>");
            
            echo("</div>");
            
            $ThePage->DivEnd();
        $ThePage->DivEnd();
    $TheTable->TableCellEnd();
$TheTable->TableRowEnd();

$TheTable->TableEnd();


$ThePage->LineBreak(); // ********************


// ********************** Photos  ***********************
	
$TheTable->TableStart();
$TheTable->ColumnHeading->ColumnSpan=2;
$TheTable->TableColumnHeading("Photo(s)");
$TheTable->Columns[1]->FormElementWidth=null;

$TheTable->TableRowStart();

	$TheTable->TableCell(0,"Photo:");
	$TheTable->TableCellStart(1);
		if ($MediaID>0)
		{
			$TheTable->FormButton("UploadPhoto","Change Photo","class='registerbutton' style='width:100px;' id='UploadPhoto' alt='Upload a different photo' title='Upload a different photo' onclick='DoSelectPhoto()'"); // Select Different Species
			$ThePage->LineBreak();
			$ImgTag=TBL_Media::GetImgTagFromID($Database,$MediaID,"Name",150,150,TBL_MEDIA_VERSION_THUMBNAIL,"CallingPage"); // 300 by 300
			echo("$ImgTag");
		}
		else 
		{
			$TheTable->FormButton("UploadPhoto","Upload Photo","class='registerbutton' style='width:100px;' id='UploadPhoto' alt='Upload a photo' title='Upload a photo' onclick='DoSelectPhoto()'"); // Select Different Species
			echo("<span style='color:#708c94; font-size:10pt; text-align:top; vertical-align:top; margin-top:4px;'>&nbsp;&nbsp;Note: Please select small JPG or PNG files.".
			        "&nbsp;</span>");
		}
	$TheTable->TableCellEnd();
	
$TheTable->TableRowEnd();

// ----- Photo 2 -----

$TheTable->TableRowStart();

    $TheTable->TableCell(0,"Photo:");
    $TheTable->TableCellStart(1);
        if ($Media2ID>0)
        {
            $TheTable->FormButton("UploadPhoto","Change Photo","class='registerbutton' style='width:100px;' id='UploadPhoto' alt='Upload a different photo' title='Upload a different photo' onclick='DoSelectPhoto2()'"); // Select Different Species
            $ThePage->LineBreak();
            $ImgTag=TBL_Media::GetImgTagFromID($Database,$Media2ID,"Name",150,150,TBL_MEDIA_VERSION_THUMBNAIL,"CallingPage"); // 300 by 300
            echo("$ImgTag");
        }
        else
        {
            $TheTable->FormButton("UploadPhoto","Upload Photo","class='registerbutton' style='width:100px;' id='UploadPhoto' alt='Upload a photo' title='Upload a photo' onclick='DoSelectPhoto2()'"); // Select Different Species
        }
    $TheTable->TableCellEnd();

$TheTable->TableRowEnd();

// ----- Photo 3 -----

$TheTable->TableRowStart();

$TheTable->TableCell(0,"Photo:");
$TheTable->TableCellStart(1);
if ($Media3ID>0)
{
    $TheTable->FormButton("UploadPhoto","Change Photo","class='registerbutton' style='width:100px;' id='UploadPhoto' alt='Upload a different photo' title='Upload a different photo' onclick='DoSelectPhoto3()'"); // Select Different Species
    $ThePage->LineBreak();
    $ImgTag=TBL_Media::GetImgTagFromID($Database,$Media3ID,"Name",150,150,TBL_MEDIA_VERSION_THUMBNAIL,"CallingPage"); // 300 by 300
    echo("$ImgTag");
}
else
{
    $TheTable->FormButton("UploadPhoto","Upload Photo","class='registerbutton' style='width:100px;' id='UploadPhoto' alt='Upload a photo' title='Upload a photo' onclick='DoSelectPhoto3()'"); // Select Different Species
}
$TheTable->TableCellEnd();

$TheTable->TableRowEnd();

// ----- Photo 4 -----

$TheTable->TableRowStart();

$TheTable->TableCell(0,"Photo:");
$TheTable->TableCellStart(1);
if ($Media4ID>0)
{
    $TheTable->FormButton("UploadPhoto","Change Photo","class='registerbutton' style='width:100px;' id='UploadPhoto' alt='Upload a different photo' title='Upload a different photo' onclick='DoSelectPhoto4()'"); // Select Different Species
    $ThePage->LineBreak();
    $ImgTag=TBL_Media::GetImgTagFromID($Database,$Media4ID,"Name",150,150,TBL_MEDIA_VERSION_THUMBNAIL,"CallingPage"); // 300 by 300
    echo("$ImgTag");
}
else
{
    $TheTable->FormButton("UploadPhoto","Upload Photo","class='registerbutton' style='width:100px;' id='UploadPhoto' alt='Upload a photo' title='Upload a photo' onclick='DoSelectPhoto4()'"); // Select Different Species
}
$TheTable->TableCellEnd();

$TheTable->TableRowEnd();

//---

$TheTable->TableEnd();

$ThePage->LineBreak();


$TheTable->FormHidden("TakeAction",$TakeAction);


if ($MediaID>0)
{
	$TheTable->FormHidden("MediaID",$MediaID);
}

$TheTable->FormHidden("OrganismDataID",$OrganismDataID);
$TheTable->FormHidden("MediaID",$MediaID);
$TheTable->FormHidden("Media2ID",$Media2ID);
$TheTable->FormHidden("Media3ID",$Media3ID);
$TheTable->FormHidden("Media4ID",$Media4ID);
$TheTable->FormHidden("CallingPage",$CallingPage);

$TheTable->FormHidden("South",$South);
$TheTable->FormHidden("East",$East);
$TheTable->FormHidden("Datum",$Datum);

$TheTable->FormHidden("OrganismInfoID",$OrganismInfoID);

echo("<center>");

$TheTable->FormButton("Submit","Submit","onclick='DoSubmit()'");

echo("</center>");

$TheTable->FormEnd();

$ThePage->LineBreak();

$ThePage->BodyEnd();

?>

