<template>
	<el-input
		v-model="value"
		:placeholder="placeholder"
	></el-input>
</template>

<script>
import BlockField from 'components/BlockField';

export default {
	name: 'text-field',
	extends: BlockField
};
</script>