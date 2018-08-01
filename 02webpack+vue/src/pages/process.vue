<template>
    <el-container class="process">
        <el-header>
            <span class="title">进程</span>
        </el-header>
        <el-main>
            <div class="controller">
                备份：
                <el-switch
                        v-model="backup"
                        active-color="#13ce66"
                        inactive-color="#ff4949">
                </el-switch><br/><br/>
                <el-button type="primary" @click="syncProcess">同步</el-button>
            </div>
            <div class="panel" v-if="message.length > 0">
                <p v-for="item in message">{{item.data}}</p>
            </div>
        </el-main>
    </el-container>
</template>

<script>
    import { OPEN_BACKUP, STOP_BACKUP, SYNC_PROCESS, BACKUP_STATUS } from '../config/url';
    export default {
        name: 'process',
        data() {
            return {
                backup: false,
                message: []
            };
        },
        methods: {
            getBackupStatus() {
                this.axios.get(BACKUP_STATUS).then(res => {
                    if (res.data.status === 'success') {
                        this.backup = true;
                    } else {
                        this.backup = false;
                    }
                });
            },
            openBackup() {
                this.axios.get(OPEN_BACKUP).then(res => {
                    console.log(res.data);
                });
            },
            stopBackup() {
                this.axios.get(STOP_BACKUP).then(res => {
                    console.log(res.data);
                });
            },
            syncProcess() {
                this.message = [];
                let source = new EventSource(SYNC_PROCESS);
                source.addEventListener('message', event => {
                    this.message.push(event);
                    if (event.data === 'end') {
                        source.close();
                    }
                });
            }
        },
        watch: {
            backup() {
                if (this.backup) this.openBackup();
                if (!this.backup) this.stopBackup();
            }
        },
        beforeMount() {
            this.getBackupStatus();
        }
    };
</script>

<style scoped lang="less">
    .process {
        padding: 0 60px;
        .el-header {
            line-height: 60px;
        }
        .el-button {
            width: 100px;
        }
    }
    .el-main {
        display: flex;
        flex-direction: row;
        .controller {
            flex: 1;
        }
        .panel {
            flex: 4;
            background-color: #fff;
        }
    }
    .title {
        display: inline-block;
        font-size: 36px;
        font-weight: bold;
        margin-right: 40px;
    }
    .backup {
        margin: 40px 0;
    }
</style>
