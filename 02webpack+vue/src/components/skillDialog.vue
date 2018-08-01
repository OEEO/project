<template>
    <el-dialog
            :visible.sync="dialogVisible"
            width="40%">
        <ul>
            <li v-for="item in skillList" :key="item.summoner_id" class="hero">
                <img @click="selectHero(item)" width="48" height="48" :src="imgUrl(item.summoner_id)">
                <p @click="selectHero(item)">{{item.summoner_name}}</p>
            </li>
        </ul>
        <span slot="footer" class="dialog-footer">
            <el-button @click="dialogVisible = false">取 消</el-button>
            <el-button type="primary" @click="dialogVisible = false">确 定</el-button>
        </span>
    </el-dialog>
</template>

<script>
    import { SKILL_LIST } from '../config/url';
    export default {
        name: 'heroDialog',
        props: {
            left: Object,
            right: Object
        },
        data() {
            return {
                dialogVisible: false,
                game: this.$route.params.game || 'lol',
                index: 0,
                side: '',
                skillList: [],
            };
        },
        methods: {
            getSkill() {
                this.axios.get(SKILL_LIST, {
                    params: {
                        game: this.game
                    }
                }).then(res => {
                    let data = res.data;
                    this.skillList = data.data;
                });
            },
            imgUrl(id) {
                if (this.game === 'lol') {
                    return `http://ossweb-img.qq.com/images/lol/img/spell/${id}.png`;
                } else {
                    return `http://game.gtimg.cn/images/yxzj/img201606/summoner/${id}.jpg`;
                }
            },
            selectHero(item) {
                const side = this.side;
                const index = this.index;
                const id = item.summoner_id;
                this[side].skill.length = index + 1;
                this[side].skill.splice(index, 1, id);
                let left = this.left;
                let right = this.right;
                this.dialogVisible = false;
                this.$emit('changeBP', left, right);
            }
        },
        beforeMount() {
            this.getSkill();
        },
        watch: {
            'heroType': 'toggleHero',
            'hero': 'toggleHero'
        }
    };
</script>

<style scoped lang="less">
    .el-input {
        width: 200px;
    }
    .el-radio+.el-radio {
        margin-left: 16px;
    }
    ul {
        padding-left: 0;
    }
    .hero {
        display: inline-block;
        width: 100px;
        height: 88px;
        text-align: center;
        img {
            display: inline-block;
            background-color: #EBEEF5;
            width: 48px;
            height: 48px;
            cursor: pointer;
        }
        p {
            cursor: pointer;
        }
    }
</style>
