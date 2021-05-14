import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

const routes = [
    {
        path: '/',
        name: 'Index',
        component: () => import( '@/view/Index')
    }
]

const router = new VueRouter({
    routes
})

//前置路由，判断是否登录
router.beforeEach((to, from, next) => {
    // 如果用户访问的是登录页面 直接放行
    if (to.path === '/login') return next();
    // 从localStorage中得到token
    const token = localStorage.getItem('accessToken');
    // 如果没有token值 那么就跳转到'/login
    if (!token) return next('/login');
    // 如果有token则放行
    next();
})

export default router
