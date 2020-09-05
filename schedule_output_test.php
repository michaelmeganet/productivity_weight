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
                <select id="period" name='period' v-model='period'>
                    <option v-for='data in periodList' v-bind:value='data'>{{data}}</option>
                </select>
                <br>
                <label> Status      : </label>
                <select id="status" name="status" v-model="status"  @change='getAllJobList()'>
                    <option value="active">Active Jobs</option>
                    <option value="billing">Billing Jobs</option>
                    <option value="cancelled">Cancelled Jobs</option>
                </select>
                <br>
                <label> Job Type      : </label>
                <select id="jobfintype" name="jobfintype" v-model="jobfintype">
                    <option value="unfinished">Un-Finished Jobs</option>
                    <option value="finished">Finished Jobs</option>
                </select>
                <button id='refData' name='refData' @click='getAllJobList()' v-if='period != "" && status != "" && jobfintype != "" ' >Refresh Data</button>
            </div>
            <br>

            <div v-if='jobfintype == "unfinished"'>
                List of Unfinished Jobs :<br>
                <select name ="unfinJob" id="unfinJob" v-model="unfinJob" size="10" @change='getUnFinJobListDetails();getUnFinJobOutput()'>
                    <option v-for="data in unfinJobList" v-bind:value="data.sid">{{data.sid}} | {{data.quono}}</option>
                </select>
                Selected : {{unfinJob}}
                <br>
                <div>
                    Scheduling Details :<br>
                    <div v-if="unfinJobListDetail == '' && unfinJob != ''">
                        Cannot find details, does job actually exists?
                    </div>
                    <div v-if='unfinJobListDetail != "" && unfinJob != ""'>
                        <div>
                            <table style="text-align: center;padding: 0px 3px 0px 3px;border:1px;border-style:solid">
                                <thead style="border:1px;border-style:solid">
                                    <tr style="border:1px;border-style:solid">
                                        <th style="border:1px;border-style:solid" rowspan="2">SID</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">BID</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">QID</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">Quono</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">CID</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">Quantity</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">Grade</th>
                                        <th style="border:1px;border-style:solid" colspan="7">Dimensions</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">Process</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">Cutting Type</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">No. Position</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">Issue Date</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">JL For</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">Status</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">Date of Completion</th>
                                    </tr>
                                    <tr>
                                        <th style="border:1px;border-style:solid">MDT</th>
                                        <th style="border:1px;border-style:solid">MDW</th>
                                        <th style="border:1px;border-style:solid">MDL</th>
                                        <th style="border:1px;border-style:solid"></th>
                                        <th style="border:1px;border-style:solid">FDT</th>
                                        <th style="border:1px;border-style:solid">FDW</th>
                                        <th style="border:1px;border-style:solid">FDL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for='data in unfinJobListDetail'>                                
                                        <td style="border:1px;border-style:solid">{{data.sid}}</td>
                                        <td style="border:1px;border-style:solid">{{data.bid}}</td>
                                        <td style="border:1px;border-style:solid">{{data.qid}}</td>
                                        <td style="border:1px;border-style:solid">{{data.quono}}</td>
                                        <td style="border:1px;border-style:solid">{{data.cid}}</td>
                                        <td style="border:1px;border-style:solid">{{data.quantity}}</td>
                                        <td style="border:1px;border-style:solid">{{data.grade}}</td>
                                        <td style="border:1px;border-style:solid">{{data.mdt}}</td>
                                        <td style="border:1px;border-style:solid">{{data.mdw}}</td>
                                        <td style="border:1px;border-style:solid">{{data.mdl}}</td>
                                        <td style="border:1px;border-style:solid"></td>
                                        <td style="border:1px;border-style:solid">{{data.fdt}}</td>
                                        <td style="border:1px;border-style:solid">{{data.fdw}}</td>
                                        <td style="border:1px;border-style:solid">{{data.fdl}}</td>
                                        <td style="border:1px;border-style:solid">{{data.process}}</td>
                                        <td style="border:1px;border-style:solid">{{data.cuttingtype}}</td>
                                        <td style="border:1px;border-style:solid">{{data.noposition}}</td>
                                        <td style="border:1px;border-style:solid">{{data.date_issue}}</td>
                                        <td style="border:1px;border-style:solid">{{data.jlfor}}</td>
                                        <td style="border:1px;border-style:solid">{{data.status}}</td>
                                        <td style="border:1px;border-style:solid">{{data.dateofcompletion}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <br>
                <br>
                <div>
                    Output Log :
                    <div v-if="unfinJobListOutput == 'empty' && unfinJob != ''">
                        Cannot find log, is job has been started?
                    </div>
                    <div v-if='unfinJobListOutput !="empty" && unfinJob != ""'>
                        <table>
                            <thead>
                                <tr>
                                    <th style="border:1px;border-style:solid">POID</th>
                                    <th style="border:1px;border-style:solid">SID</th>
                                    <th style="border:1px;border-style:solid">Job Type</th>
                                    <th style="border:1px;border-style:solid">Start Date</th>
                                    <th style="border:1px;border-style:solid">Start By</th>
                                    <th style="border:1px;border-style:solid">Machine</th>
                                    <th style="border:1px;border-style:solid">End Date</th>
                                    <th style="border:1px;border-style:solid">End By</th>
                                    <th style="border:1px;border-style:solid">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for='data in unfinJobListOutput'>
                                    <td style="border:1px;border-style:solid" v-for='rows in data'>{{rows}}</td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>           

            <div v-if='jobfintype == "finished"'>
                List of finished Jobs :<br>
                <select name ="finJob" id="finJob" v-model="finJob" size="10" @change='getFinJobListDetails();getFinJobOutput()'>
                    <option v-for="data in finJobList" v-bind:value="data.sid">{{data.sid}} | {{data.quono}}</option>
                </select>
                Selected : {{finJob}}
                <br>
                <div>
                    Scheduling Details :<br>
                    <div v-if="finJobListDetail == '' && finJob != ''">
                        Cannot find details, does job actually exists?
                    </div>
                    <div v-if='finJobListDetail != "" && finJob != ""'>
                        <div>
                            <table style="text-align: center;padding: 0px 3px 0px 3px;border:1px;border-style:solid">
                                <thead style="border:1px;border-style:solid">
                                    <tr style="border:1px;border-style:solid">
                                        <th style="border:1px;border-style:solid" rowspan="2">SID</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">BID</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">QID</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">Quono</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">CID</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">Quantity</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">Grade</th>
                                        <th style="border:1px;border-style:solid" colspan="7">Dimensions</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">Process</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">Cutting Type</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">No. Position</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">Issue Date</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">JL For</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">Status</th>
                                        <th style="border:1px;border-style:solid" rowspan="2">Date of Completion</th>
                                    </tr>
                                    <tr>
                                        <th style="border:1px;border-style:solid">MDT</th>
                                        <th style="border:1px;border-style:solid">MDW</th>
                                        <th style="border:1px;border-style:solid">MDL</th>
                                        <th style="border:1px;border-style:solid"></th>
                                        <th style="border:1px;border-style:solid">FDT</th>
                                        <th style="border:1px;border-style:solid">FDW</th>
                                        <th style="border:1px;border-style:solid">FDL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for='data in finJobListDetail'>                                
                                        <td style="border:1px;border-style:solid">{{data.sid}}</td>
                                        <td style="border:1px;border-style:solid">{{data.bid}}</td>
                                        <td style="border:1px;border-style:solid">{{data.qid}}</td>
                                        <td style="border:1px;border-style:solid">{{data.quono}}</td>
                                        <td style="border:1px;border-style:solid">{{data.cid}}</td>
                                        <td style="border:1px;border-style:solid">{{data.quantity}}</td>
                                        <td style="border:1px;border-style:solid">{{data.grade}}</td>
                                        <td style="border:1px;border-style:solid">{{data.mdt}}</td>
                                        <td style="border:1px;border-style:solid">{{data.mdw}}</td>
                                        <td style="border:1px;border-style:solid">{{data.mdl}}</td>
                                        <td style="border:1px;border-style:solid"></td>
                                        <td style="border:1px;border-style:solid">{{data.fdt}}</td>
                                        <td style="border:1px;border-style:solid">{{data.fdw}}</td>
                                        <td style="border:1px;border-style:solid">{{data.fdl}}</td>
                                        <td style="border:1px;border-style:solid">{{data.process}}</td>
                                        <td style="border:1px;border-style:solid">{{data.cuttingtype}}</td>
                                        <td style="border:1px;border-style:solid">{{data.noposition}}</td>
                                        <td style="border:1px;border-style:solid">{{data.date_issue}}</td>
                                        <td style="border:1px;border-style:solid">{{data.jlfor}}</td>
                                        <td style="border:1px;border-style:solid">{{data.status}}</td>
                                        <td style="border:1px;border-style:solid">{{data.dateofcompletion}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <br>
                <br>
                <div>
                    Output Log :
                    <div v-if="finJobListOutput == 'empty' && finJob != ''">
                        Cannot find log, is job has been started?
                    </div>
                    <div v-if='finJobListOutput !="empty" && finJob != ""'>
                        <table>
                            <thead>
                                <tr>
                                    <th style="border:1px;border-style:solid">POID</th>
                                    <th style="border:1px;border-style:solid">SID</th>
                                    <th style="border:1px;border-style:solid">Job Type</th>
                                    <th style="border:1px;border-style:solid">Start Date</th>
                                    <th style="border:1px;border-style:solid">Start By</th>
                                    <th style="border:1px;border-style:solid">Machine</th>
                                    <th style="border:1px;border-style:solid">End Date</th>
                                    <th style="border:1px;border-style:solid">End By</th>
                                    <th style="border:1px;border-style:solid">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for='data in finJobListOutput'>
                                    <td style="border:1px;border-style:solid" v-for='rows in data'>{{rows}}</td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
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
        status: '',
        jobfintype: '',
        unfinJob: '',
        finJob: '',

        //lists variable
        periodList: '',
        unfinJobList: '',
        finJobList: '',
        unfinJobListDetail: '',
        finJobListDetail: '',
        unfinJobListOutput: '',
        finJobListOutput: ''
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
            let status = this.status;
            axios.post(this.phpajaxresponsefile, {
                action: 'getUnFinJobList',
                period: period,
                status: status
            }).then(function (response) {
                console.log('ongetUnFinJobList Function...');
                console.log(response.data);
                schOutVue.unfinJobList = response.data;
            });
        },
        getFinJobList: function () {
            let period = this.period;
            let status = this.status;
            axios.post(this.phpajaxresponsefile, {
                action: 'getFinJobList',
                period: period,
                status: status
            }).then(function (response) {
                console.log('ongetFinJobList Function...');
                console.log(response.data);
                schOutVue.finJobList = response.data;
            });
        },
        getFinJobListDetails: function () {
            let period = this.period;
            let sid = this.finJob;
            let finjoblist = this.finJobList;
            let finJobListDetail = finjoblist.filter(d => d.sid === sid);
            console.log('filtered finJobList...');
            console.log(finJobListDetail);
            schOutVue.finJobListDetail = finJobListDetail;
        },
        getUnFinJobListDetails: function () {
            let period = this.period;
            let sid = this.unfinJob;
            let unfinjoblist = this.unfinJobList;
            let unfinJobListDetail = unfinjoblist.filter(d => d.sid === sid);
            console.log('filtered unfinJobList...');
            console.log(unfinJobListDetail);
            schOutVue.unfinJobListDetail = unfinJobListDetail;
        },
        getUnFinJobOutput: function () {
            let period = this.period;
            let sid = this.unfinJob;
            axios.post(this.phpajaxresponsefile, {
                action: 'getUnFinJobOutput',
                period: period,
                sid: sid
            }).then(function (response) {
                console.log('on getUnFinJobOutput( period=' + period + ' & sid=' + sid + ' Function...');
                console.log(response.data);
                schOutVue.unfinJobListOutput = response.data;
            });
        },
        getFinJobOutput: function () {
            let period = this.period;
            let sid = this.finJob;
            axios.post(this.phpajaxresponsefile, {
                action: 'getFinJobOutput',
                period: period,
                sid: sid
            }).then(function (response) {
                console.log('on getFinJobOutput( period=' + period + ' & sid=' + sid + ' Function...');
                console.log(response.data);
                schOutVue.finJobListOutput = response.data;
            });
        }
    },
    computed: {
    },
    mounted: function () {
        this.getPeriod();
    }
});
        </script>
    </body>
</html>
