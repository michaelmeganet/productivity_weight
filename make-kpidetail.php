<!DOCTYPE html>
<html>
    <head>
        <script src="./assets/jquery-2.1.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    </head>
    <body>
        <?php
        include_once 'class/dbh.inc.php';
        include_once 'class/variables.inc.php';
        include_once 'class/phhdate.inc.php';
        // echo "\$period = $period <br>";
        // echo "<br>";
        ?>
        <h2>Generate Kpi Detail Table</h2>
        <div id="mainArea">
            <form action='' method='post'>
                <label> Select Period : </label>
                <select id="period" name='period' v-model='period'>
                    <option v-for='data in periodList' bind:value='data'>{{data}}</option>
                </select>            
                <input type='submit' value='Generate KPI Detail Table' v-show='period != ""' />
            </form>
        </div>
        <div>
            <a href="./show-kpidetail-table.php">view KPI Detail Table</a>
        </div>

        <?php

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
            echo "\$sqlUpdate = $sqlUpdate <br>";
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
            echo "\$sqlInsert = $sqlInsert <br>";
            $objInsert = new SQLBINDPARAM($sqlInsert, $insertArray);
            $result = $objInsert->InsertData2();
            #echo "$result <br>";
            return $result;
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
        ?>
        <?php
        if (isset($_POST['period'])) {
            $period = $_POST['period'];
        }
        if (isset($period)) {
            $objNPeriod = new NextPeriod($period);
            $nextPeriod = $objNPeriod->get_nextPeriod();
            $year = '20' . substr($period, 0, 2);
            $month = substr($period, 2, 2);
            $date = $year . '-' . $month . '-00';


            $cntIns = 0;
            $cntUpd = 0;
            $errIns = 0;
            $errUpd = 0;
            $cnt = 0;
            $cntNoChange = 0;

            // 'kpidid' => $kpidid,
            // 'sid' => $sid,
            // 'qid' => $qid,
            // 'jobcode' => $jobcode,
            // 'jobtype' => $jobtype,
            // 'grade' => $grade,
            // 'totalquantity' => $totalquantity,
            // 'remainingquantity' => $remainingquantity,
            // 'jobdonequantity' => $jobdonequantity,
            // 'unit_weight' => $unit_weight,
            // 'total_weight' => $total_weight,
            // 'dimensions' => $dimensions,
            // 'jlfor' => $jlfor,
            // 'index_gain_in_kg' => $index_gain_in_kg,
            // 'jobno' => $jobno,
            // 'dateofcompletion' => $dateofcompletion,
            // 'cid' => $cid,
            // 'poid' => $poid,
            // 'cuttingtype' => $cuttingtype,
            // 'staffname' => $staffname,
            // 'machineModel' => $machineModel,
            // 'start_time' => $start_time,
            // 'end_time' => $end_time,
            // 'duration' => $duration,
            // 'model' => $model,
            try {

                $prodWArray = get_weightDetails($period, $nextPeriod, $date); //fetch data based on period
                echo "Found " . count($prodWArray) . " data.<br>";
                #print_r($prodWArray);
                //begin loop on production_weight data:
                $out_array = array();
                $ctx = 0;
                foreach ($prodWArray as $prodWData) {
                    try {
                        $ctx++;
                        echo "====== Begin Process data No.$ctx ======<br>";
                        $kpidid = null;
                        $poid = $prodWData['poid'];
                        $sid = $prodWData['sid'];
                        $quono = $prodWData['quono'];
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
                        $staffid = $prodWData['staffid'];
                        $staffname = $prodWData['staffname'];
                        $mcid = $prodWData['mcid'];
                        $machineid = $prodWData['machineid'];
                        #$machineModel = $prodWData['machineModel'];
                        $model = $prodWData['model'];

                        if ($poid) {
                            echo "Found poid = $poid<br>";
                            $prodOutArray = get_outputDetails($period, $nextPeriod, $poid, $sid);
                            #$kpidid++;
                            $jobtype = $prodOutArray['jobtype'];
                            $remainingquantity = $prodOutArray['remainingquantity'];
                            $jobdonequantity = $prodOutArray['quantity'];
                            $index_gain_in_kg = round($jobdonequantity * $unit_weight, 2);
                            $start_time = $prodOutArray['date_start'];
                            $end_time = $prodOutArray['date_end'];
                            #echo "start = $start_time; end = $end_time;\n";
                            if ($start_time == null || $end_time == null) {
                                echo "start time / end time is null<br>";
                                $duration = 0;
                            } else {
                                echo "start time / end time exists<br>";
                                $diff = abs(strtotime($end_time) - strtotime($start_time));
                                $hours = ($diff / 3600); #floor($diff / 3600);
                                #$minutes = floor(($diff - ($hours * 60 * 60)) / 60);
                                #$seconds = floor(($diff - ($hours * 60 * 60) - ($minutes * 60)));
                                #$duration = "$hours hours, $minutes minutes, $seconds seconds";
                                $duration = round($hours, 2);
                            }
                        } else {
                            echo "Cannot find poid.. <br>";
                            $jobtype = null;
                            $remainingquantity = null;
                            $jobdonequantity = null;
                            $index_gain_in_kg = 0;
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
                            'quono' => $quono,
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
                            'index_gain_in_kg' => $index_gain_in_kg,
                            'jobno' => $jobno,
                            'dateofcompletion' => $dateofcompletion,
                            'cid' => $cid,
                            'poid' => $poid,
                            'cuttingtype' => $cuttingtype,
                            'staffid' => $staffid,
                            'staffname' => $staffname,
                            'mcid' => $mcid,
                            'machineid' => $machineid,
                            #'machineModel' => $machineModel,
                            'start_time' => $start_time,
                            'end_time' => $end_time,
                            'duration' => $duration,
                            'model' => $model,
                        );
                        echo "processing these data :<br>";
                        print_r($out_array);
                        echo "<br>";
                        $cnt++;
                        $existCheck = check_existing_KPI_detail($out_array, $period);
                        if ($existCheck == 'empty') {
                            echo "These data is not yet inserted to database<br>";
                            $insResult = insert_KPI_Detail($out_array, $period);
                            if ($insResult == 'insert ok!') {
                                echo "<span style='color:green'>Insert successful!</span><br>";
                                $cntIns++;
                            } else {
                                echo "<span style='color:red'><strong>Insert failed! Please check these data!</strong></span><br>";
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
                                echo "<span style='background-color:black;color:white'><strong>Found existing data : <br></strong></span>";
                                print_r($existingData);
                                echo "<br>";
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
                                if ($index_gain_in_kg != $existingData['index_gain_in_kg']) {
                                    $upd_array['index_gain_in_kg'] = $index_gain_in_kg;
                                    unset($index_gain_in_kg);
                                }
                                if ($dateofcompletion != $existingData['dateofcompletion']) {
                                    $upd_array['dateofcompletion'] = $dateofcompletion;
                                    unset($dateofcompletion);
                                }
                                if ($poid != $existingData['poid']) {
                                    $upd_array['poid'] = $poid;
                                    unset($poid);
                                }
                                if ($staffid != $existingData['staffid']) {
                                    $upd_array['staffid'] = $staffid;
                                    unset($staffid);
                                }
                                if ($staffname != $existingData['staffname']) {
                                    $upd_array['staffname'] = $staffname;
                                    unset($staffname);
                                }
                                if ($mcid != $existingData['mcid']) {
                                    $upd_array['mcid'] = $mcid;
                                    unset($mcid);
                                }
                                if ($machineid != $existingData['machineid']) {
                                    $upd_array['machineid'] = $machineid;
                                    unset($machineid);
                                }
                                #if ($machineModel != $existingData['machineModel']) {
                                #    $upd_array['machineModel'] = $machineModel;
                                #    unset($machineModel);
                                #}
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
                                    echo "Data needed to be updated for kpidid = $kpidid: <br>";
                                    echo "<pre>";
                                    print_r($upd_array);
                                    echo "</pre>";
                                    $updResult = update_KPI_Detail($upd_array, $period, $kpidid);
                                    if ($updResult == 'Update ok!') {
                                        echo "<span style='color:green'>Update successful!</span><br>";
                                        $cntUpd++;
                                    } else {
                                        echo "<span style='color:red'><strong>Update failed! Please check these data!</strong></span><br>";
                                        $errUpd++;
                                    }
                                } else {
                                    echo "<span style='color:blue'>Data doesn't need to be updated for kpidid = $kpidid</span><br>";
                                    $cntNoChange++;
                                }
                                #$kpidid++;
                                break;
                            case 112: //Cannot find output data in production_output current period
                                echo"cannot find output for sid = $sid, poid = $poid<br>";
                                break;
                            case 113: //Cannot find output data in production_output next period
                                echo"cannot find output for sid = $sid, poid = $poid<br>";
                                break;
                        }
                    }

                    echo "====== End Process data No.$ctx ======<br><br>";
                }
            } catch (Exception $ex) {
                $code = $ex->getCode();
                $msg = $ex->getMessage();
                switch ($code) {
                    case 101:
                        echo "Cannot find data! query->$msg<br>";
                        #$output = array('errorcode' => $code, 'message' => 'Cannot find data', 'query' => $msg);
                        #echo json_encode($err_array);
                        break;
                }
            }
            $output = array('msg' => "Found $cnt Data.\nSuccess Insert : $cntIns; Fail Insert : $errIns; Success Update : $cntUpd; Fail Update : $errUpd; Not Changed Data : $cntNoChange");
            echo $output['msg'] . '<br>';
        }
        ?>


        <script>
var kpiDetailsVue = new Vue({
    el: '#mainArea',
    data: {
        phpajaxresponsefile: 'kpidetails.axios.php',
        period: '',
        loading: false,
        periodList: ''
    },
    watch: {
    },
    methods: {
        getPeriod: function () {
            axios.post(this.phpajaxresponsefile, {
                action: 'getPeriod' // var_dump object(stdClass)#1 (1) {  ["action"]=>   string(9) "getPeriod"
            }).then(function (response) {
                console.log('onGetPeriod Function....');
                console.log(response.data);
                kpiDetailsVue.periodList = response.data;
            });
        }
    },

    mounted: function () {
        this.getPeriod();
    }
});
        </script>
    </body>
    <!--
        <table>
            <tr>
                <th>kpidid</th><th>sid</th><th>jobcode</th><th>jobtype</th><th>grade</th><th>total qty</th><th>remaining qty</th><th>jobdone qty</th>
                <th>unit_weight</th><th>total_weight</th><th>dimension</th><th>jlfor</th><th>index gain in kg</th><th>jobno</th>
                <th>date of completion</th><th>cid</th><th>poid</th><th>cutting type</th><th>staff name</th><th>Machine name</th><th>start time</th>
                <th>end time</th><th>duration</th><th>model</th>
            </tr>
            <tr>
                <td><?php #echo "$kpidid";             ?></td><td><?php #echo "$sid";             ?></td><td><?php #echo "$jobcode";             ?></td>
                <td><?php #echo "$jobtype";             ?></td><td><?php #echo "$grade";             ?></td><td><?php #echo "$totalquantity";             ?></td>
                <td><?php #echo "$remainingquantity";             ?></td><td><?php #echo "$jobdonequantity";             ?></td>
                <td><?php #echo "$unit_weight";             ?></td><td><?php #echo "$total_weight";             ?></td><td><?php #echo "$dimensions";             ?></td>
                <td><?php #echo "$jlfor";             ?></td><td><?php #echo "$index_gain_in_kg";             ?></td><td><?php #echo "$jobno";             ?></td>
                <td><?php #echo "$dateofcompletion";             ?></td><td><?php #echo "$cid";             ?></td><td> <?php #echo $poid;             ?></td>
                <td><?php #echo $cuttingtype;             ?></td><td><?php #echo $staffname;             ?></td><td><?php #echo $machineModel;             ?></td>
                <td><?php #echo $start_time;             ?></td><td><?php #echo $end_time;             ?></td><td><?php #echo $duration;             ?></td><td><?php #echo $model;             ?></td>
    
            </tr>
        </table>
    -->
</html>
