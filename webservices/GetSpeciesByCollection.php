<?php

//**************************************************************************************
// FileName: GetLEAFSpeciesList.php
//
//**************************************************************************************

//**************************************************************************************
// Includes
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/utilities/ServerUtil.php");
require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");


//**************************************************************************************
// Server-side functions
//**************************************************************************************

$CollectionID=GetIntParameter("CollectionID");

//DebugWriteln("CollectionID=$CollectionID");

//**************************************************************************************
// Server-side functions
//**************************************************************************************

// Indexed column (used for fast and accurate table cardinality)
$sIndexColumn = "OrganismInfoID";

// DB table to use
$sTable = "View_LEAF_SpeciesByCollection";

// Database connection information
$gaSql['user'] = "sa";
$gaSql['password'] = "cheatgrass";
$gaSql['db'] = "invasive";

$ComputerName=strtolower(GetComputerName());
$gaSql['server']=$ComputerName;

// Columns
// If you don't want all of the columns displayed you need to hardcode $aColumns array with your elements.
// If not this will grab all the columns associated with $sTable

$aColumns = array("SciName","Name","Family","OrganismInfoID","CollectionID");

// 
// ODBC connection
//

$connectionInfo = array("UID" => $gaSql['user'], "PWD" => $gaSql['password'], "Database"=>$gaSql['db'],"ReturnDatesAsStrings"=>true);
$gaSql['link'] = sqlsrv_connect( $gaSql['server'], $connectionInfo);

//$gaSql['link']=NewConnection(INVASIVE_DATABASE);

$params = array();
$options = array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

// Ordering 
$sOrder = "";
if ( isset( $_GET['iSortCol_0'] ) ) {
$sOrder = "ORDER BY ";
for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ ) {
if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" ) {
$sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
".addslashes( $_GET['sSortDir_'.$i] ) .", ";
}
}
$sOrder = substr_replace( $sOrder, "", -2 );
if ( $sOrder == "ORDER BY" ) {
$sOrder = "";
}
}

// Filtering 

$sWhere = "";
if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" ) 
{
    $sWhere = "WHERE (";
    for ( $i=0 ; $i<count($aColumns) ; $i++ ) 
    {
        $sWhere .= $aColumns[$i]." LIKE '%".addslashes( $_GET['sSearch'] )."%' OR ";
    }
    $sWhere = substr_replace( $sWhere, "", -3 );
    $sWhere .= ')';
}

$sWhere.= "WHERE CollectionID=$CollectionID";


// Individual column filtering

for ( $i=0 ; $i<count($aColumns) ; $i++ ) {
if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' ) {
if ( $sWhere == "" ) {
$sWhere = "WHERE ";
} else {
$sWhere .= " AND ";
}
$sWhere .= $aColumns[$i]." LIKE '%".addslashes($_GET['sSearch_'.$i])."%' ";
}
}

// Paging 
$top = (isset($_GET['iDisplayStart']))?((int)$_GET['iDisplayStart']):0 ;
$limit = (isset($_GET['iDisplayLength']))?((int)$_GET['iDisplayLength'] ):10;
$sQuery = "SELECT TOP $limit ".implode(",",$aColumns)."
FROM $sTable
$sWhere ".(($sWhere=="")?" WHERE ":" AND ")." $sIndexColumn NOT IN
(
SELECT $sIndexColumn FROM
(
SELECT TOP $top ".implode(",",$aColumns)."
FROM $sTable
$sWhere
$sOrder
)
as [virtTable]
)
$sOrder";

$rResult = sqlsrv_query($gaSql['link'],$sQuery) or die("$sQuery: " . sqlsrv_errors());

$sQueryCnt = "SELECT * FROM $sTable $sWhere";

$rResultCnt = sqlsrv_query( $gaSql['link'], $sQueryCnt ,$params, $options) or die (" $sQueryCnt: " . sqlsrv_errors());

$iFilteredTotal = sqlsrv_num_rows( $rResultCnt );

$sQuery = " SELECT * FROM $sTable ";

$rResultTotal = sqlsrv_query( $gaSql['link'], $sQuery ,$params, $options) or die(sqlsrv_errors());

$iTotal = sqlsrv_num_rows( $rResultTotal );

$output = array(
"sEcho" => intval($_GET['sEcho']),
"iTotalRecords" => $iTotal,
"iTotalDisplayRecords" => $iFilteredTotal,
"aaData" => array()
);

while ($aRow=sqlsrv_fetch_array($rResult))
{   
    $row=array();
    
    for ( $i=0 ; $i<count($aColumns) ; $i++ )
    {   
        if ( $aColumns[$i] != ' ' )
        {   
            $v = $aRow[ $aColumns[$i] ];
            
            $row[]=$v;
        }
    }
    
    //$OrganismInfoID=$aRow[$aColumns[3]]; // this is the third column in nthe table (view) and it represents OrganismInfoID
    
    //$Link1="<a href='/cwis438/Websites/LEAF/Species_Profile.php?OrganismInfoID=$OrganismInfoID&WebSiteID=20' class='registerbutton' style='width:77px; color:#000000;'>View Profile</a>";
    //$Link2="<a href='/cwis438/Websites/LEAF/Species_Profile.php?OrganismInfoID=$OrganismInfoID&WebSiteID=20' class='registerbutton' style='width:105px; color:#000000;'>Add Observation</a>";
    
   // $row[].=$Link1."&nbsp;".$Link2;
    
    //$row[].=$Link2;
    
    If (!empty($row)) { $output['aaData'][] = $row; }
}
 
echo json_encode( $output );

?>