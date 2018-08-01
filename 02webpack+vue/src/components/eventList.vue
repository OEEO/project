<template>
    <el-container>
        <el-main class="event-list">
            <event
		            class="event"
		            v-for="(event, index) in eventList"
		            :event="event"
		            :eventOptions="eventOptions"
		            :memberOptions="memberOptions"
		            ref="event"
		            :key="index"></event>
        </el-main>
        <el-footer class="event-footer">
            <el-button @click="addEvent">添加事件</el-button>
            <el-button @click="setLinePoints">设置拐点</el-button>
        </el-footer>
    </el-container>
</template>

<script>
    import event from './event';
    import { SET_LINE, EVENT_LIST, PLAYER_INFO } from '../config/url';

    export default {
        name: 'eventList',
        components: {
            event
        },
        props: {
            eventList: Array,
            point: Object
        },
        data() {
            return {
                checked: false,
                game: this.$route.params.game || 'lol',
                id: this.$route.params.id || 1,
                newEventList: [],
                eventOptions: [],
                memberOptions: [],
            };
        },
        methods: {
            addEvent() {
                this.$emit('addEvent');
            },
            setLinePoints() {
                let eventDOMs = this.$refs.event;
                this.newEventList = [];
                eventDOMs.forEach(value => {
                    if (value.selected) {
                        this.newEventList.push(value.newEvent);
                    }
                });
                this.axios.post(SET_LINE, {
                    game: this.game,
                    id: this.id,
                    time: this.point.coord[0],
                    data: this.newEventList
                }).then(res => {
                    console.log(res.data);
                });
            },
            initEventOptions() {
                this.axios.get(EVENT_LIST).then(res => {
                    const datas = res.data.data;
                    datas.forEach(data => {
                        if (data.value === this.game) {
                            this.eventOptions = data.children;
                        }
                    });
                });
            },
            initMemberOptions() {
                this.axios.get(PLAYER_INFO, {
                    params: {
                        game: this.game,
                        id: this.id
                    }
                }).then(res => {
                    let data = res.data.data;
                    if (data) {
                        let leftPlayers = data.left.players;
                        let rightPlayers = data.right.players;
                        this.memberOptions = [...leftPlayers, ...rightPlayers];
                    }
                });
            },
        },
        beforeMount() {
            this.initEventOptions();
            this.initMemberOptions();
        }
    };
</script>

<style scoped lang="less">
    .nav {
        height: 30px;
    }

    .event {
	    flex: 0 0 360px;
        box-sizing: border-box;
        height: 360px;
        padding: 30px;
        border: 1px solid #DCDFE6;
	    border-radius: 5px;
        background-color: #fff;
	    margin: 0 20px 0 0;
    }

    .event-list {
        display: flex;
	    flex-wrap: nowrap;
	    overflow: hidden;
	    overflow-x: auto;
	    height: 400px;
	    padding: 0;
    }

    .event-footer {
        text-align: right;
        padding-top: 20px;
    }
</style>
