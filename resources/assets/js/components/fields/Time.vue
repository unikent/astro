<template>
	<el-time-select v-model="value" />
</template>

<script>
import baseFieldMixin from '../../mixins/baseFieldMixin';

export default {
	name: 'time-field',
	mixins: [baseFieldMixin]
};
</script>