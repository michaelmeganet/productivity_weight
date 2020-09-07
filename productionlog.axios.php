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

        $qr = "SELECT $proschtab.* , premachining.`process` as processname "
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

        $qr = "SELECT $proschtab.*, premachining.process as processname "
                . "FROM $proschtab LEFT JOIN premachining "
                . "ON $proschtab.process = premachining.pmid "
                . "WHERE dateofcompletion IS NOT NULL AND status = '$status'";
        if ($manual == 'yes') {
            $qr .= " AND MID(quono,10,3) LIKE 'M%'";
        } elseif ($manual == 'no') {
            $qr .= " AND MID(quono,10,3) NOT LIKE 'M%'";
        }
        #echo $qr;
        $objSQL = new SQL($qr);
        $finData = $objSQL->getResultRowArray();
        #print_r($finData);
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
    case 'getJobWorkDetail':
        $arr_JobSchDetail = json_decode(json_encode($received_data->jobListDetail), true);
        $arr_JobOutDetail = json_decode(json_encode($received_data->jobListOutput), true);
        #echo "arr_jobOUtDetail = <br>";
        #print_r($arr_JobOutDetail);
        #echo "<br>";
        #echo "arr_jobSchDetail = <br>";
        #print_r($arr_JobSchDetail[0]);
        #echo "<br>";

        $objJWDetail = new JOB_WORK_DETAIL($arr_JobSchDetail[0]['cuttingtype'], $arr_JobSchDetail[0]['process'], $arr_JobOutDetail);
        $resultJWDetail = $objJWDetail->get_arr_jobWork();
        echo json_encode($resultJWDetail);
    default:
        break;
}

class JOB_WORK_DETAIL {

    private $cuttingtype;
    private $processcode;
    private $jobtype;
    private $millingarray;
    private $grindingtype;
    private $jobOutputList;
    private $arr_jobWork;

    function __construct($cuttingtype, $processcode, $jobOutputList) {
        $this->cuttingtype = $cuttingtype;
        $this->processcode = $processcode;
        #echo "processcode = $processcode";
        $this->jobOutputList = $jobOutputList;
        $jobtype = $this->get_job_type();
        $this->jobtype = $this->jobtype;
        $milling_array = $this->get_milling_array();
        $this->millingarray = $milling_array;
        $grindingtype = $this->get_grinding_code();
        $this->grindingtype = $grindingtype;
        $arr_jobWork = $this->get_work_detail($jobtype, $milling_array, $grindingtype);
        $this->arr_jobWork = $arr_jobWork;
        #echo "jobtype = $jobtype<br>\n";
        #echo "milling_array : ";
        #print_r($milling_array);
        #echo "<br>\n";
        #echo "grindingtype = $grindingtype<br>\n";
        #echo "JobWork Results :<br>\n";
        #print_r($arr_jobWork);
        #echo "<br>\n";
    }

    function get_work_detail($jobtype, $milling_array, $grindingtype) {
        $jobWorkArray = array();
        #echo "in get_work_detail\n---milling array\n";
        #print_r($milling_array);

        $chk_jobtake = $this->search_in_output('jobtake');
        if ($chk_jobtake != 'empty') { //if job_take exists
            if ($chk_jobtake['date_end'] == ('' || null)) {//if not yet ended
                $jobWorkArray[] = ['Job Take' => 'Taken'];
            } else { //if ended
                $jobWorkArray[] = ['Job Take' => 'Taken'];
            }
        } else {//if Job Take don't exists
            $jobWorkArray[] = ['Job Take' => 'Not yet Taken'];
        }

        if ($jobtype != 'none') { //get the value for cuttingtype
            $outputData = $this->search_in_output($jobtype);
            $cutting = ucwords($jobtype);
            if ($outputData != 'empty') { //if data is found
                #echo "outputData = \n";
                #print_r($outputData['date_end']);
                if ($outputData['date_end'] == ('' || null)) { //if not yet ended
                    $jobWorkArray[] = [$cutting => 'On-Progress'];
                } else { //if ended
                    $jobWorkArray[] = [$cutting => 'Finished'];
                }
            } else {//If there's no data
                $jobWorkArray[] = [$cutting => 'Not Started'];
            }
        }

        if (!empty($milling_array)) { //loop in milling type
            #echo "milling array is not empty\n";
            foreach ($milling_array as $data_row) { // get the value for millingtype
                foreach ($data_row as $key => $val) {
                    #echo $millingKey."\n";
                    $outputMilling = $this->search_in_output($key);
                    $millingKey = ucwords($key);
                    if ($outputMilling != 'empty') {//if there's milling data
                        if ($outputMilling['date_end'] == ('' || null)) { //if not yet ended
                            $jobWorkArray[] = [$millingKey => 'On-Progress'];
                        } else { //if ended
                            $jobWorkArray[] = [$millingKey => 'Finished'];
                        }
                    } else {//if no milling data
                        $jobWorkArray[] = [$millingKey => 'Not Started'];
                    }
                }
            }
        } else {
            #echo "milling array is empty\n";
        }

        if ($grindingtype != 'none') { //get the value for cuttingtype
            $outputGrinding = $this->search_in_output($grindingtype);
            $grinding = ucwords($grindingtype);
            if ($outputGrinding != 'empty') { //if data is found
                if ($outputGrinding['date_end'] == ('' || null)) { //if not yet ended
                    $jobWorkArray[] = [$grinding => 'On-Progress'];
                } else { //if ended
                    $jobWorkArray[] = [$grinding => 'Finished'];
                }
            } else {//If there's no data
                $jobWorkArray[] = [$grinding => 'Not Started'];
            }
        }

        return $jobWorkArray;
    }

    function search_in_output($jobtype) {
        $jobOutputList = $this->jobOutputList;
        if ($jobOutputList != 'empty') {
            #echo "joboutput is not empty\n";
            #echo "searching $jobtype in job outputlist:\n";
            #print_r($jobOutputList);
            $result = array_filter($jobOutputList, function($var) use ($jobtype) {
                return ($var['jobtype'] == trim($jobtype));
            });
            if (!empty($result)) {
                #echo "onsearch---\n";
                #print_r($result);
                return $result[0];
            } else {
                return 'empty';
            }
        } else {
            return 'empty';
        }
    }

    function get_milling_array() {
        $processcode = $this->processcode;
        $millingarray = array();
        $qr = "SELECT * FROM premachining WHERE pmid = $processcode";
        $objSQL = new SQL($qr);
        $result = $objSQL->getResultOneRowArray();
        if (!empty($result)) {
            $topMilling = $result['top1'] + $result['bottom2'];
            #echo "topMilling = $topMilling <br>\n";
            $widthMilling = $result['sidel3'] + $result['sider4'];
            #echo "widthMilling = $widthMilling<br>\n";
            $lengthMilling = $result['sideb5'] + $result['sidet6'];
            #echo "lengthMilling = $lengthMilling<br>\n";
            if ($topMilling > 0) {
                $millingarray[] = ['milling' => 'Milling Thickness'];
            }
            if ($widthMilling > 0) {
                $millingarray[] = ['millingwidth' => 'Milling Width'];
            }
            if ($lengthMilling > 0) {
                $millingarray[] = ['millinglength' => 'Milling Length'];
            }
        }
        return $millingarray;
    }

    function get_grinding_code() {
        $processcode = $this->processcode;
        $qr = "SELECT process FROM premachining WHERE pmid = $processcode";
        $objSQL = new SQL($qr);
        $result = $objSQL->getResultOneRowArray();
        if (!empty($result)){
            $processname = $result['process'];
        }else{
            $processname = 'none';
        }
        $gotRG = stripos($processname, 'RG');
        #echo "gotRG = $gotRG\n";
        $gotSG = stripos($processname, 'SG');
        #echo "gotSG = $gotSG\n";
        if ($gotRG !== FALSE) {
            $grindingcode = 'roughgrinding';
        } elseif ($gotSG !== FALSE) {
            $grindingcode = 'precisiongrinding';
        } else {
            $grindingcode = 'none';
        }
        return $grindingcode;
    }

    function get_job_type() {
        $cuttingtype = $this->cuttingtype;
        $gotCNC = stripos($cuttingtype, 'CNC');
        #var_dump($gotCNC);
        $gotManual = stripos($cuttingtype, 'MANUAL');
        #var_dump($gotManual);
        $gotBandsaw = stripos($cuttingtype, 'BANDSAW');
        #var_dump($gotBandsaw);

        if ($gotCNC !== FALSE) {
            $cuttingcode = 'cncmachining';
        } elseif ($gotManual !== FALSE) {
            $cuttingcode = 'manual';
        } elseif ($gotBandsaw !== FALSE) {
            $cuttingcode = 'bandsaw';
        } else {
            $cuttingcode = 'none';
        }
        #echo "cuttingtype = $cuttingtype<br>";
        #echo "gotCNC = $gotCNC<br>";
        #echo "gotManual = $gotManual<br>";
        #echo "gotBandsaw = $gotBandsaw<br>";
        #echo "cuttingcode = $cuttingcode<br>";
        return $cuttingcode;
    }

    function get_arr_jobWork() {
        return $this->arr_jobWork;
    }

    function set_arr_jobWork($input) {
        $this->arr_jobWork = $input;
    }

}

?>