<template>
	<el-slider v-model="value" />
</template>

<script>
import baseFieldMixin from '../../mixins/baseFieldMixin';

export default {
	name: 'slider-field',
	mixins: [baseFieldMixin]
};
</script>