<template>
    <div class="gold-chart-page">
        <div class="main-header">
            <div class="title">事件经济曲线</div>
            <el-switch
                    v-model="autoUpdate"
                    active-text="自动刷新">
            </el-switch>
        </div>
        <div class="gold-chart">
            <e-chart :options="options" @click="handleChartClick"></e-chart>
        </div>
        <div class="gold-chart-alert" v-show="chartAlertBoxShow">
            <el-form>
                <div class="chart-alert-main">
                    <el-form-item>
                        <el-button type="primary">添加成员</el-button>
                        <el-button type="success">添加事件</el-button>
                        <el-button type="danger" @click="showDelEventBox">删除事件</el-button>
                    </el-form-item>

                    <el-form-item>
                        <el-select v-model="chartEventsValue" placeholder="事件" class="sel-event">
                            <el-option
                                    v-for="item in chartEvents"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value">
                            </el-option>
                        </el-select>

                        <el-select v-model="p1Value" placeholder="p1" class="sel-p1">
                            <el-option
                                    v-for="item in p1"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value">
                            </el-option>
                        </el-select>

                        <el-select v-model="p2Value" placeholder="p2" class="sel-p2">
                            <el-option
                                    v-for="item in p2"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>

                    <div v-if="curPointMsg">
                        {{ curPointMsg.name + ': ' + curPointMsg.value }}
                    </div>

                </div>
            </el-form>
        </div>
    </div>
</template>

<script>
    import utils from '../utils';
    import url from '../config/url';
    import ECharts from 'vue-echarts/components/ECharts';
    import 'echarts/lib/chart/line';
    import 'echarts/lib/chart/bar';
    import 'echarts/lib/component/tooltip';
    import 'echarts/lib/component/legend';
    import 'echarts/lib/component/dataZoom';
    import 'echarts/lib/component/title';

    export default {
        name: 'gold-chart',
        components: {eChart: ECharts},
        data() {
            return {
                curCount: 0, //模拟添加，当前
                autoUpdate: false, //自动更新
                autoUpdateTime: 1000,
                chartAlertBoxShow: false, // 事件框
                startTime: new Date(),
                timer: null,
                virtualTimer: null,
                goldsData: [],
                timeData: [],
                curPointMsg: null,
                chartEvents: [
                    { value: '001', label: '敌方被杀' },
                    { value: '002', label: '敌方塔被摧毁' },
                    { value: '003', label: '事件3' },
                    { value: '004', label: '事件4' },
                ],
                chartEventsValue: '',
                p1: [
                    { value: '001', label: '英雄1' },
                    { value: '002', label: '英雄2' },
                    { value: '003', label: '英雄3' },
                    { value: '004', label: '英雄4' },
                ],
                p1Value: '',
                p2: [
                    { value: '001', label: '英雄1' },
                    { value: '002', label: '英雄2' },
                    { value: '003', label: '英雄3' },
                    { value: '004', label: '英雄4' },
                ],
                p2Value: '',
                options: {
                    legend: {

                    },
                    dataRange: {

                    },
                    tooltip: {
                        trigger: 'axis',
                        position: function (pt) {
                            return [pt[0], '10%'];
                        }
                    },
                    // title: {
                    //     left: 'center',
                    //     text: '事件经济曲线',
                    // },
                    toolbox: {
                        feature: {
                            dataZoom: {
                                yAxisIndex: 'none'
                            },
                            restore: {},
                            saveAsImage: {}
                        }
                    },
                    xAxis: {
                        min: 0,
                        type: 'category',
                        boundaryGap: ['20%', '20%'],
                        data: [],
                        // 座标箭头
                        axisLine: {
                            symbol: ['none', 'arrow'],
                            symbolOffset: [0, 10]
                        },
                    },
                    yAxis: {
                        min(value) {
                            return value.min - 500;
                        },
                        max(value) {
                            return value.max + 500;
                        },
                        type: 'value',
                        boundaryGap: false,
                        // 座标箭头
                        axisLine: {
                            symbol: ['none', 'arrow'],
                            symbolOffset: [-10, 10]
                        },
                    },
                    dataZoom: [{
                        type: 'inside',
                        start: 0,
                        end: 100
                    }, {
                        start: 0,
                        end: 10,
                        handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                        handleSize: '80%',
                        handleStyle: {
                            color: '#fff',
                            shadowBlur: 3,
                            shadowColor: 'rgba(0, 0, 0, 0.6)',
                            shadowOffsetX: 2,
                            shadowOffsetY: 2
                        }
                    }],
                    series: [
                        {
                            name: '左队减右队经济差',
                            type: 'line',
                            smooth: false, // 平滑
                            sampling: 'average',
                            symbol: 'rect',
                            symbolSize: 8,
                            itemStyle: {
                                // 线颜色
                                normal: {
                                    color: '#c3292f',
                                },
                            },
                            data: []
                        }
                    ]
                }
            };
        },

        computed: {
            game() {
                return this.$route.params.game;
            },
            id() {
                return this.$route.params.id || 425181;
            }
        },

        methods: {
            transTime(t) {
                let h = Math.floor(+t / 3600) + '';
                let m = Math.floor((+t - h * 3600) / 60) + '';
                let s = +t % 60 + '';
                let str = '';
                if (+h) {
                    h = h.length === 2 ? h : '0' + h;
                    str += `${h}:`;
                }
                m = m.length === 2 ? m : '0' + m;
                s = s.length === 2 ? s : '0' + s;
                str += `${m}:${s}`;
                return str;
            },
            timeTos(str) {
                let arr = str.split(':').reverse();
                let result = 0;
                result = +arr[0] + +arr[1] * 60;
                if (arr[2]) {
                    result += +arr[2] * 3600;
                }
                return result;
            },
            getGoldData() {
                let that = this;

                this.axios.get(url.ECONOMIC_DATA, {
                    params: {
                        id: this.id
                    }
                })
                    .then(res => {
                        console.log('chart', res);
                        // 数据
                        let arr = res.data.data;
                        let times = [];
                        let goldsDiff = []; // 左队减右队
                        arr.forEach(dot => {
                            let time = this.transTime(dot.time);
                            times.push(time);
                            goldsDiff.push(dot.left_total - dot.right_total);
                        });
                        // 左队减右队经济差
                        this.goldsData = goldsDiff;
                        this.timeData = times;

                        // that.options.series[0].data = goldsDiff;
                        // // 时间
                        // that.options.xAxis.data = times;

                    });
            },
            handleChartClick(params) {
                console.log(params);
                // 点击定位图标
                this.autoUpdate = false;
                this.chartAlertBoxShow = true;
                this.curPointMsg = {
                    name: params.seriesName,
                    value: params.value
                };
            },
            showDelEventBox() {
                let that = this;
                this.$confirm('此操作将永久删除该事件, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.$message({
                        type: 'success',
                        message: '删除成功!'
                    });

                    that.delEvent();
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '已取消删除'
                    });
                });
            },

            delEvent() {

            }
        },

        mounted() {
            this.getGoldData();
            // 开启自动更新，让watch监听到
            this.autoUpdate = true;
        },

        watch: {
            autoUpdate() {
                if (this.autoUpdate) {
                    let that = this;
                    // 模拟添加
                    this.timer = setInterval(() => {
                        if (that.goldsData[this.curCount]) {
                            that.options.series[0].data.push(that.goldsData[this.curCount]);
                            that.options.xAxis.data.push(that.timeData[this.curCount]);
                            this.curCount++;
                        }  else {
                            clearInterval(this.timer);
                            this.timer = null;
                        }
                    }, this.autoUpdateTime);
                } else {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            }
        },

        destroyed() {
            clearInterval(this.timer);
            this.timer = null;
        }
    };
</script>

<style lang="less" scoped>
    .gold-chart-page {
        padding-left: 120px;

        .main-header {
            display: flex;
            height: 65px;
            align-items: center;
            .title {
                font-size: 32px;
                font-weight: 700;
                margin-right: 120px;
            }
        }

        .gold-chart {
            width: 100%;
            .echarts {
                width: 100%;
                height: 500px;
            }
        }

        .gold-chart-alert {
            margin-top: 50px;
            text-align: center;
            .sel-event {

            }
        }
        

    }
</style>
