<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
        <title></title>
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
        <h2>SUMMARY KPI</h2>
        <div id='mainArea'>
            <form action='' method='POST'>
                <div> <!--period area-->
                    Period :
                    <select v-model='period' id='period' name='period' @change="summType=''">
                        <option v-for='data in periodList' v-bind:value='data'>{{data}}</option>
                    </select>
                </div>
                <div> <!--period area-->
                    Status :
                    <select v-model='jobstatus' id='jobstatus' name='jobstatus' @change="summType=''">
                        <option value='finished'>Finished Jobs</option>
                        <option value='unfinished'>Unfinished Jobs</option>
                    </select>
                    <button type="submit" >Submit</button>
                </div>
            </form>
            <br>
            <br>
            <div>
                <?php
                include_once 'class/dbh.inc.php';
                include_once 'class/variables.inc.php';
                include_once 'class/phhdate.inc.php';
                include_once 'class/joblistwork.inc.php';

                if (isset($_POST['period']) && (isset($_POST['jobstatus']))) {
                    $period = $_POST['period'];
                    $jobstatus = $_POST['jobstatus'];
                    $summType = 'all';
                    $day = '00';
                    $kpidetailstable = 'kpidetails_' . $period;
                    $year = '20' . substr($period, 0, 2);
                    $month = substr($period, 2, 2);
                    $date = $year . '-' . $month . '-' . $day;
                    echo "<div style='text-align:center'>";
                    echo "<b style='font-size:2em'>KPI MONTHLY SUMMARY BY STAFF NAME, MACHINE</b><br>";
                    echo "<h2>JOBS = " . strtoupper($jobstatus) . "&nbsp;&nbsp;PERIOD = $year-$month</h2><br>";
                    echo "</div>";

                    try {
                        if ($jobstatus == 'unfinished') {
                            throw new Exception('begin unfinished records', 191);
                        }
                        $staffList = get_distinctStaff($kpidetailstable, $date, $summType);
                        if ($staffList == 'empty') {
                            throw new Exception('There\'s no staff found!', 101);
                        }
                        foreach ($staffList as $data_staff) {
                            $staffid = $data_staff['staffid'];
                            $staffDetails = get_staffDetails($staffid);
                            if ($staffDetails != 'empty') {
                                $staffname = $staffDetails['name'];
                            } else {
                                $staffname = null;
                            }
                            $machineList = get_distinctMachine($kpidetailstable, $date, $summType, $staffid);
                            foreach ($machineList as $data_machine) {
                                $mcid = $data_machine['mcid'];
                                $machineid = $data_machine['machineid'];
                                $mcDetail = get_machineDetails($mcid);
                                if ($mcDetail != 'empty') {
                                    #echo "<pre>MachineLists : ";
                                    #print_r($mcDetail);
                                    #echo "</pre>";
                                    $machine_name = $mcDetail['name'];
                                    $machine_model = $mcDetail['model'];
                                    $index_per_hour = $mcDetail['index_per_hour'];
                                    $index_per_shift = $index_per_hour * 8;
                                } else {
                                    $machine_name = null;
                                    $machine_model = null;
                                    $index_per_hour = null;
                                    $index_per_shift = null;
                                }
                                $filteredDetails = get_filteredDetails($kpidetailstable, $date, $summType, $staffid, $mcid);
                                if ($filteredDetails != 'empty') {
                                    //begin calculate kpi (based on staffid and mcid
                                    $index_gain_sum = 0;
                                    #$det_kpi_row = array();
                                    $cnt = 0;
                                    foreach ($filteredDetails as $data_row) {
                                        $cnt++;
                                        $jd_qty = $data_row['jobdonequantity'];
                                        $unit_weight = $data_row['unit_weight'];
                                        $start_time = $data_row['start_time'];
                                        $end_time = $data_row['end_time'];
                                        if ($jd_qty) {
                                            $index_gain_in_kg = $jd_qty * $unit_weight;
                                        } else {
                                            $index_gain_in_kg = 0;
                                        }
                                        //fetch current KPI
                                        $kpiVal = get_kpiTimeTableDetails($start_time);
                                        #echo "kpiVal = $kpiVal<br>";
                                        $index_gain_sum = $index_gain_sum + ($index_gain_in_kg * $kpiVal);
                                    }
                                    if ($index_per_shift) {
                                        $calculatedKPI = round($index_gain_sum / $index_per_shift, 3);
                                    } else {
                                        $calculatedKPI = 0;
                                    }
                                    //create array of the current sum
                                    $det_kpi_row[] = array(
                                        'machineid' => $machineid,
                                        'machinename' => $machine_name,
                                        'machinemodel' => $machine_model,
                                        'index_gain_sum' => $index_gain_sum,
                                        'totalkpi' => $calculatedKPI,
                                        'data_found' => $cnt
                                    );
                                    //push this to det_KPI array
                                } else {
                                    
                                }
                            }
                            $det_KPI[] = array(
                                'staffid' => $staffid,
                                'staffname' => $staffname,
                                'details' => $det_kpi_row
                            );
                            unset($det_kpi_row);
                        }
                    } catch (Exception $ex) {
                        $code = $ex->getCode();
                        switch ($code) {
                            case 101: //cannot find staff list
                                //check if current status is unfinished or not
                                echo "Cannot find Staff for period = $period.<br>";
                                break;
                            case 191: //begin unfinished jobs
                                $qrU = "SELECT * FROM $kpidetailstable WHERE poid IS NULL AND jlfor = 'CJ'";
                                $objSQLU = new SQL($qrU);
                                $kpiData = $objSQLU->getResultRowArray();
                                echo "qr = $qrU<br>";
                                echo "Found " . count($kpiData) . " Datas.<br>";
                                #echo "<pre>";
                                #print_r($result);
                                #echo "</pre>";
                                $unfinKPIDetails = array();
                                foreach ($kpiData as $data_row) {
                                    $schedulingtable = "production_scheduling_$period";
                                    $jobcode = $data_row['jobcode'];
                                    $cuttingtype = $data_row['cuttingtype'];
                                    $sid = $data_row['sid'];
                                    $qrSID = "SELECT process FROM $schedulingtable WHERE sid = $sid";
                                    $objSQLSID = NEW SQL($qrSID);
                                    $resultProcessCode = $objSQLSID->getResultOneRowArray();
                                    $processcode = $resultProcessCode['process'];
                                    $totalquantity = $data_row['totalquantity'];
                                    $objJWDetail = new JOB_WORK_DETAIL($jobcode, $cuttingtype, $processcode, $totalquantity);
                                    $ex_jobwork = $objJWDetail->get_ex_jobWork();
                                    #echo "sid = $sid; jobcode = $jobcode; cuttingtype = $cuttingtype; processcode = $processcode; totalquantity = $totalquantity<br>";
                                    #print_r($ex_jobwork);
                                    #echo "<br>";
                                    if ($ex_jobwork['millingwidth'] == 'true' && $ex_jobwork['millinglength'] == 'true') {
                                        
                                    } else {
                                        foreach ($ex_jobwork as $processname => $processstatus) {
                                            if ($processstatus == 'true') {
                                                $rand_index_per_shift = get_randomVirtualValues($processname);
                                                $unit_weight = $data_row['unit_weight'];
                                                if ($totalquantity != 0) {
                                                    $index_gain_in_kg = $unit_weight * $totalquantity;
                                                } else {
                                                    $index_gain_in_kg = 0;
                                                }
                                                $inv_Nu_KPI = $index_gain_in_kg / $rand_index_per_shift * 9.8;
                                                //slide in the individual value into data_row;
                                                $offset = 12;
                                                $data_row['index_gain_in_kg'] = $index_gain_in_kg;
                                                $new_datarow = array_slice($data_row, 0, $offset, true) +
                                                        array('individual_kpi' => number_format(round($inv_Nu_KPI, 7), 7)) +
                                                        array_slice($data_row, $offset, NULL, true);
                                                $unfinKPIDetails[$processname][] = $new_datarow;
                                            }
                                        }
                                    }
                                }
                                foreach ($unfinKPIDetails as $processname => $details) {
                                    $machinename = get_virtualMachineName($processname);
                                    $totaldata = count($details);
                                    $sum_index_gain = 0;
                                    $sum_KPI = 0;
                                    foreach ($details as $detail_row) {
                                        $sum_index_gain += $detail_row['index_gain_in_kg'];
                                        $sum_KPI += $detail_row['individual_kpi'];
                                    }
                                    $det_KPI[] = array(
                                        'machinename' => $machinename,
                                        'index_gain_sum' => $sum_index_gain,
                                        'totalkpi' => $sum_KPI,
                                        'data_found' => $totaldata
                                    );
                                }
                                #echo "<pre>";
                                #print_r($det_KPI);
                                #echo "</pre>";
                                break;
                        }
                    }
                    #echo "<pre>"
                    #. "Data List :";
                    #print_r($det_KPI);
                    #echo "</pre>";
                    if ($jobstatus == 'finished') {
                        foreach ($det_KPI as $data) {
                            $stid = $data['staffid'];
                            $stnm = $data['staffname'];
                            $dtl = $data['details'];
                            ?>
                            <table style="width:auto">
                                <thead>
                                    <tr>
                                        <th><?php echo $stid; ?></th>
                                        <th><?php echo $stnm; ?></th>
                                        <th></th>
                                    </tr>
                                </thead>
                            </table>
                            <table>
                                <thead>
                                    <?php
                                    foreach ($dtl as $data_row) {
                                        echo "<tr>";
                                        #print_r();
                                        foreach ($data_row as $key => $row) {
                                            echo "<th style='width:10%'>$key</th>";
                                        }
                                        echo "</tr>";
                                        break;
                                    }
                                    ?>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($dtl as $data_row) {
                                        echo "<tr>";
                                        #print_r();
                                        foreach ($data_row as $key => $val) {
                                            echo "<td style='width:10%'>$val</td>";
                                        }
                                        echo "</tr>";
                                    }
                                    ?>

                                </tbody>
                            </table>
                            <br>
                            <br>
                            <?php
                        }
                    } else {
                        ?>
                        <table>
                            <thead>
                                <tr>
                                    <?php
                                    foreach($det_KPI as $data_row){
                                        foreach($data_row as $key=>$val){
                                            echo "<th>$key</th>";
                                        }
                                        break;
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                    <?php
                                    foreach($det_KPI as $data_row){
                                        echo "<tr>";
                                        foreach($data_row as $key=>$val){
                                            echo "<td>$val</td>";
                                        }
                                        echo "</tr>";
                                    }
                                    ?>
                                
                            </tbody>
                        </table>
                        <?php
                    }
                    ?>
                    <?php
                }

                function get_virtualMachineName($processname) {
                    switch ($processname) {
                        case 'cncmachining':
                            $machinename = 'Virtual CNC Machine';
                            break;
                        case 'manual':
                            $machinename = 'Virtual Manual Cut Machine';
                            break;
                        case 'bandsaw':
                            $machinename = 'Virtual Bandsaw Machine';
                            break;
                        case 'milling':
                            $machinename = 'Virtual Milling Machine (Surface)';
                            break;
                        case 'millingwidth':
                            $machinename = 'Virtual Milling Machine (Side|Width)';
                            break;
                        case 'millinglength':
                            $machinename = 'Virtual Milling Machine (Side|Length)';
                            break;
                        case 'roughgrinding':
                            $machinename = 'Virtual Rough Grinding Machine';
                            break;
                        case 'precisiongrinding':
                            $machinename = 'Virtual Surface Grinding Machine';
                            break;
                    }
                    return $machinename;
                }

                function get_randomVirtualValues($processname) {
                    $qr = "SELECT DISTINCT index_per_hour FROM machine WHERE index_per_hour IS NOT NULL AND index_per_hour != 0 AND ";
                    switch ($processname) {
                        case 'cncmachining':
                            $qr .= "  machineid LIKE 'CNC%' ";
                            break;
                        case 'manual':
                            $qr .= "  machineid LIKE 'MCT%' ";
                            break;
                        case 'bandsaw':
                            $qr .= "  machineid LIKE 'BSC%' ";
                            break;
                        case 'milling':
                            $qr .= "  machineid LIKE 'MMG%' AND name LIKE '%surface%' ";
                            break;
                        case 'millingwidth':
                            $qr .= "  machineid LIKE 'MMG%' AND name LIKE '%side%' ";
                            break;
                        case 'millinglength':
                            $qr .= "  machineid LIKE 'MMG%' AND name LIKE '%side%' ";
                            break;
                        case 'roughgrinding':
                            $qr .= "  machineid LIKE 'RGG%' ";
                            break;
                        case 'precisiongrinding':
                            $qr .= "  machineid LIKE 'SGG%' ";
                            break;
                    }
                    $objSQL = new SQL($qr);
                    $result = $objSQL->getResultRowArray();
                    $arr_index_per_hour = array();
                    foreach ($result as $data_row) {
                        foreach ($data_row as $key => $val) {
                            $arr_index_per_hour[] = $val;
                        }
                    }
                    #echo"<pre>Index Per Hour Array Random Value (Virtual $processname):";
                    #print_r($arr_index_per_hour);
                    #echo "</pre>";
                    $rand_index_per_hour = array_rand($arr_index_per_hour, 1);
                    #echo "Selected Random Value : {$arr_index_per_hour[$rand_index_per_hour]}<br>";
                    return $arr_index_per_hour[$rand_index_per_hour];
                }

                function get_staffDetails($staffid) {
                    $qr = "SELECT * FROM admin_staff WHERE staffid = '$staffid'";
                    $objSQL = new SQL($qr);
                    $result = $objSQL->getResultOneRowArray();
                    if (!empty($result)) {
                        return $result;
                    } else {
                        return 'empty';
                    }
                }

                function get_kpiTimeTableDetails($start_time) {
                    $date = date_format(date_create($start_time), 'Y-m-d');
                    $time = date_format(date_create($start_time), 'H:i:s');
                    $shift1S = date_format(date_create('08:00:00'), 'H:i:s');
                    $shift1E = date_format(date_create('19:59:59'), 'H:i:s');
                    $shift2S = date_format(date_create('20:00:00'), 'H:i:s');
                    $shift2E = date_format(date_create('07:59:59'), 'H:i:s');
                    $qr = "SELECT * FROM kpitimetable WHERE date = '$date'";
                    $objSQL = new SQL($qr);
                    $result = $objSQL->getResultOneRowArray();
                    if (!empty($result)) {
                        if ($time >= $shift1S && $time <= $shift1E) {
                            $shiftVal = $result['shift1'];
                        } elseif ($time >= $shift2S && $time <= $shift2E) {
                            $shiftVal = $result['shift2'];
                        }
                        if ($shiftVal = 1) {
                            $kpiVal = 9.8;
                        } elseif ($shiftVal = 0) {
                            $kpiVal = 7.35;
                        }
                        return $kpiVal;
                    } else {
                        return 'empty';
                    }
                }

                function get_filteredDetails($table, $date, $summType, $staffid, $mcid) {
                    if ($summType == 'daily') {
                        $qr = "SELECT * FROM $table "
                                . "WHERE poid IS NOT NULL AND jlfor = 'CJ' AND NOT jobtype ='cncmachining' AND staffid = '$staffid' "
                                . "AND mcid = $mcid AND DATE_FORMAT(dateofcompletion,'%Y %m %d') = DATE_FORMAT('$date','%Y %m %d') "
                                . "ORDER BY dateofcompletion, staffid, mcid ASC";
                    } elseif ($summType == 'all') {
                        $qr = "SELECT * FROM $table "
                                . "WHERE poid IS NOT NULL AND jlfor = 'CJ' AND NOT jobtype ='cncmachining' AND staffid = '$staffid' "
                                . "AND mcid = $mcid AND DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m') "
                                . "ORDER BY dateofcompletion, staffid, mcid ASC";
                    }
                    $objSQL = new SQL($qr);
                    $result = $objSQL->getResultRowArray();
                    if (!empty($result)) {
                        return $result;
                    } else {
                        return 'empty';
                    }
                }

                function get_machineDetails($mcid) {
                    $qr = "SELECT * FROM machine WHERE mcid = $mcid";
                    $objSQL = new SQL($qr);
                    $result = $objSQL->getResultOneRowArray();
                    if (!empty($result)) {
                        return $result;
                    } else {
                        return 'empty';
                    }
                }

                function get_distinctMachine($table, $date, $summType, $staffid) {
                    if ($summType == 'daily') {
                        $qr = "SELECT DISTINCT mcid,machineid FROM $table WHERE poid IS NOT NULL AND jlfor = 'CJ' AND NOT jobtype ='cncmach' AND staffid = '$staffid' AND DATE_FORMAT(dateofcompletion,'%Y %m %d') = DATE_FORMAT('$date','%Y %m %d') ORDER BY mcid ASC";
                    } elseif ($summType == 'all') {
                        $qr = "SELECT DISTINCT mcid,machineid FROM $table WHERE poid IS NOT NULL AND jlfor = 'CJ' AND NOT jobtype ='cncmach' AND staffid = '$staffid' AND DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m') ORDER BY mcid ASC";
                    }
                    $objSQL = new SQL($qr);
                    $result = $objSQL->getResultRowArray();
                    if (!empty($result)) {
                        return $result;
                    } else {
                        return 'empty';
                    }
                }

                function get_distinctStaff($table, $date, $summType) {
                    if ($summType == 'daily') {
                        $qr = "SELECT DISTINCT staffid FROM $table WHERE poid IS NOT NULL AND jlfor = 'CJ' AND NOT jobtype ='cncmach' AND DATE_FORMAT(dateofcompletion,'%Y %m %d') = DATE_FORMAT('$date','%Y %m %d')";
                    } elseif ($summType == 'all') {
                        $qr = "SELECT DISTINCT staffid FROM $table WHERE poid IS NOT NULL AND jlfor = 'CJ' AND NOT jobtype ='cncmach' AND DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m')";
                    }
                    $objSQL = new SQL($qr);
                    $result = $objSQL->getResultRowArray();
                    if (!empty($result)) {
                        return $result;
                    } else {
                        return 'empty';
                    }
                }
                ?>
            </div>
        </div>
        <script>
var sumKPIVue = new Vue({
    el: '#mainArea',
    data: {
        phpajaxresponsefile: 'kpidetails.axios.php',
        period: '',
        summType: '',
        day: '',
        loading: false,
        jobstatus: 'finished',

        periodList: '',
        dayList: '',
        kpiList: ''
    },
    watch: {
        summType: function () {
            if (this.summType === 'daily') {
                this.getDayList();
            }
        }
    },
    methods: {
        getPeriod: function () {
            axios.post(this.phpajaxresponsefile, {
                action: 'getPeriod'
            }).then(function (response) {
                console.log('on getPeriod....');
                console.log(response.data);
                sumKPIVue.periodList = response.data;
            });
        },
        getDayList: function () {
            axios.post(this.phpajaxresponsefile, {
                action: 'getDayList',
                period: sumKPIVue.period
            }).then(function (response) {
                console.log('on getDayList');
                console.log(response.data);
                sumKPIVue.dayList = response.data;
            });
        },
        getKPIDetail: function () {
            this.loading = true;
            axios.post(this.phpajaxresponsefile, {
                action: 'getKPIDetail',
                period: sumKPIVue.period,
                staffid: sumKPIVue.staffid,
                summType: sumKPIVue.summType,
                day: sumKPIVue.day
            }).then(function (response) {
                console.log('on getKPIDetail...');
                console.log(response.data);
                sumKPIVue.kpiList = response.data;
                sumKPIVue.loading = false;
            });
        }
    },
    mounted: function () {
        this.getPeriod();
    }
});
        </script>
    </body>
</html>
