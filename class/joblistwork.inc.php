<?php

Class JOB_WORK_DETAIL {

    Protected $jobcode;
    Protected $cuttingtype;
    Protected $totalquantity;
    Protected $processcode;
    Protected $jobtype;
    Protected $millingarray;
    Protected $grindingtype;
    Protected $jobOutputList;
    Protected $arr_jobWork;
    Protected $ex_jobWork;

    #private $rundb;

    Public function __construct($jobcode, $cuttingtype, $processcode, $totalquantity, $jobOutputList = null) {
        #$this->rundb = $rundb;
        $this->set_jobcode($jobcode);
        $this->set_totalquantity($totalquantity);
        $this->set_cuttingtype($cuttingtype);
        $this->set_processcode($processcode);
        #echo "processcode = $processcode";
        $this->set_jobOutputList($jobOutputList);
        //check if there's existing jobwork_detail on database for this jobcode
        $ex_jobWork = $this->check_existing_jobworkstatus($jobcode);
        if ($ex_jobWork == 'empty') {//if there's no data yet
            $jobtype = $this->get_job_type();
            $this->set_jobtype($jobtype);
            $milling_array = $this->get_milling_array();
            $this->set_millingarray($milling_array);
            $grindingtype = $this->get_grinding_code();
            $this->set_grindingtype($grindingtype);
            //insert this data into database
            $this->insert_jobwork_details($jobcode, $jobtype, $milling_array, $grindingtype);
            $this->set_ex_jobWork($this->check_existing_jobworkstatus($jobcode));
        } else { //if there's already data in database for this jobcode
            $this->set_ex_jobWork($ex_jobWork);
            $this->parse_existing_jobwork($ex_jobWork);
            $jobtype = $this->get_jobtype();
            $milling_array = $this->get_millingarray();
            $grindingtype = $this->get_grindingtype();
        }

        // put code to check data in joblistwork_detail
        //end code to check
        if ($jobOutputList != null) {
            $arr_jobWork = $this->get_work_detail($jobtype, $milling_array, $grindingtype);
            $this->set_arr_jobWork($arr_jobWork);
        }
        #echo "jobtype = ";
        #print_r($jobtype);
        #echo "<br>\n";
        #echo "milling_array : ";
        #print_r($milling_array);
        #echo "<br>\n";
        #echo "grindingtype = $grindingtype<br>\n";
        #echo "JobWork Results :<br>\n";
        #print_r($arr_jobWork);
        #echo "<br>\n";
    }

    Protected function parse_existing_jobwork($ex_jobWork) {
        $jobtype = array();
        $milling_array = array();
        $grindingtype = 'none';
        foreach ($ex_jobWork as $key => $data) {
            if ($data == 'true') {
                switch ($key) {
                    case 'cncmachining':
                        $jobtype[] = $key;
                        break;
                    case 'manual':
                        $jobtype[] = $key;
                        break;
                    case 'bandsaw':
                        $jobtype[] = $key;
                        break;
                    case 'milling':
                        $milling_array[] = array($key => 'Milling Thickness');
                        break;
                    case 'millingwidth':
                        $milling_array[] = array($key => 'Milling Width');
                        break;
                    case 'millinglength':
                        $milling_array[] = array($key => 'Milling Length');
                        break;
                    case 'roughgrinding':
                        $grindingtype = $key;
                        break;
                    case 'precisiongrinding':
                        $grindingtype = $key;
                        break;
                }
            }
        }
        $this->set_jobtype($jobtype);
        $this->set_millingarray($milling_array);
        $this->set_grindingtype($grindingtype);
    }

    Protected function insert_jobwork_details($jobcode, $jobtype, $milling_array, $grindingtype) {
//parse each data into one array to insert
        $insertArray = array();
        $insertArray['jobcode'] = $jobcode;
        if (!empty($jobtype)) {
            foreach ($jobtype as $data) {
                $insertArray[$data] = 'true';
            }
        }
        if (!empty($milling_array)) {
            foreach ($milling_array as $data_row) {
                foreach ($data_row as $key => $val) {
                    $insertArray[$key] = 'true';
                }
            }
        }
        if ($grindingtype != 'none') {
            $insertArray[$grindingtype] = 'true';
        }
        #echo "insert Array = ";
        #print_r($insertArray);

        $table = "joblist_work_status";
        $cnt = 0;
        $sqlInsert = "INSERT INTO $table SET ";
        $cntArr = count($insertArray);
        foreach ($insertArray as $key => $value) {
            $cnt++;
            $sqlInsert .= $key . "=:$key";     //--> adds the key as parameter
            if ($cnt != $cntArr) {
                $sqlInsert .= ", ";      //--> if not final key, writes comma to separate between indexes
            }
        }
        #echo "\$sqlInsert = $sqlInsert <br>";
        $objInsert = new SQLBINDPARAM($sqlInsert, $insertArray);
        $result = $objInsert->InsertData2();
        #echo "$result <br>";
        return $result;
    }

    Protected function check_existing_jobworkstatus($jobcode) {
        $qr = "SELECT * FROM joblist_work_status WHERE jobcode = '$jobcode'";
        $objSQL = new SQL($qr);
        $result = $objSQL->getResultOneRowArray();
        #echo "qr = $qr<br>\n";
        #echo "result = <br>";
        #var_dump($result);
        if (!empty($result)) {
            return $result;
        } else {
            return 'empty';
        }
    }

    Public function check_job_work_status($arr_jobwork) { //Call this function from outside of class
        foreach ($arr_jobwork as $data_row) {
            $key = $data_row['process'];
            $val = $data_row['status'];
#echo "currently checked : <br>\n";
#echo "key = $key;   val = $val;<br>\n";
            switch ($key) {
                /* //removed jobtake detection
                case 'Job Take':
                    if ($val != 'Taken') {
#echo "not yet taken<br>\n";
                        $info = 'Not Taken Yet';
                        return $info;
                    }
                    break;
                 * 
                 */
                default:
#echo "case is not jobtake<br>\n";
                    if ($val == 'Partial') {
#echo "case is partial<br>\n";
                        $info = 'Quantity Partial Finish';
                    } elseif ($val != 'Finished') {
#echo "case is not finished<br>\n";
                        $info = 'Not End Yet';
                    } elseif ($val == 'Finished') {
#echo "case is finished<br>\n";
                        $info = 'Finished';
                    }
                    return $info;
                    break;
            }
        }
    }

    Protected function get_work_detail($arr_jobtype, $milling_array, $grindingtype) {
        $jobWorkArray = array();
        $totalquantity = $this->get_totalquantity();
#echo "total quantity = $totalquantity<br>\n";
#echo "in get_work_detail\n---milling array\n";
#print_r($milling_array);
#echo"\n in get_work_detail\n---arr_jobtype\n";
#print_r($arr_jobtype);
        /*/ removed jobtake checker
          $chk_jobtake = $this->search_in_output('jobtake');
          if ($chk_jobtake != 'empty') { //if job_take exists
          if ($chk_jobtake['date_end'] == ('' || null)) {//if not yet ended
          $jobWorkArray[] = array('process' => 'Job Take', 'status' => 'Taken');
          } else { //if ended
          $jobWorkArray[] = array('process' => 'Job Take', 'status' => 'Taken');
          }
          } else {//if Job Take don't exists
          $jobWorkArray[] = array('process' => 'Job Take', 'status' => 'Not Yet Taken');
          }
         /**/
        if (!empty($arr_jobtype)) {
            foreach ($arr_jobtype as $jobtype) {
#echo "jobtypedata = $jobtype_data";
                $outputData = $this->search_in_output($jobtype);
                $qty_done_data = $this->get_total_quantity_done($jobtype);
                $cutting = ucwords($jobtype);
                if ($outputData != 'empty') { //if data is found
#echo "outputData = \n";
#print_r($outputData['date_end']);
                    if ($outputData['date_end'] == ('' || null)) { //if not yet ended
                        $jobWorkArray[] = array('process' => $cutting, 'status' => 'On-Progress');
                    } else { //if ended
                        if ($totalquantity != $qty_done_data) {
                            $jobWorkArray[] = array('process' => $cutting, 'status' => 'Partial');
                        } else {
                            $jobWorkArray[] = array('process' => $cutting, 'status' => 'Finished');
                        }
                    }
                } else {//If there's no data
                    $jobWorkArray[] = array('process' => $cutting, 'status' => 'Not Started');
                }
            }
        }
        /* This is old jobtype checker
          if ($jobtype != 'none') { //get the value for cuttingtype
          $outputData = $this->search_in_output($jobtype);
          $qty_done_data = $this->get_total_quantity_done($jobtype);
          $cutting = ucwords($jobtype);
          if ($outputData != 'empty') { //if data is found
          #echo "outputData = \n";
          #print_r($outputData['date_end']);
          if ($outputData['date_end'] == ('' || null)) { //if not yet ended
          $jobWorkArray[] = array('process' => $cutting, 'status' => 'On-Progress');
          } else { //if ended
          if ($totalquantity != $qty_done_data) {
          $jobWorkArray[] = array('process' => $cutting, 'status' => 'Partial');
          } else {
          $jobWorkArray[] = array('process' => $cutting, 'status' => 'Finished');
          }
          }
          } else {//If there's no data
          $jobWorkArray[] = array('process' => $cutting, 'status' => 'Not Started');
          }
          }
         */
        if (!empty($milling_array)) { //loop in milling type
#echo "milling array is not empty\n";
            foreach ($milling_array as $data_row) { // get the value for millingtype
                foreach ($data_row as $key => $val) {
#echo $millingKey."\n";
                    $outputMilling = $this->search_in_output($key);
                    $qty_done_milling = $this->get_total_quantity_done($jobtype);
                    $millingKey = ucwords($key);
                    if ($outputMilling != 'empty') {//if there's milling data
                        if ($outputMilling['date_end'] == ('' || null)) { //if not yet ended
                            $jobWorkArray[] = array('process' => $millingKey, 'status' => 'On-Progress');
                        } else { //if ended
                            if ($totalquantity != $qty_done_milling) {
                                $jobWorkArray[] = array('process' => $millingKey, 'status' => 'Partial');
                            } else {
                                $jobWorkArray[] = array('process' => $millingKey, 'status' => 'Finished');
                            }
                        }
                    } else {//if no milling data
                        $jobWorkArray[] = array('process' => $millingKey, 'status' => 'Not Started');
                    }
                }
            }
        } else {
#echo "milling array is empty\n";
        }

        if ($grindingtype != 'none') { //get the value for cuttingtype
            $outputGrinding = $this->search_in_output($grindingtype);
            $qty_done_grinding = $this->get_total_quantity_done($jobtype);
            $grinding = ucwords($grindingtype);
            if ($outputGrinding != 'empty') { //if data is found
                if ($outputGrinding['date_end'] == ('' || null)) { //if not yet ended
                    $jobWorkArray[] = array('process' => $grinding, 'status' => 'On-Progress');
                } else { //if ended
                    if ($totalquantity != $qty_done_grinding) {
                        $jobWorkArray[] = array('process' => $grinding, 'status' => 'Partial');
                    } else {
                        $jobWorkArray[] = array('process' => $grinding, 'status' => 'Finished');
                    }
                }
            } else {//If there's no data
                $jobWorkArray[] = array('process' => $grinding, 'status' => 'Not Started');
            }
        }

        return $jobWorkArray;
    }

    Protected function get_total_quantity_done($jobtype) {
        $jobOutputList = $this->get_jobOutputList();
        $qty_sum = 0;
        if ($jobOutputList != 'empty') {
            foreach ($jobOutputList as $output_data) {
                if ($output_data['jobtype'] == $jobtype) {
                    $qty_sum += (int) $output_data['quantity'];
                }
            }
        } else {
            return 'empty';
        }
#echo "qty sum = $qty_sum<br>\n";
        return $qty_sum;
    }

    Protected function search_in_output($jobtype) {
        $jobOutputList = $this->get_jobOutputList();
#echo "jobtype here :".$jobOutputList['jobtype']."<br>\n";
        if ($jobOutputList != 'empty') {
#echo "joboutput is not empty<br>\n";
#echo "searching $jobtype in job outputlist:<br>\n";
#print_r($jobOutputList);
#echo"<br>";
            $result = array_filter($jobOutputList, function($var) use ($jobtype) {
#echo "try searching $jobtype....<br>\n";
#echo "jobtype = ".$var['jobtype']."<br>\n";
                return ($var['jobtype'] == trim($jobtype));
            });
            if (!empty($result)) {
#echo "onsearch---\n";
#print_r($result);
                foreach ($result as $data_row) {
                    return $data_row;
                    break;
                }
#return $result[];
            } else {
#echo "cannot find $jobtype\n";
                return 'empty';
            }
        } else {
            return 'empty';
        }
    }

    Protected function get_milling_array() {
#$rundb = $this->rundb;
        $processcode = $this->get_processcode();
        $millingarray = array();
        $qr = "SELECT * FROM premachining WHERE pmid = $processcode";
#$resultMil = $rundb->Query($qr);
#$result = $rundb->FetchArray($resultMil);
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
                $millingarray[] = array('milling' => 'Milling Thickness');
            }
            if ($widthMilling > 0) {
                $millingarray[] = array('millingwidth' => 'Milling Width');
            }
            if ($lengthMilling > 0) {
                $millingarray[] = array('millinglength' => 'Milling Length');
            }
        }
        return $millingarray;
    }

    Protected function get_grinding_code() {
#$rundb = $this->rundb;
        $processcode = $this->get_processcode();
        $qr = "SELECT process FROM premachining WHERE pmid = $processcode";
#$resultMil = $rundb->Query($qr);
#$result = $rundb->FetchArray($resultMil);
        $objSQL = new SQL($qr);
        $result = $objSQL->getResultOneRowArray();
        if (!empty($result)) {
            $processname = $result['process'];
        } else {
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

    Protected function get_job_type() {
        $cuttingtype = $this->get_cuttingtype();
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
        if ($cuttingcode != 'none') {
            $jobtype_arr[] = $cuttingcode;
        }
#return $cuttingcode;
        return $jobtype_arr;
    }

    //getter and setter area
    
    Public function get_arr_jobWork() { //this can be called from outside of class
        return $this->arr_jobWork;
    }

    Protected function set_arr_jobWork($input) {
        $this->arr_jobWork = $input;
    }

    Public function get_ex_jobwork() { //this can be called from outside of class
        return $this->ex_jobWork;
    }

    Protected function set_ex_jobwork($input) {
        $this->ex_jobWork = $input;
    }

    Protected function get_jobcode() {
        return $this->jobcode;
    }

    Protected function set_jobcode($input) {
        $this->jobcode = $input;
    }

    Protected function get_cuttingtype() {
        return $this->cuttingtype;
    }

    Protected function set_cuttingtype($input) {
        $this->cuttingtype = $input;
    }

    Protected function get_totalquantity() {
        return $this->totalquantity;
    }

    Protected function set_totalquantity($input) {
        $this->totalquantity = $input;
    }

    Protected function get_processcode() {
        return $this->processcode;
    }

    Protected function set_processcode($input) {
        $this->processcode = $input;
    }

    Protected function get_jobtype() {
        return $this->jobtype;
    }

    Protected function set_jobtype($input) {
        $this->jobtype = $input;
    }

    Protected function get_millingarray() {
        return $this->millingarray;
    }

    Protected function set_millingarray($input) {
        $this->millingarray = $input;
    }

    Protected function get_grindingtype() {
        return $this->grindingtype;
    }

    Protected function set_grindingtype($input) {
        $this->grindingtype = $input;
    }

    Protected function get_jobOutputList() {
        return $this->jobOutputList;
    }

    Protected function set_jobOutputList($input) {
        $this->jobOutputList = $input;
    }

}

?>