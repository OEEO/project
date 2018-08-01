<template>
    <transition :name="transitionName" mode="out-in">
        <keep-alive>
            <router-view></router-view>
        </keep-alive>
    </transition>
</template>

<script>
    export default{
        data() {
            return {
                transitionName: 'fade',
                route: {}
            };
        }
    };
</script>

<style scoped lang="less">

</style>
