<!DOCTYPE html>
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
        <h2>PRODUCTION KPI INDEX </h2>
        <div id ='mainAreaKPI'>
            <div>
                <label>Select Period :</label>
                <select id='period' name='period' v-model='period' @change='getStaffList()'>
                    <option v-for='data in periodList' v-bind:value='data' >{{data}}</option>
                </select>
            </div>
            <div>
                <label>Select User :</label>
                <select id='staffname' name='staffname' v-model='staffName' @change='getMachineListbyStaff()'>
                    <option v-for='data in staffList' v-bind:value='data.staffname'>{{data.staffname}}</option>
                </select>
            </div>
            <div>
                <label>Select Machine :</label>
                <select id='machinename' name='machinename' v-model='machineName' onchange='console.log(this.value)'>
                    <option v-for='data in machineListbyStaff' v-bind:value='data.machineModel'>{{data.machineModel}}</option>
                </select>
            </div>
            <div>
                <table>
                    
                </table>
            </div>
        </div>
    </table>
    <script>
        let kpiVue = new Vue({
        el: '#mainAreaKPI',
        data: {
            phpajaxresponsefile: 'calculatekpi.axios.php',
            period: '',
            staffName: '',
            machineName: '',

            //lists
            periodList: '',
            staffList: '',
            machineListbyStaff: '',
            dataList: '',
            
            totalIndexGain: '',
            weekdayKPI: '',
            weekendKPI: ''
        },
        watch: {

        },
        methods: {
            getPeriodList: function () {
                axios.post(this.phpajaxresponsefile, {
                    action: 'getPeriod'
                }).then(function (response) {
                    console.log('on getPeriod...');
                    console.log(response.data);
                    kpiVue.periodList = response.data;
                });
            },
            getStaffList: function () {
                let period = this.period;
                axios.post(this.phpajaxresponsefile, {
                    action: 'getStaffList',
                    period: period
                }).then(function (response) {
                    console.log('on get stafflist...');
                    console.log(response.data);
                    kpiVue.staffList = response.data;
                });
            },
            getMachineListbyStaff: function (){
                let period = this.period;
                let staff = this.staffName;
                axios.post(this.phpajaxresponsefile,{
                    action: 'getMachineListbyStaff',
                    period: period,
                    staff: staff
                }).then(function(response){
                    console.log('in getMachineListbyStaff');
                    console.log(response.data);
                    kpiVue.machineListbyStaff = response.data;
                });
            }

        },
        mounted: function () {
            this.getPeriodList();
        }

        });
    </script>

</body>
</html>