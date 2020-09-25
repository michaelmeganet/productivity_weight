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
    </head>
    <body>
        <h2>SUMMARY KPI</h2>
        <div id='mainArea'>

            <div> <!--period area-->
                Period :
                <select v-model='period' id='period' name='period' @change="summType=''">
                    <option v-for='data in periodList' v-bind:value='data'>{{data}}</option>
                </select>
            </div>
            <div v-if='period!= ""'><!--summary monthly/daily area-->
                Summary Type :
                <select v-model='summType' id='summType' name='summType' @change='day = ""'>
                    <option value='all'>Monthly</option>
                    <option value='daily'>Daily</option>
                </select>
            </div>
            <div v-if='summType == "daily"'><!-- date selection -->
                Date :
                <select v-model="day" id="day" name="day">
                    <option v-for="data in dayList" v-bind:value="data">{{data}}</option>
                </select>
                <button type="button" @click="getKPIDetail">Submit</button>
            </div>
            <div v-else-if='summType != "daily" && summType!=""'>
                Date :
                <input type='text' value='Show All' disabled/>
                <button type="button" @click="getKPIDetail">Submit</button>
            </div>
            <br>
            <br>
            <div>
                <?php
                if (isset($_POST['period']) && isset($_POST['summType']) && isset($_POST['day'])) {
                    $period = $_POST['period'];
                    $summType = $_POST['summType'];
                    $day = $_POST['day'];
                    $kpidetailstable = 'kpidetails_' . $period;
                    $year = '20' . substr($period, 0, 2);
                    $month = substr($period, 2, 2);
                    if ($summType == 'all') {
                        $day = '00';
                    }
                    $date = $year . '-' . $month . '-' . $day;
                    try {
                        $staffList = get_distinctStaff($kpidetailstable, $date, $summType);
                        if ($staffList == 'empty') {
                            throw new Exception('There\'s no staff found!', 101);
                        }
                        foreach ($staffList as $data_staff) {
                            $staffid = $data_staff['staffid'];
                            $machineList = get_distinctMachine($kpidetailstable, $date, $summType, $staffid);
                            foreach ($machineList as $data_machine) {
                                $mcid = $data_machine['mcid'];
                                $machineid = $data_machine['machineid'];
                                $filteredDetails = get_filteredDetails($kpidetailstable, $date, $summType, $staffid, $mcid);
                                if($filteredDetails != 'empty'){
                                    //begin calculate kpi (based on staffid and mcid
                                }
                            }
                        }
                    } catch (Exception $ex) {
                        $code = $ex->getCode();
                        switch ($code) {
                            case 101: //cannot find staff list

                                break;
                        }
                    }
                }

                function get_filteredDetails($table, $date, $summType, $staffid, $mcid) {
                    if ($summType == 'daily') {
                        $qr = "SELECT * FROM $table WHERE staffid = '$staffid' AND mcid = $mcid AND DATE_FORMAT(dateofcompletion,'%Y %m %d') = DATE_FORMAT('$date','%Y %m %d')";
                    } elseif ($summType == 'all') {
                        $qr = "SELECT * FROM $table WHERE staffid = '$staffid' AND mcid = $mcid AND DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m')";
                    }
                    $objSQL = new SQL($qr);
                    $result = $objSQL->getResultRowArray();
                    if (!empty($result)){
                        return $result;
                    }else{
                        return 'empty';
                    }
                }

                function get_distinctMachine($table, $date, $summType, $staffid) {
                    if ($summType == 'daily') {
                        $qr = "SELECT DISTINCT mcid,machineid FROM $table WHERE staffid = '$staffid' AND DATE_FORMAT(dateofcompletion,'%Y %m %d') = DATE_FORMAT('$date','%Y %m %d')";
                    } elseif ($summType == 'all') {
                        $qr = "SELECT DISTINCT mcid,machineid FROM $table WHERE staffid = '$staffid' AND DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m')";
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
                        $qr = "SELECT DISTINCT staffid FROM $table WHERE DATE_FORMAT(dateofcompletion,'%Y %m %d') = DATE_FORMAT('$date','%Y %m %d')";
                    } elseif ($summType == 'all') {
                        $qr = "SELECT DISTINCT staffid FROM $table WHERE DATE_FORMAT(dateofcompletion,'%Y %m') = DATE_FORMAT('$date','%Y %m')";
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
