<template>
	<el-form ref="form" :model="event" label-width="40px" size="mini" class="event-wrapper">
		<el-form-item label="事件">
			<el-select v-model="eventName" placeholder="请选择事件">
				<el-option
						v-for="item in eventOptions"
						:key="item.value"
						:label="item.label"
						:value="item.value">
				</el-option>
			</el-select>
			<el-checkbox class="selected" v-model="selected">添加</el-checkbox>
		</el-form-item>
		<div class="member-wrapper">
			<el-form-item v-for="(member, index) in members" :label="`p${index + 1}`" :key="index">
				<el-select v-model="members[index]" placeholder="请选择成员">
					<el-option
							v-for="(item, index) in memberOptions"
							:key="index"
							:label="item.heroName"
							:value="index">
					</el-option>
				</el-select>
				<el-button size="mini" icon="el-icon-delete" circle @click="deleteMember(index)"></el-button>
			</el-form-item>
		</div>
		<el-button class="add-member" size="mini" icon="el-icon-circle-plus-outline" @click="addMember">添加成员</el-button>
	</el-form>
</template>

<script>
    export default {
        name: 'event',
        props: {
            eventOptions: Array,
            memberOptions: Array,
            event: Object,
        },
        data() {
            return {
                eventName: '',
                members: [],
                selected: false,
                newEvent: this.event
            };
        },
        methods: {
            init() {
                this.eventName = this.event.type;
                for (let key in this.event) {
                    if (this.event.hasOwnProperty(key)) {
                        let reg = /^p(\d*)$/;
                        if (reg.test(key)) {
                            this.members[key.replace(reg, '$1') - 1] = this.event[key];
                        }
                    }
                }
            },
            deleteMember(i) {
                this.members.splice(i, 1);
            },
            addMember() {
                this.members.push(0);
            },
            changeEventName() {
                this.newEvent.type = this.eventName;
            },
            changeEventMember() {
                this.members.forEach((value, index) => {
                    this.newEvent[`p${index + 1}`] = parseInt(value);
                });
            }
        },
        watch: {
            eventName: 'changeEventName',
            members: 'changeEventMember'
        },
        beforeMount() {
            this.init();
        }
    };
</script>

<style scoped lang="less">
	.add-member {
		float: right;
		margin-top: 10px;
	}
	.selected {
		/*transform: scale(1.5, 1.5);*/
		float: right;
	}
	.member-wrapper {
		height: 220px;
		overflow: auto;
		border-top: 1px solid #E4E7ED;
		border-bottom: 1px solid #E4E7ED;
		padding-top: 10px;
		margin-top: -10px;
	}
</style>
