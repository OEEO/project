<template>
    <el-dialog
            :visible.sync="dialogVisible"
            width="80%">
        <el-input
                placeholder="请输入英雄key或英雄名"
                v-model="hero"
                size="small"
                clearable>
        </el-input>
        <el-radio-group v-model="heroType" size="medium" v-if="game === 'lol'">
            <el-radio :label="1">所有英雄</el-radio>
            <el-radio label="Fighter">战士</el-radio>
            <el-radio label="Mage">法师</el-radio>
            <el-radio label="Assassin">刺客</el-radio>
            <el-radio label="Tank">坦克</el-radio>
            <el-radio label="Marksman">射手</el-radio>
            <el-radio label="Support">辅助</el-radio>
        </el-radio-group>
        <ul>
            <li v-for="item in heroList" :key="item.key" class="hero" v-if="item.visible">
                <img @click="selectHero(item)" width="48" height="48" :src="imgUrl(item)">
                <p @click="selectHero(item)">{{game === 'lol' ? item.name : item.cname}}</p>
            </li>
        </ul>
        <span slot="footer" class="dialog-footer">
            <el-button @click="dialogVisible = false">取 消</el-button>
            <el-button type="primary" @click="dialogVisible = false">确 定</el-button>
        </span>
    </el-dialog>
</template>

<script>
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
                hero: '',
                index: 0,
                type: '',
                side: '',
                heroList: {},
                heroType: 1,
            };
        },
        methods: {
            imgUrl(item) {
                if (this.game === 'lol') {
                    return `http://ossweb-img.qq.com/images/lol/img/champion/${item.image.full}`;
                } else {
                    return `http://game.gtimg.cn/images/yxzj/img201606/heroimg/${item.ename}/${item.ename}.jpg`;
                }
            },
            toggleHero: function () {
                const heroList = this.heroList;
                const heroType = this.heroType;
                const heroName = this.hero;
                for (const key in heroList) {
                    if (heroList.hasOwnProperty(key)) {
                        const hero = heroList[key];
                        const lol = this.game === 'lol';
                        const tags = lol ? hero.tags : '';
                        const name = lol ? hero.name : hero.cname;
                        const id = lol ? hero.id : hero.ename.toString();
                        const heroKey = lol ? hero.key : '';
                        hero.visible = true;
                        if (heroType !== 1 && tags.indexOf(heroType) === -1) {
                            hero.visible = false;
                        }
                        if (heroName !== '') {
                            hero.visible = false;
                            if (name.indexOf(heroName) !== -1 ||
                                id.indexOf(heroName) !== -1 ||
                                heroKey.indexOf(heroName) !== -1) {
                                hero.visible = true;
                            }
                        }
                    }
                }
            },
            selectHero(item) {
                const side = this.side;
                const type = this.type;
                const index = this.index;
                const name = this.game === 'lol' ? item.name : item.cname;
                const id = this.game === 'lol' ? item.id : item.ename;
                this[side][type].length = index + 1;
                this[side][type + 'Hero'].length = index + 1;
                this[side][type].splice(index, 1, id);
                this[side][type + 'Hero'].splice(index, 1, name);
                let left = this.left;
                let right = this.right;
                this.dialogVisible = false;
                this.$emit('changeBP', left, right);
            }
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
