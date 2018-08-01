<template>
    <div class="com-instance-nav">
        <div class="nav" v-show="showNav">
            <nav class="app-nav">
                <router-link
                        class="com-instance-nav-item"
                        to="/home">回首页
                </router-link>
                <router-link
                        class="com-instance-nav-item"
                        :to="{name: 'seat-selection', params:{game:game, id:id, origin:origin}}">选座
                </router-link>
                <router-link
                        class="com-instance-nav-item"
                        :to="{name: 'bp', params:{game:game, id:id, origin:origin}}">BP
                </router-link>
                <router-link
                        class="com-instance-nav-item"
                        :to="{name: 'gold-chart', params:{game:game, id:id, origin:origin}}">经济曲线
                </router-link>
                <router-link
                        class="com-instance-nav-item"
                        :to="{name: 'dragon', params:{game:game, id:id, origin:origin}}">大小龙
                </router-link>
                <router-link
                        class="com-instance-nav-item"
                        :to="{name: 'process', params:{game:game, id:id, origin:origin}}">进程
                </router-link>
            </nav>
        </div>
        <div class="btn-wrap" @click="showNav = !showNav">
            <el-button type="primary"
                       class="show-btn"
                       :icon="showNav ? 'el-icon-d-arrow-left' : 'el-icon-d-arrow-right'">
            </el-button>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'com-instance-nav',
        data() {
            return {
                showNav: false,
            };
        },

        computed: {
            id() {
                return this.$route.params.id;
            },
            game() {
                return this.$route.params.game;
            },
            origin() {
                return this.$route.params.origin;
            }
        },

        updated() {
            console.log(this.id);
        }
    };
</script>

<style lang="less" scoped>
    .com-instance-nav {
        height: 100%;
        position: absolute;
        left: 0;
        top: 0;
        display: flex;
        align-items: center;
        .nav {
            width: 75px;
            height: 100%;
            background-color: #aaa;
            border: 1px solid #aaa;
        }
        .com-instance-nav-item {
            display: inline-block;
            text-decoration: none;
            color: black;
            width: 75px;
            height: 75px;
            line-height: 75px;
            background-color: #fff;
            text-align: center;
            box-sizing: border-box;
            border-bottom: 1px solid #aaa;
            transition: 0.3s all ease-in-out;
            &:hover {
                background-color: #4897fa;
                color: #fff;
            }
        }
        .btn-wrap {
            cursor: pointer;
            height: 20px;
            width: 20px;
            font-size: 50px;
            color: mediumspringgreen;
            .show-btn {
                padding: 5px;
                height: 50px;
            }
        }
    }
</style>
