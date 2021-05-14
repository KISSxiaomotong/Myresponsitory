<template>
    <div>
        <h2>这里是首页</h2>
    </div>
</template>

<script>
    import { hello } from '@/api/index'
    export default {
        name: "Index",
        data(){

        },
        created(){
            hello().then(res => {
                console.log(res.data);
            });
        },
        methods:{

        }
    }
</script>

<style scoped>

</style>
