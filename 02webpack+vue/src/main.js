import Vue from 'vue';
import vueRouter from 'vue-router';
import 'babel-polyfill';

import App from './App.vue';
import routes from './routes';

import axios from 'axios';
import vueAxios from 'vue-axios';

import './ui/element-ui.js';
// import API_ROOT from './config/url';

window.Promise = Promise;

// axios.defaults.baseURL = API_ROOT;

Vue.use(vueRouter);
Vue.use(vueAxios, axios);

// axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
// axios.defaults.transformRequest = (data) => {
//     return qs.stringify(data);
// };

let router = new vueRouter({
    mode: 'hash',
    routes: routes,
    linkActiveClass: 'active'
});

router.afterEach((to, from) => {
    // 添加统计等操作
});

new Vue({
    router,
    // store,
    template: '<App/>',
    components: { App }
}).$mount('#app');
