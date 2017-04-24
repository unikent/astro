<template>
	<el-input-number size="small" v-model="value" />
</template>

<script>
import baseFieldMixin from '../../mixins/baseFieldMixin';

export default {
	name: 'number-field',
	mixins: [baseFieldMixin]
};
</script>