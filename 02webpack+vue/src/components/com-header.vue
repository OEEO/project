<template>
    <div class="common-header">
        <router-link to="/home">
            OCR管理后台
        </router-link>

        <el-switch
                style="display: inline-block"
                v-model="isLocal"
                class="origin-box"
                @change="changeOrigin"
                active-color="#13ce66"
                inactive-color="#ff4949"
                active-text="本地"
                inactive-text="远端">
        </el-switch>
    </div>
</template>

<script>
    import utils from '../utils';
    export default {
        name: 'header',
        data() {
            return {
                isLocal: utils.getQueryString('r') === 'local', //是否本地
            };
        },

        methods: {
            // 切换近端、远端
            changeOrigin() {
                if (this.isLocal) {
                    window.location.href = window.location.href.replace('r=root', 'r=local');
                }  else {
                    window.location.href = window.location.href.replace('r=local', 'r=root');
                }
            }
        },
    };
</script>

<style lang="less" scoped>
    @import "../less/variable";
    .common-header{
        height: @headerHeight;
        line-height: @headerHeight;
        font-size: 32px;
        font-weight: 700;
        padding-left: 30px;
        background-color: #d6d6d6;
        a {
            text-decoration: none;
            color: #333;
            transition: 0.3s all ease-in-out;
            &:hover {
                color: #67abfb;
            }
        }
        .origin-box{
            margin-left: 100px;
        }
    }
</style>