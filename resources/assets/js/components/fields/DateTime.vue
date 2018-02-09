<template>
	<el-date-picker type="datetime" v-model="value" />
</template>

<script>
import BlockField from 'components/BlockField';

export default {
	name: 'date-time-field',
	extends: BlockField
};
</script>