<template>
	<el-cascader :options="options" v-model="value" />
</template>

<script>
import BaseFieldMixin from './BaseFieldMixin';

export default {
	name: 'nested-options-field',
	mixins: [BaseFieldMixin]
};
</script>