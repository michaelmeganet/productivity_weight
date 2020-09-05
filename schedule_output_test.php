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
        <div id="mainArea">
            <div>
                <label> Select Period : </label>
                <select id="period" name='period' v-model='period' @change='getAllJobList()'>
                    <option v-for='data in periodList' v-bind:value='data'>{{data}}</option>
                </select>
                <br>
                <label> Job Type      : </label>
                <select id="jobfintype" name="jobfintype" v-model="jobfintype">
                    <option value="unfinished">Un-Finished Jobs</option>
                    <option value="finished">Finished Jobs</option>
                </select>
            </div>
            <br>

            <div v-if='jobfintype == "unfinished"'>
                List of Unfinished Jobs :<br>
                <select name ="unfinJob" id="unfinJob" v-model="unfinJob" size="10" @change='getUnFinJobDetail()'>
                    <option v-for="data in unfinJobList" v-bind:value="data.sid">{{data.sid}} | {{data.quono}}</option>
                </select>
                Selected : {{unfinJob}}
                <br>
                Details :
                <div v-if="unfinJobListDetail == 'empty' && unfinJob != ''">
                    Cannot find details, is job has been started?
                </div>
                <div v-if='unfinJobListDetail !="empty" && unfinJob != ""'>
                    <table>
                        <thead>
                            <tr>
                                <th>POID</th>
                                <th>SID</th>
                                <th>Job Type</th>
                                <th>Start Date</th>
                                <th>Start By</th>
                                <th>Machine</th>
                                <th>End Date</th>
                                <th>End By</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for='data in unfinJobListDetail'>
                                <td v-for='rows in data'>{{rows}}</td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>           

            <div v-if='jobfintype == "finished"'>
                List of finished Jobs :<br>
                <select name ="finJob" id="finJob" v-model="finJob" size="10" @change='getFinJobDetail()'>
                    <option v-for="data in finJobList" v-bind:value="data.sid">{{data.sid}} | {{data.quono}}</option>
                </select>
                Selected : {{finJob}}
                <br>
                Details :
                <div v-if="finJobListDetail == 'empty' && finJob != ''">
                    Cannot find details, is job has been started?
                </div>
                <div v-if='finJobListDetail != "empty" && finJob != ""'>
                    <table>
                        <thead>
                            <tr>
                                <th>POID</th>
                                <th>SID</th>
                                <th>Job Type</th>
                                <th>Start Date</th>
                                <th>Start By</th>
                                <th>Machine</th>
                                <th>End Date</th>
                                <th>End By</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for='data in finJobListDetail'>
                                <td v-for='rows in data'>{{rows}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <script>
var schOutVue = new Vue({
    el: '#mainArea',
    data: {
        phpajaxresponsefile: 'productionlog.axios.php',

        //selection variables
        period: '',
        jobfintype: '',
        unfinJob: '',
        finJob: '',

        //lists variable
        periodList: '',
        unfinJobList: '',
        finJobList: '',
        unfinJobListDetail: '',
        finJobListDetail: ''
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
                schOutVue.periodList = response.data;
            });
        },
        getAllJobList: function () {
            this.getUnFinJobList();
            this.getFinJobList();
        },
        getUnFinJobList: function () {
            let period = this.period;
            axios.post(this.phpajaxresponsefile, {
                action: 'getUnFinJobList',
                period: period
            }).then(function (response) {
                console.log('ongetUnFinJobList Function...');
                console.log(response.data);
                schOutVue.unfinJobList = response.data;
            });
        },
        getFinJobList: function () {
            let period = this.period;
            axios.post(this.phpajaxresponsefile, {
                action: 'getFinJobList',
                period: period
            }).then(function (response) {
                console.log('ongetFinJobList Function...');
                console.log(response.data);
                schOutVue.finJobList = response.data;
            });
        },
        getUnFinJobDetail: function () {
            let period = this.period;
            let sid = this.unfinJob;
            axios.post(this.phpajaxresponsefile, {
                action: 'getUnFinJobDetail',
                period: period,
                sid: sid
            }).then(function (response) {
                console.log('on getUnFinJobDetail( period=' + period + ' & sid=' + sid + ' Function...');
                console.log(response.data);
                schOutVue.unfinJobListDetail = response.data;
            });
        },
        getFinJobDetail: function () {
            let period = this.period;
            let sid = this.finJob;
            axios.post(this.phpajaxresponsefile, {
                action: 'getFinJobDetail',
                period: period,
                sid: sid
            }).then(function (response) {
                console.log('on getFinJobDetail( period=' + period + ' & sid=' + sid + ' Function...');
                console.log(response.data);
                schOutVue.finJobListDetail = response.data;
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
