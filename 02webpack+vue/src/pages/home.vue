<template>
    <div class="home-page" @click="blockClickEvent">
        <div class="new-instance-btn-wrap">
            <el-button type="primary" @click="addInstanceModalShow = true">新建实例</el-button>
        </div>
        <div class="instances">
            <instance-item
                    v-if="instances.length > 0"
                    v-for="(item, index) in instances"
                    @deleteInstance="deleteInstance(item.game, item.id)"
                    @goToInstance="getGames(item.game, item.id)"
                    :key="index"
                    :status="item.status"
                    :title="item.game + item.id">
            </instance-item>

            <!--第几局-->
            <div class="games-modal" v-show="gamesModalShow" @click.stop="">
                <div class="game-list">
                    <div class="game-item"
                         v-for="(gameItemId, i) in curGames"
                         @click="goToInstance(curGameName, gameItemId)">
                        第{{ numTch[i] }}局
                    </div>
                </div>
                <div class="games-modal-btns">
                    <el-button type="success" @click="addGameItemModalShow = true">添加</el-button>
                    <!--<el-button type="success" @click="addGameItem">添加</el-button>-->
                    <el-button type="primary" @click="gamesModalShow = false">关闭</el-button>
                </div>
                <div class="add-game-item-modal" v-show="addGameItemModalShow">
                    <el-form>
                        <el-form-item label="当前局数" label-width="80px">
                            <el-input v-model="addFormData.index"></el-input>
                        </el-form-item>
                        <el-form-item label="左队" label-width="80px">
                            <el-select
                                    filterable
                                    allow-create
                                    v-model="addFormData.leftTeamId"
                                    placeholder="请选择左队">
                                <el-option
                                        :label="item.label"
                                        :value="item.value"
                                        v-for="item in addFormData.teamName"
                                        :key="item">
                                </el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item label="右队" label-width="80px">
                            <el-select
                                    filterable
                                    allow-create
                                    v-model="addFormData.rightTeamId"
                                    placeholder="请选择右队">
                                <el-option
                                        :label="item.label"
                                        :value="item.value"
                                        v-for="item in addFormData.teamName"
                                        :key="item">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item>
                            <el-button type="success"
                                       @click="addGameItem">添加</el-button>
                            <el-button type="primary"
                                       @click="addGameItemModalShow = false">关闭</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </div>

        </div>
        <div class="add-instance-modal" v-show="addInstanceModalShow" @click.stop="">
            <add-instance-form
                    :addFormData="addFormData"
                    @addInstance="addInstance"
                    @closeTheModal="closeTheModal">
            </add-instance-form>
        </div>
    </div>
</template>

<script>
    import url from '../config/url.js';
    import instanceItem from '../components/home/instance-item.vue';
    import addInstanceForm from '../components/home/add-instance-form.vue';
    export default {
        name: 'home',
        components: {instanceItem, addInstanceForm},

        data() {
            return {
                numTch: ['一', '二', '三', '四', '五', '六', '七', '八', '九'],
                instances: [], // 所有实例参数
                addInstanceModalShow: false, // 添加实例表单
                gamesModalShow: false, // 局列表
                addGameItemModalShow: false, // 添加一局表单
                curGames: [],
                curGameName: '', //当前点击进入的实例 游戏类型
                curGamesId: '', //当前点击进入的实例id
                options: [],
                allTeamName: [],
                addFormData: {
                    game: {
                        value: '',
                        labels: ['kog', 'lol']
                    },
                    teamName: [],
                    leftTeamId: '',
                    rightTeamId: '',
                    liveStreaming: {
                        value: '',
                        labels: ['1', '2']
                    },
                    id: '',
                    index: 1,
                    // input: ''
                },
                gameItemNum: -1, // 第几局
            };
        },

        computed: {
            count() {
                return this.instances.length;
            }
        },

        methods: {
            // 点击空白处
            blockClickEvent() {
                this.gamesModalShow = false;
            },
            getProcessList() {
                let that = this;

                this.axios.get(url.PROCESS_LIST)
                    .then(res => {
                        that.instances = res.data.data;
                        console.log('实例', that.instances);
                    })
                    .catch(e => {
                        console.error(e);
                    });
            },
            getHomePlayers() {
                let that = this;
                this.axios.get(url.HOME_PLAYERS)
                    .then(res => {
                        that.options = res.data.data;
                        that.options.forEach(game => {
                            game.children.forEach(team => {
                                that.addFormData.teamName.push({
                                    label: team.label,
                                    value: team.value
                                });
                            });
                        });
                    })
                    .catch(err => {
                        console.log(err);
                    });
            },
            getGames(game, id) {
                this.axios.get(url.HOME_GAMES + `?id=${id}&game=${game}`)
                    .then(res => {
                        console.log('当前实例id', id);
                        console.log('局id数组', res.data.data);
                        this.curGames = res.data.data;
                        // 测试
                        // this.curGames = ['', '111', '222'];

                        this.curGameName = game;
                        this.curGamesId = id;
                        this.gamesModalShow = true;
                    })
                    .catch(err => {
                        console.log(err);
                    });
            },
            // 添加实例
            addInstance() {
                this.addInstanceModalShow = false;
                let that = this;
                let formData = this.addFormData;
                let game = formData.game.value;
                let leftId = formData.leftTeamId;
                let rightId = formData.rightTeamId;
                let data = {
                    game: game,
                    leftId: leftId,
                    rightId: rightId,
                    // 测试 id
                    // id: formData.id,
                    id: formData.id + formData.index,
                    input: formData.liveStreaming.value
                };
                console.log(data);
                this.axios.post(url.ADD_PROCESS, data)
                    .then(res => {
                        console.log(res);
                        that.getProcessList();
                    })
                    .catch(err => {
                        console.log(err);
                    });
            },
            goToInstance(game, id) {
                this.$router.push({
                    name: 'instance',
                    params: {
                        game: game,
                        id: id
                    }
                });
            },

            closeTheModal() {
                this.addInstanceModalShow = false;
            },
            deleteInstance(game, id) {
                console.log(url.DEL_PROCESS + `?game=${game}&id=${id}`);
                let that = this;
                this.axios.get(url.DEL_PROCESS + `?game=${game}&id=${id}`)
                    .then(res => {
                        console.log(res);
                        that.getProcessList();
                    })
                    .catch(err => {
                        console.log(err);
                    });
            },
            //添加局
            addGameItem() {
                this.addGameItemModalShow = false;

                let data = this.addFormData;
                let index = data.index;
                let leftTeamId = data.leftTeamId;
                let rightTeamId = data.rightTeamId;

                let game = this.curGameName;
                let curGamesId = this.curGamesId;
                let id = curGamesId.slice(0, -1);
                let matchId = id + index;

                // left, right 测试数据
                let left = [
                    { 'name': 'name', 'playerId': '0', 'teamId': leftTeamId },
                    { 'name': 'name', 'playerId': '0', 'teamId': leftTeamId },
                    { 'name': 'name', 'playerId': '0', 'teamId': leftTeamId },
                    { 'name': 'name', 'playerId': '0', 'teamId': leftTeamId },
                    { 'name': 'name', 'playerId': '0', 'teamId': leftTeamId }
                ];

                let right = [
                    { 'name': 'name', 'playerId': '0', 'teamId': rightTeamId },
                    { 'name': 'name', 'playerId': '0', 'teamId': rightTeamId },
                    { 'name': 'name', 'playerId': '0', 'teamId': rightTeamId },
                    { 'name': 'name', 'playerId': '0', 'teamId': rightTeamId },
                    { 'name': 'name', 'playerId': '0', 'teamId': rightTeamId }
                ];

                let postData = {
                    id: matchId,
                    game: game,
                    data: {
                        left: left,
                        right: right
                    }
                };
                console.log('提交的数据', postData);
                this.axios.post(url.PLAYERS, postData)
                    .then(res => {
                        console.log('添加局成功');
                        console.log(res);

                        this.getGames(game, curGamesId);
                    });

            }
        },

        mounted() {
            console.log(url.PROCESS_LIST);
            this.getProcessList();
            this.getHomePlayers();
        }
    };
</script>

<style lang="less" scoped>
    @import "../less/variable";
    .home-page {
        position: relative;
        padding-left: 160px;
        padding-right: 170px;
    }
    .new-instance-btn-wrap{
        padding-top: 10px;
        margin-bottom: 20px;
        .el-button--primary {

        }
    }
    .instances {
        position: relative;
        display: flex;
        justify-content: flex-start;
        flex-wrap: wrap;
    }
    .add-instance-modal {
        position: absolute;
        top: 35px;
        background-color: #fff;
        border: 1px solid #333;
        left: 50%;
        transform: translateX(-50%);
        width: 520px;
        box-shadow: @boxShadow;
    }
    @gamesModalHeight: 200px;
    .games-modal {
        position: absolute;
        top: 160px;
        left: 50%;
        transform: translateX(-50%);
        width: 1000px;
        box-sizing: border-box;
        height: @gamesModalHeight;
        padding: 20px 0;
        background-color: #eee;
        border: 1px solid #555;
        .game-list {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }
        .game-item {
            cursor: pointer;
            height: 100px;
            line-height: 100px;
            text-align: center;
            width: 100px;
            background-color: #aaa;
        }
        .games-modal-btns {
            position: absolute;
            right: 20px;
            bottom: 10px;
        }
        .add-game-item-modal {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            text-align: right;
            padding: 30px;
        }
    }
</style>
