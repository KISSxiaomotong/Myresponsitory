import $axios from '@/utils/request'

export function hello() {
    return $axios.post('/ss',{'s':2333})
}
