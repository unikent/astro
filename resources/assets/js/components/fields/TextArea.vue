<template>
	<el-input type="textarea" v-model="value" :autosize="{ minRows: 3, maxRows: 5}" />
</template>

<script>
import BlockField from 'components/BlockField';

export default {
	name: 'textarea-field',
	extends: BlockField
};
</script>