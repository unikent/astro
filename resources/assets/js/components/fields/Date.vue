<template>
	<el-date-picker v-model="value" />
</template>

<script>
import BlockField from 'components/BlockField';

export default {
	name: 'date-field',
	extends: BlockField
};
</script>