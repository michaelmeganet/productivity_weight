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

function get_adminstaff_name($staffid) {
    if ($staffid != '') {
        $qr2 = "SELECT name FROM admin_staff WHERE staffid = '$staffid'";
        #echo $qr2;
        $objSQL2 = new SQL($qr2);
        $result = $objSQL2->getResultOneRowArray();
        #print_r($result);
        return $result['name'];
    } else {
        return '';
    }
}

function get_job_output($period, $sid) {
    $proouttab = "production_output_" . $period;
    $qr = "SELECT * FROM $proouttab WHERE sid = $sid";
    $objSQL = new SQL($qr);
    $results = $objSQL->getResultRowArray();
    foreach ($results as $key => $val) {
        $start_by = get_adminstaff_name($val['start_by']);
        $end_by = get_adminstaff_name($val['end_by']);
        $results[$key]['start_by'] = $start_by;
        $results[$key]['end_by'] = $end_by;
    }
    #echo $qr;
    if (!empty($results)) {
        return $results;
    } else {
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
        $status = $received_data->status;
        $manual = $received_data->manual;
        $proschtab = "production_scheduling_" . $period;

        $qr = "SELECT * , premachining.`process` as processname "
                . "FROM $proschtab LEFT JOIN premachining "
                . "ON $proschtab.process = premachining.pmid "
                . "WHERE dateofcompletion IS NULL AND status = '$status'";
        if ($manual == 'yes') {
            $qr .= " AND MID(quono,10,3) LIKE 'M%'";
        } elseif ($manual == 'no') {
            $qr .= " AND MID(quono,10,3) NOT LIKE 'M%'";
        }
        #echo $qr;
        $objSQL = new SQL($qr);
        $unfinData = $objSQL->getResultRowArray();
        echo json_encode($unfinData);
        break;
    case 'getFinJobList':
        $period = $received_data->period;
        $status = $received_data->status;
        $manual = $received_data->manual;
        $proschtab = "production_scheduling_" . $period;

        $qr = "SELECT *, premachining.`process` as processname "
                . "FROM $proschtab LEFT JOIN premachining "
                . "ON $proschtab.process = premachining.pmid "
                . "WHERE dateofcompletion IS NOT NULL AND status = '$status'";
        if ($manual == 'yes') {
            $qr .= " AND MID(quono,10,3) LIKE 'M%'";
        } elseif ($manual == 'no') {
            $qr .= " AND MID(quono,10,3) NOT LIKE 'M%'";
        }
        $objSQL = new SQL($qr);
        $finData = $objSQL->getResultRowArray();
        echo json_encode($finData);
        break;
    case 'getUnFinJobOutput':
        $period = $received_data->period;
        $sid = $received_data->sid;
        $detailData = get_job_output($period, $sid);

        echo json_encode($detailData);
        break;
    case 'getFinJobOutput':
        $period = $received_data->period;
        $sid = $received_data->sid;
        $detailData = get_job_output($period, $sid);

        echo json_encode($detailData);
        break;
    default:
        break;
}
?>