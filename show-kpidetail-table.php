<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <script src="./assets/jquery-2.1.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
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
        // echo "\$period = $period <br>";
        // echo "<br>";
        ?>       
        <div id='mainArea'>
            <form action='' method='post'>
                <label> Select Period : </label>
                <select id="period" name='period' v-model='period'>
                    <option v-for='data in periodList' bind:value='data'>{{data}}</option>
                </select>            
                <input type='submit' value='Generate KPI Detail Table' v-show='period != ""' />
            </form>
        </div>
        <?php
        if (isset($_POST['period'])) {
            $period = $_POST['period'];
        }
        if (isset($period)) {
            $kpidetailstable = "kpidetails_$period";
            // $qr = "SELECT * FROM $kpidetailstable ORDER BY kpidid ASC";
            $qr = "SELECT * FROM $kpidetailstable WHERE poid IS NOT NULL AND jlfor = 'CJ' AND jobtype != 'cncmach' ".
            "ORDER  BY staffid, mcid asc  ";
            echo " $qr =  $qr <br>";
            $objSQL = new SQL($qr);
            $result = $objSQL->getResultRowArray();
            if (!empty($result)) {
                ?>
                <table>
                    <thead>
                        <tr>
                            <?php
                            foreach ($result as $data_row) {
                                foreach ($data_row as $key => $val) {
                                    echo "<th>$key</th>";
                                }
                                break;
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($result as $data_row) {
                            echo "<tr>";
                            foreach ($data_row as $key => $val) {
                                echo "<td>$val</td>";
                            }
                            echo "</tr>";
                        }
                        ?>
                    </tbody>

                </table>
                <?php
            } else {
                echo "There's no data for period = $period<br>";
            }
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
</html>
