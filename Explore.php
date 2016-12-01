<?php
//**************************************************************************************
// FileName: Explore.php
// Author: GN, RS, BF, NK
// The Explore page for the LEAF website (Living Atlas of East African Flora)
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
require_once("C:/Inetpub/wwwroot/cwis438/classes/DBTable/TBL_InsertLogs.php");
require_once("C:/Inetpub/wwwroot/cwis438/classes/DBTable/TBL_Visits.php");
require_once("C:/Inetpub/wwwroot/cwis438/classes/DBTable/TBL_OrganismData.php");
require_once("C:/Inetpub/wwwroot/cwis438/classes/DBTable/TBL_OrganismInfos.php");
require_once("C:/Inetpub/wwwroot/cwis438/classes/DBTable/REL_WebsiteToProject.php");
require_once("C:/Inetpub/wwwroot/cwis438/classes/DBTable/TBL_Projects.php");

//**************************************************************************************
// Security
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Server-side functions
//**************************************************************************************

//**************************************************************************************
// Parameters
//**************************************************************************************


//**************************************************************************************
// Server-Side code
//**************************************************************************************

// HighCharts - Plant Observations: Get count of the observations added to LEAF by year

$ObservationsSelectString="SELECT DATEPART(yyyy, TBL_InsertLogs.DateUploaded) AS Year, COUNT(TBL_OrganismData.ID) AS Observations
                                        FROM TBL_Visits INNER JOIN
                                            REL_WebsiteToProject ON TBL_Visits.ProjectID = REL_WebsiteToProject.ProjectID LEFT OUTER JOIN
                                            TBL_InsertLogs ON TBL_Visits.InsertLogID = TBL_InsertLogs.ID LEFT OUTER JOIN
                                            TBL_OrganismData ON TBL_Visits.ID = TBL_OrganismData.VisitID
                                        WHERE (REL_WebsiteToProject.WebsiteID = 20) AND (TBL_OrganismData.ID IS NOT NULL)
                                        GROUP BY DATEPART(yyyy, TBL_InsertLogs.DateUploaded)
                                        ORDER BY Year";

$OrganismsByYearSet=$Database->Execute($ObservationsSelectString);

$Count3=0;

while ($OrganismsByYearSet->FetchRow())
{
    $Count3++;
}

// HighCharts - Projects: Get count of the projects added to LEAF by year

$ProjectsSelectString="SELECT COUNT(TBL_Projects.ProjName) AS Projects, DATEPART(yyyy, TBL_Projects.StartDate) AS Year
                            FROM TBL_Projects INNER JOIN
                                REL_WebsiteToProject ON TBL_Projects.ID = REL_WebsiteToProject.ProjectID
                            WHERE     (REL_WebsiteToProject.WebsiteID = 20)
                            GROUP BY DATEPART(yyyy, TBL_Projects.StartDate)
                            ORDER BY Year";

$ProjectsSet=$Database->Execute($ProjectsSelectString);

$CountProject=0;

while ($ProjectsSet->FetchRow())
{
    $CountProject++;
}

// HighCharts - Top 5 Species: Get top 5 species observed in LEAF 

$Top5SpeciesSelectString="SELECT TOP (5) TBL_OrganismData.OrganismInfoID, COUNT(TBL_OrganismData.ID) AS NumObservations
                            FROM TBL_OrganismData INNER JOIN
                                TBL_Visits ON TBL_Visits.ID = TBL_OrganismData.VisitID INNER JOIN
                                REL_WebsiteToProject ON TBL_Visits.ProjectID = REL_WebsiteToProject.ProjectID
                            WHERE     (REL_WebsiteToProject.WebsiteID = 20)
                            GROUP BY TBL_OrganismData.OrganismInfoID
                            ORDER BY NumObservations DESC";


// HIGHCHARTS - Top 5 Contributing Projects (according to organismdata counts added)

$Top5ProjectsSelectString="SELECT TOP (5) COUNT(TBL_OrganismData.ID) AS Observations, TBL_Projects.ProjName
                            FROM TBL_Visits INNER JOIN
                                REL_WebsiteToProject ON TBL_Visits.ProjectID = REL_WebsiteToProject.ProjectID INNER JOIN
                                TBL_OrganismData ON TBL_Visits.ID = TBL_OrganismData.VisitID LEFT OUTER JOIN
                                TBL_Projects ON TBL_Visits.ProjectID = TBL_Projects.ID
                            WHERE (REL_WebsiteToProject.WebsiteID = 20) AND (TBL_OrganismData.ID IS NOT NULL)
                            GROUP BY TBL_Projects.ProjName
                            ORDER BY Observations DESC";

// Map data

$WebSiteID=20;


// NumVisits

$SelectString="SELECT COUNT(*) AS NumVisits
               FROM TBL_Visits INNER JOIN
                    REL_WebsiteToProject ON TBL_Visits.ProjectID = REL_WebsiteToProject.ProjectID
               WHERE (REL_WebsiteToProject.WebsiteID = 20)";

$NumVisitSet=$Database->Execute($SelectString);

$NumVisits=$NumVisitSet->Field("NumVisits");

// NumAreas

$SelectString="SELECT COUNT(*) AS NumAreas
                FROM TBL_Areas INNER JOIN
                    REL_WebsiteToProject ON TBL_Areas.ProjectID = REL_WebsiteToProject.ProjectID
                WHERE (REL_WebsiteToProject.WebsiteID = 20)";
	    
$NumAreasSet=$Database->Execute($SelectString);
	    
$NumAreas=$NumAreasSet->Field("NumAreas");

$ProjectZoom=4;

// Addis

$ProjectLatitude="9.03";  
$ProjectLongitude="38.74";

//**************************************************************************************
// HTML Header block, client-side includes, and client-side functions
//**************************************************************************************

$ThePage=new PageSettings();

$ThePage->HeaderStart("Living Atlas of East African Flora");

?>

<style>
    
#map-container
{
    padding: 6px;
    border-width: 1px;
    border-style: solid;
    border-color: #ccc #ccc #999 #ccc;
    -webkit-box-shadow: rgba(64, 64, 64, 0.5) 0 2px 5px;
    -moz-box-shadow: rgba(64, 64, 64, 0.5) 0 2px 5px;
    box-shadow: rgba(64, 64, 64, 0.1) 0 2px 5px;
    width: 890px; /* */
	margin-left:auto;
	margin-right:auto;
	height:400px; 
	overflow:hidden;
}    
    
#map_canvas
{
    width: 620px; 
    height: 400px;
    margin-top:-50px;
}

#preloader
{
    position: relative;
    top: 60px;
    text-align: center;
	width:200px;
	height:50px;
}

</style> 

<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyD9RHlLKYGsbelja9DzYOHq1YpNMFFbMDE&sensor=false"></script>

<script type="text/javascript" src="/data_user/newmang/_Map/markerclusterer_compiled.js"></script>

<script src="http://www.google.com/jsapi"></script>

<script type="text/javascript" src="/cwis438/Includes/JQuery/jquery-1.5.2.min.js"></script>
<script src="/cwis438/Includes/JQuery/jquery-1.5.2.min.js" type="text/javascript"></script>
<script src="/cwis438/Includes/Highcharts/highcharts.js" type="text/javascript"></script>

<script type='text/javascript' src='/cwis438/Includes/Highcharts/leaf.js'></script>

<!-- tooltip -->

<script src="/cwis438/Includes/JQuery/jquery-ui-1.10.4.custom.js" type="text/javascript"></script>

<script type="text/javascript">

var infowindow = null;
var map;
var geocoder;
var marker = null;
var markersArray = [];
var myLatLng = null;
var myLat;
var myLng;

var NumVisits=parseInt("<?php echo $NumVisits;?>");

var NumAreas=parseInt("<?php echo $NumAreas;?>");

var WebSiteID=20;

var NumAreasReturned=null;

var RealData=0;

var markers=[];

var UserZoom=true;

$(document).ready(function()
{
    //alert("Initializing...");
    
	infowindow = new google.maps.InfoWindow({content: "loading..."});
    
    var MapCenter = new google.maps.LatLng(9.03,38.74);  //  Addis 

    var myOptions =
    {
        zoom: 4,
        center: MapCenter,
        mapTypeId: google.maps.MapTypeId.TERRAIN,
        zoomControlOptions:
		{
			style: google.maps.ZoomControlStyle.SMALL
		}
    }

    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions); // map

    var bounds = new google.maps.LatLngBounds();
    
    var InitialBounds="((-90.0, -180.0), (90.0, 180.0))"; // Initial Bounds for entire world; get all project data for initial load
    
    var url='/cwis438/webservices/GetData_Scalable.php?ZoomLevel=8&Bounds='+InitialBounds; //&ZoomLevel='+ZoomLevel+'&Bounds='+Bounds

    var Project_ImageURL='/cwis438/includes/GoogleMapsJS_API_V3/images/YellowDot75Percent.png';
    
    if (NumAreas>5000)
    {
        url='/cwis438/webservices/GetData_Scalable.php?ZoomLevel=8&Bounds='+InitialBounds; //&ZoomLevel='+ZoomLevel+'&Bounds='+Bounds
        Project_ImageURL='/cwis438/includes/GoogleMapsJS_API_V3/images/YellowDot75Percent.png';
    }
    else
    {
    	url='/cwis438/webservices/GetData.php?WebSiteID=20';
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
});

function ShowInfoWindow() 
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
    	
    	var NewZoomLevel=ZoomLevel+3; // +2?
    	//var NewZoomLevel=10; // 8?
    	
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
        
        var newurl='/cwis438/webservices/GetData_Scalable.php?ZoomLevel='+NewZoomLevel+'&Bounds='+NewBounds; //&ZoomLevel='+ZoomLevel+'&Bounds='+Bounds

        if (NumAreas>5000) // was NumAreas>5000 but that was remembering total project num areas always...
        {
            newurl='/cwis438/webservices/GetData_Scalable.php?ZoomLevel='+NewZoomLevel+'&Bounds='+NewBounds; //&ZoomLevel='+ZoomLevel+'&Bounds='+Bounds
            NewProject_ImageURL='/cwis438/includes/GoogleMapsJS_API_V3/images/YellowDot75Percent.png';
        }
        else
        {
        	newurl='/cwis438/webservices/GetData.php?WebSiteID=20';
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
        
        //var NewNumAreasReturned=NewDataPoints.length;
        //alert("NewNumAreasReturned==="+NewNumAreasReturned);

        var NewTitle="Click to zoom in...";
        
        for (var i=0; i<NewDataPoints.length; i++)
        { 
            var NewObject = NewDataPoints[i]; 
            
            var NewX=NewObject['X'];
        
            var NewY=NewObject['Y'];

            var NewAreaName=NewObject['AreaName'];
            
            if (RealData>0)
            {
            	//var NewAreaID=NewObject['AreaID'];
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

        //success: function() {
		//$("#preloader").hide();
	    //}

        $("#preloader").hide();
    }
    else
    {
        // do nothing...
        //alert("not user zoom...");
    }
    
    UserZoom=true;
}

function FindAndShowSelectedPoint()
{
    
    // get the user selected value typed into the text entry search box...

    var SelectedPointID=document.getElementById('pointsearch'); 
    
    //alert("you entered: "+SelectedPointID.value);
    
    // query for tree entered into text entry search box...

    var url='/cwis438/websites/CitSci/webservices/GetPointData.php?AreaName='+SelectedPointID.value;
    
    var data = jQuery.parseJSON(
        jQuery.ajax({
            url: url, 
            async: false,
            dataType: 'json'
        }).responseText
    );

    var PointData=data['PointData']; 

    var FoundPointLatitude=PointData['Y'];
    
    var FoundPointLongitude=PointData['X']; 

    var AreaName=PointData['AreaName'];

    var AreaID=PointData['AreaID'];

    var icon = new google.maps.MarkerImage("/cwis438/websites/MyTreeTracker/images/icons/GreenBlueHaloDot.png");
    
    //create new single marker object for the found point

    var siteLatLng = new google.maps.LatLng(FoundPointLatitude,FoundPointLongitude);
    
        //var sites = markers[i];
        var marker = new google.maps.Marker({
            position: siteLatLng,
            icon: icon,
            map: map,
            zIndex: -9999

        });

        map.setZoom(10);
        map.panTo(marker.position);
}
</script>
   
<script>
    
//  HIGHCHARTS - Plant Observations

$(document).ready(function() {
    chart = new Highcharts.Chart({
        chart:
        {
            renderTo: 'PlantObservationsContainer',
            type: 'area'
        },
        title:
        {
            text: 'Plant Observations'
        },
        xAxis:
        {
            categories: [
            <?php 
                $OrganismsByYearSet=$Database->Execute($ObservationsSelectString);
	         	$i=1;
	         	while ($OrganismsByYearSet->FetchRow())
	         	{
	         		$Separator=",";
		            if ($i==$Count3) $Separator="";
	         		$YearLabel=$OrganismsByYearSet->Field("Year");
			        echo("'$YearLabel'"."$Separator");
			        $i++;
	         	} 

            ?>
            ]
        },
        yAxis:
        {
            title:
            {
                text: '# Observations'
            }
        },
        plotOptions:
        {
            area: {
                //stacking: 'normal',
                lineColor: '#666666',
                lineWidth: 1,
                marker:
                {
                    lineWidth: 1,
                    lineColor: '#666666'
                }
            }
        },
        series: 
        [
            {
            showInLegend: false,
            name: 'Plant Observations',
            data: [
                    <? 
                    $OrganismsByYearSet=$Database->Execute($ObservationsSelectString);
                    $i=1;
                    $CumulativeSpeciesObservations=0;
                    while ($OrganismsByYearSet->FetchRow())
                    {
                        $NumSpeciesObservations=$OrganismsByYearSet->Field("Observations");
                        if ($NumSpeciesObservations==null) $NumSpeciesObservations=0;
                        
                        $CumulativeSpeciesObservations=$CumulativeSpeciesObservations+$NumSpeciesObservations;
                             		
                        $Separator=",";
                        if ($i==$Count3) $Separator="";
                        	        
                        echo("$CumulativeSpeciesObservations"."$Separator");
                        $i++;
                    }
                    
                    ?>
                  ]
            }
        ]
    });
});    

//  HIGHCHARTS - PROJECTS

$(document).ready(function() {
    chart = new Highcharts.Chart({
        chart:
        {
            renderTo: 'ProjectsContainer',
            type: 'area'
        },
        title:
        {
            text: 'LEAF Projects'
        },
        xAxis:
        {
            categories: [
            <?php 
                $ProjectsSet=$Database->Execute($ProjectsSelectString);
	         	$i=1;
	         	while ($ProjectsSet->FetchRow())
	         	{
	         		$Separator=",";
		            if ($i==$CountProject) $Separator="";
	         		$YearLabel=$ProjectsSet->Field("Year");
			        echo("'$YearLabel'"."$Separator");
			        $i++;
	         	} 

            ?>
            ]
        },
        yAxis:
        {
            title:
            {
                text: '# Projects'
            },
            allowDecimals: false
        },
        plotOptions:
        {
            area: {
                //stacking: 'normal',
                lineColor: '#666666',
                lineWidth: 1,
                marker:
                {
                    lineWidth: 1,
                    lineColor: '#666666'
                }
            }
        },
        series: 
        [
            {    
            showInLegend: false,
            name: 'Projects',
            data: [
                    <? 
                    $ProjectsSet=$Database->Execute($ProjectsSelectString);
                    $i=1;
                    $CumulativeProjects=0;
                    while ($ProjectsSet->FetchRow())
                    {
                        $NumProjects=$ProjectsSet->Field("Projects");
                        if ($NumProjects==null) $NumProjects=0;
                        
                        $CumulativeProjects=$CumulativeProjects+$NumProjects;
                             		
                        $Separator=",";
                        if ($i==$CountProject) $Separator="";
                        	        
                        echo("$CumulativeProjects"."$Separator");
                        $i++;
                    }
                    
                    ?>
                  ]
            }
        ]
    });
});    

// HighCharts: TOP 5 Species

$(document).ready(function() {
   chart = new Highcharts.Chart({
      chart: {
         renderTo: 'Top5SpeciesContainer',
         plotBackgroundColor: null,
         plotBorderWidth: null,
         plotShadow: false
      },
      title: {
         text: 'Top 5 Most Commonly Observed Species (%)'
      },
      tooltip: {
         formatter: function() {
            return '<b>'+ this.point.name +'</b>: '+ Math.round(this.percentage) +' %';
         }
      },
      plotOptions: {
         pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            size: '75%',
            dataLabels: {
               enabled: true
            },
            showInLegend: true
         }
      },
       series: [{
         type: 'pie',
         name: 'Browser share',
         data:
         [
            <? 
            
            $Set=$Database->Execute($Top5SpeciesSelectString);
            
            $i=1;
            
            while ($Set->FetchRow()) 
            {
            	$Separator=",";
            	if ($i==5) $Separator="";
            	$Name=TBL_OrganismInfos::GetName($Database,$Set->Field(1),true);
                $remove[] = "'"; $remove[] = '"'; $remove[] = "-"; $remove[] = ".";  
                $Name = str_replace($remove,"",$Name);
            	$Value=$Set->Field(2);
            	$Value1=round($Value,2);
	            ?>
	         	['<?echo ($Name); ?>', <? echo($Value1); ?> ]<?echo($Separator)?>
	            
	            <?
	            $i++;
            }
            ?>
         ]
      }]
   });
});

// HighCharts: TOP 5 Projects

$(document).ready(function() {
   chart = new Highcharts.Chart({
      chart: {
         renderTo: 'Top5ProjectsContainer',
         plotBackgroundColor: null,
         plotBorderWidth: null,
         plotShadow: false
      },
      title: {
         text: 'Top 5 Contributing Projects (%)'
      },
      tooltip: {
         formatter: function() {
            return '<b>'+ this.point.name +'</b>: '+ Math.round(this.percentage) +' %';
         }
      },
      plotOptions: {
         pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            size: '75%',
            dataLabels: 
            {
               enabled: true

            },
            showInLegend: true
         }
      },
       series: [{
         type: 'pie',
         name: 'Browser share',
         data:
         [
            <? 
            
            $ProjectsSet=$Database->Execute($Top5ProjectsSelectString);
            
            $i=1;
            
            while ($ProjectsSet->FetchRow()) 
            {
            	$Separator=",";
            	if ($i==5) $Separator="";
            	$ProjName=$ProjectsSet->Field("ProjName");
                $remove[] = "'"; $remove[] = '"'; $remove[] = "-"; $remove[] = ".";  
                $ProjName = str_replace($remove,"",$ProjName);
            	$Value=$ProjectsSet->Field("Observations");
            	$Value1=round($Value,2);
	            ?>                   
	         	['<?echo ($ProjName); ?>', <? echo($Value1); ?> ]<?echo($Separator)?>
	            
	            <?
	            $i++;
            }
            ?>
         ]
      }]
   });
});
    
</script>    



<?php

$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart();

echo ("<h2 style='width:60%; font:bold 18px trebuchet ms; color:#3A5C83;'>MAP (All Plant Occurrences)</h2>");

?>

<div id="map-container">
      
      <div id='preloader'>Loading map...</div> 
      
      <!-- ---------------------- main map ---------------------- -->
      <div id="map_canvas" style='position:relative; float:left;'></div>
      <!-- ---------------------- main map ---------------------- -->
      
      <!-- ----------------------  location info  ---------------------- -->
      <div id="legend" style="margin-top:-50px; position:relative; display:block; float:right; top:0px; right:0px; width:264px; height:400px; background-color:#C3BC9F;"> 
          <div style="padding:6px; color:#484727;">
              <h2>Legend</h2>
              <div style='line-height:14px; width:272px; height:14px; text-align:left;'>
                <?php
                    echo("<img src='/cwis438/websites/CitSci/Map/images/OrangeBlackOutlineDot.png'></img> Single Observation<br><br>");
                    echo("<img src='/cwis438/includes/GoogleMapsJS_API_V3/images/YellowDot75Percent.png'></img> Clustered Observations<br><br>");
                ?>
              </div>
              
              <!-- Map search by location name -->
              
              <div id='pointsearchcontainer' style='margin-top:80px; font-size:14px;'>
                    <label style='font-size:14px;' for='pointsearch'>Search by Observation Name:</label>
                    <input type='text' id='pointsearch' title='Search' placeholder='Example: Site 1' style='width:150px; margin-top:6px;'/>
                    <input type='button' style='width:65px; height:22px; font-size:14px; margin-top:0px; border: none; padding: 0;' class='registerbutton' onclick='FindAndShowSelectedPoint();' value='Search'/>
              </div>
                           
              <!-- Reset Map: Currently this reloads the entire page, instead of just the map...ideas for reloading map when encompassed in document.ready function? -->
              <div style='margin-top:30px;'>
                    <input type='button' style='width:100px; height:22px; margin-left:70px; margin-top:2px; font-size:14px; border: none; padding: 0;' class='registerbutton' onclick='window.location.reload();' value='Reset Map'/> 
              </div>
              
          </div>
      </div>
      <!-- ----------------------  location info  ---------------------- -->
      
      <br style="clear:all;" />
    
</div>

<?php

//  OBSERVATIONS

echo("<div style='height:450px;'>");
    echo ("</br><h2 style='width:60%; font:bold 18px trebuchet ms; color:#3A5C83;'>OBSERVATIONS</h2>");
    
    // Observations/year chart
    echo("<div id='PlantObservationsContainer' style='display:inline-block; width:300px; height:200px; float:left; margin-left:0px; margin-right:30px;'></div>"); //margin-top:50px; margin-left:10px;
    
    // Top 5 species chart
    
    echo("<div id='Top5SpeciesContainer' style='float:right; display:inline-block; width:580px; height:350px;'></div>");
    
echo("</div>");



//  PROJECTS

echo("<div style='height:400px;'>");
    echo ("</br><h2 style='width:60%; font:bold 18px trebuchet ms; color:#3A5C83;'>PROJECTS</h2>");
    
    echo("<div id='ProjectsContainer' style='display:inline-block; width:300px; height:200px; float:left; margin-right:30px; margin-left:0px;'></div>"); //margin-top:50px; margin-left:10px;
    
    // Top 5 Projects chart
    
    echo("<div id='Top5ProjectsContainer' style='float:right; display:inline-block; width:580px; height:350px;'></div>");
    
echo("</div>");


$ThePage->BodyEnd();

?>
