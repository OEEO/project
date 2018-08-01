<template>
    <div class="index-page">
        <com-header></com-header>
        <div class="main">
            <router-view></router-view>
            <!--共同菜单-->
            <com-instance-nav v-show="leftInstanceNavShow"></com-instance-nav>
        </div>
    </div>
</template>

<script>
    import comHeader from '../components/com-header.vue';
    import comInstanceNav from '../components/com-instance-nav.vue';
    export default {
        name: 'index',
        components: {comHeader, comInstanceNav},

        data() {
            return {

            };
        },

        computed: {
            leftInstanceNavShow() {
                let href = this.$route.path;
                let notHome = href.indexOf('/home') === -1;
                let notInstance = href.indexOf('/instance') === -1;
                return notHome && notInstance;
            }
        },
    };
</script>

<style lang="less" scoped>
    @import "../less/variable";
    .index-page {
        .main {
            position: relative;
            width: 100%;
            min-height: @mainHeight;
            background-color: #f2f2f2;
        }
    }
</style>