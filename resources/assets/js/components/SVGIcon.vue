<template>
    <span style="font-size:0"
            v-html="require(`@theme/assets/img/svg/${icon}.svg`)"
    ></span>
</template>
<script>
    export default {
        name: 'svg-icon',
        props: ['icon']
    }
</script>