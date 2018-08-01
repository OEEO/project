<template>
    <div class="add-instance-form">
        <h1>新建实例</h1>
        <el-form>
            <el-form-item label="比赛" :label-width="formLabelWidth">
                <el-select v-model="addFormData.game.value" placeholder="请选择比赛">
                    <el-option
                            :label="item"
                            :value="item"
                            v-for="item in addFormData.game.labels"
                            :key="item">
                    </el-option>
                </el-select>
            </el-form-item>

            <el-form-item label="比赛id" :label-width="formLabelWidth">
                <el-input v-model="addFormData.id"></el-input>
            </el-form-item>

            <el-form-item label="左队" :label-width="formLabelWidth">
                <el-select
                        filterable
                        allow-create
                        v-model="addFormData.leftTeamId"
                        placeholder="请选择左队">
                    <el-option
                            :label="item.label"
                            :value="item.value"
                            v-for="item in addFormData.teamName"
                            :key="item">
                    </el-option>
                </el-select>
            </el-form-item>

            <el-form-item label="右队" :label-width="formLabelWidth">
                <el-select
                        filterable
                        allow-create
                        v-model="addFormData.rightTeamId"
                        placeholder="请选择右队">
                    <el-option
                            :label="item.label"
                            :value="item.value"
                            v-for="item in addFormData.teamName"
                            :key="item">
                    </el-option>
                </el-select>
            </el-form-item>

            <el-form-item label="直播流" :label-width="formLabelWidth">
                <el-select
                        filterable
                        allow-create
                        v-model="addFormData.liveStreaming.value"
                        placeholder="请选择直播流">
                    <el-option
                            :label="item"
                            :value="item"
                            v-for="item in addFormData.liveStreaming.labels"
                            :key="item">
                    </el-option>
                </el-select>
            </el-form-item>

            <el-form-item label="当前局数" :label-width="formLabelWidth">
                <el-input v-model="addFormData.index"></el-input>
            </el-form-item>

            <el-form-item>
                <el-button type="success"
                           @click="addInstance">添加</el-button>
                <el-button type="primary"
                           @click="closeTheModal">关闭</el-button>
            </el-form-item>
        </el-form>
    </div>
</template>

<script>
    export default {
        name: 'add-instance-form',
        props: ['addFormData'],
        data() {
            return {
                formLabelWidth: '80px',
            };
        },

        computed: {

        },

        methods: {
            closeTheModal() {
                this.$emit('closeTheModal');
            },
            addInstance() {
                this.$emit('addInstance');

            }
        }
    };
</script>

<style lang="less" scoped>
    .add-instance-form {
        padding-left: 30px;
        .el-select,
        .el-input{
            width: 360px;
        }
    }
</style>