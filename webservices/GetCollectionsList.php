<?php

//**************************************************************************************
// FileName: GetCollectionsList.php
//
//**************************************************************************************

//**************************************************************************************
// Includes
//**************************************************************************************
require_once("C:/Inetpub/wwwroot/utilities/ServerUtil.php");

//**************************************************************************************
// Server-side functions
//**************************************************************************************

// Indexed column (used for fast and accurate table cardinality)
$sIndexColumn = "Name";

// DB table to use
$sTable = "TBL_Collections";

// Database connection information
$gaSql['user'] = "sa";
$gaSql['password'] = "cheatgrass";
$gaSql['db'] = "invasive";

$ComputerName=strtolower(GetComputerName());
$gaSql['server']=$ComputerName;

// Columns

$aColumns = array("ID","Name","Citation","WebsiteID","DateAdded","DateModified");

// ODBC connection

$connectionInfo = array("UID" => $gaSql['user'], "PWD" => $gaSql['password'], "Database"=>$gaSql['db'],"ReturnDatesAsStrings"=>true);
$gaSql['link'] = sqlsrv_connect( $gaSql['server'], $connectionInfo);

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
if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" ) {
$sWhere = "WHERE (";
for ( $i=0 ; $i<count($aColumns) ; $i++ ) {
$sWhere .= $aColumns[$i]." LIKE '%".addslashes( $_GET['sSearch'] )."%' OR ";
}
$sWhere = substr_replace( $sWhere, "", -3 );
$sWhere .= ')';
}

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
    
    $Date=$aRow[$aColumns[4]];
    
    // convert date
    
    $Date=date("F j, Y", strtotime($Date));    
    
    //  Jim's Date code
        //$Date=new Date;
        //$Date->SetDateFromSQLString($Date);
        //$Date=$Date->GetCalender();
    
    $row[4]=$Date;
    
    $CollectionID=$aRow[$aColumns[0]]; 
    
    $Link1="<a href='javascript:DoViewCollection($CollectionID)' class='registerbutton' style='width:125px; color:#000000;'>View Collection</a>";
    
    $row[].=$Link1;
    
    //$row[].=$Link2;
    
    If (!empty($row)) { $output['aaData'][] = $row; }
}
 
echo json_encode( $output );

?>
