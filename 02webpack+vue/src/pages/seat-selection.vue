<template>
    <div class="seat-selection-page">
        <div class="main-box-header">
            <div class="title">选座情况</div>
            <el-switch
                    v-model="autoUpdate"
                    active-text="自动刷新">
            </el-switch>
        </div>
        <div class="seats-wrap">
            <div class="left-team">
                <el-cascader
                        :placeholder="player.name"
                        v-for="(player, index) in leftTeam"
                        v-if="leftTeam.length > 0"
                        class="player"
                        expand-trigger="hover"
                        :key="player.value"
                        :show-all-levels="false"
                        :options="options"
                        v-model="leftTeamOptions[index]"
                        @change="handleChange">
                </el-cascader>
            </div>
            <div class="right-team">
                <el-cascader
                        :placeholder="player.name"
                        v-for="(player, index) in rightTeam"
                        v-if="rightTeam.length > 0"
                        class="player"
                        expand-trigger="hover"
                        :key="player.value"
                        :show-all-levels="false"
                        :options="options"
                        v-model="rightTeamOptions[index]"
                        @change="handleChange">
                </el-cascader>
            </div>
        </div>
        <div class="btn-wrap">
            <el-button type="primary" @click="submitSeatSel">提交</el-button>
            <el-button type="primary" @click="getSeats">刷新</el-button>
        </div>
    </div>
</template>

<script>
    import url from '../config/url.js';
    import comHeader from '../components/com-header.vue';
    export default {
        name: 'seat-selection',
        data() {
            return {
                timer: null, // 定时器
                autoUpdate: false, // 是否自动更新
                autoUpdateTime: 2000, // 自动更新频率
                leftTeam: [0, 0, 0, 0, 0], // 左队选座数据
                rightTeam: [0, 0, 0, 0, 0],
                options: [], // 所有英雄列表
                leftTeamOptions: [], // 左队修改选座储存数据
                rightTeamOptions: [],
            };
        },

        computed: {
            game() {
                return this.$route.params.game;
            },
            id() {
                return this.$route.params.id;
            }
        },

        methods: {
            getLabelOfValue(name, teamId, id) {
                let result = '';
                this.options.some(game => {
                    if (game.value === name) {
                        game.children.some(team => {
                            if (team.value === teamId) {
                                team.children.some(player => {
                                    if (player.value === id) {
                                        result = player.label;
                                        return true;
                                    }
                                });
                                return true;
                            }
                        });
                        return true;
                    }
                });
                return result;
            },
            submitSeatSel() {
                let id = this.id || 1;
                let game = this.game || 'kog';
                let left = this.leftTeam;
                let right = this.rightTeam;
                this.leftTeamOptions.forEach((player, i) => {
                    if (player.length > 0) {
                        left[i] = {
                            name: this.getLabelOfValue(game, player[1], player[2]),
                            playerId: player[2],
                            teamId: player[1],
                        };
                    }
                });
                this.rightTeamOptions.forEach((player, i) => {
                    if (player.length > 0) {
                        right[i] = {
                            name: this.getLabelOfValue(game, player[1], player[2]),
                            playerId: player[2],
                            teamId: player[1],
                        };
                    }
                });
                console.log(this.rightTeamOptions, '重要');

                let data = {
                    id: id,
                    game: game,
                    data: {
                        left: left,
                        right: right
                    }
                };

                this.axios.post(url.PLAYERS, data)
                    .then(res => {
                        console.log('设置成功');
                        console.log(res);
                    });

            },
            handleChange(value) {
                this.autoUpdate = false;
            },
            getHomePlayers() {
                let that = this;
                this.axios.get(url.HOME_PLAYERS)
                    .then(res => {
                        that.options = res.data.data;
                    });
            },
            getSeats() {
                this.leftTeamOptions = [];
                this.rightTeamOptions = [];
                let that = this;
                let id = this.id;
                let game = this.game || 'kog';
                this.axios.get(url.PLAYERS + `?id=${id}&game=${game}`)
                    .then(res => {
                        console.log(res);
                        let data = res.data.data;
                        if (!data) {
                            return;
                        }
                        that.leftTeam = data.left;
                        that.rightTeam = data.right;
                    })
                    .catch(err => {
                        console.log(err);
                    });
            }
        },

        mounted() {
            this.getHomePlayers();
            this.getSeats();
        },

        watch: {
            autoUpdate() {
                if (this.autoUpdate) {
                    this.timer = setInterval(() => {
                        this.getSeats();
                        console.log('2秒自动刷新一次');
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
    @import "../less/variable";

    @playerWidth: 200px;
    .seat-selection-page{
        position: relative;
        min-height: @mainHeight;
        padding-left: 120px;
        padding-right: 181px;
    }
    .main-box-header {
        display: flex;
        height: 65px;
        align-items: center;
        .title {
            font-size: 32px;
            font-weight: 700;
            margin-right: 120px;
        }
    }
    .seats-wrap {
        margin-top: 20px;
    }
    .left-team,
    .right-team {
        display: flex;
        justify-content: space-between;
    }
    .right-team {
        margin-top: 60px;
    }
    .player {
        cursor: pointer;
        width: @playerWidth;
        height: @playerWidth;
        line-height: @playerWidth;
        box-sizing: border-box;
        overflow: hidden;
        background-color: #909399;
    }
    .btn-wrap {
        margin-top: 50px;
        text-align: right;
    }
    .player-sel-modal {
        position: absolute;
        top: 20px;
    }

</style>

<style lang="less">
    .seat-selection-page {
        .el-cascader__label {
            padding: 0;
            text-align: center;
            color: #fff;
        }
        .el-input__inner {
            background-color: transparent;
            padding: 0;
            color: #fff;
            border: none;
            width: 100%;
            text-align: center;
        }
        .el-input__inner::-webkit-input-placeholder {
            color: #fff;
        }
        .el-input__icon:before {
            content: '';
        }
        .el-cascader__label {
        }
    }
</style>