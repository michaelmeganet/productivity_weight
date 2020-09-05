<?php

include_once 'class/dbh.inc.php';
include_once 'class/variables.inc.php';
include_once 'class/abstract_workpcsnew.inc.php';
include_once 'class/reverse-dimension.inc.php';
include_once 'class/phhdate.inc.php';

function debug_to_console($data) {

    if (is_array($data)) {
        $output = "<script>console.log( 'Debug Objects: " . implode(',', $data) . "' );</script>";
    } else {
        $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";

        echo $output;
    }
}

function get_job_detail($period,$sid){
    $proouttab = "production_output_".$period;
    $qr = "SELECT * FROM $proouttab WHERE sid = $sid";
    $objSQL = new SQL($qr);
    $results = $objSQL->getResultRowArray();
    #echo $qr;
    if (!empty($results)){
        return $results;
    }else{
        return "empty";
    }
}
$received_data = json_decode(file_get_contents("php://input"));
// print_r($received_data);
// echo "<br>";
// debug_to_console($received_data);
// var_dump($received_data);
## vardump get the result of
#  object(stdClass)#1 (1) {  ["action"]=>   string(9) "getPeriod"
# the response is Array(8) [ "2008", "2007", "2006", "2005", "2004", "2003", "2002", "2001" ]
$data_output = array();
$action = $received_data->action;


switch ($action) {
    case 'getPeriod':
        $objDate = new DateNow();
        $currentPeriod_int = $objDate->intPeriod();
        $currentPeriod_str = $objDate->strPeriod();

        $EndYYYYmm = 2001;
        $objPeriod = new generatePeriod($currentPeriod_int, $EndYYYYmm);
        $setofPeriod = $objPeriod->generatePeriod3();

        echo json_encode($setofPeriod);
        break;
    case 'getUnFinJobList':
        $period = $received_data->period;
        $proschtab = "production_scheduling_".$period;
        
        $qr = "SELECT * FROM $proschtab WHERE dateofcompletion IS NULL";
        $objSQL = new SQL($qr);
        $unfinData = $objSQL->getResultRowArray();
        echo json_encode($unfinData);
        break;
    case 'getFinJobList':
        $period = $received_data->period;
        $proschtab = "production_scheduling_".$period;
        
        $qr = "SELECT * FROM $proschtab WHERE dateofcompletion IS NOT NULL";
        $objSQL = new SQL($qr);
        $finData = $objSQL->getResultRowArray();
        echo json_encode($finData);
        break;
    case 'getUnFinJobDetail':
        $period = $received_data->period;
        $sid = $received_data->sid;
        $detailData = get_job_detail($period, $sid);
        
        echo json_encode($detailData);
        break;
    case 'getFinJobDetail':
        $period = $received_data->period;
        $sid = $received_data->sid;
        $detailData = get_job_detail($period, $sid);
        
        echo json_encode($detailData);
        break;
    default:
        break;
}
?>