<template>
	<el-cascader :options="options" v-model="value" />
</template>

<script>
import baseFieldMixin from '../../mixins/baseFieldMixin';

export default {
	name: 'nested-options-field',
	mixins: [baseFieldMixin]
};
</script>