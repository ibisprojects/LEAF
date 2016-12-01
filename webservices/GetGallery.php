<?php

//**************************************************************************************
// FileName: GetGallery.php
// Purpose: To get the media from flickr for LEAF species profile - Gallery Tab
//**************************************************************************************

//**************************************************************************************
// Includes
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");
require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");
require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Permissions.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_OrganismInfos.php");
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_OrganismData.php");

//**************************************************************************************
// Database
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Parameters
//**************************************************************************************

$OrganismInfoID=GetIntParameter("OrganismInfoID");

//**************************************************************************************
// Server-side functions
//**************************************************************************************

$SelectString="SELECT TBL_OrganismInfos.Name, TBL_OrganismInfos.Description, TBL_OrganismInfos.Habitat, TBL_OrganismInfos.UniqueFeatures, TBL_Media.FilePath, 
                      TBL_Media.ID AS MediaID
                FROM REL_MediaToOrganismInfo INNER JOIN
                    TBL_Media ON REL_MediaToOrganismInfo.MediaID = TBL_Media.ID RIGHT OUTER JOIN
                    TBL_OrganismInfos ON REL_MediaToOrganismInfo.OrganismInfoID = TBL_OrganismInfos.ID
                WHERE (TBL_OrganismInfos.ID = $OrganismInfoID)";

$Set=$Database->Execute($SelectString);

$Name=$Set->Field("Name");
$Description=$Set->Field("Description");
$UniqueFeatures=$Set->Field("UniqueFeatures");
$Habitat=$Set->Field("Habitat");
$MediaID=$Set->Field("MediaID");
$FilePath=$Set->Field("FilePath");

//**************************************************************************************
// EOL
//**************************************************************************************

$TSNSet=REL_OrganismInfoToTSN::GetSet($Database,$OrganismInfoID);
$TSN=$TSNSet->Field("TSN");

$SciName=TBL_TaxonUnits::GetScientificNameFromTSN($Database,$TSN);

?>

<link rel="stylesheet" href="/cwis438/Websites/LEAF/stylesheets/jquery-ui.css" />

<style>
.FlickrImage
{
    height: 100px;
    float: left;
	margin-left:4px;
	margin-top:4px;
	/*width: 160px;*/
}

</style>

<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

<script>

(function()
{
    var SciName="<?php echo($SciName);?>";
	var flickerAPI = "http://api.flickr.com/services/feeds/photos_public.gne?jsoncallback=?";
	
	$.getJSON(flickerAPI,{tags:SciName,tagmode:"any",format:"json"}).done( // tags: "mount ranier"
			function(data) 
			{
				$.each(data.items,function(i,item) 
						{
				             $("<img class='FlickrImage'>").attr("src",item.media.m).appendTo("#images");
				             if ( i === 9 ) // 3
					         {
					             return false;        
					         }
					     }
			     );
			 });
	 })();

</script>

<?php

//**************************************************************************************
// HTML
//**************************************************************************************

echo("<h2>Gallery</h2>");

//----------------------------------------------
// add description from db if any exists...
//----------------------------------------------

//echo("<div id='Wikipedia_Link' style='margin-bottom:12px;'></div>");

echo("<div style='height:200px; width:100%; border:solid 1px white;'>");

echo("<div id='images'></div>");

echo("</div>");

echo("<br/>");

echo("Images for $SciName provided by Flickr");

echo("<br/>");

echo("<br/>");

//

?>


