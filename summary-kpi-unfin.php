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
        <h2>SUMMARY KPI - UNFINISHED JOBLISTS</h2>
        <div id='mainArea'>
            <form action='' method='POST'>
                <div> <!--period area-->
                    Period :
                    <select v-model='period' id='period' name='period'>
                        <option v-for='data in periodList' v-bind:value='data'>{{data}}</option>
                    </select>
                </div>
                <div> <!--Virtual Machine Area-->
                    Process Name :
                    <select v-model='processname' id='processname' name='processname'>
                        <option v-for='data in processNames' v-bind:value='data'>{{data}}</option>
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

                if (isset($_POST['period']) && isset($_POST['processname'])) {
                    $period = $_POST['period'];
                    $processname = $_POST['processname'];
                    $kpidetailstable = 'kpidetails_' . $period;
                    $year = '20' . substr($period, 0, 2);
                    $month = substr($period, 2, 2);
                    $currmonth = date('m');
                    $currday = date('d');
                    $curryear = date('Y');
                    if ($month == $currmonth && $year == $curryear) {
                        $noofdays = $currday;
                        $overTxt = '(Current Date)';
                    } else {
                        $date = $year . "-" . $month . "-01";
                        $noofdays = date_format(date_create($date), 't');
                        $overTxt = '(Last Date of Month)';
                    }
                    echo "<div style='text-align:center'>";

                    echo "<b style='font-size:2em'>ESTIMATED KPI INDEX MONTHLY UNFINISHED JOBS DETAILS REPORT</b><br>";
                    echo "<b style='font-size:1.5em>PERIOD : 01-$month-$year to $noofdays-$month-$year  $overTxt </b><br>";
                    echo "<h2>PROCESS NAME : ". strtoupper($processname)." </h2><br>";

                    echo "</div>";

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
                            foreach ($ex_jobwork as $key => $processstatus) {
                                if ($processstatus == 'true' && $processname == $key) {
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
                                            array('estimated_individual_kpi' => number_format(round($inv_Nu_KPI, 7), 7)) +
                                            array_slice($data_row, $offset, NULL, true);
                                    $unfinKPIDetails[$processname][] = $new_datarow;
                                }
                            }
                        }
                    }
                    #echo "<pre>";
                    #print_r($unfinKPIDetails);
                    #echo "</pre>";
                    foreach ($unfinKPIDetails as $processname => $detail_row) {
                        $machinename = get_virtualMachineName($processname);
                        ?>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width:10%">Machine Name :</th>
                                    <th><?php echo $machinename; ?></th>
                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                        </table>
                            <table>
                                <thead>
                                    <?php
                                    foreach ($detail_row as $val_row) {
                                        echo "<tr>";
                                        foreach ($val_row as $key => $val) {
                                            echo "<td>$key</td>";
                                        }
                                        echo "</tr>";
                                        break;
                                    }
                                    ?>
                                </thead>
                                <tbody>
                                    <?php
                                    $sum_totalweight = 0;
                                    $sum_invkpi = 0;
                                    foreach ($detail_row as $val_row) {
                                        echo "<tr>";
                                        foreach ($val_row as $key => $val) {
                                            echo "<td>$val</td>";
                                        }
                                        echo "</tr>";
                                        $sum_totalweight += floatval($val_row['total_weight']);
                                        $sum_invkpi += floatval($val_row['estimated_individual_kpi']);
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="11" style="text-align:right"><b>Sum of Total Weight :</b></td>
                                        <td><b><?php echo $sum_totalweight; ?></b></td>
                                    </tr>
                                    <tr>
                                        <td colspan="12" style="text-align:right"><b>Sum of Estimated Individual KPI :</b></td>
                                        <td><b><?php echo number_format(round($sum_invkpi, 7), 7); ?></b></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Total KPI :</th>
                                    <th><?php echo $sum_invkpi; ?></th>
                                </tr>
                            </thead>
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
                    #if ($summType == 'daily') {
                    $qr = "SELECT * FROM $table "
                            . "WHERE poid IS NOT NULL AND jlfor = 'CJ' AND NOT jobtype ='cncmach' AND staffid = '$staffid' "
                            . "AND mcid = $mcid AND DATE_FORMAT(dateofcompletion,'%Y %m %d') = DATE_FORMAT('$date','%Y %m %d') "
                            . "ORDER BY dateofcompletion, staffid, mcid ASC";
                    /* } elseif ($summType == 'all') {
                      $qr = "SELECT * FROM $table "
                      . "WHERE poid IS NOT NULL AND jlfor = 'CJ' AND NOT jobtype ='cncmach' AND staffid = '$staffid' "
                      . "AND mcid = $mcid AND DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m') "
                      . "ORDER BY dateofcompletion, staffid, mcid ASC";
                      } */
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
                    #if ($summType == 'daily') {
                    $qr = "SELECT DISTINCT mcid,machineid FROM $table WHERE poid IS NOT NULL AND jlfor = 'CJ' AND NOT jobtype ='cncmach' AND staffid = '$staffid' AND DATE_FORMAT(dateofcompletion,'%Y %m %d') = DATE_FORMAT('$date','%Y %m %d') ORDER BY mcid ASC";
                    /* } elseif ($summType == 'all') {
                      $qr = "SELECT DISTINCT mcid,machineid FROM $table WHERE poid IS NOT NULL AND jlfor = 'CJ' AND NOT jobtype ='cncmach' AND staffid = '$staffid' AND DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m') ORDER BY mcid ASC";
                      } */
                    $objSQL = new SQL($qr);
                    $result = $objSQL->getResultRowArray();
                    if (!empty($result)) {
                        return $result;
                    } else {
                        return 'empty';
                    }
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

                function get_distinctStaff($table, $date, $summType) {
                    #if ($summType == 'daily') {
                    $qr = "SELECT DISTINCT staffid FROM $table WHERE poid IS NOT NULL AND jlfor = 'CJ' AND NOT jobtype ='cncmach' AND DATE_FORMAT(dateofcompletion,'%Y %m %d') = DATE_FORMAT('$date','%Y %m %d')";
                    /* } elseif ($summType == 'all') {
                      $qr = "SELECT DISTINCT staffid FROM $table WHERE poid IS NOT NULL AND jlfor = 'CJ' AND NOT jobtype ='cncmach' AND DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m')";
                      } */
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
        <script>
var sumKPIVue = new Vue({
    el: '#mainArea',
    data: {
        phpajaxresponsefile: 'kpidetails.axios.php',
        period: '',
        summType: '',
        day: '',
        loading: false,
        processname: '',

        periodList: '',
        dayList: '',
        kpiList: '',
        processNames:['cncmachining','bandsaw','manual','milling','millingwidth','millinglength','roughgrinding','precisiongrinding']
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
