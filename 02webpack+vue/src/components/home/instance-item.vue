<template>
    <div class="instance-item" :class="{redShadow: isRedShadow}">
        <div class="title">{{ title }}</div>
        <div class="desc">status: {{ status }}</div>
        <div class="btns">
            <el-button type="success" @click="goToInstance">进入</el-button>
            <el-button type="danger" @click="deleteInstance">删除</el-button>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'instance-item',
        props: ['title', 'status'],
        data() {
            return {

            };
        },

        computed: {
            isRedShadow() {
                return this.status !== 'running' && this.status !== 'idel';
            }
        },

        methods: {
            deleteInstance() {
                this.$emit('deleteInstance');
            },
            goToInstance() {
                this.$emit('goToInstance');
            }
        }
    };
</script>

<style lang="less" scoped>
    @import "../../less/variable";
    @instanceItemWidth: 200px;
    .instance-item {
        position: relative;
        width: @instanceItemWidth;
        height: @instanceItemWidth;
        margin-bottom: 30px;
        background-color: #eee;
        box-shadow: @greenShadow;
        margin-right: 60px;
    }
    .title {
        height: 50px;
        line-height: 50px;
        padding-left: 10px;
        font-size: 20px;
    }
    .desc {
        padding-left: 10px;
    }
    .btns {
        box-sizing: border-box;
        padding: 0 10px;
        width: 100%;
        position: absolute;
        bottom: 20px;
        text-align: center;
        display: flex;
        justify-content: space-between;
    }
    .redShadow {
        box-shadow: @redShadow;
    }
</style>