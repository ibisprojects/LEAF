<?php
//**************************************************************************************
// FileName: Add_Single_Plant.php
// Author: GN, RS
// Purpose: Allows users to enter x,y coordinates, photos for a single plant to add to LEAF
//**************************************************************************************

//require_once("Classes/Page.php");

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

//**************************************************************************************
// Database connection
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Security
//**************************************************************************************

// Check if we are supposed to run the form as a guest user
$RunAsGuest=GetIntParameter("RunAsGuest",0);

if ($RunAsGuest==17) // make a unique code for GLEDN access
{
	// Do form without logging in - use guest account
	$SelectString="SELECT * ".
		"FROM TBL_People ".
		"WHERE Login='GLEDN Guest'";

	$PersonSet=$Database->Execute($SelectString);
	$UserID=$PersonSet->Field("ID");
	$UserSessionID=TBL_UserSessions::GetID($Database,$UserID);
}
else
{
	// Make the user log in if they are not currently
	CheckLogin2($Database,PERMISSION_USER);

	$UserID=GetUserID();
	$UserSessionID=TBL_UserSessions::GetID($Database,GetUserID());
}

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
$East=GetIntParameter("East",0); // default western (0)
$South=GetIntParameter("South",0); // default northern (1)
$Datum=GetIntParameter("Datum",STDATUM_WGS_84); // was default set to 1; use STDATUM_WGS_84 which is a 2.
$ZoneIndex=GetIntParameter("ZoneIndex",-1);
$X=GetFloatParameter("lon"); // X
$Y=GetFloatParameter("lat"); // Y
$Accuracy=GetFloatParameter("Accuracy",null);
$Projection=GetIntParameter("Projection",STPROJECTION_GEOGRAPHIC); // jjg - converted all code as an STPROJECTION definition
$VisitDateObject=GetDateParameter("Date");
$OrganismInfoID=GetIntParameter("OrganismInfoID");
$SpeciesID=GetIntParameter("SpeciesID");

if ($SpeciesID>0) $OrganismInfoID=$SpeciesID;

$ProjectCode="LEAF";
$ProjectID=GetIntParameter("ProjectID"); // used for form submittal
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

//$CallingPage=GetStringParameter("CallingPage",null);

//**************************************************************************************
// Server-side code
//**************************************************************************************
/*
$ProjectSet=TBL_Projects::GetSet($Database,$ProjectCode);
$ProjectID=$ProjectSet->Field("ID");

if ($SceneLayerID>0)
{
	$Set=TBL_SceneLayers::GetSetFromID($Database,$SceneLayerID);
	
	$OrganismInfoID=$Set->Field("OrganismInfoID");
}

$UserSessionID=TBL_UserSessions::GetID($Database,$UserID);
*/
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

if ($TakeAction=="NewSpecified") // jjg - add this to to keep the longitudes from switching sign below
{
	if ($X<0) $East=0;
	else $East=1;	
}

if ($Projection==STPROJECTION_GEOGRAPHIC) // LL
{
	if ($East==0)
	// user selected lat/long and western hemisphere using radio button
	{
		if ($X>0) // coordinates entered by user are positive
		{
			$X=-$X;
		}
	}
	else // user selected western hemisphere using radio button
	{
		if ($X<0) // coordinates entered by user are positive
		{
			$X=-$X;
		}
	}
}

$MyDefaultCallingPage="/cwis438/websites/LEAF/Add_Data.php".
	"?OrganismDataID=$OrganismDataID".
	"&AreaName=$AreaName".
	"&AreaComments=$AreaComments".
	"&Datum=$Datum".
	"&ZoneIndex=$ZoneIndex".
	"&Projection=$Projection".
	"&X=$X".
	"&Y=$Y".
	"&Accuracy=$Accuracy".
	"&ProjectID=$ProjectID".
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
        "&RunAsGuest=17".
	"&TakeAction=AddPlant";

// Take Actions

if (($TakeAction=="New")||($TakeAction=="NewSpecified")) // called from the AddPoint on map (or someone else who wants to pass in data) with TakeAction=NewSpecified
{
	if ($TakeAction!="NewSpecified")
	{
		$Datum=TBL_UserSettings::Get($Database,"Datum",STDATUM_WGS_84);
		$ZoneIndex=TBL_UserSettings::Get($Database,"ZoneIndex",-1);
		$South=TBL_UserSettings::Get($Database,"South",0);
		$East=TBL_UserSettings::Get($Database,"East",0);
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
		if ($OrganismInfoID==0) $OrganismInfoID=$SpeciesID;
		
		//DebugWriteln("AreaComments=$AreaComments UserID=$UserID ProjectID=$ProjectID X=$X Y=$Y AreaName=$AreaName p=$Present OrganismDataID=$OrganismDataID ");
		$OrganismDataID=TBL_OrganismData::AddPoint($Database,$UserID,$ProjectID,$X,$Y,
			$CoordinateSystemID,$AreaName,$VisitDateObject,$Present,null,$OrganismInfoID,$Accuracy,true,AREA_SUBTYPE_POINT,null,$AreaComments,$VisitComments);
		//DebugWriteln("test");
	}
	else 
	{
		// we may need to eventually add $AreaComments and $VisitComments to the update function as well; gjn
		
		TBL_OrganismData::UpdatePoint($Database,$OrganismDataID,$ProjectID,$X,$Y,
			$CoordinateSystemID,$AreaName,$VisitDateObject,$Present,null,$OrganismInfoID,$Accuracy);	
	}
	
	// redirect
	
	//DebugWriteln("CallingPage=$CallingPage*");
	//DebugWriteln(strlen($CallingPage));

	if ($CallingPage!=null) // have a calling page (this is not currently used)
	{
		RedirectFromRoot($CallingPage);
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
			
			RedirectFromRoot("/cwis438/Websites/GLEDN/Submit_Confirmation.php?VisitID=$VisitID");
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
else if ($TakeAction=="SearchOrganismInfo")
{	
	$Zone=$ZoneIndex+1;
	
	$CoordinateSystemID=LKU_CoordinateSystems::GetIDFromProjection($Database,$Projection,$Zone,$South,$Datum);
		
	// Redirect to go find an new OrganismInfo
	
	$OrganismInfoCallingPage=$MyDefaultCallingPage."&MediaID=$MediaID&OrganismInfoID=";
	
	$OrganismInfoCallingPage=urlencode($OrganismInfoCallingPage);	
	
	//DebugWriteln("OrganismInfoCallingPage=$OrganismInfoCallingPage");
	
	RedirectFromRoot("/cwis438/Browse/Organism/OrganismInfo_List.php?CallingPage=$OrganismInfoCallingPage");
}
else if ($TakeAction=="SelectPhoto")
{	
	// Redirect to go find a Photo
	
	$PhotoCallingPage=$MyDefaultCallingPage."&RunAsGuest=17&MediaID=";
	
	//DebugWriteln("PhotoCallingPage=$PhotoCallingPage");
	
	$PhotoCallingPage=urlencode($PhotoCallingPage);	
	
	$MyCallingLabel="To Data Entry Form";
	
	RedirectFromRoot("/cwis438/contribute/Upload/upload_media.php?TakeAction=NewMedia&CallingPage=$PhotoCallingPage&CallingLabel=$MyCallingLabel");
}
else if ($TakeAction=="SelectPhoto2")
{
    // Redirect to go find a Photo2

    $PhotoCallingPage=$MyDefaultCallingPage."&RunAsGuest=17&Media2ID=";

    //DebugWriteln("PhotoCallingPage=$PhotoCallingPage");

    $PhotoCallingPage=urlencode($PhotoCallingPage);

    $MyCallingLabel="To Data Entry Form";

    RedirectFromRoot("/cwis438/contribute/Upload/upload_media.php?TakeAction=NewMedia&CallingPage=$PhotoCallingPage&CallingLabel=$MyCallingLabel");
}
else if ($TakeAction=="SelectPhoto3")
{
    // Redirect to go find a Photo3

    $PhotoCallingPage=$MyDefaultCallingPage."&RunAsGuest=17&Media3ID=";

    //DebugWriteln("PhotoCallingPage=$PhotoCallingPage");

    $PhotoCallingPage=urlencode($PhotoCallingPage);

    $MyCallingLabel="To Data Entry Form";

    RedirectFromRoot("/cwis438/contribute/Upload/upload_media.php?TakeAction=NewMedia&CallingPage=$PhotoCallingPage&CallingLabel=$MyCallingLabel");
}
else if ($TakeAction=="SelectPhoto4")
{
    // Redirect to go find a Photo4

    $PhotoCallingPage=$MyDefaultCallingPage."&RunAsGuest=17&Media4ID=";

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
	$East=GetIntParameter("East",0); // default western (0)
	$South=GetIntParameter("South",0); // default northern (1)
	$Datum=GetIntParameter("Datum",STDATUM_WGS_84); // was default to 1
	$ZoneIndex=GetIntParameter("ZoneIndex",-1);
	$Projection=GetIntParameter("Projection",STPROJECTION_GEOGRAPHIC); // jjg - converted all code as an STPROJECTION definition
	$X=GetFloatParameter("X");
	$Y=GetFloatParameter("Y");
	$Accuracy=GetFloatParameter("Accuracy",null);
	$ProjectID=GetIntParameter("ProjectID");
	$Present=GetIntParameter("Present",1);
	$OrganismInfoID=GetIntParameter("OrganismInfoID");
	
	//DebugWriteln("ProjectID=$ProjectID");
	
	if (stripos($CallingPage,"?OrganismInfoID=")!==false)
	{
		$CallingPage.=$OrganismInfoID;
		//DebugWriteln("CallingPageRevised=$CallingPage");
	}
	
	$TakeAction="Update";
}

$Zones=GetUTMZones();

$Code=GetGoogleMapsAuthorizationCode();

//DebugWriteln("Code=$Code");

//**************************************************************************************
// HTML Header block and client-side includes
//**************************************************************************************

//$TheLeafPage=new Page();

$ThePage=new PageSettings;

$TheTable=new TableSettings(TABLE_FORM);

$Title="Add A Single Plant Observation";

$ThePage->HeaderStart("Living Atlas of East African Flora");

//DebugWriteln("$Code"); //<?echo('$Code'); 

?>

<script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAQPvB0ts8qmQXUh8cm-AArBQCP740twLTTljPdp_skv77R4jXHRTxRK8uugVIMKEKvntYa-Mvfo35TA&sensor=false"></script>   

<SCRIPT TYPE="text/javascript" LANGUAGE="javascript" SRC="/cwis438/includes/FormUtil.js"></SCRIPT>
<SCRIPT TYPE="text/javascript" LANGUAGE="javascript" SRC="/cwis438/includes/ProjectionUtil.js"></SCRIPT>
<SCRIPT TYPE="text/javascript" LANGUAGE="javascript" SRC="/cwis438/includes/DOMElement.js"></SCRIPT>
<SCRIPT TYPE="text/javascript" LANGUAGE="JavaScript" SRC="/cwis438/includes/DOMStyle.js"></SCRIPT>

<SCRIPT LANGUAGE="JavaScript">

function DoSubmit() // TakeAction
{	
	// ************************************************************************************
	
	var ErrorString=null;
	
	// first ensure they have selected a species
	
	var OrganismInfoID=document.forms.geoForm.OrganismInfoID.value;
	//alert("OrganismInfoID="+OrganismInfoID);
	
	if (OrganismInfoID==0)
	{	
		var SpeciesID=document.geoForm.SpeciesID.value;
		//alert("SpeciesID="+SpeciesID);
		if (SpeciesID==0)
		{
		    alert("Sorry, you must select a species");
			ErrorString="Sorry, you must select a species";
		}
		else
		{
			OrganismInfoID=SpeciesID;
		}
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

function DoSelectSpecies()
{	
	// change select species text client-side to refelect selected species from pick list
	
	//var Species=DOMElement_GetElementByID("OrganismInfoID");
	
	//var OrganismInfoName=Species[Species.selectedIndex].innerHTML;
	
	//var Element=DOMElement_GetElementByID("SelectedSpeciesName");
	
	//DOMElement_SetInnerHTML(Element,OrganismInfoName);
	
	// set the value of the hidden pick list to be the selected value
	
	//document.geoForm.OrganismInfoID.value=Species[Species.selectedIndex].value;
}

//<![CDATA[

var map = null;
var prev_pin = null;

function xmlRequest(url, data, callback)
{

	xmlHttpReq = false; 
	if(window.XMLHttpRequest) { 
		try { 
			xmlHttpReq = new XMLHttpRequest(); 
		} catch(e) { 
			xmlHttpReq = false; 
		}
	} else if(window.ActiveXObject) { 
		try { 
			xmlHttpReq = new ActiveXObject("Msxml2.XMLHTTP"); 
		} catch(e) { 
			try { 
				xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP"); 
			} catch(e) { 
				xmlHttpReq = false; 
			} 
		} 
	}

	if (!xmlHttpReq) { return false; }

	xmlHttpReq.open('POST', url, true);
	xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xmlHttpReq.onreadystatechange = function() { 
		if (xmlHttpReq.readyState == 4) { 
    		//document.getElementById('debug').innerHTML = xmlHttpReq.responseText;
			switch (xmlHttpReq.status) { 
				// TODO: handle other status values, error checking, etc
				case 200: 
					// Call the desired callback function 
					eval(callback + '(xmlHttpReq.responseXML);'); 
					break; 
				default: 
					break; 
			} 
		} 
	} 
	xmlHttpReq.send(data);
	return true;
}

function displayGeoResults(geoXML)
{
var status = document.getElementById('status');

	try {
    	var resultset = geoXML.getElementsByTagName('ResultSet');
    	var result    = resultset[0].getElementsByTagName('Result');
    	var warning   = false;
		    try {
			    warning  = result[0].getAttribute('warning');
		    } catch (e) { }

    	var lat       = result[0].getElementsByTagName('latitude');
    	var lng       = result[0].getElementsByTagName('longitude');
    	var zip       = result[0].getElementsByTagName('uzip');

    	var latDiv = document.getElementById('lat'); // lat Y
    	var lngDiv = document.getElementById('lon'); // lng X
    	var zipDiv = document.getElementById('zip');
	
    	latDiv.value = lat[0].firstChild.nodeValue;
    	lngDiv.value = lng[0].firstChild.nodeValue;
		try {
                    zipDiv.value = '';
    		zipDiv.value = zip[0].firstChild.nodeValue;
		} catch(e) { }
	
    	var point = new GPoint(parseFloat(lng[0].firstChild.data), parseFloat(lat[0].firstChild.data));
    	var marker = new GMarker(point);
    	map.addOverlay(marker);
	
		if (prev_pin) { 
			map.removeOverlay(prev_pin); 
			prev_pin = null; 
		} 
		prev_pin = marker;

		map.centerAndZoom(point, 0);
		map.setMapType( _TERRAIN_TYPE );
    	//map.setMapType( _HYBRID_TYPE );

		if (!warning)
		{
    		status.innerHTML = "Found address.";
		} else {
			status.innerHTML = warning;
		}
	} catch(e) { 
		status.innerHTML = "Failed to retrieve and parse geo results!";
	}
}


function onLoad() {
        
        alert("onLoad");
	map = new GMap(document.getElementById("map"));
	map.addControl(new GSmallMapControl());
	map.addControl(new GMapTypeControl());
	map.centerAndZoom(new GPoint(39.70458984375, 8.52887305273525), 37); // (-73.98880, 40.749337), 5)

	GEvent.addListener(map, 'click', function(overlay, point) { 
		if (prev_pin) { 
			map.removeOverlay(prev_pin); 
			prev_pin = null; 
		} 
		if (point) { 
			pin = new GMarker(point); 
			map.addOverlay(pin); 
			prev_pin = pin; 

			latDiv = document.getElementById('lat'); // lat Y
			lngDiv = document.getElementById('lon');  // lon X
			//alert(point.x);
			lngDiv.value = point.x;
			latDiv.value = point.y;

			var zoomlevel = map.getZoom();
		    
		    var MetersArray=[20088000,10044000,5022000,2511000,1255500,627750,313875,156937,156937,78468,39234,19617,9808,4909,2452,1226,613,306,153,76,38]; // 0, 1, 2 , 3 , 4 ,5 etc...
		    
		    /*
			Zoom level 0 1:20088000.56607700 meters
			 Zoom level 1 1:10044000.28303850 meters
			 Zoom level 2 1:5022000.14151925 meters
			 Zoom level 3 1:2511000.07075963 meters
			 Zoom level 4 1:1255500.03537981 meters
			 Zoom level 5 1:627750.01768991 meters
			 Zoom level 6 1:313875.00884495 meters
			 Zoom level 7 1:156937.50442248 meters
			 Zoom level 8 1:78468.75221124 meters
			 Zoom level 9 1:39234.37610562 meters
			 Zoom level 10 1:19617.18805281 meters
			 Zoom level 11 1:9808.59402640 meters
			 Zoom level 12 1:4909.29701320 meters
			 Zoom level 13 1:2452.14850660 meters
			 Zoom level 14 1:1226.07425330 meters
			 Zoom level 15 1:613.03712665 meters
			 Zoom level 16 1:306.51856332 meters
			 Zoom level 17 1:153.25928166 meters
			 Zoom level 18 1:76.62964083 meters
			 Zoom level 19 1:38.31482042 meters
		    */
			
			Accuracy = document.getElementById('Accuracy'); // Accuracy

			//var Accuracy=627750; // default is zoom level 5 for defaul map settings which equates to 1:627750 (see table below)
			
			Accuracy.value = MetersArray[zoomlevel];
			//alert("zoomLevel="+zoomlevel);
		} 
	});

}

function lookupLocation()
{
	var form = document.getElementById('geoForm');

    street = form.street.value;
    city = form.city.value;
    state = form.state.value;

    if (!city || !state) {
       alert("At least fill in the city and state!");
       return false;
    }

	if (prev_pin) { 
		map.removeOverlay(prev_pin); 
		prev_pin = null; 
	}

	var status = document.getElementById('status'); 
	status.innerHTML = "looking up address...please wait...";

	url = 'geo_lookup.php'; 
	urldata = "street=" + form.street.value + "&" + "city=" + form.city.value + "&" + "state=" + form.state.value;

	if (!xmlRequest(url, urldata, 'displayGeoResults')) 
	{ 
		status.innerHTML = "Error in looking up geo info"; 
	}
}

//]]>

</SCRIPT>

<?php

$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart(null,null,null,$AdditionalAttributes="onload='onLoad()'"); // onload map event???

//echo("<body onload='initialize()' onunload='GUnload()'>");

echo("<c><h1>Add A Single Plant Observation</h1><c><br><br>");

echo("<form action='/cwis438/websites/LEAF/Add_Single_Plant.php' method='POST' id='geoForm' name='geoForm'>");

// **************************** Species ****************************

$TheTable->TableStart();
$TheTable->ColumnHeading->ColumnSpan=2;
//$TheTable->TableColumnHeading("Species");

$Name="Please select a species";
if ($OrganismInfoID>0) $Name=TBL_OrganismInfos::GetName($Database,$OrganismInfoID);

if ($ComputerName=="Ibis-live1") // Ibis-live1
{
    $SpeciesSelectString = "SELECT TBL_TaxonUnits.TSN, TBL_OrganismInfos.Name, TBL_OrganismInfos.ID, TBL_TaxonUnits.UnitName1, TBL_TaxonUnits.UnitName2
        FROM REL_OrganismInfoToTSN INNER JOIN
                      TBL_OrganismInfos ON REL_OrganismInfoToTSN.OrganismInfoID = TBL_OrganismInfos.ID INNER JOIN
                      TBL_TaxonUnits ON REL_OrganismInfoToTSN.TSN = TBL_TaxonUnits.TSN
        WHERE (TBL_TaxonUnits.TSN = 31890) OR
                      (TBL_TaxonUnits.TSN = '32974') OR
                      (TBL_TaxonUnits.TSN = '503854') OR
                      (TBL_TaxonUnits.TSN = '27776') OR
                      (TBL_TaxonUnits.TSN = '506068') AND (TBL_OrganismInfos.Name = 'Asian bittersweet') OR
                      (TBL_TaxonUnits.TSN = '20293') OR
                      (TBL_TaxonUnits.TSN = '35286') AND (TBL_OrganismInfos.Name = 'Bush honeysuckles') OR
                      (TBL_TaxonUnits.TSN = '35298') AND (TBL_OrganismInfos.Name = 'Amur honeysuckle') OR
                      (TBL_TaxonUnits.TSN = '35299') AND (TBL_OrganismInfos.Name = 'Morrows honeysuckle') OR
                      (TBL_TaxonUnits.TSN = '35306') AND (TBL_OrganismInfos.Name = 'Tatarian honeysuckle') OR
                      (TBL_TaxonUnits.TSN = '32977') OR
                      (TBL_TaxonUnits.TSN = '32976') OR
                      (TBL_TaxonUnits.TSN = '36962') OR
                      (TBL_TaxonUnits.TSN = '36428') OR
                      (TBL_TaxonUnits.TSN = '504706') OR
                      (TBL_TaxonUnits.TSN = '36335') AND (TBL_OrganismInfos.Name = 'Canada Thistle') OR
                      (TBL_TaxonUnits.TSN = '28573') OR
                      (TBL_TaxonUnits.TSN = '25898') OR
                      (TBL_TaxonUnits.TSN = '32979') OR
                      (TBL_TaxonUnits.TSN = '41874') OR
                      (TBL_TaxonUnits.TSN = '18857') OR
                      (TBL_TaxonUnits.TSN = '30238') OR
                      (TBL_TaxonUnits.TSN = '35406') OR
                      (TBL_TaxonUnits.TSN = '29588') OR
                      (TBL_TaxonUnits.TSN = '35405') OR
                      (TBL_TaxonUnits.TSN = '503474') OR
                      (TBL_TaxonUnits.TSN = '36958') OR
                      (TBL_TaxonUnits.TSN = '28985') OR
                      (TBL_TaxonUnits.TSN = '32980') OR
                      (TBL_TaxonUnits.TSN = '501902') OR
                      (TBL_TaxonUnits.TSN = '18603') OR
                      (TBL_TaxonUnits.TSN = '38886') OR
                      (TBL_TaxonUnits.TSN = '503549') OR
                      (TBL_TaxonUnits.TSN = '35363') OR
                      (TBL_TaxonUnits.TSN = '41450') OR
                      (TBL_TaxonUnits.TSN = '502954') OR
                      (TBL_TaxonUnits.TSN = '20923') OR
                      (TBL_TaxonUnits.TSN = '184481') OR
                      (TBL_TaxonUnits.TSN = '501481') OR
                      (TBL_TaxonUnits.TSN = '504744') OR
                      (TBL_TaxonUnits.TSN = '22797') OR
                      (TBL_TaxonUnits.TSN = '29894') OR
                      (TBL_TaxonUnits.TSN = '18835') OR
                      (TBL_TaxonUnits.TSN = '35283') OR
                      (TBL_TaxonUnits.TSN = '503065') OR
                      (TBL_TaxonUnits.TSN = '20889') OR
                      (TBL_TaxonUnits.TSN = '503829') OR
                      (TBL_TaxonUnits.TSN = '529930') AND (TBL_OrganismInfos.Name = 'Kudzu') OR
                      (TBL_TaxonUnits.TSN = '28064') OR
                      (TBL_TaxonUnits.TSN = '42203') OR
                      (TBL_TaxonUnits.TSN = '20914') OR
                      (TBL_TaxonUnits.TSN = '24883') OR
                      (TBL_TaxonUnits.TSN = '35787') OR
                      (TBL_TaxonUnits.TSN = '36394') OR
                      (TBL_TaxonUnits.TSN = '22798') OR
                      (TBL_TaxonUnits.TSN = '28755') OR
                      (TBL_TaxonUnits.TSN = '43194') OR
                      (TBL_TaxonUnits.TSN = '503379') OR
                      (TBL_TaxonUnits.TSN = '27079') OR
                      (TBL_TaxonUnits.TSN = '41072') OR
                      (TBL_TaxonUnits.TSN = '35785') OR
                      (TBL_TaxonUnits.TSN = '29473') OR
                      (TBL_TaxonUnits.TSN = '40864') OR
                      (TBL_TaxonUnits.TSN = '33460') OR
                      (TBL_TaxonUnits.TSN = '36459') AND (TBL_OrganismInfos.Name = 'Russian knapweed') OR
                      (TBL_TaxonUnits.TSN = '27770') OR
                      (TBL_TaxonUnits.TSN = '503432') OR
                      (TBL_TaxonUnits.TSN = '38140') OR
                      (TBL_TaxonUnits.TSN = '501966') OR
                      (TBL_TaxonUnits.TSN = '501347') AND (TBL_OrganismInfos.ID = 43) OR
                      (TBL_TaxonUnits.TSN = '40833') AND (TBL_OrganismInfos.Name = 'tall mannagrass') OR
                      (TBL_TaxonUnits.TSN = '22310') OR
                      (TBL_TaxonUnits.TSN = '28827') OR
                      (TBL_TaxonUnits.TSN = '23067') OR
                      (TBL_TaxonUnits.TSN = '28632') OR
                      (TBL_TaxonUnits.TSN = '29795') OR
                      (TBL_TaxonUnits.TSN = '502576') OR
                      (TBL_TaxonUnits.TSN = '27950') OR
                      (TBL_TaxonUnits.TSN = '36972') OR
                      (TBL_TaxonUnits.TSN = 502075) OR
                      (TBL_TaxonUnits.TSN = 43368) OR                      
                      (TBL_TaxonUnits.TSN = 29895) OR
                      (TBL_TaxonUnits.TSN = 506929) AND (TBL_OrganismInfos.ID = 83)
        ORDER BY TBL_OrganismInfos.Name"; 
}
else
{
    $SpeciesSelectString="SELECT TBL_OrganismInfos.Name, MIN(TBL_OrganismInfos.ID) AS OrganismInfoID
        FROM REL_OrganismInfoToTSN INNER JOIN
                      TBL_OrganismInfos ON REL_OrganismInfoToTSN.OrganismInfoID = TBL_OrganismInfos.ID ".
                      "GROUP BY TBL_OrganismInfos.Name ".
	    "HAVING Name = 'Amur honeysuckle' ".
		"OR Name = 'Amur privet' ".
		"OR Name = 'Amur silvergrass' ".
		"OR Name = 'Autumn Olive' ".
		"OR Name = 'Asian bittersweet' ".
		"OR Name = 'babysbreath' ". 
		"OR Name = 'Bush honeysuckles' ".
		"OR Name = 'border privet' ".
		"OR Name = 'Brown knapweed' ".
		"OR Name = 'Bull thistle' ".
		"OR Name = 'callery pear' ".
		"OR Name = 'Canada thistle' ".
		"OR Name = 'California privet' ".
		"OR Name = 'carolina buckthorn' ".
		"OR Name = 'Chinese Lespedeza' ".
		"OR Name = 'Chinese yam' ".
		"OR Name = 'chinese privet' ".
		"OR Name = 'Chinese silvergrass' ".
		"OR Name = 'Chocolate Vine' ".
		"OR Name = 'common periwinkle' ".
		"OR Name = 'Common reed' ".
		"OR Name = 'Common teasel' ".
		"OR Name = 'Cow parsley' ".
		"OR Name = 'cutleaf teasel' ".
		"OR Name = 'Dalmatian toadflax' ".
		"OR Name = 'Diffuse Knapweed' ".
		"OR Name = 'Erect hedgeparsley' ".
		"OR Name = 'European privet' ".
		"OR Name = 'European swallow-wort' ".
		"OR Name = 'fig buttercup' ".
		"OR Name = 'Flowering rush' ".
		"OR Name = 'fly honeysuckle' ". // Morrow's,
		"OR Name = 'Flowering Rush' ".
		"OR Name = 'Giantreed' ".
		"OR Name = 'Giant Hogweed' ".
		"OR Name = 'giant knotweed' ".
		"OR Name = 'garden valerian' ".
		"OR Name = 'Garlic Mustard' ".
		"OR Name = 'Greater celandine' ".
		"OR Name = 'Glossy buckthorn' ". // Smooth,
		//"OR TBL_OrganismInfos.Name = 'gypsyflower, Houndstongue' ". // Houndstongue
		"OR Name = 'Hairy bittercress' ".
		"OR Name = 'Hedgeparsley' ".
		"OR Name = 'Japanese barberry' ".
		"OR Name = 'Japanese honeysuckle' ".
		"OR Name = 'Japanese Hop' ".
		"OR Name = 'Japanese knotweed' ".
		"OR Name = 'Japanese stiltgrass' ".
		"OR Name = 'Kudzu' ".
		"OR Name = 'Leafy spurge' ". // wolf's milk
		//"OR TBL_OrganismInfos.Name = 'Louis swallow-wort' ".
		"OR Name = 'medusahead' ".
		"OR Name = 'Mile-a-minute weed' ".
		"OR Name = 'Multiflora rose' ".
		"OR Name = 'Musk thistle' ".
		"OR Name = 'marsh thistle' ".
		"OR Name = 'Narrowleaf bittercress' ". // , Bushy rock-cress
		"OR Name = 'Norway Maple' ".
		"OR Name = 'paleyellow iris' ".
		"OR Name = 'Perennial Pepperweed' ".
		"OR Name = 'Purple-loosestrife' ".
		"OR Name = 'Plumeless thistle' ".
		"OR Name = 'Poison Hemlock' ".
		"OR Name = 'reed mannagrass' ".
		"OR Name = 'Royal Paulownia' ".
		"OR Name = 'Russian knapweed' ".
		"OR Name = 'Russian Olive' ".
		"OR Name = 'sand ryegrass' ".
		"OR Name = 'Scotch cottonthistle' ".
		"OR Name = 'Scotchbroom' ".
		"OR Name = 'spotted knapweed' ".
		"OR Name = 'Tall mannagrass' ". // , English water
		"OR Name = 'Tamarisk' ".
		"OR Name = 'Tansy' ".
		"OR Name = 'Tartarian honeysuckle' ".
		"OR Name = 'Tree-of-heaven' ".
		"OR Name = 'Turkish wartycabbage' ".
		"OR Name = 'Wild grape' ".
		"OR Name = 'wild parsnip' ".
		"OR Name = 'Wild teasel' ". // , common teasel
		"OR Name = 'winged burning bush' ".
		"OR Name = 'winter creeper' ".
		"OR Name = 'Yellow Star Thistle' ".
	"ORDER BY Name";
}

$SpeciesSet=$Database->Execute($SpeciesSelectString);

$TheTable->Columns[1]->FormElementWidth=null;

$TheTable->TableRowStart();
	$TheTable->TableCell(0,"Species:");
	$TheTable->TableCellStart(1); // 1
		echo("<div style='float:left; height:24px;'>"); // border:thin solid green; 
		if ($ComputerName=="Ibis-live1") // Ibis-live1
		{
		    $TheTable->FormRecordSet("OrganismInfoID",$OrganismInfoID,$SpeciesSet,array("Name"),3,true," -- Select a Species -- ",0,1,false,"width='150' style='width:150px;' onchange='DoSelectSpecies()'",true,"25%");
		}
		else
		{
		    $TheTable->FormRecordSet("OrganismInfoID",$OrganismInfoID,$SpeciesSet,array("Name"),2,true," -- Select a Species -- ",0,1,false,"width='150' style='width:150px;' onchange='DoSelectSpecies()'",true,"25%");
		}
		echo("</div>");
		
		echo("<div style='float:right; width:70%; height:24px;'>"); // border:thin solid red; 
			echo("<div style='float:left; width:80px; vertical-align:text-bottom; padding-bottom:0px; padding-top:4px; height:20px;'>Not Listed?</div>"); // border:thin solid blue; 
			$TheTable->FormButton("Change","Other Species","class='ButtonStyle' id='ChangeSpecies' alt='Choose a different species' title='Choose a different species' onclick='DoSearchOrganismInfo()'"); // Select Different Species
		echo("</div>");
		
	$TheTable->TableCellEnd();
	
$TheTable->TableRowEnd();

if ($OrganismInfoID>0)
{
	$TheTable->TableRowStart();
		$TheTable->TableCell(0,"Selected Species:");
		$TheTable->TableCellStart(1);
			$OrganismName=TBL_OrganismInfos::GetName($Database,$OrganismInfoID,true);
			echo("<div id='SelectedSpeciesName'>$OrganismName</div>");
		$TheTable->TableCellEnd();
	$TheTable->TableRowEnd();
}

$TheTable->TableEnd();

// **************************** Location ****************************

if ($X==0) $X="";
if ($Y==0) $Y="";

$TheTable->TableStart();
//$TheTable->TableColumnHeading("Location");

$TheTable->TableRowStart();
    $TheTable->TableCellStart();
        $ThePage->DivStart();
            echo("<div style='color:#0198ab; font-size:10pt; margin-top:10px; margin-bottom:10px;'>Please either (a) enter latitude longitude coordinates below from your GPS you used, or (b) click on the map to the right and zoom in to determine and provide an accurate location.</div>");
            $ThePage->DivStart("style='float:left; width:325px; height:255px;'"); // border: solid 1px red;
                
                $ThePage->DivStart("style='width:322px;'");
                    echo("<label style='display:inline-block; width:110px;'>Location Name:</label>");
                    $TheTable->FormTextEntry("AreaName",$AreaName,100); // need to change lat to Y ---- id='lat' type='text' size='20' name='lat' value='' style="background-color: #eee"
                $ThePage->DivEnd();
            
                $ThePage->DivStart("style='width:322px;'");
                    echo("<label style='display:inline-block; width:110px;'>Latitude:</label>"); 
                    $TheTable->FormTextEntry("lat",$Y,100,"id='lat'"); // need to change lat to Y ---- id='lat' type='text' size='20' name='lat' value='' style="background-color: #eee"
                $ThePage->DivEnd();
                
                $ThePage->DivStart("style='width:322px;'");
                    echo("<label style='display:inline-block; width:110px;'>Longitude:</label>");
                    $TheTable->FormTextEntry("lon",$X,100,"id='lon'"); // set max length to 100
                $ThePage->DivEnd();
                
                $ThePage->DivStart("style='width:322px;'");
                    echo("<label style='display:inline-block; width:110px;'>Accuracy:</label>");
                    $TheTable->FormTextEntry("Accuracy",$Accuracy,100,"id='Accuracy'"); // set max length to 100
                    echo("<span style='color:#0198ab; font-size:10pt;'>meters</span>");
                $ThePage->DivEnd();
                
                $ThePage->DivStart("style='width:322px;'");
                    echo("<label style='display:inline-block; width:110px;'>Site Description:</label>");
                    $TheTable->FormTextEntry("AreaComments",$AreaComments,100); // need to change lat to Y ---- id='lat' type='text' size='20' name='lat' value='' style="background-color: #eee"
                    echo("<span style='color:#0198ab; font-size:10pt;'>optional</span>");
                $ThePage->DivEnd();
                
                $ThePage->DivStart("style='width:322px;'");
                    echo("<label style='display:inline-block; width:110px;'>Name:</label>");
                    $TheTable->FormTextEntry("Name:",$ReporterName,100);
                    echo("<span style='color:#0198ab; font-size:10pt;'>optional</span>");
                $ThePage->DivEnd();
                
                $ThePage->DivStart("style='width:322px;'");
                    echo("<label style='display:inline-block; width:110px;'>Email:</label>");
                    $TheTable->FormTextEntry("Email:",$Email,100);
                    echo("<span style='color:#0198ab; font-size:10pt;'>optional</span>");
                $ThePage->DivEnd();

            $ThePage->DivEnd();
            
            $ThePage->DivStart("style='float:right; width:455px; height:265px; margin-right:10px;'");
                      
            echo("<div id='content'>
            <div id='map' style='height: 255px; width: 455px; border: 1px solid #CCCCCC'></div>
            </div>");
            
           $ThePage->DivEnd();
        $ThePage->DivEnd();
    $TheTable->TableCellEnd();
$TheTable->TableRowEnd();

$TheTable->TableEnd();


$ThePage->LineBreak(); // ********************


// **************************** Date ****************************

$TheTable->TableStart();
$TheTable->Columns[0]->Width="25%";

$TheTable->ColumnHeading->ColumnSpan=2;
//$TheTable->TableColumnHeading("Date of Sighting"); // Enter coordinates for the point location

$TheTable->TableRowStart();
	$TheTable->TableCell(0,"Date:");
	$TheTable->TableCellStart(1);
		$TheTable->FormDate("Date",$VisitDateObject->Year,$VisitDateObject->Month,$VisitDateObject->Day);
	$TheTable->TableCellEnd();
$TheTable->TableRowEnd();

$TheTable->TableEnd();

$ThePage->LineBreak();

// ********************** Photos  ***********************
	
$TheTable->TableStart();
$TheTable->ColumnHeading->ColumnSpan=2;
//$TheTable->TableColumnHeading("Photo");
$TheTable->Columns[1]->FormElementWidth=null;

$TheTable->TableRowStart();

	$TheTable->TableCell(0,"Photo:");
	$TheTable->TableCellStart(1);
		if ($MediaID>0)
		{
			$TheTable->FormButton("UploadPhoto","Change Photo","class='ButtonStyle' id='UploadPhoto' alt='Upload a different photo' title='Upload a different photo' onclick='DoSelectPhoto()'"); // Select Different Species
			$ThePage->LineBreak();
			$ImgTag=TBL_Media::GetImgTagFromID($Database,$MediaID,"Name",150,150,TBL_MEDIA_VERSION_THUMBNAIL,"CallingPage"); // 300 by 300
			echo("$ImgTag");
		}
		else 
		{
			$TheTable->FormButton("UploadPhoto","Upload Photo","class='ButtonStyle' id='UploadPhoto' alt='Upload a photo' title='Upload a photo' onclick='DoSelectPhoto()'"); // Select Different Species
			echo("<span style='color:#708c94; font-size:10pt; text-align:top; vertical-align:top; margin-top:4px;'>&nbsp;&nbsp;Note: Please select small JPG or PNG files.");
		}
	$TheTable->TableCellEnd();
	
$TheTable->TableRowEnd();

// ----- Photo 2 -----

$TheTable->TableRowStart();

    $TheTable->TableCell(0,"Photo:");
    $TheTable->TableCellStart(1);
        if ($Media2ID>0)
        {
            $TheTable->FormButton("UploadPhoto","Change Photo","class='ButtonStyle' id='UploadPhoto' alt='Upload a different photo' title='Upload a different photo' onclick='DoSelectPhoto2()'"); // Select Different Species
            $ThePage->LineBreak();
            $ImgTag=TBL_Media::GetImgTagFromID($Database,$Media2ID,"Name",150,150,TBL_MEDIA_VERSION_THUMBNAIL,"CallingPage"); // 300 by 300
            echo("$ImgTag");
        }
        else
        {
            $TheTable->FormButton("UploadPhoto","Upload Photo","class='ButtonStyle' id='UploadPhoto' alt='Upload a photo' title='Upload a photo' onclick='DoSelectPhoto2()'"); // Select Different Species
        }
    $TheTable->TableCellEnd();

$TheTable->TableRowEnd();

// ----- Photo 3 -----

$TheTable->TableRowStart();

$TheTable->TableCell(0,"Photo:");
$TheTable->TableCellStart(1);
if ($Media3ID>0)
{
    $TheTable->FormButton("UploadPhoto","Change Photo","class='ButtonStyle' id='UploadPhoto' alt='Upload a different photo' title='Upload a different photo' onclick='DoSelectPhoto3()'"); // Select Different Species
    $ThePage->LineBreak();
    $ImgTag=TBL_Media::GetImgTagFromID($Database,$Media3ID,"Name",150,150,TBL_MEDIA_VERSION_THUMBNAIL,"CallingPage"); // 300 by 300
    echo("$ImgTag");
}
else
{
    $TheTable->FormButton("UploadPhoto","Upload Photo","class='ButtonStyle' id='UploadPhoto' alt='Upload a photo' title='Upload a photo' onclick='DoSelectPhoto3()'"); // Select Different Species
}
$TheTable->TableCellEnd();

$TheTable->TableRowEnd();

// ----- Photo 4 -----

$TheTable->TableRowStart();

$TheTable->TableCell(0,"Photo:");
$TheTable->TableCellStart(1);
if ($Media4ID>0)
{
    $TheTable->FormButton("UploadPhoto","Change Photo","class='ButtonStyle' id='UploadPhoto' alt='Upload a different photo' title='Upload a different photo' onclick='DoSelectPhoto4()'"); // Select Different Species
    $ThePage->LineBreak();
    $ImgTag=TBL_Media::GetImgTagFromID($Database,$Media4ID,"Name",150,150,TBL_MEDIA_VERSION_THUMBNAIL,"CallingPage"); // 300 by 300
    echo("$ImgTag");
}
else
{
    $TheTable->FormButton("UploadPhoto","Upload Photo","class='ButtonStyle' id='UploadPhoto' alt='Upload a photo' title='Upload a photo' onclick='DoSelectPhoto4()'"); // Select Different Species
}
$TheTable->TableCellEnd();

$TheTable->TableRowEnd();

//---

$TheTable->TableEnd();

$ThePage->LineBreak();

$ThePage->LineBreak();

$ThePage->LineBreak();

$ThePage->LineBreak();

$TheTable->FormHidden("TakeAction",$TakeAction);

$TheTable->FormHidden("SpeciesID",$OrganismInfoID);

if ($TakeAction=="Returned")
{
	//$TheTable->FormHidden("SpeciesID",$OrganismInfoID);
}

if ($OrganismInfoID>0)
{
	$TheTable->FormHidden("OrganismName",$OrganismName);
	//$TheTable->FormHidden("SpeciesID",$OrganismInfoID);
}

if ($MediaID>0)
{
	$TheTable->FormHidden("MediaID",$MediaID);
}

$TheTable->FormHidden("OrganismDataID",$OrganismDataID);
$TheTable->FormHidden("MediaID",$MediaID);
$TheTable->FormHidden("Media2ID",$Media2ID);
$TheTable->FormHidden("Media3ID",$Media3ID);
$TheTable->FormHidden("Media4ID",$Media4ID);
//$TheTable->FormHidden("CallingPage",$CallingPage);
$TheTable->FormHidden("ProjectID",$ProjectID);
$TheTable->FormHidden("South",$South);
$TheTable->FormHidden("East",$East);
$TheTable->FormHidden("Datum",$Datum);
$TheTable->FormHidden("RunAsGuest",$RunAsGuest);

echo("<center>");

$TheTable->FormButton("Submit","Submit","onclick='DoSubmit()'");

echo("</center>");

$TheTable->FormEnd();

$ThePage->LineBreak();

$ThePage->BodyEnd();

//**************************************************************************************
// Client-side JavaScript for after load
//**************************************************************************************

?>
<SCRIPT LANGUAGE="JavaScript">
	
function DoProjectionLL() // LL Selected
{	
	//document.geoForm.ZoneIndex.disabled=true;
	
	//var Element=DOMElement_GetElementByID("XLabel");
	
	//Element.innerHTML="Longitude:";
		
	//var Element=DOMElement_GetElementByID("YLabel");
	
	//Element.innerHTML="Latitude:";
}

function DoProjectionUTM() // UTM Selected
{
	//document.geoForm.ZoneIndex.disabled=false;
	
	//var Element=DOMElement_GetElementByID("XLabel");
	
	//Element.innerHTML="Easting:";
		
	//var Element=DOMElement_GetElementByID("YLabel");
	
	//Element.innerHTML="Northing:";
}

<?

if ($Projection==STPROJECTION_GEOGRAPHIC) echo("DoProjectionLL();\n");
else echo("DoProjectionUTM();\n");

?>
</SCRIPT>

