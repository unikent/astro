<template>
	<el-time-select v-model="value" />
</template>

<script>
import BlockField from 'components/BlockField';

export default {
	name: 'time-field',
	extends: BlockField
};
</script>