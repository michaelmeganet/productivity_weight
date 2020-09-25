<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <script src="./assets/jquery-2.1.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    </head>
    <body>
        <div class="" id='mainLog'>
            <div>
            <form action='' method='post'>
                <label> Select Period : </label>
                <select id="period" name='period' v-model='period'>
                    <option v-for='data in periodList' bind:value='data'>{{data}}</option>
                </select>
                <input type='submit' value='generate table (production_weight_period)' v-show='period != ""' />

            </form>
            <p>Select period to generate data in production_weight_period table</p>
            <p>Truncate production_weight_period table  <span><a href='./truncate-production_weight.php' target='_blank'>click here</a></span></p>
            </div>
            <?php
            if (isset($_POST['period'])){
                echo "period = ".$_POST['period']."<br>";
            ?>
            <div>
                <input type='hidden' v-model='period' value='<?php echo $_POST['period']; ?>' />
                <?php include_once 'main_index.php'; ?>
            </div>
            <?php
            }
            ?>
               
            
        </div>
        <div>
        <a href="./calculate-kpi.php">get kpi page</a>     
       </div>
       <div>
       <a href="./schedule_output_test.php">check Scheduling output page</a>
       </div>
       <div>
       <a href="./make-kpidetail.php">generate KPI Details</a>
       </div>
       <div>
       <a href="./show-kpidetail-table.php">view KPI Detail Table</a>
       </div>
       <div>
           <table>
               <tr>
                   <td colspan="2">Show Summary : </td>
               </tr>
               <tr>
                   <td>&nbsp;&nbsp;</td>
                   <td><a href="./summary-kpi-simple.php">view KPI Monthly Summary (Simplified)</a></td>
               </tr>
               <tr>
                   <td>&nbsp;&nbsp;</td>
                   <td><a href="./summary-kpi.php">view KPI Summary (Detailed)</a></td>
               </tr>
           </table>
       
       </div>
 
        <script>
            var logVue = new Vue({
                el: '#mainLog',
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