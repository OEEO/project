module.exports = [
    {
        path: '/',
        name: 'index',
        component: r => require(['../pages/index.vue'], r),
        children: [
            {
                path: '',
                redirect: 'home'
            },
            {
                path: 'home',
                name: 'home',
                component: r => require(['../pages/home.vue'], r),
            },
            {
                path: 'instance/:game?/:id?',
                name: 'instance',
                component: r => require(['../pages/instance.vue'], r)
            },
            {
                path: 'seat-selection/:game?/:id?',
                name: 'seat-selection',
                component: r => require(['../pages/seat-selection.vue'], r)
            },
            {
                path: 'bp/:game?/:id?',
                name: 'bp',
                component: r => require(['../pages/bp.vue'], r)
            },
            {
                path: 'gold-chart/:game?/:id?',
                name: 'gold-chart',
                component: r => require(['../pages/gold-chart.vue'], r)
            },
            {
                path: 'dragon/:game?/:id?',
                name: 'dragon',
                component: r => require(['../pages/dragon.vue'], r)
            },
            {
                path: 'process/:game?/:id?',
                name: 'process',
                component: r => require(['../pages/process.vue'], r)
            }
        ]
    }
];
