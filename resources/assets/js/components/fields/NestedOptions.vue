<template>
	<el-cascader :options="options" v-model="value" />
</template>

<script>
import BaseFieldMixin from './BaseFieldMixin';

export default {
	name: 'NestedOptionsField',
	mixins: [BaseFieldMixin]
};
</script>