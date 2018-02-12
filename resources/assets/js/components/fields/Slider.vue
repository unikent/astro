<template>
	<el-slider v-model="value" />
</template>

<script>
import BlockField from 'components/BlockField';

export default {
	name: 'slider-field',
	extends: BlockField
};
</script>