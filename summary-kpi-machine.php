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
                    <input v-model='summType' id='summType' name='summType'/>
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

                if (isset($_POST['period']) && isset($_POST['summType'])) {
                    $period = $_POST['period'];
                    $summType = $_POST['summType'];
                    $kpidetailstable = 'kpidetails_' . $period;
                    $year = '20' . substr($period, 0, 2);
                    $month = substr($period, 2, 2);
                    if ($summType == 'all') {
                        #$i_day = 1;
                        #$day = sprintf('%02d', $i_day);
                        #$date = $year . '-' . $month . '-' . $day;
                        #$totaldate = date_format(date_create($date), 't');
                    } else {
                        $day = sprintf('%02d', $i_day);
                        $date = $year . '-' . $month . '-' . $day;
                        $totaldate = $i_day;
                    }
                    #$day = sprintf('%02d', $i_day);
                    #echo "selected day = $day<br>";
                    #echo "Total date in this month = $totaldate";
                    echo "<div style='text-align:center'>";
                    echo "<b style='font-size:2em'>KPI INDEX MONTHLY DETAILS REPORT BY MACHINE</b><br>";
                    echo "<h2>DATE = $month-$year</h2><br>";
                    echo "</div>";
                    $day = sprintf('%02d', 00);
                    $date = $year . '-' . $month . '-' . $day;
                    #echo "<h3>Processing Date '$date'</h3><br>";
                    try {
                        $machineList = get_distinctMachine($kpidetailstable, $date, $summType);
                        if ($machineList == 'empty') {
                            throw new Exception('There\'s no machine found!', 101);
                        }
                        #echo "<span style= 'color:white;background-color:blue'>found " . count($staffList) . " staff</span><br>";

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
                            $calculatedKPI = 0;
                            $cnt = 0;
                            $staffList = get_distinctStaff($kpidetailstable, $date, $summType, $mcid);
                            #echo "<span style= 'color:white;background-color:black'>found " . count($machineList) . " machines</span><br>";
                            foreach ($staffList as $data_staff) {
                                $staffid = $data_staff['staffid'];
                                $staffDetails = get_staffDetails($staffid);
                                if ($staffDetails != 'empty') {
                                    $staffname = $staffDetails['name'];
                                } else {
                                    $staffname = null;
                                }
                                $filteredDetails = get_filteredDetails($kpidetailstable, $date, $summType, $staffid, $mcid);
                                if ($filteredDetails != 'empty') {
                                    //begin calculate kpi (based on staffid and mcid
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
                                    $det_kpi_row2[] = array(
                                        'staffid' => $staffid,
                                        'staffname' => $staffname,
                                        'details' => $det_kpi_row_details
                                    );
                                    unset($det_kpi_row_details);
                                } else {
                                    
                                }
                            }
                            $det_kpi_row[] = array(
                                'machineid' => $machineid,
                                'machinename' => $machine_name,
                                'machinemodel' => $machine_model,
                                'index_per_shift' => $index_per_shift,
                                'totalkpi' => $calculatedKPI,
                                'bystaff' => $det_kpi_row2,
                            );
                            unset($det_kpi_row2);
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
                        $det_KPI = $det_kpi_row;
                        unset($det_kpi_row);
                    }

                    #echo "<pre>"
                    #. "Data List :";
                    #print_r($det_KPI);
                    #echo "</pre>";

                    if (!empty($det_KPI)) {
                        foreach ($det_KPI as $machine_row) {
                            $bystaff_row = $machine_row['bystaff'];
                            ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th style='width:20%'><?php echo $machine_row['machineid'] ?></th>
                                        <th style='width:20%'>Machine Index per Shift : <?php echo $machine_row['index_per_shift'] ?></th>
                                    </tr>
                                    <tr>
                                        <th><?php echo $machine_row['machinename'] ?></th>
                                        <th style='width:20%'><?php echo $machine_row['machinemodel'] ?></th>
                                    </tr>
                                </thead>
                            </table>
                            <table>
                                <?php
                                ?>

                                <thead>
                                    <?php
                                    foreach ($bystaff_row as $detail_rows) {
                                        $staffid = $detail_rows['staffid'];
                                        $staffname = $detail_rows['staffname'];
                                        $details = $detail_rows['details'];
                                        foreach ($details as $data_row) {
                                            echo "<tr>";
                                            echo "<th>staff_id</th>";
                                            echo "<th>staffname</th>";
                                            echo "<th>&nbsp;</th>";
                                            echo "<th>&nbsp;</th>";
                                            foreach ($data_row as $key => $val) {
                                                echo "<th>$key</th>";
                                            }
                                            echo "</tr>";
                                            break;
                                        }
                                        break;
                                    }
                                    ?>

                                </thead>
                                <tbody>
                                    <?php
                                    $sum_totalweight = 0;
                                    $sum_invkpi = 0;
                                    $sum_manual_invkpi = 0;
                                    foreach ($bystaff_row as $detail_rows) {
                                        $staffid = $detail_rows['staffid'];
                                        $staffname = $detail_rows['staffname'];
                                        $details = $detail_rows['details'];
                                        foreach ($details as $data_row) {
                                            echo "<tr>";
                                            echo "<td>$staffid</td>";
                                            echo "<td>$staffname</td>";
                                            echo "<td>&nbsp;</td>";
                                            echo "<td>&nbsp;</td>";
                                            foreach ($data_row as $key => $val) {
                                                echo "<td>$val</td>";
                                            }
                                            $unit_weight = $data_row['unit_weight'];
                                            $qty = $data_row['jobdonequantity'];
                                            $totalweight = $qty * $unit_weight;
                                            #$totalweight = $data_row['total_weight'];
                                            $start_time = $data_row['start_time'];
                                            $manual_kpiVal = get_kpiTimeTableDetails($start_time);
                                            if ($machine_row['index_per_shift'] != 0) {
                                                $manual_invkpi = round(($totalweight / $machine_row['index_per_shift'] * $manual_kpiVal), 7);
                                            } else {
                                                $manual_invkpi = 0;
                                            }
                                            $sum_manual_invkpi += $manual_invkpi;
                                            $sum_totalweight += $data_row['total_weight'];
                                            $sum_invkpi += $data_row['individual_kpi'];
                                            echo"</tr>";
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td colspan="15" style="text-align:right"><b>Sum of Total Weight :</b></td>
                                        <td><b><?php echo $sum_totalweight; ?></b></td>
                                    </tr>
                                    <tr>
                                        <td colspan="16" style="text-align:right"><b>Sum of Individual KPI :</b></td>
                                        <td><b><?php echo number_format(round($sum_invkpi, 7), 7); ?></b></td>
                                    </tr>
                                </tbody>
                                <?php ?>
                            </table>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Total KPI : <?php echo $machine_row['totalkpi']; ?></th>
                                        <th>&nbsp;</th>
                                        <th colspan="2" style="width:auto;background-color:white">
                                            <?php
                                            echo "<b>".number_format(round($sum_manual_invkpi,7),7)."</b>";
                                            ?>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th>Program Calculation</th>
                                        <th>&nbsp;</th>                                        
                                        <th colspan="2" style="width:auto;background-color:white">
                                            <b>Result by Manual Calculation</b><br>
                                            Sum of Total Weight / Index Capacity Per Shift * KPI Value per Shift
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                            <?php
                            #echo $machine_row['machineid'];
                            #echo "<pre>";
                            #print_r($machine_row);
                            #echo "</pre>";
                        }
                    } else {
                        echo "Cannot find data.....";
                    }

                    /*
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
                      $sum_manual_invkpi = 0;
                      $sum_totalweight = 0;
                      $sum_invkpi = 0;
                      foreach ($details as $data_row) {
                      echo "<tr>";
                      #print_r();
                      foreach ($data_row as $key => $val) {
                      echo "<td>$val</td>";
                      }
                      echo "</tr>";
                      $unit_weight = $data_row['unit_weight'];
                      $qty = $data_row['jobdonequantity'];
                      $totalweight = $qty * $unit_weight;
                      #$totalweight = $data_row['total_weight'];
                      $start_time = $data_row['start_time'];
                      $manual_kpiVal = get_kpiTimeTableDetails($start_time);
                      if ($data['index_per_shift'] != 0) {
                      $manual_invkpi = round(($totalweight / $data['index_per_shift'] * $manual_kpiVal), 7);
                      } else {
                      $manual_invkpi = 0;
                      }
                      $sum_manual_invkpi += $manual_invkpi;
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
                      echo "<b>" . number_format(round($sum_manual_invkpi, 7), 7) . "</b>";
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
                      Sum of Total Weight / Index Capacity Per Shift * KPI Value per Shift
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
                     * 
                     */
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
                    if ($summType == 'daily') {
                        $qr = "SELECT * FROM $table "
                                . "WHERE poid IS NOT NULL AND jlfor = 'CJ' AND NOT jobtype ='cncmach' AND staffid = '$staffid' "
                                . "AND mcid = $mcid AND DATE_FORMAT(dateofcompletion,'%Y %m %d') = DATE_FORMAT('$date','%Y %m %d') "
                                . "ORDER BY dateofcompletion, staffid, mcid ASC";
                    } elseif ($summType == 'all') {
                        $qr = "SELECT * FROM $table "
                                . "WHERE poid IS NOT NULL AND jlfor = 'CJ' AND NOT jobtype ='cncmach' AND staffid = '$staffid' "
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

                function get_distinctMachine($table, $date, $summType) {
                    if ($summType == 'daily') {
                        $qr = "SELECT DISTINCT mcid,machineid FROM $table WHERE poid IS NOT NULL AND jlfor = 'CJ' AND NOT jobtype ='cncmach' AND DATE_FORMAT(dateofcompletion,'%Y %m %d') = DATE_FORMAT('$date','%Y %m %d') ORDER BY mcid ASC";
                    } elseif ($summType == 'all') {
                        $qr = "SELECT DISTINCT mcid,machineid FROM $table WHERE poid IS NOT NULL AND jlfor = 'CJ' AND NOT jobtype ='cncmach' AND DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m') ORDER BY mcid ASC";
                    }
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

                function get_distinctStaff($table, $date, $summType, $mcid) {
                    if ($summType == 'daily') {
                        $qr = "SELECT DISTINCT staffid FROM $table WHERE poid IS NOT NULL AND mcid = $mcid AND jlfor = 'CJ' AND NOT jobtype ='cncmach' AND DATE_FORMAT(dateofcompletion,'%Y %m %d') = DATE_FORMAT('$date','%Y %m %d')";
                    } elseif ($summType == 'all') {
                        $qr = "SELECT DISTINCT staffid FROM $table WHERE poid IS NOT NULL AND mcid = $mcid AND jlfor = 'CJ' AND NOT jobtype ='cncmach' AND DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m')";
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
        summType: 'all',
        day: '',
        loading: false,

        periodList: '',
        dayList: '',
        kpiList: ''
    },
    watch: {
        summType: function () {
            if (this.summType === 'daily') {
                this.getDayList();
            } else {
                this.summType = 'all';
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
