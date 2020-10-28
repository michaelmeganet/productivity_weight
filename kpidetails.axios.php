<?php

include_once 'class/dbh.inc.php';
include_once 'class/variables.inc.php';
include_once 'class/abstract_workpcsnew.inc.php';
include_once 'class/reverse-dimension.inc.php';
include_once 'class/phhdate.inc.php';

$received_data = json_decode(file_get_contents("php://input"));
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
    case 'generateKpiDetails':
        $period = $received_data->period;
        $objNPeriod = new NextPeriod($period);
        $nextPeriod = $objNPeriod->get_nextPeriod();
        $year = '20' . substr($period, 0, 2);
        $month = substr($period, 2, 2);
        $date = $year . '-' . $month . '-00';
        //this is generating the Kpidetails table
        //table format :
        //kpidid@;sid@;qid@;Job Code@;Job Type@;Grade@;Total Quantity@;Remaining Quantity@;Job done Quantity@;;Unit weight@;Total Weight@
        //Dimensions@;jlfor@;Index Gain in KG@;jobno@;date of completion@;cid@;Cutting type@;Staff Name@;Machine Model@;Start Time@;End Time'@;duration (hour)@;Model@
        $cntIns = 0;
        $cntUpd = 0;
        $errIns = 0;
        $errUpd = 0;
        $cnt = 0;
        $cntNoChange = 0;
        try {
            $prodWArray = get_weightDetails($period, $nextPeriod, $date); //fetch data based on period
            #echo "Found " . count($prodWArray) . " data.\n";
            #print_r($prodWArray);
            //begin loop on production_weight data:
            $kpidid = null;
            $out_array = array();
            foreach ($prodWArray as $prodWData) {
                try {
                    $poid = $prodWData['poid'];
                    $sid = $prodWData['sid'];
                    $qid = $prodWData['qid'];
                    $jobcode = $prodWData['jobcode'];
                    $grade = $prodWData['grade'];
                    $totalquantity = $prodWData['quantity'];
                    $unit_weight = $prodWData['unit_weight'];
                    $total_weight = $prodWData['total_weight'];
                    $dimensions = $prodWData['dimension'];
                    $jlfor = $prodWData['jlfor'];
                    $jobno = $prodWData['jobno'];
                    $dateofcompletion = $prodWData['dateofcompletion'];
                    $cid = $prodWData['cid'];
                    $cuttingtype = $prodWData['cuttingtype'];
                    $staffname = $prodWData['staffname'];
                    $machineModel = $prodWData['machineModel'];
                    $model = $prodWData['model'];

                    if ($poid) {
                        #echo "poid = $poid\n";
                        $prodOutArray = get_outputDetails($period, $nextPeriod, $poid, $sid);
                        #$kpidid++;
                        $jobtype = $prodOutArray['jobtype'];
                        $remainingquantity = $prodOutArray['remainingquantity'];
                        $jobdonequantity = $prodOutArray['quantity'];
                        $unit_gain_kg = round($jobdonequantity * $unit_weight, 2);
                        $start_time = $prodOutArray['date_start'];
                        $end_time = $prodOutArray['date_end'];
                        #echo "start = $start_time; end = $end_time;\n";
                        if ($start_time == null || $end_time == null) {
                            $duration = 0;
                        } else {
                            $diff = abs(strtotime($end_time) - strtotime($start_time));
                            $hours = ($diff / 3600); #floor($diff / 3600);
                            #$minutes = floor(($diff - ($hours * 60 * 60)) / 60);
                            #$seconds = floor(($diff - ($hours * 60 * 60) - ($minutes * 60)));
                            #$duration = "$hours hours, $minutes minutes, $seconds seconds";
                            $duration = round($hours, 2);
                        }
                    } else {
                        $jobtype = null;
                        $remainingquantity = null;
                        $jobdonequantity = null;
                        $unit_gain_kg = 0;
                        $start_time = null;
                        $end_time = null;
                        #echo "start = $start_time; end = $end_time;\n";
                        $duration = 0;
                    }
                    #echo "duration = $duration\n";
                    $out_array = array(
                        'kpidid' => $kpidid,
                        'sid' => $sid,
                        'qid' => $qid,
                        'jobcode' => $jobcode,
                        'jobtype' => $jobtype,
                        'grade' => $grade,
                        'totalquantity' => $totalquantity,
                        'remainingquantity' => $remainingquantity,
                        'jobdonequantity' => $jobdonequantity,
                        'unit_weight' => $unit_weight,
                        'total_weight' => $total_weight,
                        'dimensions' => $dimensions,
                        'jlfor' => $jlfor,
                        'unit_gain_kg' => $unit_gain_kg,
                        'jobno' => $jobno,
                        'dateofcompletion' => $dateofcompletion,
                        'cid' => $cid,
                        'poid' => $poid,
                        'cuttingtype' => $cuttingtype,
                        'staffname' => $staffname,
                        'machineModel' => $machineModel,
                        'start_time' => $start_time,
                        'end_time' => $end_time,
                        'duration' => $duration,
                        'model' => $model,
                    );
                    $cnt++;
                    $existCheck = check_existing_KPI_detail($out_array, $period);
                    if ($existCheck == 'empty') {
                        $insResult = insert_KPI_Detail($out_array, $period);
                        if ($insResult == 'insert ok!') {
                            $cntIns++;
                        } else {
                            $errIns++;
                        }
                    } else {
                        throw new Exception('There\'s existing data', 111);
                    }
                    //sid
                } catch (Exception $e) {
                    $code = $e->getCode();
                    switch ($code) {
                        case 111: //there's already data inside this table, begin update process
                            $existingData = $existCheck;
                            $kpidid = $existingData['kpidid'];
                            $upd_array = array();
                            if ($jobtype != $existingData['jobtype']) {
                                $upd_array['jobtype'] = $jobtype;
                                unset($jobtype);
                            }
                            if ($remainingquantity != $existingData['remainingquantity']) {
                                $upd_array['remainingquantity'] = $remainingquantity;
                                unset($remainingquantity);
                            }
                            if ($jobdonequantity != $existingData['jobdonequantity']) {
                                $upd_array['jobdonequantity'] = $jobdonequantity;
                                unset($jobdonequantity);
                            }
                            if ($unit_gain_kg != $existingData['unit_gain_kg']) {
                                $upd_array['unit_gain_kg'] = $unit_gain_kg;
                                unset($unit_gain_kg);
                            }
                            if ($dateofcompletion != $existingData['dateofcompletion']) {
                                $upd_array['dateofcompletion'] = $dateofcompletion;
                                unset($dateofcompletion);
                            }
                            if ($poid != $existingData['poid']) {
                                $upd_array['poid'] = $poid;
                                unset($poid);
                            }
                            if ($staffname != $existingData['staffname']) {
                                $upd_array['staffname'] = $staffname;
                                unset($staffname);
                            }
                            if ($machineModel != $existingData['machineModel']) {
                                $upd_array['machineModel'] = $machineModel;
                                unset($machineModel);
                            }
                            if ($start_time != $existingData['start_time']) {
                                $upd_array['start_time'] = $start_time;
                                unset($start_time);
                            }
                            if ($end_time != $existingData['end_time']) {
                                $upd_array['end_time'] = $end_time;
                                unset($end_time);
                            }
                            if ($duration != $existingData['duration']) {
                                $upd_array['duration'] = $duration;
                                unset($duration);
                            }
                            if ($model != $existingData['model']) {
                                $upd_array['model'] = $model;
                                unset($model);
                            }

                            //update now
                            if (!empty($upd_array)) {
                                $updResult = update_KPI_Detail($upd_array, $period, $kpidid);
                                if ($updResult == 'update ok!') {
                                    $cntUpd++;
                                } else {
                                    $errUpd++;
                                }
                            } else {
                                $cntNoChange++;
                            }
                            #$kpidid++;
                            break;
                        case 112: //Cannot find output data in production_output current period
                            echo"cannot find output for sid = $sid, poid = $poid";
                            break;
                        case 113: //Cannot find output data in production_output next period
                            echo"cannot find output for sid = $sid, poid = $poid";
                            break;
                    }
                }
            }
        } catch (Exception $ex) {
            $code = $ex->getCode();
            $msg = $ex->getMessage();
            switch ($code) {
                case 101:
                    $output = array('errorcode' => $code, 'message' => 'Cannot find data', 'query' => $msg);
                    #echo json_encode($err_array);
                    break;
            }
        }
        $output = array('msg' => "Found $cnt Data.\nSuccess Insert : $cntIns; Fail Insert : $errIns; Success Update : $cntUpd; Fail Update : $errUpd; Not Changed Data : $cntNoChange");
        echo json_encode($output);
        break;
    case 'showKpiDetailTable':
        $period = $received_data->period;
        $kpidetailtbl = 'kpidetails_' . $period;
        $qr = "SELECT * FROM $kpidetailtbl ORDER BY kpidid";
        $objSQL = new SQL($qr);
        $result = $objSQL->getResultRowArray();
        echo json_encode($result);
        break;
    case 'getDayList':
        $period = $received_data->period;
        $year = '20'.substr($period,0,2);
        $month = substr($period,2,2);
        $day = '01';
        $date = $year.'-'.$month.'-'.$day;
        #echo $date;
        $noofdays = date_format(date_create($date),'t');
        echo $noofdays;
        break;
}

function check_existing_KPI_detail($checkArray, $period) {
    $table = "kpidetails_$period";
    $sid = $checkArray['sid'];
    $qid = $checkArray['qid'];
    $jobcode = $checkArray['jobcode'];
    $jobno = $checkArray['jobno'];
    $poid = $checkArray['poid'];
    $qr = "SELECT * FROM $table WHERE sid = $sid AND qid = $qid AND jobcode = '$jobcode' AND jobno = $jobno AND (poid = '$poid' OR poid IS NULL)";
    $objSQL = new SQL($qr);
    $result = $objSQL->getResultOneRowArray();
    #echo $qr.'\n';
    #print_r($result);
    if (!empty($result)) {
        return $result;
    } else {
        return 'empty';
    }
}

function update_KPI_Detail($updateArray, $period, $kpidid) {
    $table = "kpidetails_$period";
    $cnt = 0;
    foreach ($updateArray as $key => $value) {
        $cnt++;
        ${$key} = $value;
        #echo "$cnt)  $key : $value\n" . "<br>";
//                    debug_to_console("$key => $value");
    }
    // $arrayKeys = array_keys($insertArray);    //--> fetches the keys of array
    ##$lastArrayKey = array_pop($insertArray); //--> fetches the last key of the compiled keys of array
    end($updateArray); // move the internal pointer to the end of the array
    $lastArrayKey = key($updateArray);  // fetches the key of the element pointed to by the internal pointer
    $sqlUpdate = "UPDATE $table SET ";
    #begin loop
    foreach ($updateArray as $key => $value) {

        ${$key} = trim($value);
        $columnHeader = $key; // creates new variable based on $key values
        #echo $columnHeader . " = " . $$columnHeader . "<br>";
        $sqlUpdate .= $columnHeader . "=:{$columnHeader}";     //--> adds the key as parameter
        if ($columnHeader != $lastArrayKey) {
            $sqlUpdate .= ", ";      //--> if not final key, writes comma to separate between indexes
        } else {
            #do nothing         //--> if yes, do nothing
        }
    }
    # end loop
    $sqlUpdate .= " WHERE kpidid = $kpidid";
    #echo "\$sqlUpdate = $sqlUpdate <br>";
    $objInsert = new SQLBINDPARAM($sqlUpdate, $updateArray);
    $result = $objInsert->UpdateData2();
    #echo "$result <br>";
    return $result;
}

function insert_KPI_Detail($insertArray, $period) {
    $table = "kpidetails_$period";
    $cnt = 0;
    foreach ($insertArray as $key => $value) {
        $cnt++;
        ${$key} = $value;
        #echo "$cnt)  $key : $value\n" . "<br>";
//                    debug_to_console("$key => $value");
    }
    // $arrayKeys = array_keys($insertArray);    //--> fetches the keys of array
    ##$lastArrayKey = array_pop($insertArray); //--> fetches the last key of the compiled keys of array
    end($insertArray); // move the internal pointer to the end of the array
    $lastArrayKey = key($insertArray);  // fetches the key of the element pointed to by the internal pointer
    $sqlInsert = "INSERT INTO $table SET ";
    #begin loop
    foreach ($insertArray as $key => $value) {

        ${$key} = trim($value);
        $columnHeader = $key; // creates new variable based on $key values
        #echo $columnHeader . " = " . $$columnHeader . "<br>";
        $sqlInsert .= $columnHeader . "=:{$columnHeader}";     //--> adds the key as parameter
        if ($columnHeader != $lastArrayKey) {
            $sqlInsert .= ", ";      //--> if not final key, writes comma to separate between indexes
        } else {
            #do nothing         //--> if yes, do nothing
        }
    }
    # end loop
    #echo "\$sqlInsert = $sqlInsert <br>";
    $objInsert = new SQLBINDPARAM($sqlInsert, $insertArray);
    $result = $objInsert->InsertData2();
    #echo "$result <br>";
    return $result;
}

function get_outputDetails($period, $nextPeriod, $poid, $sid) {
    $prootab = "production_output_$period";
    $proo2tab = "production_output_$nextPeriod";
    $qr = "(SELECT * FROM $prootab WHERE poid = $poid AND sid = $sid AND NOT jobtype = 'jobtake')";
    $objSQL = new SQL($qr);
    $result = $objSQL->getResultOneRowArray();
    if (!empty($result)) {
        return $result;
    } else {
        if (check_table($proo2tab)) {
            $qr2 = "(SELECT * FROM $proo2tab WHERE poid = $poid AND sid = $sid AND NOT jobtype = 'jobtake')";
            $objSQL2 = new SQL($qr2);
            $result2 = $objSQL2->getResultOneRowArray();
            if (!empty($result2)) {
                return $result2;
            } else {
                Throw new Exception('Cannot find output data in ' . $proo2tab . '!', 113);
            }
        } else {
            Throw new Exception('Cannot find output data in ' . $prootab . '!', 112);
        }
    }
}

function get_weightDetails($period, $nextPeriod, $date) {
    $prowtab = "production_weight_" . $period;
    $prow2tab = "production_weight_" . $nextPeriod;
    $qr = "(SELECT * FROM $prowtab WHERE DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m')) ";
    if (check_table($prow2tab)) {
        $qr .= "UNION (SELECT * FROM $prow2tab WHERE DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m'))";
    }
    $objSQL = new SQL($qr);
    $result = $objSQL->getResultRowArray();
    if (!empty($result)) {
        return $result;
    } else {
        Throw new Exception("$qr", 101);
    }
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

?>