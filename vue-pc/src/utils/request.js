import axios from 'axios'
import qs from "qs"

//响应时间，基础URL，请求头
axios.defaults.timeout = 5000;
axios.defaults.baseURL = process.env.VUE_APP_BASE_API
axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=UTF-8;multipart/form-data';

//添加请求拦截器，在请求头中加token，从localStorage取出token
axios.interceptors.request.use(
    config => {
        if (localStorage.getItem('Cusertoken')) {
            config.headers.Ctoken = localStorage.getItem('Cusertoken');
        }
        return config;
    },
    error => {
        return Promise.reject(error);
    });

// 封装axios的post请求
const post = (url, params) => {
    return new Promise((resolve, reject) => {
        axios
            .post(url, qs.stringify(params))
            .then(response => {
                resolve(response.data);
            })
            .catch(error => {
                reject(error);
            });
    });
};

// 封装axios的get请求
const get = (url, query) => {
    return axios.get(url, query);
};

const $axios = {
    post: post,
    get: get
};

export default $axios;
