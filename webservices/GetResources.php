<?php

//**************************************************************************************
// FileName: GetResources.php
// Purpose: To get the resources for LEAF species profile - Resources Tab
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
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/TBL_Links.php");

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

$UserID=getUserID();

$MyCallingPage="/cwis438/websites/LEAF/Species_Profile.php".
        "?OrganismInfoID=$OrganismInfoID".
        "&TakeAction=Returned&WebSiteID=20#resources_tab";

$MyCallingPage=urlencode($MyCallingPage);

$MyCallingLabel="To Species Profile";

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

$SciNameEOL=str_replace(" ","+",$SciName);

$jsonurl="http://eol.org/api/search/1.0.json?q=$SciNameEOL&page=1&exact=true&filter_by_taxon_concept_id=&filter_by_hierarchy_entry_id=&filter_by_string=&cache_ttl=";
$json=file_get_contents($jsonurl,0,null,null);
$json_output=json_decode($json);

foreach ( $json_output->results as $objects )
{
    $eol_id=$objects->id;
}

?>

<link rel="stylesheet" href="/cwis438/Websites/LEAF/stylesheets/jquery-ui.css" />

<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

<script>

$(document).ready(function()
{
	//------------
	// EOL
	//------------

    var eol_id=<?php echo($eol_id);?>;

    var eol_url='http://eol.org/api/pages/1.0/'+eol_id+'.json?images=2&videos=0&sounds=0&maps=0&text=2&iucn=false&subjects=overview&licenses=all&details=true&common_names=true&callback=?';
    
    $.getJSON(eol_url,function(EOLData)
    {
        var EOL_Link="<p><a href='http://eol.org/pages/"+eol_id+"/overview' target='NewWindow' style='text-decoration:none;'>Encylopedia of Life</a></p>";
        
        $("#EOL_Link").append(EOL_Link);
    });

	//------------
	// wikipedia
	//------------

    var SearchTerm="<?php echo($SciName);?>";

    var url="http://en.wikipedia.org/w/api.php?action=query&prop=extracts&exintro=1&exchars=300&titles="+SearchTerm+"&redirects=0&format=json&callback=?";
    
    $.getJSON(url,function(DescriptionData)
    {
            var Wikipedia_Link="<p><a href='http://en.wikipedia.org/wiki/"+SearchTerm+"' target='NewWindow' style='text-decoration:none;'>Wikipedia</a></p>";
            
            $("#Wikipedia_Link").append(Wikipedia_Link);
    });

    $("#resources_tab").animate({height:$("#Wikipedia_Link").height()+580},600); // 330; was 242; 220 (ht of map) + 12 buffer for bottom of div
    
}); // end of document.ready function...

</script>

<?php

//**************************************************************************************
// HTML
//**************************************************************************************

echo("<h2>Online Resources</h2>");

//----------------------------------------------
// show default links to Wikipedia, EOL, Flickr
//----------------------------------------------

echo("<div id='Wikipedia_Link' style='margin-bottom:12px;'></div>");

echo("<div id='EOL_Link' style='margin-bottom:12px;'></div>");

echo("<div id='Flickr_Link'><a href='http://www.flickr.com/search/?q=$SciName' target='NewWindow' style='text-decoration:none;'>Flickr Photos</a></div>");

echo("<br/>");

//---------------------------------------------------------
// Query for/show related links to this organisminfo record
//---------------------------------------------------------

$LinkSet=TBL_Links::GetSet($Database,$PersonID=null,$OrganismInfoID,$AreaID=null,$ProjectID=null);

echo("<h2>Additional LEAF Resources</h2>");

if ($LinkSet->FetchRow())
{
    echo("Below is a list of additional resources for $SciName tha have been suggested and contributed by our growing team of LEAF experts.");
    
    echo("<br/>");
    
    echo("<br/>");
    
    $LinkSet=TBL_Links::GetSet($Database,$PersonID=null,$OrganismInfoID,$AreaID=null,$ProjectID=null);
    
    while($LinkSet->FetchRow())
    {
        $Label=$LinkSet->Field("Label");
        $URL=$LinkSet->Field("URL");
        echo("<a href='$URL' target='NewWindow'>$Label</a>");
        
        echo("<br/>");
    }
    
    echo("<br/>");
}
    
if (TBL_Permissions::HasPermission($Database,4,$UserID)) // 4 is expert right?
{
    $Link1=GetLink("/cwis438/contribute/link_edit.php","Add Resource",
            "TakeAction=New".
            "&WebSiteID=20".
            "&OrganismInfoID=$OrganismInfoID".
            "&CallingPage=$MyCallingPage".
            "&CallingLabel=$MyCallingLabel",null,"class='registerbutton' style='width:100px;'");

    $Link2=GetLink("/cwis438/contribute/Link_List.php","Edit My Resources",
            "OrganismInfoID=$OrganismInfoID".
            "&WebSiteID=20".
            "&CallingPage=$MyCallingPage".
            "&CallingLabel=$MyCallingLabel",null,"class='registerbutton' style='width:120px;'");

    echo($Link1."&nbsp;|&nbsp;".$Link2);
}

echo("<br/>");

?>


