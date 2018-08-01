<template>
    <el-container class="dragon">
        <el-header>
            <span class="title">大小龙管理</span>
            <el-switch
                    v-model="autoRefresh"
                    active-text="自动刷新">
            </el-switch>
        </el-header>
        <el-main>
            <div class="wrap">
                <h3>{{game === 'lol' ? '大龙' : "主宰"}}</h3>
                <div class="form-group">
                    <input type="text" v-model="left_zhuzai">
                    <span>vs</span>
                    <input type="text" v-model="right_zhuzai">
                </div>
                <el-button type="primary" @click="modify" class="modify">修改</el-button>
            </div>
            <div class="wrap">
                <h3>{{game === 'lol' ? '小龙' : "暴君"}}</h3>
                <div class="form-group">
                    <input type="text" v-model="left_baojun">
                    <span>vs</span>
                    <input type="text" v-model="right_baojun">
                </div>
                <el-button type="primary" @click="modify" class="modify">修改</el-button>
            </div>
            <div class="wrap" v-if="game === 'kog'">
                <h3>黑暗暴君</h3>
                <div class="form-group">
                    <input type="text" v-model="left_darkbaojun">
                    <span>vs</span>
                    <input type="text" v-model="right_darkbaojun">
                </div>
                <el-button type="primary" @click="modify" class="modify">修改</el-button>
            </div>
        </el-main>
        <el-footer height="" class="dragon-footer">
            <el-button type="primary" @click="getDragon">刷新</el-button>
        </el-footer>
    </el-container>
</template>

<script>
    import { BUFF_LIST } from '../config/url';
    export default {
        name: 'dragon',
        data() {
            return {
                autoRefresh: false,
                game: this.$route.params.game || 'lol',
                id: this.$route.params.id || 1,
                left_baojun: '',
                right_baojun: '',
                left_darkbaojun: '',
                right_darkbaojun: '',
                left_zhuzai: '',
                right_zhuzai: ''
            };
        },
        methods: {
            getDragon() {
                this.axios.get(BUFF_LIST, {
                    params: {
                        game: this.game,
                        id: parseInt(this.id)
                    }
                }).then(res => {
                    let data = res.data.data;
                    if (data) {
                        this.left_baojun = data.left_baojun;
                        this.right_baojun = data.right_baojun;
                        this.left_darkbaojun = data.left_darkbaojun;
                        this.right_darkbaojun = data.right_darkbaojun;
                        this.left_zhuzai = data.left_zhuzai;
                        this.right_zhuzai = data.right_zhuzai;
                    }
                });
            },
            refreshDragon() {
                if (this.autoRefresh === true) {
                    this.refresh = setInterval(this.getDragon, 2000);
                } else {
                    clearInterval(this.refresh);
                }
            },
            modify() {
                this.axios.post(BUFF_LIST, {
                    game: this.game,
                    id: parseInt(this.id),
                    data: {
                        left_baojun: this.left_baojun,
                        right_baojun: this.right_baojun,
                        left_darkbaojun: this.left_darkbaojun,
                        right_darkbaojun: this.right_darkbaojun,
                        left_zhuzai: this.left_zhuzai,
                        right_zhuzai: this.right_zhuzai
                    }
                }).then(res => {
                    console.log(res.data);
                });
            }
        },
        watch: {
            autoRefresh: 'refreshDragon',
        },
        activated() {
            this.getDragon();
            // this.autoRefresh = true;
        },
        deactivated() {
            this.autoRefresh = false;
        }
    };
</script>

<style scoped lang="less">
    .dragon {
        padding: 0 60px;
        .el-header {
            line-height: 60px;
        }
        .dragon-footer {
            text-align: right;
            margin-top: 40px;
        }
        .el-button {
            width: 120px;
        }
    }
    .title {
        display: inline-block;
        font-size: 24px;
        font-weight: bold;
        margin-right: 40px;
        padding-left: 20px;
    }
    .el-main {
        display: flex;
        flex-wrap: wrap;
        padding-top: 0;
        justify-content: space-between;
    }
    .wrap {
        flex: 0 1 405px;
        margin-top: 20px;
        background-color: #fff;
        h3 {
            padding-left: 20px;
        }
        .form-group {
            display: flex;
            justify-content: space-around;
            input {
                display: inline-block;
                height: 64px;
                width: 86px;
                text-align: center;
            }
            span {
                font-size: 36px;
                font-weight: bold;
                line-height: 64px;
            }
        }
        .modify {
            margin-top: 20px;
            margin-left: 50%;
            margin-bottom: 10px;
            transform: translateX(-50%)
        }
    }
</style>
