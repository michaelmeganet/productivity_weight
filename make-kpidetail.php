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
        <?php
        include_once 'class/dbh.inc.php';
        include_once 'class/variables.inc.php';
        include_once 'class/phhdate.inc.php';
        $period = '2009';
        // echo "\$period = $period <br>";
        // echo "<br>";
        ?>
        <h2>HTML Table</h2>

        <table>
            <tr>
                <th>kpidid</th><th>sid</th><th>jobcode</th><th>jobtype</th><th>grade</th><th>total qty</th><th>remaining qty</th><th>jobdone qty</th>
                <th>unit_weight</th><th>total_weight</th><th>dimension</th><th>jlfor</th><th>index gain in kg</th><th>jobno</th>
                <th>date of completion</th><th>cid</th><th>poid</th><th>cutting type</th><th>staff name</th><th>Machine name</th><th>start time</th>
                <th>end time</th><th>duration</th><th>model</th>
            </tr>


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
            ?>
            <?php
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


            $prowtab = "production_weight_" . $period;
            $prow2tab = "production_weight_" . $nextPeriod;
            $qr = "(SELECT * FROM $prowtab WHERE DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m')) ";
            // if (check_table($prow2tab)) {
            //     $qr .= "UNION (SELECT * FROM $prow2tab WHERE DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m'))";
            // }
            // echo " \$qr =  $qr <br>";
            $objSQL = new SQL($qr);
            $result = $objSQL->getResultRowArray();

            $prodWArray = $result;

            $kpidid = 1;
            $out_array = array();

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
            foreach ($prodWArray as $prodWData) {

                // echo "list down array of prodWData<br>";
                // print_r($prodWDatay);
                // echo "<br>";
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
                    //echo "poid = $poid\n";
                    $prodOutArray = get_outputDetails($period, $nextPeriod, $poid, $sid);
                    $kpidid++;
                    $jobtype = $prodOutArray['jobtype'];
                    $remainingquantity = $prodOutArray['remainingquantity'];
                    $jobdonequantity = $prodOutArray['quantity'];
                    $index_gain_in_kg = round($jobdonequantity * $unit_weight, 2);
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
                    $index_gain_in_kg = 0;
                    $start_time = null;
                    $end_time = null;
                    #echo "start = $start_time; end = $end_time;\n";
                    $duration = 0;
                }
                ?>


                <tr>
                    <td><?php echo "$kpidid"; ?></td><td><?php echo "$sid"; ?></td><td><?php echo "$jobcode"; ?></td>
                    <td><?php echo "$jobtype"; ?></td><td><?php echo "$grade"; ?></td><td><?php echo "$totalquantity"; ?></td>
                    <td><?php echo "$remainingquantity"; ?></td><td><?php echo "$jobdonequantity"; ?></td>
                    <td><?php echo "$unit_weight"; ?></td><td><?php echo "$total_weight"; ?></td><td><?php echo "$dimensions"; ?></td>
                    <td><?php echo "$jlfor"; ?></td><td><?php echo "$index_gain_in_kg"; ?></td><td><?php echo "$jobno"; ?></td>
                    <td><?php echo "$dateofcompletion"; ?></td><td><?php echo "$cid"; ?></td><td> <?php echo $poid; ?></td>
                    <td><?php echo $cuttingtype; ?></td><td><?php echo $staffname; ?></td><td><?php echo $machineModel; ?></td>
                    <td><?php echo $start_time; ?></td><td><?php echo $end_time; ?></td><td><?php echo $duration; ?></td><td><?php echo $model; ?></td>

                </tr>

                <?php
                /* $out_array = array(
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
                  'index_gain_in_kg' => $index_gain_in_kg,
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
                  ); */
                $cnt++;
                // if ($existCheck == 'empty') {
                //     //$insResult = insert_KPI_Detail($out_array, $period);
                //     if ($insResult == 'insert ok!') {
                //         $cntIns++;
                //     } else {
                //         $errIns++;
                //     }
                // } else {
                //     throw new Exception('There\'s existing data', 111);
                // }
            }
            ?>
        </table>
    </body>
</html>
