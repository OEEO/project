<template>
    <div class="chart">
        <div class="main">
            <div class="chart-wrapper">
                <div class="myChart" ref="myChart" style="width: 800px;height: 400px;"></div>
                <el-checkbox v-model="shouldShowEvent" class="showEvent">显示事件</el-checkbox>
            </div>
            <event-list
		            class="event-wrapper"
		            v-if="eventShow"
		            @addEvent="addEvent"
		            :point="activePoint"
		            :eventList="eventList"></event-list>
        </div>
        <el-dialog
                title="提示"
                :visible.sync="moveEventPointShow"
                width="30%"
                :before-close="handleClose">
            <span>是否移动当前事件点至{{moveTo.coord ? moveToDate : ''}}？</span>
            <span slot="footer" class="dialog-footer">
                <el-button @click="moveEventPointShow = false">取 消</el-button>
                <el-button type="primary" @click="moveEventPoint">确 定</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
    import echarts from 'echarts/lib/echarts';
    import 'echarts/lib/chart/line';
    import 'echarts/lib/component/tooltip';
    import 'echarts/lib/component/title';
    import 'echarts/lib/component/dataZoom';
    import 'echarts/lib/component/markPoint';
    import { ECONOMIC_DATA, EVENT_NEAR } from '../config/url';
    import eventList from './eventList';

    export default {
        name: 'economic-chart',
        components: {
            eventList
        },
        data() {
            return {
                game: this.$route.params.game || 'lol',
                id: this.$route.params.id || 425181,
                myChart: '',                // 图表
                data: [],                   // 传入的数据
                activePoint: {
                    // name: '',    索引
                    // coord: ''    坐标
                },                          // 当前点击的点
                eventPoint: [               // 事件点
                    // {
                    //     name: '',    索引
                    //     coord: ''    坐标
                    // }
                ],                          // 所有事件点
                eventPointIndex: [],        // 所有事件点的索引
                activePointIndex: '',       // 当前点击的点的索引
                moveTo: {
                    // name: '',    索引
                    // coord: ''    坐标
                },                          // 将事件点移至的点
                option: {},                 // Echarts的属性
                isEventPoint: false,        // 当前点击的点是否是事件点
                haveEventPoint: false,      // 是否已经有事件点
                eventPointShow: false,      // 添加事件点
                moveEventPointShow: false,  // 移动事件点
                eventShow: false,           // 显示事件
                eventPanelShow: false,      // 添加事件
                shouldShowEvent: false,     // 显示事件点
                symbolSize: 0,              // 事件点大小，默认不显示
                isClick: true,              // 判断点击事件和拖动事件
                timerHandler: '',           // 定时器，将改变isClick的值
                eventList: [],              // 事件列表
            };
        },
        computed: {
            moveToDate() {
                let date;
                if (this.moveTo.coord) {
                    date = this.moveTo.coord[0];
                    date = Math.floor(date / 60) + ':' + (date % 60).toString().padStart(2, 0);
                }
                return date;
            }
        },
        methods: {
            // 点击图表上的点
            // mouseDownEvent(params) {
            //     this.isClick = true;
            //     this.isEventPoint = false;
            //     this.timerHandler = setTimeout(() => {
            //         this.isClick = false;
            //         switch (params.componentType) {
            //             case 'series':
            //                 if (this.eventPointIndex.indexOf(params.dataIndex) !== -1) {
            //                     this.activePointIndex = params.dataIndex;
            //                     this.activePoint = {
            //                         name: params.dataIndex,
            //                         coord: params.data.value
            //                     };
            //                     this.isEventPoint = true;
            //                     return;
            //                 } else {
            //                     this.activePointIndex = '';
            //                 }
            //                 break;
            //             case 'markPoint':
            //                 this.activePointIndex = params.data.name;
            //                 this.activePoint = params.data;
            //                 this.isEventPoint = true;
            //                 break;
            //         }
            //     }, 200);
            // },
            // mouseUpEvent(params) {
            //     if (this.isClick) {
            //         clearTimeout(this.timerHandler);
            //     } else {
            //         if (!this.isEventPoint) return;
            //         switch (params.componentType) {
            //             case 'series':
            //                 if (this.eventPointIndex.indexOf(params.dataIndex) !== -1) {
            //                     this.haveEventPoint = true;
            //                     setTimeout(() => {
            //                         this.haveEventPoint = false;
            //                     }, 5000);
            //                 } else {
            //                     console.log(params);
            //                     console.log(this.activePoint);
            //                     this.moveEventPointShow = true;
            //                     this.moveTo = {
            //                         name: params.dataIndex,
            //                         coord: params.data.value
            //                     };
            //                 }
            //                 break;
            //             case 'markPoint':
            //                 this.haveEventPoint = true;
            //                 setTimeout(() => {
            //                     this.haveEventPoint = false;
            //                 }, 5000);
            //         }
            //     }
            // },
            clickPoint(params) {
                setTimeout(() => {
                    // if (!this.isClick) return;
                    switch (params.componentType) {
                        case 'series':
                            if (this.eventPointIndex.indexOf(params.dataIndex) !== -1) {
                                return;
                            }
                            this.eventPointShow = true;
                            this.activePoint = {
                                name: params.dataIndex,
                                coord: params.data.value
                            };
                            this.eventShow = true;
                            this.activePointIndex = params.dataIndex;
                            // console.log(params);
                            this.axios.get(EVENT_NEAR, {
                                params: {
                                    id: this.id,
                                    game: this.game,
                                    time: params.data.value[0]
                                }
                            }).then(res => {
                                let data = res.data;
                                this.eventList = data.data;
                            });
                            break;
                        case 'markPoint':
                            this.eventShow = true;
                            this.activePoint = params.data;
                            this.axios.get(EVENT_NEAR, {
                                params: {
                                    id: this.id,
                                    game: this.game,
                                    time: params.data.coord[0]
                                }
                            }).then(res => {
                                let data = res.data;
                                this.eventList = data.data;
                            });
                            this.activePointIndex = params.data.name;
                            break;
                    }
                });
            },
            // 添加事件点
            addEventPoint() {
                this.eventPointIndex.push(this.activePointIndex);
                this.eventPoint.push(this.activePoint);
                this.eventPointShow = false;
                this.eventShow = true;
                this.formChart();
            },
            // 添加事件
            addEvent() {
                this.eventList.push(
                    {
                        p1: 1,
                        p2: 2,
                        type: ''
                    }
                );
            },
            // 移动事件点
            moveEventPoint() {
                this.moveEventPointShow = false;
                this.eventPoint.forEach((value, index, array) => {
                    if (value.name.toString() === this.activePointIndex.toString()) {
                        array[index] = this.moveTo;
                    }
                });
                this.eventPointIndex.forEach((value, index, array) => {
                    if (value.toString() === this.activePointIndex.toString()) {
                        array[index] = this.moveTo.name;
                    }
                });
                this.formChart();
            },
            // 获取数据
            getData() {
                this.data = [];
                this.axios.get(ECONOMIC_DATA, {
                    params: {
                        game: this.game,
                        id: this.id
                    }
                }).then(res => {
                    const data = res.data.data;
                    for (let i = 0; i < data.length; i++) {
                        let pointInfo = data[i];
                        let point = {
                            value: [pointInfo.time, parseInt(pointInfo.left_total) - parseInt(pointInfo.right_total)]
                        };
                        if (pointInfo.isInflexion) {
                            this.eventPoint.push({
                                name: i,
                                coord: point.value
                            });
                        }
                        this.data.push(point);
                    }
                    this.$nextTick(this.formChart);
                });
            },
            // 形成图表
            formChart() {
                this.myChart = echarts.init(this.$refs.myChart);
                this.option = {
                    tooltip: {

                    },
                    xAxis: {
                        type: 'value',
                        axisLabel: {
                            formatter: function (value) {
                                let minutes = Math.floor(value / 60);
                                let second = Math.floor(value % 60).toString().padStart(2, 0);
                                let text = `${minutes}: ${second}`;
                                return text;
                            }
                        }
                    },
                    yAxis: {
                        type: 'value',
                        axisLabel: {
                            formatter: function (value) {
                                return Math.abs(value);
                            }
                        }
                    },
                    dataZoom: [
                        {
                            type: 'slider',
                            xAxisIndex: [0],
                            show: true
                        }, {
                            type: 'inside',
                            xAxisIndex: [0]
                        }
                    ],
                    series: [{
                        data: this.data,
                        type: 'line',
                        markPoint: {
                            symbolSize: this.symbolSize,
                            itemStyle: {
                                color: '#039cda',
                                borderColor: '#fff',
                                borderWidth: 1
                            },
                            label: {
                                show: false
                            },
                            data: this.eventPoint
                        }
                    }]
                };
                this.myChart.setOption(this.option);
            },
            // 显示事件
            showEvent() {
                this.eventShow = false;
                let chart = this.myChart;
                if (this.shouldShowEvent) {
                    this.symbolSize = 30;
                    chart.on('mousedown', this.mouseDownEvent);
                    chart.on('mouseup', this.mouseUpEvent);
                    chart.on('click', this.clickPoint);
                    this.formChart();
                } else {
                    this.symbolSize = 0;
                    chart.off('click', this.clickPoint);
                    this.formChart();
                }
            }
        },
        watch: {
            shouldShowEvent: 'showEvent',
        },
        beforeMount() {
            this.getData();
        },
        mounted() {
            // this.formChart();
        }
    };
</script>

<style scoped>
    .chart{
        width: 100%;
    }
    .main {
        display: flex;
        flex-wrap: wrap;
    }
    .myChart {
        flex: 0 0 800px;
    }
    .event-wrapper {
        flex: 1 0 360px;
        height: 450px;
        position: relative;
        overflow: hidden;
        /*text-align: center;*/
    }
    .showEvent {
        margin: 40px;
    }
</style>
