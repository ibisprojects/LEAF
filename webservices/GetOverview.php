<?php

//**************************************************************************************
// FileName: GetOverview.php
// Purpose: To get the overview for LEAF species profile - Overview Tab
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
require_once("C:/Inetpub/wwwroot/cwis438/Classes/DBTable/LKU_Ranks.php");

//**************************************************************************************
// Database
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// Parameters
//**************************************************************************************

$OrganismInfoID=GetIntParameter("OrganismInfoID");

$CallingPage="/cwis438/websites/LEAF/Species_Profile.php?OrganismInfoID=$OrganismInfoID&WebSiteID=20";
$CallingLabel=urlencode("To Species Profile");

//**************************************************************************************
// Server-side functions
//**************************************************************************************

$SelectString="SELECT TBL_OrganismInfos.Name, TBL_OrganismInfos.Description, TBL_OrganismInfos.Habitat, TBL_OrganismInfos.UniqueFeatures, TBL_Media.FilePath, 
                      TBL_Media.ID AS MediaID, TBL_OrganismInfos.RankID
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
$Status=$Set->Field("RankID");
   

//**************************************************************************************
// EOL
//**************************************************************************************

//$Set=TBL_OrganismInfos::GetSetFromID($Database,$OrganismInfoID);
//$CommonName=$Set->Field("Name");

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

// wikipedia

//http://en.wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=content&format=json&titles=$SciName";

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
    
    //console.log(eol_url);
    
    $.getJSON(eol_url,function(EOLData)
    {
        var EOL_FullDescription=EOLData.dataObjects[0].description;

        // replace all <br> tags if prsent in original EOL text
        
        var regex = new RegExp("<br>",'gi');
        
        EOL_FullDescription=EOL_FullDescription.replace(regex," ");

        // truncate string at 300 characters...
        
        var EOL_ShortDescription=EOL_FullDescription.substring(0,300);

        var EOL_Description="<p>"+EOL_ShortDescription+"... <span style=''>Source: <a href='http://eol.org/pages/"+eol_id+"/overview' target='NewWindow' style='text-decoration:none;'>Encyclopedia of Life</a></span></p>";
        
        $("#EOL_Description").append(EOL_Description);

        //alert("hi"+EOLData.dataObjects[1].IUCNConservationStatus); //EOLData.identifier, EOLData.scientificName, dataObjects[0].identifier
    });

	//------------
	// wikipedia
	//------------

    var SearchTerm="<?php echo($SciName);?>";

    // Get description/introductory text from page article
    
    //var url1="http://en.wikipedia.org/w/api.php?action=parse&format=json&page="+searchTerm1+"&prop=text&section=0&callback=?";
    //var url2="http://en.wikipedia.org/w/api.php?action=query&prop=extracts&titles="+searchTerm1+"&format=json&exintro=1&callback=?";
    //var url3="http://en.wikipedia.org/w/api.php?action=query&prop=extracts&exintro=1&exchars=300&titles="+searchTerm1+"&format=json&callback=?";
    //var url4="http://en.wikipedia.org/w/api.php?action=query&prop=revisions&rvprop=content&format=json&titles=Acacia%20abyssinica&rvsection=0";
    var url="http://en.wikipedia.org/w/api.php?action=query&prop=extracts&exintro=1&exchars=300&titles="+SearchTerm+"&redirects=0&format=json&callback=?";
    
    $.getJSON(url,function(DescriptionData)
    {
        var Page=DescriptionData.query.pages;
        
        for(var prop in Page) if(Page.hasOwnProperty(prop))
        {
            var value=Page[prop];

            if (value.extract)
            {
                var Full_Wikipedia_Description=value.extract;
    
                var Wikipedia_Description=Full_Wikipedia_Description.substring(0,Full_Wikipedia_Description.length-3);
                Wikipedia_Description=Wikipedia_Description.replace("<p>","");
                Wikipedia_Description=Wikipedia_Description.replace("</p>","");
                //Wikipedia_Description=Wikipedia_Description.replace("<b>","");
                //Wikipedia_Description=Wikipedia_Description.replace("</b>","");
                var regex = new RegExp("<b>",'gi');
                Wikipedia_Description = Wikipedia_Description.replace(regex,"");
                var regex = new RegExp("</b>",'gi');
                Wikipedia_Description = Wikipedia_Description.replace(regex,"");
                
                Wikipedia_Description="<p>"+Wikipedia_Description+"... <span style=''>Source: <a href='http://en.wikipedia.org/wiki/"+SearchTerm+"' target='NewWindow' style='text-decoration:none;'>Wikipedia</a></span></p>";
                
                $("#Wikipedia_Description").append(Wikipedia_Description);
            }
        }

        //$("#overview_tab").animate({height:$("#Wikipedia_Description").height()+500},600); // 330; was 242; 220 (ht of map) + 12 buffer for bottom of div
        
    });

    // get first image from infobox on page
    
    var url="http://en.wikipedia.org/w/api.php?action=parse&format=json&page="+SearchTerm+"&redirects&prop=text&callback=?";

    //alert(url);
    
    $.getJSON(url,function(data)
    {
        if (data.parse)
        {
              wikiHTML = data.parse.text["*"];
              var readData = $('<div>' + data.parse.text["*"] + '</div>');
              var box = readData.find('.infobox');
              var imageURL = box.find('img').first().attr('src'); // null
        
              // Check if page has images
        
              if (imageURL.length>0)
              {
          	    $("#photo").append("<img src='"+imageURL+"'/>");
              }
              else
              {
            	  $("#photo").append("<img src='/cwis438/images/NoPhoto.jpg' width='220' height='147' />&nbsp;");
              }
        }
        else
        {
        	$("#photo").append("<img src='/cwis438/images/NoPhoto.jpg' width='220' height='147' />&nbsp;");
        }
    });
    
    //$("#overview_tab").animate({height:$("#Wikipedia_Description").height()+$("#Wikipedia_Description").height()+$("#EOL_Description").height()+$("#LEAF_Description").height()+100},600); // 330; was 242; 220 (ht of map) + 12 buffer for bottom of div
    
    //$("#overview_tab").animate({height:$("#Wikipedia_Description").height()+580},600); // 330; was 242; 220 (ht of map) + 12 buffer for bottom of div

    //alert($("#Wikipedia_Description").height());

    // ------------------------------------------------------------------------------------------------
    // now that we have loaded the html into the page, go call the google maps initialize function...
    // ------------------------------------------------------------------------------------------------
    
    initialize();
    
}); // end of document.ready function...

</script>

<?php

//**************************************************************************************
// HTML
//**************************************************************************************

echo("<div id='photo' style='display:inline-block; float:left; margin-right:6px; height:147px; width:220px;'></div>"); // photo div

echo("<div id='map_container'>"); // see Species_Profile for css for map_container styles...
    echo("<div id='map_canvas'>Map</div>");
echo("</div>");

echo("<div id='stats' style='display:inline-block; float:left; margin-right:6px; height:120px; width:218px; margin-top:16px;'>");

// IUCN status

// Get IUCN Names from LKU_Ranks

if ($Status!=null)
{
    $SelectString="SELECT Description
                     FROM LKU_Ranks
                     WHERE ID=$Status";

    $IUCN_Name_Set=$Database->Execute($SelectString);

    $IUCN_Status_Name=$IUCN_Name_Set->Field("Description");
}
else
{
    $IUCN_Status_Name="Unknown";
} 

switch ($Status) 
{
    case 46:  // Least Concern
        $IUCN_image="__240px-Status_iucn3_1_LC_svg.png";
        $IUCN_icon="200px-Status_iucn_LC_icon_svg.png";
        break;
    case 47:  // Near Threatened
        $IUCN_image="__240px-Status_iucn3_1_NT_svg.png";
        $IUCN_icon="200px-Status_iucn_NT_icon_svg.png";
        break;
    case 48:   // Vulnerable
        $IUCN_image="__240px-Status_iucn3_1_VU_svg.png";
        $IUCN_icon="200px-Status_iucn_VU_icon_svg.png";
        break;
    case 49:   // Endangered
        $IUCN_image="__240px-Status_iucn3_1_EN_svg.png";
        $IUCN_icon="200px-Status_iucn_EN_icon_svg.png";
        break;
    case 50:  // Critically Endangered
        $IUCN_image="__240px-Status_iucn3_1_CR_svg.png";
        $IUCN_icon="200px-Status_iucn_CR_icon_svg.png";
        break;
    case 51:   // Extinct in the wild
        $IUCN_image="__240px-Status_iucn3_1_EW_svg.png";
        $IUCN_icon="200px-Status_iucn_EW_icon_svg.png";
        break;
    case 52:   // Extinct
        $IUCN_image="__240px-Status_iucn3_1_EX_svg.png";
        $IUCN_icon="200px-Status_iucn_EX_icon_svg.png";
        break;
    case 53:   // Data Deficient
        $IUCN_image="__240px-Status_none_DD_svg.png";
        $IUCN_icon="200px-Status_iucn_DD_icon_svg.png";
        break;
    case 54:   // Not Evaluated
        $IUCN_image="__240px-Status_none_NE_svg.png";
        $IUCN_icon="200px-Status_iucn_NE_icon_svg.png";
        break;
    default:
        $IUCN_image="____240px-Status_iucn3_1_Empty.png";
        $IUCN_icon="Status_Fossil_icon_svg.png";
        $IUCN_Status_Name="Unknown";
        break;
}


    echo("<p style='margin-bottom:4px;'><img src='/cwis438/websites/LEAF/images/IUCN_Icons/$IUCN_image' width='218' /></p>"); 
    
    echo("<div style='margin-bottom:4px; border-bottom: solid 1px grey; height:40px;'>");
        echo("<div style='width:112px; float:left;'><img src='/cwis438/websites/LEAF/images/IUCN_Icons/$IUCN_icon' width='18' height='18'/>&nbsp;<span style='font-style:Arial; font-size:9pt;'>IUCN Status:</span></div>");
        echo("<div style=''><a href='http://www.iucnredlist.org/about' style='text-decoration:none; font-size:10pt;'>$IUCN_Status_Name</a></div>");
    echo("</div>");
    
    $CountString="SELECT COUNT(*) AS NumRecords 
		FROM TBL_OrganismData 
        WHERE (OrganismInfoID = $OrganismInfoID)";
    
    $NumOccurrencesSet=$Database->Execute($CountString);
    $NumOccurrences=$NumOccurrencesSet->Field("NumRecords");
    if ($NumOccurrences==0) $NumOccurrences="<span style=''>No ocurrences.</span>"; //color:#96984D;
    echo("<p>Occurrences: $NumOccurrences</p>");

    $NumCollectionsCountString="SELECT COUNT(DISTINCT CollectionID) AS NumCollections
        FROM REL_OrganismInfoToCollection
        WHERE (OrganismInfoID = $OrganismInfoID)";
    
    $NumCollectionsSet=$Database->Execute($NumCollectionsCountString);
    $NumCollections=$NumCollectionsSet->Field("NumCollections");
    if ($NumCollections==0) $NumCollections="<span style=''>Not in a collection.</span>";
    echo("<p>Collections: $NumCollections</p>");
    
    echo("<p>Projects:</p>");

echo("</div>"); // stats div; border:solid 1px grey;

echo("<br style='clear:both;'>");

echo("<div style='display:relative; height:auto; width:727px;'>"); //border:solid 1px green;

echo("<h2>Description</h2>");

//----------------------------------------------
// add description from db if any exists...
//----------------------------------------------

$LEAF_Description="";

$Description=TBL_OrganismInfos::GetFieldValue($Database,"Description",$OrganismInfoID);

if (($Description=="")||($Description==null)) // default, no description thus far...
{
    $LEAF_Description="The Living Atlas of East African Flora (LEAF) is compiling detailed species descriptions and life history information for the plants of east Africa. ";
}
else // the database has a description already entered for this organimsinfoid...
{
    if ((substr($Description,-1)!='.')&&(substr($Description,-1)!='?')&&(substr($Description,-1)!='!')) // if the entry in the DB has no punctuation, add a period...
    {
        $Description.=".";
    }
    
    $LEAF_Description=$Description; //color:#5E7630; 
}

//----------------------------------------------
// add life history from db if any exists...
//----------------------------------------------

$LifeHistory=TBL_OrganismInfos::GetFieldValue($Database,"LifeHistory",$OrganismInfoID);

if (($LifeHistory=="")||($LifeHistory==null)) // default, no decsription thus far...
{
    //do nothing...
}
else // the database has a life history entry already entered for this organimsinfoid...
{
    if ((substr($LifeHistory,-1)!='.')&&(substr($LifeHistory,-1)!='?')&&(substr($LifeHistory,-1)!='!')) // if the entry in the DB has no punctuation, add a period...
    {
        $LifeHistory.=". ";
    }

    $LEAF_Description.=" <span style='text-decoration:underline;'>Life History</span>: ".$LifeHistory; //color:#5E7630; 
}

// if eiether one is not blank... add this...

$HasDescription=false;
$HasLifeHistory=false;

if (($Description!=="")&&($Description!==null)) $HasDescription=true;
if (($LifeHistory!=="")&&($LifeHistory!==null)) $HasLifeHistory=true;

if (($HasDescription)||($HasLifeHistory))
{
    $LEAF_Description.=" <span style=''>Source: <a href='#' style='text-decoration:none;'>LEAF</a>.</span>"; //<span style='color:#96984D;'>$Description</span>
}

$HasExpertPermission=TBL_Permissions::HasPermission($Database,5); // 5 i think is expert (we use 1,3,4,5,6) (user,tester,coordinator,expert,admin)

$String="If you are an expert botanist/taxonomist with a working knowledge of <i>$SciName</i> and would like to contribute to our description and/or 
life history information, please consider submitting a <a href='/cwis438/UserManagement/RequestNewUserLevel.php?WebSiteID=20' style='text-decoration:none;'>request</a> to become
a LEAF expert today.";

if($HasExpertPermission)
{
    $String="<a href='/cwis438/contribute/OrganismInfo_Edit.php?OrganismInfoID=$OrganismInfoID&CallingPage=$CallingPage&CallingLabel=$CallingLabel' style='text-decoration:none;' class='Inline_HREF_Button'>
        Edit Description</a>";
}

echo("<div id='LEAF_Description' style='margin-bottom:12px;'><p>$LEAF_Description <span style='color:#96984D;'>$String</span></p></div>");

echo("<div id='Wikipedia_Description' style='margin-bottom:12px;'></div>");

echo("<div id='EOL_Description'></div>");

echo("<br/>");

//$MyPage=$_SERVER['PHP_SELF'];

echo("<!-- AddToAny BEGIN -->
<a class='a2a_dd' href='http://www.addtoany.com/share_save?linkurl=http%3A%2F%2Fibis.colostate.edu%2Fleaf&amp;linkname=LEAF%20Species%20Profile'><img src='http://static.addtoany.com/buttons/share_save_171_16.png' width='171' height='16' border='0' alt='Share'/></a>
<script type='text/javascript'>
var a2a_config = a2a_config || {};
a2a_config.linkname = 'LEAF Species Profile';
a2a_config.linkurl = 'http://ibis.colostate.edu$CallingPage';
</script>
<script type='text/javascript' src='//static.addtoany.com/menu/page.js'></script>
<!-- AddToAny END -->");

echo("<br/>");

?>


