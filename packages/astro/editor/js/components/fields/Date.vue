<template>
	<el-date-picker v-model="value" />
</template>

<script>
import baseFieldMixin from '../../mixins/baseFieldMixin';

export default {
	name: 'date-field',
	mixins: [baseFieldMixin]
};
</script>