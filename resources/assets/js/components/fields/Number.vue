<template>
	<el-input-number size="small" v-model="value" />
</template>

<script>
import BlockField from 'components/BlockField';

export default {
	name: 'number-field',
	extends: BlockField
};
</script>