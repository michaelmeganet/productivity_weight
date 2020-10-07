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
                <div v-if='period!= ""'><!--summary monthly/daily area-->
                    Summary Type :
                    <select v-model='summType' id='summType' name='summType' @change='day = "";jobstatus="finished"'>
                        <option value='all'>Monthly</option>
                        <option value='daily'>Daily</option>
                    </select>
                </div>
                <div v-if='summType == "daily"'><!-- date selection -->
                    Date :
                    <select v-model="day" id="day" name="day">
                        <option v-for="data in dayList" v-bind:value="data">{{data}}</option>
                    </select></br>
                    Joblist Status :
                    <input type='text' v-model='jobstatus' id='jobstatus' name='jobstatus' value='finished' readonly/>
                    <button type="submit" >Submit</button>
                </div>
                <div v-else-if='summType != "daily" && summType!=""'>
                    Date :
                    <input type='text' id='day' name='day' value='Show All' readonly /></br>
                    Joblist Status :         
                    <select v-model='jobstatus' id="jobstatus" name="jobstatus">
                        <option value="finished">Finished</option>
                        <option value="unfinished">Unfinished</option>
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

                if (isset($_POST['period']) && isset($_POST['summType']) && isset($_POST['day']) && isset($_POST['jobstatus'])) {
                    $period = $_POST['period'];
                    $summType = $_POST['summType'];
                    $i_day = $_POST['day'];
                    $jobstatus = $_POST['jobstatus'];
                    $kpidetailstable = 'kpidetails_' . $period;
                    $year = '20' . substr($period, 0, 2);
                    $month = substr($period, 2, 2);
                    if ($summType == 'all') {
                        $i_day = 1;
                        $day = sprintf('%02d', $i_day);
                        $date = $year . '-' . $month . '-' . $day;
                        $totaldate = date_format(date_create($date), 't');
                    } else {
                        $day = sprintf('%02d', $i_day);
                        $date = $year . '-' . $month . '-' . $day;
                        $totaldate = $i_day;
                    }
                    $day = sprintf('%02d', $i_day);
                    #echo "selected day = $day<br>";
                    #echo "Total date in this month = $totaldate";
                    echo "<div style='text-align:center'>";
                    if ($summType == 'daily') {
                        echo "<b style='font-size:2em'>KPI INDEX DAILY DETAILS REPORT</b><br>";
                        echo "<h2>DATE = $day-$month-$year</h2><br>";
                    } else {
                        echo "<b style='font-size:2em'>KPI INDEX MONTHLY DETAILS REPORT</b><br>";
                        echo "<h2>DATE = $month-$year</h2><br>";
                    }
                    echo "</div>";
                    if ($jobstatus == 'unfinished') {
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
                    } else {
                        for ($i = $i_day; $i <= $totaldate; $i++) {
                            $day = sprintf('%02d', $i);
                            $date = $year . '-' . $month . '-' . $day;
                            #echo "<h3>Processing Date '$date'</h3><br>";
                            try {
                                $staffList = get_distinctStaff($kpidetailstable, $date, $summType);
                                if ($staffList == 'empty') {
                                    throw new Exception('There\'s no staff found!', 101);
                                }
                                #echo "<span style= 'color:white;background-color:blue'>found " . count($staffList) . " staff</span><br>";
                                foreach ($staffList as $data_staff) {
                                    $staffid = $data_staff['staffid'];
                                    $staffDetails = get_staffDetails($staffid);
                                    if ($staffDetails != 'empty') {
                                        $staffname = $staffDetails['name'];
                                    } else {
                                        $staffname = null;
                                    }
                                    $machineList = get_distinctMachine($kpidetailstable, $date, $summType, $staffid);
                                    #echo "<span style= 'color:white;background-color:black'>found " . count($machineList) . " machines</span><br>";
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
                                            $calculatedKPI = 0;
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
                                                $single_KPI = ($index_gain_in_kg * $kpiVal);
                                                if ($index_per_shift) {
                                                    $inv_KPI = $single_KPI / $index_per_shift;
                                                } else {
                                                    $inv_KPI = 0;
                                                }
                                                $calculatedKPI += round($inv_KPI, 7);
                                                //slide in the individual value into data_row;
                                                $offset = 12;
                                                $new_datarow = array_slice($data_row, 0, $offset, true) +
                                                        array('individual_kpi' => number_format(round($inv_KPI, 7), 7)) +
                                                        array_slice($data_row, $offset, NULL, true);
                                                #$data_row['individual_kpi'] = $inv_KPI;
                                                $det_kpi_row_details[] = $new_datarow;
                                            }
                                            //create array of the current sum
                                            #echo "Generating staffid = $staffid, machine id = $machineid<br>Found $cnt Data<br> <strong>Total KPI is $calculatedKPI.</strong><br>";
                                            $det_kpi_row[] = array(
                                                'staffid' => $staffid,
                                                'staffname' => $staffname,
                                                'machineid' => $machineid,
                                                'machinename' => $machine_name,
                                                'machinemodel' => $machine_model,
                                                'index_per_shift' => $index_per_shift,
                                                'totalkpi' => $calculatedKPI,
                                                'details' => $det_kpi_row_details
                                            );
                                            unset($det_kpi_row_details);
                                        } else {
                                            
                                        }
                                    }
                                }
                            } catch (Exception $ex) {
                                $code = $ex->getCode();
                                switch ($code) {
                                    case 101: //cannot find staff list
                                        #echo "Cannot find Staff for period = $period.<br>";
                                        break;
                                }
                            }
                            if (isset($det_kpi_row)) {
                                $det_KPI[$date] = $det_kpi_row;
                                unset($det_kpi_row);
                            }
                        }
                    }
                    #echo "<pre>"
                    #. "Data List :";
                    #print_r($det_KPI);
                    #echo "</pre>";
                    if (!empty($det_KPI)) {
                        foreach ($det_KPI as $date => $kpi_row) {
                            echo "<h3>$date</h3><br>";
                            foreach ($kpi_row as $key => $data) {
                                ?>
                                <table style="width:auto">
                                    <tr>
                                        <th><?php echo "(" . $data['staffid'] . ") " . $data['staffname']; ?></th>
                                        <th><?php echo "" . $data['machinename'] . " - " . $data['machinemodel']; ?></th>
                                        <th>&nbsp;</th>
                                        <th><?php echo "Index Capacity Per Shift : " . $data['index_per_shift']; ?> </th>
                                    </tr>
                                </table>
                                <tr>
                                    <td colspan="10">
                                        <?php
                                        #echo "<h4>" . $data['staffid'] . " >> " . $data['staffname'] . " >> " . $data['machinename'] . " >> " . $data['machinemodel'] . " </h4><br>";
                                        $details = $data['details'];
                                        ?>
                                        <table>
                                            <thead>
                                                <?php
                                                foreach ($details as $data_row) {
                                                    echo "<tr>";
                                                    #print_r();
                                                    foreach ($data_row as $key => $row) {
                                                        echo "<th>$key</th>";
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
                                                foreach ($details as $data_row) {
                                                    echo "<tr>";
                                                    #print_r();
                                                    foreach ($data_row as $key => $val) {
                                                        echo "<td>$val</td>";
                                                    }
                                                    echo "</tr>";
                                                    $sum_totalweight += floatval($data_row['total_weight']);
                                                    $sum_invkpi += floatval($data_row['individual_kpi']);
                                                }
                                                ?>
                                                <tr>
                                                    <td colspan="11" style="text-align:right"><b>Sum of Total Weight :</b></td>
                                                    <td><b><?php echo $sum_totalweight; ?></b></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="12" style="text-align:right"><b>Sum of Individual KPI :</b></td>
                                                    <td><b><?php echo number_format(round($sum_invkpi, 7), 7); ?></b></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <table style="width:auto">
                                    <tr>
                                        <td colspan="2" style="width:auto;background-color:white">
                                            <?php
                                            echo "<b>Total KPI = " . number_format(round($data['totalkpi'], 7), 7) . "</b><br>";
                                            ?>
                                        </td>
                                        <td colspan="2" style="width:auto;background-color:white">
                                            &nbsp;
                                        </td>
                                        <td colspan="2" style="width:auto;background-color:white">
                                            <?php
                                            if ($data['index_per_shift'] != 0) {
                                                $manual_calc = round($sum_totalweight / $data['index_per_shift'] * 9.8, 7);
                                            } else {
                                                $manual_calc = 0;
                                            }
                                            echo "<b>$manual_calc</b>";
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="width:auto;background-color:white">
                                            <?php
                                            echo "<b>Result by Program Calculation</b><br>";
                                            ?>
                                        </td>
                                        <td colspan="2" style="width:auto;background-color:white">
                                            &nbsp;
                                        </td>
                                        <td colspan="2" style="width:auto;background-color:white">
                                            <b>Result by Manual Calculation</b><br>
                                            Sum of Total Weight / Index Capacity Per Shift * 9.8
                                        </td>
                                    </tr>
                                </table>
                                <hr><br>
                                <?php
                            }
                        }
                    } else {
                        echo "Cannot find data for $day-$month-$year";
                    }
                    ?>

                    <?php
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
                this.jobstatus = 'finished';
            }else{
                this.day = 'Show All';
                this.jobstatus = 'finished';
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
