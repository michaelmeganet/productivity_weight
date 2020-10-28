<!DOCTYPE html>
<html>
    <head>
        <style>
            table {
                font-family: arial, sans-serif;
                border-collapse: collapse;
                width: 100%;
            }

            td, th {
                border: 1px solid #dddddd;
                text-align: left;
                padding: 8px;
            }

            tr:nth-child(even) {
                background-color: #dddddd;
            }
        </style>
    </head>
    <body>
        <h2>PRODUCTION WEIGHT </h2>

        <?php

        function callSqlInsert($insertArray, $table) {
            $cnt = 0;
            foreach ($insertArray as $key => $value) {
                $cnt++;
                ${$key} = $value;
                echo "$cnt)  $key : $value\n" . "<br>";
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
                echo $columnHeader . " = " . $$columnHeader . "<br>";

                /* $dbg->review($columnHeader." = ".$$columnHeader."<br>"); */ //this is for debugging, not yet implemented

                $sqlInsert .= $columnHeader . "=:{$columnHeader}";     //--> adds the key as parameter
                if ($columnHeader != $lastArrayKey) {
                    $sqlInsert .= ", ";      //--> if not final key, writes comma to separate between indexes
                } else {
                    #do nothing         //--> if yes, do nothing
                }
            }
            # end loop

            echo "\$sqlInsert = $sqlInsert <br>";
            $objInsert = new SQLBINDPARAM($sqlInsert, $insertArray);
            $result = $objInsert->InsertData2();
            echo "$result <br>";
            return $result;
        }
        
        function callSqlUpdate($updateArray, $table, $wid) {
            $cnt = 0;
            foreach ($updateArray as $key => $value) {
                $cnt++;
                ${$key} = $value;
                echo "$cnt)  $key : $value\n" . "<br>";
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
                echo $columnHeader . " = " . $$columnHeader . "<br>";

                /* $dbg->review($columnHeader." = ".$$columnHeader."<br>"); */ //this is for debugging, not yet implemented

                $sqlUpdate .= $columnHeader . "=:{$columnHeader}";     //--> adds the key as parameter
                if ($columnHeader != $lastArrayKey) {
                    $sqlUpdate .= ", ";      //--> if not final key, writes comma to separate between indexes
                } else {
                    #do nothing         //--> if yes, do nothing
                }
            }
            # end loop
            $sqlUpdate .= " WHERE wid = $wid";
            echo "\$sqlUpdate = $sqlUpdate <br>";
            $objInsert = new SQLBINDPARAM($sqlUpdate, $updateArray);
            $result = $objInsert->UpdateData2();
            echo "$result <br>";
            return $result;
        }

        function search_output_data($outputTbl, $sid, $qid, $quono, $cid, $poid) {
            $qr = "SELECT * FROM $outputTbl WHERE sid = $sid AND qid = $qid AND quono = '$quono' AND cid = $cid AND (poid = '$poid' OR poid IS NULL)";
            $objSQL = new SQL($qr);

            $results = $objSQL->getResultOneRowArray();
            If (!empty($results)) {
                return $results;
            } else {
                $info = 'not exists';
            }
            return $info;
        }

        include_once 'class/dbh.inc.php';
        include_once 'class/variables.inc.php';
        include_once 'class/abstract_workpcsnew.inc.php';
        include_once 'class/reverse-dimension.inc.php';
        include_once 'class/phhdate.inc.php';
        $period = trim($_POST['period']);
// $period = '2007';
        $tbldata = 'production_scheduling_' . $period;
        $tbloutput = 'production_output_' . $period;
//echo "tbldata = $tbldata;;  tbloutput = $tbloutput";
        $qr = "SELECT * FROM $tbldata WHERE status NOT LIKE '%billing%' ORDER BY sid";
        echo "\$qr = $qr <br>";
        $objSQL = new SQL($qr);
        $results = $objSQL->getResultRowArray();
        $arr_mainLog = array();
        $count = 0;
        $table = "production_weight_" . $period;

        $outputDataList = "SELECT * FROM $table";
        ?>

<!-- <table style="width: 100%;">
<tr>
    <th>sid</th><th>quono</th><th>Material Code</th><th>Quantity</th><th>Unit weight</th><th>Total Weight</th><th>Dimensions</th><th>BRANCH</th><th>runningno</th>
    <th>jobno</th><th>date of completion</th><th>cid</th><th>Cutting type</th><th>Staff Name</th><th>Machine Model</th>
    <th>Start Date</th>
</tr> -->

        <?php
        $insertArray = [];
        try {
            foreach ($results as $datarow) {
                $count++;
                #echo "<pre>Current data : <br>";
                #print_r($datarow);
                #echo "<br></pre><br>";
                try {

                    $qid = $datarow['qid']; //--> check if this exists or not
                    $quono = $datarow['quono']; //--> check if this exists or not
                    $cid = $datarow['cid']; //--> check if this exists or not
                    $sid = $datarow['sid'];

                  
                    $jlfor = $datarow['jlfor'];
                    $dateofcompletion = $datarow['dateofcompletion'];
                    $runningno = $datarow['runningno'];
                    $jobno = $datarow['jobno'];
                    $grade = $datarow['grade'];
                    //echo "sid = $sid<br>";
                    $process = $datarow['process'];
                    $cncmach = $datarow['cncmach'];
                    $noposition = $datarow['noposition'];
                    $date_issue = $datarow['date_issue'];
                    $completion_date = $datarow['completion_date'];
                    $status = $datarow['status'];
                    $packing = $datarow['packing'];
                    $operation = $datarow['operation'];
                    $quantity = $datarow['quantity'];
                    $company = 'PST';
                    $fdt = $datarow['fdt'];
                    $fdl = $datarow['fdl'];
                    $fdw = $datarow['fdw'];
                    $com = strtolower(trim($datarow['company']));
                    $materialcode = $datarow['grade'];
                    echo "\$materialcode = $materialcode <br>";

                    $cuttingtype = $datarow['cuttingtype'];
                    $quantity = $datarow['quantity'];
                    $jobcode = $jlfor." ".substr($quono,0,3)." ".substr($date_issue,2,2).substr($date_issue,5,2)." ".sprintf('%04d',$runningno)." ".sprintf('%02d',$jobno)." ".substr($completion_date,2,2).substr($completion_date,5,2);
                    echo "jobcode = $jobcode<br>\n";
                    //end check
                    // if(isset($weight)){
                    //     $weight = floatval($weight) ;
                    //     if(floatval($weight) > 0.00){
                    //         echo "Line 128 in \$weight > 0 <br>";
                    //         $total_weight = $weight * $quantity;
                    //     }else{
                    //         echo "Line 131 in \$weight <= 0 <br>";
                    //         $total_weight = 0;
                    //     }
                    // } else {
                    //     $weight = 0;
                    //     $total_weight = 0;
                    // }
                    //Get Start_By Staff ID
                    $qr2 = "SELECT * FROM $tbloutput WHERE sid = $sid AND NOT jobtype = 'jobtake' ORDER BY poid";
                    $objSQL2 = new SQL($qr2);
                    $results2 = $objSQL2->getResultOneRowArray();
                    if (!empty($results2)) {
                        $staffid = $results2['start_by'];
                        //$startdate = $results2['date_start'];
                        $date_start = $results2['date_start'];
                        $poid = $results2['poid'];
                        $end_date = $results2['date_end'];
                        $mcid = $results2['machine_id'];
                        echo"mcid = $mcid\n";
                        $day = date_format(date_create($date_start), 'l');
                        $date = date_format(date_create($date_start), 'd-m-Y');
                        $netdatetime = strtotime($end_date) - strtotime($date_start);
                        $workhourval = $netdatetime / 3600;
                        $workhouronly = floor($workhourval);
                        $workhourremainder = $workhourval - $workhouronly;
                        $workminuteval = $workhourremainder / 60;
                        $workminuteonly = floor($workminuteval);
                        $workhour = $workhouronly . " Hours, " . $workminuteonly . " Minutes";
                        //  echo "startdatestr = " . strtotime($startdate) . "<br>";
                        //   echo "enddatestr = " . strtotime($end_date) . "<br>";
                        //   echo "netdatetime = " . $netdatetime . "<br>";
                        //   echo "workhour = " . $workhour . "<br>";
                    } else {
                        $poid = null;
                        $staffid = null;
                        $date_start = null;
                        $end_date = null;
                        $day = null;
                        $date = null;
                        $netdatetime = null;
                        $workhour = null;
                        $mcid = null;
                    }
                    // echo "startdate = $startdate<br>";
                    //  echo "day = $day<br>";
                    //  echo "date = $date<br>";

                    if (isset($staffid)) {
                        //Get Staff Detail
                        $qr3 = "SELECT * FROM admin_staff WHERE staffid = '$staffid'";
                        echo "\$qr3 = $qr3 <br>";
                        $objSQL3 = new SQL($qr3);
                        $results3 = $objSQL3->getResultOneRowArray();
                        $staffname = $results3['name'];
                    } else {
                        $staffname = null;
                    }
                    echo "staffname = $staffname<br>";

                    if (isset($mcid)) {
                        //Get Machine Data
                        $qr4 = "SELECT * FROM machine WHERE mcid = $mcid";
                        $objSQL4 = new SQL($qr4);
                        $results4 = $objSQL4->getResultOneRowArray();
                        $machineid = $results4['machineid'];
                        $machineModel = $results4['name'];
                        $model = $results4['model'];
                        $machine_capacity_per_hour = $results4['index_per_hour'];
                        $machine_capacity_per_shift = $machine_capacity_per_hour * 8;
                    } else {
                        $machineid = null;
                        $machineModel = null;
                        $model = null;
                        $index_per_shift = null;
                    }

                    //Begin check for qid, quono, and cid
                    $proweight_data = search_output_data($table, $sid, $qid, $quono, $cid, $poid); //--> this checks if the data already exists or not,
                    if ($proweight_data != 'not exists') {
                        throw new Exception('Data already inputted');
                    }

                    if (($fdt == 0 || $fdt == '' || $fdt == null) && $materialcode != 'hk2p') { // If no finishing, then finishing is same as raw
                        $fdt = $datarow['mdt'];
                    }
                    if (($fdw == 0 || $fdw == '' || $fdw == null) && $materialcode != 'hk2p') { // If no finishing, then finishing is same as raw
                        $fdw = $datarow['mdw'];
                    }
                    if (($fdl == 0 || $fdl == '' || $fdl == null) && $materialcode != 'hk2p') { // If no finishing, then finishing is same as raw
                        $fdl = $datarow['mdl'];
                    }
                    
                    $dimension = "$fdt x $fdw x $fdl";
                    $dimension_array_legacy = array('mdt' => $fdt, 'mdw' => $fdw, 'mdl' => $fdl, 'quantity' => $quantity);
                    echo "<br>";
                    print_r($dimension_array_legacy);
                    echo "<br>";
                    if ($materialcode != 'hk2p') {

                        echo "\$cid = $cid, \$com = $com , \$materialcode = $materialcode <br>";
                        $obj = new MATERIAL_SPECIAL_PRICE_CID($cid, $com, $materialcode, $dimension_array_legacy);
                        $weight = $obj->getWeight();
                    } else {
                        $weight = (float) 0.00;
                    }
                    $weight = floatval($weight);
                    $total_weight = floatval($weight) * floatval($quantity);
                    echo "<b>quono - $quono </b> , grade = $grade ,   $dimension <br>";
                    echo "<b> Weight = $weight , Totalweight = $total_weight</b><br>";

                    // echo "machineModel - $machineModel<br>";
                    //idv  Day   Date   Staff id   Staff name   MachineModel   Cutting Type   start_datetime   end_datetime   net_dattime   work_hour   qty   Weight(kg)
                    // $arr_mainLog[] = array(
                    //     'id' => $count,
                    //     'sid' => $sid,
                    //     'Day' => $day,
                    //     'Date' => $date,
                    //     'Staff_Id' => $staffid,
                    //     'Staff_Name' => $staffname,
                    //     'MachineModel' => $machineModel,
                    //     'Cutting_Type' => $cuttingtype,
                    //     'start_datetime' => $startdate,
                    //     'end_datetime' => $enddate,
                    //     'net_datetime' => $netdatetime,
                    //     'work_hour' => $workhour,
                    //     'qty' => $quantity,
                    //     'Weight (Kg)' => round($weight,2)
                    // );
                    // echo "-=-=-=-=-=-=-==-=-=-=-=-=-=-=-=-=-=-=-=-=--==-=-=-=-=-=-<br>";
                    if ($weight * 1 > 0) {
                        $weight = round($weight, 2);
                    } else {
                        $weight = 0.00;
                    }
                    // echo 
                    // "<tr>
                    //         <td>$sid</td><td>$quono</td><td>$materialcode</td><td>$quantity</td><td>$weight</td><td>$total_weight</td><td>$dimension</td><td>$jlfor</td><td>$runningno</td>
                    //         <td>$jobno</td><td>$dateofcompletion</td><td>$cid</td><td>$cuttingtype</td><td>$staffname</td><td>$machineModel</td>
                    //         <td>$date_start</td>
                    // </tr>";
                    echo "qid = $qid | quono = $quono | company = $company | cid = $cid | quantity = $quantity | grade = $grade | "
                    . " dimension = $dimension | process = $process | cuttingtype = $cuttingtype  | cncmach = $cncmach | "
                    . " noposition = $noposition | runningno = $runningno | jobno = $jobno | date_issue = $date_issue | "
                    . " completion_date = $completion_date | dateofcompletion = $dateofcompletion | jlfor = $jlfor | status = $status | "
                    . " staffname = $staffname |  machineModel  = $machineModel | model = $model | date_start = $date_start | end_date = $end_date | packing = $packing  | operation = $operation | unit_weight = $weight | "
                    . " total_weight = $total_weight | machine_capacity_per_shift = $machine_capacity_per_shift <br>";
                    echo"################################################################################<br>";
                    $insertArray["wid"] = null;
                    $insertArray["sid"] = $sid;
                    $insertArray["qid"] = $qid;
                    $insertArray["quono"] = $quono;
                    $insertArray["jobcode"] = $jobcode;
                    $insertArray["company"] = 'PST';
                    $insertArray["cid"] = $cid;
                    $insertArray["quantity"] = $quantity;
                    $insertArray["grade"] = $grade;
                    $insertArray["dimension"] = $dimension;
                    $insertArray["process"] = $process;
                    $insertArray["cuttingtype"] = $cuttingtype;
                    $insertArray["cncmach"] = $cncmach;
                    $insertArray["noposition"] = $noposition;
                    $insertArray["runningno"] = $runningno;
                    $insertArray["jobno"] = $jobno;
                    $insertArray["date_issue"] = $date_issue;
                    $insertArray["completion_date"] = $completion_date;
                    $insertArray["dateofcompletion"] = $dateofcompletion;
                    $insertArray["jlfor"] = $jlfor;
                    $insertArray["poid"] = $poid;
                    $insertArray["status"] = $status;
                    $insertArray["staffid"] = $staffid;
                    $insertArray["staffname"] = $staffname;
                    $insertArray["mcid"] = $mcid;
                    $insertArray["machineid"] = $machineid;
                    #$insertArray["machineModel"] = $machineModel;
                    $insertArray["model"] = $model;
                    $insertArray["date_start"] = $date_start;
                    $insertArray["date_end"] = $end_date;
                    $insertArray["packing"] = $packing;
                    $insertArray["operation"] = $operation;
                    $insertArray["unit_weight"] = $weight;
                    $insertArray["total_weight"] = $total_weight;
                    $insertArray["machine_capacity_per_shift"] = $machine_capacity_per_shift;
                    print_r($insertArray);
                    echo "<br>";
                    echo "insert an array into $table <br>";
                    $insertResult = callSqlInsert($insertArray, $table);
                    echo "The insert result is " . $insertResult . "<br>";
                    echo "#####################################################################################<br>";
                    unset($dateofcompletion);
                    unset($status);
                    unset($date_start);
                    unset($end_date);
                    unset($staffname);
                    unset($mcid);
                    unset($machineid);
                    unset($machineModel);
                    unset($model);
                    unset($packing);
                    unset($index_per_shift);
                } catch (Exception $ex) {
                    echo "Item : sid = $sid; qid = $qid; quono = $quono; cid = $cid<br>" .
                    $ex->getMessage() . "<br>";
                    echo "Start Check if data updated or not :===<br><br>\n";
                    echo "proweight_data = ";
                    print_r($proweight_data);
                    echo "<br><br>\n";
                    $wid = $proweight_data['wid'];
                    $updateArray = array();
                    if (trim($dateofcompletion != trim($proweight_data['dateofcompletion']))) {
                        $updateArray['dateofcompletion'] = $dateofcompletion;
                        unset($dateofcompletion);
                    }
                    if (trim($poid) != trim($proweight_data['poid'])) {
                        $updateArray['poid'] = $poid;
                        unset($status);
                    }
                    if (trim($status) != trim($proweight_data['status'])) {
                        $updateArray['status'] = $status;
                        unset($status);
                    }
                    if (isset($date_start)) {
                        if (trim(date_format(date_create($date_start), 'Y-m-d H:i:s')) != trim($proweight_data['date_start'])) {
                            $updateArray['date_start'] = date_format(date_create($date_start), 'Y-m-d H:i:s');
                            unset($date_start);
                        }
                    }
                    if (isset($end_date)) {
                        if (trim(date_format(date_create($end_date), 'Y-m-d H:i:s')) != trim($proweight_data['date_end'])) {
                            $updateArray['date_end'] = date_format(date_create($end_date), 'Y-m-d H:i:s');
                            unset($end_date);
                        }
                    }
                    if (trim($staffid) != trim($proweight_data['staffid'])) {
                        $updateArray['staffid'] = $staffid;
                        unset($staffid);
                    }
                    if (trim($staffname) != trim($proweight_data['staffname'])) {
                        $updateArray['staffname'] = $staffname;
                        unset($staffname);
                    }
                    if (trim($machineid) != trim($proweight_data['machineid'])) {
                        $updateArray['machineid'] = $machineid;
                        unset($machineid);
                    }
                    if (trim($mcid) != trim($proweight_data['mcid'])) {
                        $updateArray['mcid'] = $mcid;
                        unset($mcid);
                    }
                    #if (trim($machineModel) != trim($proweight_data['machineModel'])) {
                    #    $updateArray['machineModel'] = $machineModel;
                    #    unset($machineModel);
                    #}
                    if (trim($model) != trim($proweight_data['model'])) {
                        $updateArray['model'] = $model;
                        unset($model);
                    }
                    if (trim($packing) != trim($proweight_data['packing'])) {
                        $updateArray['packing'] = $packing;
                        unset($packing);
                    }
                    if (trim($machine_capacity_per_shift) != trim($proweight_data['machine_capacity_per_shift'])) {
                        $updateArray['machine_capacity_per_shift'] = $machine_capacity_per_shift;
                        unset($machine_capacity_per_shift);
                    }

                    if (!empty($updateArray)) {
                        echo "List of data needs update :<br>\n";
                        print_r($updateArray);
                        echo "<br>\n";
                        $updateResult = callSqlUpdate($updateArray, $table, $wid);
                        echo "Update result : $updateResult<br>\n";
                    } else {
                        echo "Data no need to update . <br>\n";
                    }
                    echo "<br>";
                }
            }
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        } finally {
            
        }
        ?>

        <!-- </table> -->



</html>