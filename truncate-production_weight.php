<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>truncate table</title>
        <script src="./assets/jquery-2.1.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script> 
    </head>
    <body>

    <?php
        include_once 'class/dbh.inc.php';
        include_once 'class/variables.inc.php';
        include_once 'class/abstract_workpcsnew.inc.php';
        include_once 'class/reverse-dimension.inc.php';
        include_once 'class/phhdate.inc.php';


    ?>
    <div class="" id='app'>
        <form action='' method='post'>
        <label> Select Period : </label>
        <select id="period" name='period' v-model='period'>
            <option v-for='data in periodList' bind:value='data'>{{data}}</option>
        </select>
        <input type='submit' value='Show Table' v-show='period != ""' />

        </form>
<?php
        $period = trim($_POST['period']);
        // $period = '2007';
        if(isset($_POST['period'])){
            $tbl = 'production_weight_' . $period;

            //echo "tbldata = $tbldata;;  tbloutput = $tbloutput";
            $qr = "TRUNCATE $tbl";
            echo "\$qr = $qr <br>" ;
           $objSQL = new SQL($qr); 
            $results = $objSQL->getDelete();
            echo "the table $tbl has been truncated 'cleared' <br>";

        }
        
?>
    </div>
        <script>
            var logVue = new Vue({
                el: '#app',
                data: {
                    phpajaxresponsefile: 'productionlog.axios.php',
                    
                    period: '',
                    
                    periodList: ''
                },
                watch: {  
                },
                methods: {
                    getPeriod: function(){
                        axios.post(this.phpajaxresponsefile,{
                            action: 'getPeriod' // var_dump object(stdClass)#1 (1) {  ["action"]=>   string(9) "getPeriod"
                        }).then(function(response){
                           console.log('onGetPeriod Function....') ;
                           console.log(response.data);
                           logVue.periodList = response.data;
                        });
                    }
                },
                mounted: function() {
                    this.getPeriod();
                }
            });
        </script>        
    </body>
</html>