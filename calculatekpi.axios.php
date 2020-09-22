<?php

include_once 'class/dbh.inc.php';
include_once 'class/variables.inc.php';
include_once 'class/abstract_workpcsnew.inc.php';
include_once 'class/reverse-dimension.inc.php';
include_once 'class/phhdate.inc.php';

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

$received_data = json_decode(file_get_contents("php://input"));

$action = $received_data->action;

switch ($action) {
    case 'getStaffName':
        $staffid = $received_data->staffid;
        $staffname = get_adminstaff_name($staffid);
        echo json_encode($staffname);
        break;
    case 'getPeriod':
        $objDate = new DateNow();
        $currentPeriod_int = $objDate->intPeriod();
        $currentPeriod_str = $objDate->strPeriod();

        $EndYYYYmm = 2001;
        $objPeriod = new generatePeriod($currentPeriod_int, $EndYYYYmm);
        $setofPeriod = $objPeriod->generatePeriod3();

        echo json_encode($setofPeriod);
        break;
    case 'getStaffList':
        $period = $received_data->period;
        $prowtab = "production_weight_$period";
        $qr = "SELECT DISTINCT staffname FROM $prowtab";
        $objSQL = new SQL($qr);
        $results = $objSQL->getResultRowArray();
        echo json_encode($results);
        break;
    case 'getMachineListbyStaff':
        $period = $received_data->period;
        $prowtab = "production_weight_$period";
        $staff = $received_data->staff;
        $qr = "SELECT DISTINCT machineModel FROM $prowtab WHERE staffname = '$staff'";
        $objSQL = new SQL($qr);
        $results = $objSQL->getResultRowArray();
        echo json_encode($results);
        break;

    case 'getDayList':
        $period = $received_data->period;
        $year = '20' . substr($period, 0, 2);
        $month = substr($period, 2, 2);
        $objDD = new DateDayMonthYear($year . '-' . $month);
        $totalday = $objDD->get_total_day();
        echo $totalday; //do not encode it, we'll need the response value as number
        break;

    case 'getKPIDetail':
        $period = $received_data->period;
        $objNPeriod = new NextPeriod($period);
        $nextPeriod = $objNPeriod->get_nextPeriod();
        $year = '20' . substr($period, 0, 2);
        $month = substr($period, 2, 2);
        $staffid = $received_data->staffid;
        $staffname = trim(get_adminstaff_name($staffid));
        $summType = $received_data->summType;
        $day = $received_data->day;
        $date = '';
        $out_array = array();
        if ($day != '') {
            $day = sprintf('%02d', $day);
            $date = $year . '-' . $month . '-' . $day;
        } else {
            $date = $year . '-' . $month . '-00';
        }
        #echo "test : $period; $staffid; $summType; $day; $date\n";
        try {
            $staffList = get_staffnameList($date, $period, $nextPeriod);
            #print_r($staffList);
            foreach ($staffList as $staff_row) {
                $staffname = $staff_row['staffname'];
                try {
                    $machineList = get_machinemodelList($staffname, $date, $period, $nextPeriod);
                    #print_r($machineList);
                    foreach ($machineList as $machine_row) {
                        $machineModel = $machine_row['machineModel'];
                        $kpiGain = 0;
                        try {
                            $prodWDetails = get_prodw_details($staffname, $machineModel, $date, $period, $nextPeriod);
                            foreach ($prodWDetails as $data_row) {
                                $unit_weight = $data_row['unit_weight'];
                                $quantity = $data_row['quantity'];
                                $index_gain_in_kg = $unit_weight * $quantity;
                                //check for normal or overtime KPI
                                ///get time
                                $date_start = $data_row['date_start'];
                                $index_per_shift = $data_row['index_per_shift'];
                                $kpiValue = get_kpiValue($date_start); //fetch the correct KPI Value
                                $kpi = $index_gain_in_kg * $kpiValue;
                                $kpiGain += $kpi;
                                #echo "machine = $machineModel; kpiGain = $kpiGain\n";
                            }
                            if($index_per_shift > 0.00){
                            $netKpi = round($kpiGain / $index_per_shift,2);
                            }else{
                            $netKpi = 'missing machine index value, contact admin';
                            }
                            #echo ";totalkpi = $netKpi\n";
                            $out_array[] = array('staffname' => $staffname, 'machineModel' =>$machineModel, 'totalkpi' => $netKpi);
                        } catch (Exception $ex) {
                            //if cannot find any details, skip this machine  
                        }
                    }
                } catch (Exception $ex) {
                    //if can;t find any machine, skip this staff
                }
            }
        } catch (Exception $ex) {
            $code = $ex->getCode();
            switch ($code) {
                case 101:
                    $err_array = array('code' => $code, 'msg' => 'Cannot find data for ' . $staffid . ' in period = ' . $period . ' and date = ' . $date, 'Query' => $qr);
                    echo json_encode($err_array);
                    break;
            }
        }
        echo json_encode($out_array);
        break;
}

function get_kpiValue($date_start) {
    $prodStartTime = date_format(date_create($date_start), 'H:i:s');
    $prodStartDate = date_format(date_create($date_start), 'Y-m-d');
    $qr = "SELECT * FROM kpitimetable WHERE DATE_FORMAT(date,'%Y %m %d') = DATE_FORMAT('$prodStartDate','%Y %m %d');";
    $objSQL = new SQL($qr);
    $result = $objSQL->getResultOneRowArray();
    #echo "qr = $qr\n";
    #print_r($result);
    $time1start = date_format(date_create('07:00:00'), 'H:i:s');
    $time1end = date_format(date_create('17:00:00'), 'H:i:s');
    $time2start = date_format(date_create('19:00:00'), 'H:i:s');
    $time2end = date_format(date_create('05:00:00'), 'H:i:s');
    if ($prodStartTime >= $time1start && $prodStartTime <= $time1end) {
        #echo "Shift 1 detected\n";
        $kpiCode = (int)$result['shift1'];
    } else {
        #echo "Shift 2 detected\n";
        $kpiCode = (int)$result['shift2'];
    }
    if ($kpiCode == 0){
        $kpi = 7.35;
    }elseif($kpiCode == 1){
        $kpi = 9.8;
    }
    return $kpi;
}

function check_table($tblname) {
    $qr = "SHOW TABLES LIKE '$tblname'";
    $objSQL = new SQL($qr);
    $result = $objSQL->getResultOneRowArray();
    if (!empty($result)) {
        return true;
    } else {
        return false;
    }
}

function get_prodw_details($staffname, $machineModel, $date, $period, $nextPeriod) {
    $prowtab = "production_weight_$period";
    $prow2tab = "production_weight_$nextPeriod";
    if (substr($date, 8, 2) != '00') { //if there's date
        $qrext = " AND DATE_FORMAT(dateofcompletion,'%Y %m %d') = DATE_FORMAT('$date','%Y %m %d')";
    } else { //if there's no date
        $qrext = " AND DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m')";
    }
    $qr3 = "(SELECT *  FROM $prowtab WHERE staffname = '$staffname' AND machineModel = '$machineModel' $qrext)";
    if (check_table($prow2tab)) {
        $qr3 .= "UNION"
                . "(SELECT *  FROM $prow2tab WHERE staffname = '$staffname' AND machineModel = '$machineModel' $qrext)";
    }
    $objSQL3 = new SQL($qr3);
    $result = $objSQL3->getResultRowArray();
    if (empty($result)) {
        throw new Exception($qr3, 103);
    }
    return $result;
}

function get_staffnameList($date, $period, $nextPeriod) {

    if (substr($date, 8, 2) != '00') { //if there's date
        $qrext = " WHERE DATE_FORMAT(dateofcompletion,'%Y %m %d') = DATE_FORMAT('$date','%Y %m %d')";
    } else { //if there's no date
        $qrext = " WHERE DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m')";
    }
    $prowtab = "production_weight_$period";
    $prow2tab = "production_weight_$nextPeriod";
    $qr = "(SELECT DISTINCT staffname FROM $prowtab $qrext)";
    if (check_table($prow2tab)) {
        $qr .= "UNION"
                . "(SELECT DISTINCT staffname FROM $prow2tab $qrext)";
    }
    #echo $qr;
    $objSQL1 = new SQL($qr);
    $result = $objSQL1->getResultRowArray();
    #echo "stafnamelist: <br>";
    #print_r($result);
    if (empty($result)) {
        throw new Exception($qr, 102);
    }
    return $result;
}

function get_machinemodelList($staffname, $date, $period, $nextPeriod) {
    if (substr($date, 8, 2) != '00') { //if there's date
        $qrext = " AND DATE_FORMAT(dateofcompletion,'%Y %m %d') = DATE_FORMAT('$date','%Y %m %d')";
    } else { //if there's no date
        $qrext = " AND DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m')";
    }
    $prowtab = "production_weight_$period";
    $prow2tab = "production_weight_$nextPeriod";
    $qr2 = "(SELECT DISTINCT machineModel FROM $prowtab WHERE staffname = '$staffname' $qrext)";
    if (check_table($prow2tab)) {
        $qr2 .= "UNION"
                . "(SELECT DISTINCT machineModel FROM $prow2tab WHERE staffname = '$staffname' $qrext)";
    }
    #echo "qr2 = $qr2";
    $objSQL2 = new SQL($qr2);
    $machineList = $objSQL2->getResultRowArray();
    if (empty($machineList)) {
        throw new Exception('No Machine Data for this user, Please confirm with the administrator');
    }
    return $machineList;
}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

