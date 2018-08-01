<template>
    <el-container class="enconomic">
        <el-header>
            <span class="title">经济事件曲线</span>
            <el-switch
                    v-model="autoRefresh"
                    active-text="自动刷新">
            </el-switch>
        </el-header>
        <el-main>
            <economic-chart ref="chart"></economic-chart>
        </el-main>
        <el-footer height="">
            <el-button type="primary" @click="refreshData">刷新</el-button>
        </el-footer>
    </el-container>
</template>

<script>
    import economicChart from '../components/economic-chart';
    export default {
        name: 'enconomic',
        data() {
            return {
                autoRefresh: false,
                timer: ''
            };
        },
        methods: {
            refreshData() {
                this.$refs.chart.getData();
            },
            toggleRefresh() {
                if (this.autoRefresh) {
                    this.timer = setInterval(() => {
                        this.refreshData();
                    }, 2000);
                } else {
                    clearInterval(this.timer);
                }
            }
        },
        components: {
            economicChart
        },
        watch: {
            autoRefresh: 'toggleRefresh',
        }
    };
</script>

<style lang="less" scoped>
    .enconomic {
        padding: 0 60px;
        .el-header {
            line-height: 60px;
        }
        .el-footer {
            text-align: right;
        }
        .el-button {
            width: 120px;
        }
    }
    .title {
        display: inline-block;
        font-size: 36px;
        font-weight: bold;
        margin-right: 40px;
    }
    .el-main {
        display: flex;
        padding-top: 0;
        justify-content: space-around;
    }
</style>
