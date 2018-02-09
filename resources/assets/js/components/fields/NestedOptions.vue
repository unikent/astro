<template>
	<el-cascader :options="options" v-model="value" />
</template>

<script>
import BlockField from 'components/BlockField';

export default {
	name: 'nested-options-field',
	extends: BlockField
};
</script>