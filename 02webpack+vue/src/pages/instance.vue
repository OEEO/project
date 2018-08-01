<template>
    <div class="instance-page">
        <router-link
                class="instance-item"
                :to="{name: 'seat-selection', params:{game:game, id:id}}">选座
        </router-link>
        <router-link
                class="instance-item"
                :to="{name: 'bp', params:{game:game, id:id}}">BP
        </router-link>
        <router-link
                class="instance-item"
                :to="{name: 'gold-chart', params:{game:game, id:id}}">事件经济预览
        </router-link>
        <router-link
                class="instance-item"
                :to="{name: 'dragon', params:{game:game, id:id}}">大小龙
        </router-link>
    </div>
</template>

<script>
    export default {
        name: 'instance',
        data() {
            return {

            };
        },

        computed: {
            id() {
                return this.$route.params.id;
            },
            game() {
                return this.$route.params.game;
            }
        },

        methods: {

        }

    };
</script>

<style lang="less" scoped>
    @import "../less/variable";
    .instance-page {
        height: @mainHeight;
        display: flex;
        justify-content: space-around;
        align-items: center;
    }
    .instance-item {
        cursor: pointer;
        height: 250px;
        width: 220px;
        background-color: #aaa;
        color: #fff;
        text-decoration: none;
        line-height: 250px;
        font-size: 32px;
        font-weight: 700;
        text-align: center;
        box-shadow: @boxShadow;
        &:nth-child(1) {
            background-color: #2da5ca;
        }
        &:nth-child(2) {
            background-color: #cae384;
        }
        &:nth-child(3) {
            background-color: #fdb248;
        }
        &:nth-child(4) {
            background-color: #f6599e;
        }
    }
</style>