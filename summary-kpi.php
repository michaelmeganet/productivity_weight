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
            <div>
                <div v-if='loading'>
                    Loading data.....
                </div>
                <div v-else>
                    <div v-if='kpiList != ""'>
                        this should be where data is...
                        <table>
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Staff</th>
                                    <th>Machine</th>
                                    <th>KPI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for='(data,index) in kpiList'>
                                    <td>{{index+1}}</td>
                                    <td>{{data.staffname}}</td>
                                    <td>{{data.machineModel}}</td>
                                    <td>{{data.totalkpi}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <script>
var sumKPIVue = new Vue({
    el: '#mainArea',
    data: {
        phpajaxresponsefile: 'calculatekpi.axios.php',
        period: '',
        summType: '',
        day: '',
        staffid: '',
        staffname: '',
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
        },
        staffid: function () {
            if (this.staffid.length == 6) {
                this.getStaffName();
            } else {
                this.staffname = '';
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
        getStaffName: function () {
            axios.post(this.phpajaxresponsefile, {
                action: 'getStaffName',
                staffid: sumKPIVue.staffid
            }).then(function (response) {
                console.log('on getStaffName');
                console.log(response.data);
                sumKPIVue.staffname = response.data;
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
