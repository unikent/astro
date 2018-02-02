<template>
	<el-date-picker type="datetime" v-model="value" />
</template>

<script>
import baseFieldMixin from '../../mixins/baseFieldMixin';

export default {
	name: 'date-time-field',
	mixins: [baseFieldMixin]
};
</script>