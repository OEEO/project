<template>
    <el-container class="bp">
        <el-header>
            <span class="title">BP情况</span>
            <el-switch
                    v-model="autoRefresh"
                    active-text="自动刷新">
            </el-switch>
            <el-switch v-model="r" active-text="强制覆盖ocr生成数据"></el-switch>
        </el-header>
        <el-main>
            <div class="scene">
                <span>BP状态：</span>
                <el-select v-model="scene" placeholder="请选择">
                    <el-option
                    v-for="(item,index) in sceneMap"
                    :key="item.value"
                    :label="item.label"
                    :value="item.label">
                    </el-option>
                </el-select>
            </div>
            <div class="bp-wrapper">
                <div class="bp-container">
                    <div class="left ban">
                        <p>BAN</p>
                        <img :src="getImg(left.ban[0])" width="65" height="65" @click="chooseHero(0, 'ban', 'left')">
                        <img :src="getImg(left.ban[1])" width="65" height="65" @click="chooseHero(1, 'ban', 'left')">
                        <img :src="getImg(left.ban[2])" width="65" height="65" @click="chooseHero(2, 'ban', 'left')">
                        <img :src="getImg(left.ban[3])" width="65" height="65" @click="chooseHero(3, 'ban', 'left')">
                        <img :src="getImg(left.ban[4])" width="65" height="65" @click="chooseHero(4, 'ban', 'left')" v-if="game === 'lol'">
                    </div>
                    <div class="left pick">
                        <p>PICK</p>
                        <img :src="getImg(left.pick[0])" width="80" height="80" @click="chooseHero(0, 'pick', 'left')">
                        <div class="skill" v-if="game === 'kog'">
                            <img :src="skillImg(left.skill[0])" width="25" height="25" @click="chooseSkill(0, 'left')">
                        </div>
                        <div class="skill" v-if="game === 'lol'">
                            <img :src="skillImg(left.skill[0])" width="25" height="25" @click="chooseSkill(0, 'left')">
                            <img :src="skillImg(left.skill[1])" width="25" height="25" @click="chooseSkill(1, 'left')">
                        </div>
                        <img :src="getImg(left.pick[1])" width="80" height="80" @click="chooseHero(1, 'pick', 'left')">
                        <div class="skill" v-if="game === 'kog'">
                            <img :src="skillImg(left.skill[1])" width="25" height="25" @click="chooseSkill(1, 'left')">
                        </div>
                        <div class="skill" v-if="game === 'lol'">
                            <img :src="skillImg(left.skill[2])" width="25" height="25" @click="chooseSkill(2, 'left')">
                            <img :src="skillImg(left.skill[3])" width="25" height="25" @click="chooseSkill(3, 'left')">
                        </div>
                        <img :src="getImg(left.pick[2])" width="80" height="80" @click="chooseHero(2, 'pick', 'left')">
                        <div class="skill" v-if="game === 'kog'">
                            <img :src="skillImg(left.skill[2])" width="25" height="25" @click="chooseSkill(2, 'left')">
                        </div>
                        <div class="skill" v-if="game === 'lol'">
                            <img :src="skillImg(left.skill[4])" width="25" height="25" @click="chooseSkill(4, 'left')">
                            <img :src="skillImg(left.skill[5])" width="25" height="25" @click="chooseSkill(5, 'left')">
                        </div>
                        <img :src="getImg(left.pick[3])" width="80" height="80" @click="chooseHero(3, 'pick', 'left')">
                        <div class="skill" v-if="game === 'kog'">
                            <img :src="skillImg(left.skill[3])" width="25" height="25" @click="chooseSkill(3, 'left')">
                        </div>
                        <div class="skill" v-if="game === 'lol'">
                            <img :src="skillImg(left.skill[6])" width="25" height="25" @click="chooseSkill(6, 'left')">
                            <img :src="skillImg(left.skill[7])" width="25" height="25" @click="chooseSkill(7, 'left')">
                        </div>
                        <img :src="getImg(left.pick[4])" width="80" height="80" @click="chooseHero(4, 'pick', 'left')">
                        <div class="skill" v-if="game === 'kog'">
                            <img :src="skillImg(left.skill[4])" width="25" height="25" @click="chooseSkill(4, 'left')">
                        </div>
                        <div class="skill" v-if="game === 'lol'">
                            <img :src="skillImg(left.skill[8])" width="25" height="25" @click="chooseSkill(8, 'left')">
                            <img :src="skillImg(left.skill[9])" width="25" height="25" @click="chooseSkill(9, 'left')">
                        </div>
                    </div>
                </div>
                <div class="bp-container">
                    <div class="right ban">
                        <p>BAN</p>
                        <img :src="getImg(right.ban[0])" width="65" height="65" @click="chooseHero(0, 'ban', 'right')">
                        <img :src="getImg(right.ban[1])" width="65" height="65" @click="chooseHero(1, 'ban', 'right')">
                        <img :src="getImg(right.ban[2])" width="65" height="65" @click="chooseHero(2, 'ban', 'right')">
                        <img :src="getImg(right.ban[3])" width="65" height="65" @click="chooseHero(3, 'ban', 'right')">
                        <img :src="getImg(right.ban[4])" width="65" height="65" @click="chooseHero(4, 'ban', 'right')" v-if="game === 'lol'">
                    </div>
                    <div class="right pick">
                        <p>PICK</p>
                        <div class="skill" v-if="game === 'kog'">
                            <img :src="skillImg(right.skill[0])" width="25" height="25" @click="chooseSkill(0, 'right')">
                        </div>
                        <div class="skill" v-if="game === 'lol'">
                            <img :src="skillImg(right.skill[0])" width="25" height="25" @click="chooseSkill(0, 'right')">
                            <img :src="skillImg(right.skill[1])" width="25" height="25" @click="chooseSkill(1, 'right')">
                        </div>
                        <img :src="getImg(right.pick[0])" width="80" height="80" @click="chooseHero(0, 'pick', 'right')">
                        <div class="skill" v-if="game === 'kog'">
                            <img :src="skillImg(right.skill[1])" width="25" height="25" @click="chooseSkill(1, 'right')">
                        </div>
                        <div class="skill" v-if="game === 'lol'">
                            <img :src="skillImg(right.skill[2])" width="25" height="25" @click="chooseSkill(2, 'right')">
                            <img :src="skillImg(right.skill[3])" width="25" height="25" @click="chooseSkill(3, 'right')">
                        </div>
                        <img :src="getImg(right.pick[1])" width="80" height="80" @click="chooseHero(1, 'pick', 'right')">
                        <div class="skill" v-if="game === 'kog'">
                            <img :src="skillImg(right.skill[2])" width="25" height="25" @click="chooseSkill(2, 'right')">
                        </div>
                        <div class="skill" v-if="game === 'lol'">
                            <img :src="skillImg(right.skill[4])" width="25" height="25" @click="chooseSkill(4, 'right')">
                            <img :src="skillImg(right.skill[5])" width="25" height="25" @click="chooseSkill(5, 'right')">
                        </div>
                        <img :src="getImg(right.pick[2])" width="80" height="80" @click="chooseHero(2, 'pick', 'right')">
                        <div class="skill" v-if="game === 'kog'">
                            <img :src="skillImg(right.skill[3])" width="25" height="25" @click="chooseSkill(3, 'right')">
                        </div>
                        <div class="skill" v-if="game === 'lol'">
                            <img :src="skillImg(right.skill[6])" width="25" height="25" @click="chooseSkill(6, 'right')">
                            <img :src="skillImg(right.skill[7])" width="25" height="25" @click="chooseSkill(7, 'right')">
                        </div>
                        <img :src="getImg(right.pick[3])" width="80" height="80" @click="chooseHero(3, 'pick', 'right')">
                        <div class="skill" v-if="game === 'kog'">
                            <img :src="skillImg(right.skill[4])" width="25" height="25" @click="chooseSkill(4, 'right')">
                        </div>
                        <div class="skill" v-if="game === 'lol'">
                            <img :src="skillImg(right.skill[8])" width="25" height="25" @click="chooseSkill(8, 'right')">
                            <img :src="skillImg(right.skill[9])" width="25" height="25" @click="chooseSkill(9, 'right')">
                        </div>
                        <img :src="getImg(right.pick[4])" width="80" height="80" @click="chooseHero(4, 'pick', 'right')">
                    </div>
                </div>
            </div>
            <hero-dialog ref="heroDialog" :left="left" :right="right" @changeBP="changeBP"></hero-dialog>
            <skill-dialog ref="skillDialog" :left="left" :right="right"  @changeBP="changeBP"></skill-dialog>
        </el-main>
        <el-footer height="">
            <el-button type="primary" @click="getBP">刷新</el-button>
        </el-footer>
    </el-container>
</template>

<script>
import heroDialog from '../components/heroDialog';
import skillDialog from '../components/skillDialog';
import { BP_LIST, BP_STATUS } from '../config/url';
import Vue from 'vue';
export default {
    name: 'bp',
    data() {
        return {
            checked: false,
            autoRefresh: false,
            game: this.$route.params.game || 'lol',
            id: this.$route.params.id || 1,
            heroList: '',
            r: false,       // 是否强制覆盖ocr生成的数据
            scene: '',       // bp状态值
            sceneMap: '',   // 状态值和状态对应的map
            leftBan: {},
            rightBan: {},
            leftPick: {},
            rightPick: {},
            left: {
                ban: [],
                banHero: [],
                pick: [],
                pickHero: [],
                skill: []
            },
            right: {
                ban: [],
                banHero: [],
                pick: [],
                pickHero: [],
                skill: []
            }
        };
    },
    components: {
        heroDialog,
        skillDialog
    },
    methods: {
        changeBPStatus() {
            this.changeBP(this.left, this.right);
        },
        getBPStatus() {
            this.axios.get(BP_STATUS).then(res => {
                this.sceneMap = res.data.data[0].children;
            });
        },
        skillImg(id) {
            if (!id) {
                return;
            }
            if (this.game === 'lol') {
                return `http://ossweb-img.qq.com/images/lol/img/spell/${id}.png`;
            } else {
                return `http://game.gtimg.cn/images/yxzj/img201606/summoner/${id}.jpg`;
            }
        },
        getImg(id) {
            if (!id) {
                return;
            }
            if (this.game === 'lol') {
                return `http://ossweb-img.qq.com/images/lol/img/champion/${id}.png`;
            } else {
                return `http://game.gtimg.cn/images/yxzj/img201606/heroimg/${id}/${id}.jpg`;
            }
        },
        getBP() {
            this.axios
                .get(BP_LIST, {
                    params: {
                        id: parseInt(this.id),
                        game: this.game
                    }
                })
                .then(res => {
                    let data = res.data.data;
                    if (data) {
                        this.left = data.left;
                        this.right = data.right;
                        this.scene = data.scene;
                    }
                });
        },
        getHeroInfo() {
            let InfoPromise =
                this.game === 'lol'
                    ? import('../config/champion')
                    : import('../config/heroKogList');
            InfoPromise.then(value => {
                this.heroList = value;
                this.$refs.heroDialog.heroList = value;
                this.$refs.heroDialog.toggleHero();
            });
        },
        refreshBP() {
            if (this.autoRefresh === true) {
                this.refresh = setInterval(this.getBP, 2000);
            } else {
                clearInterval(this.refresh);
            }
        },
        chooseHero(index, type, side) {
            this.$refs.heroDialog.dialogVisible = true;
            this.$refs.heroDialog.index = index;
            this.$refs.heroDialog.type = type;
            this.$refs.heroDialog.side = side;
        },
        chooseSkill(index, side) {
            this.$refs.skillDialog.dialogVisible = true;
            this.$refs.skillDialog.index = index;
            this.$refs.skillDialog.side = side;
        },
        changeBP(left, right) {
            this.left = left;
            this.right = right;
            console.log(this.scene);
            this.axios
                .post(BP_LIST, {
                    id: parseInt(this.id),
                    game: this.game,
                    data: {
                        left,
                        right,
                        scene: this.scene
                    },
                    r: this.r ? 'y' : 'n'
                })
                .then(res => {
                    console.log(res.data);
                });
        }
    },
    watch: {
        autoRefresh: 'refreshBP',
        scene: 'changeBPStatus'
    },
    beforeMount() {
        this.getHeroInfo();
        this.getBPStatus();
    },
    activated() {
        this.getBP();
        // this.autoRefresh = true;
    },
    deactivated() {
        this.autoRefresh = false;
    }
};
</script>

<style scoped lang="less">
.bp {
    padding: 0 60px;
    .el-header {
        line-height: 60px;
    }
    .el-footer {
        text-align: right;
        margin-top: 40px;
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
.bp-wrapper {
    display: flex;
    padding-top: 0;
    justify-content: space-around;
}
.bp-container {
    flex: 0 1 50%;
    vertical-align: top;
    p {
        margin: 30px;
    }
    img {
        margin: 0 10px;
        vertical-align: middle;
        cursor: pointer;
    }
    .pick {
        font-size: 0;
        p {
            font-size: 16px;
        }
        img {
            margin: 0;
            vertical-align: bottom;
        }
    }
    .skill {
        display: inline-block;
        width: 25px;
        line-height: 30px;
        vertical-align: bottom;
    }
}
.left {
    text-align: left;
    .skill {
        margin-right: 10px;
        /*margin-left: -1px;*/
    }
}
.right {
    text-align: right;
    .skill {
        margin-left: 10px;
        /*margin-right: -1px;*/
    }
}
.scene {
    text-align: center;
    span {
        display: inline-block;
        height: 40px;
        line-height: 40px;
        vertical-align: middle;
        font-size: 24px;
        font-weight: bold;
    }
}
</style>
