<template>
	<el-switch on-text="" off-text="" v-model="value"></el-switch>
</template>

<script>
import BlockField from 'components/BlockField';

export default {
	name: 'switch-field',
	extends: BlockField
};
</script>