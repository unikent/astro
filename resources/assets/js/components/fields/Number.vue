<template>
	<el-input-number size="small" v-model="value" />
</template>

<script>
import BaseFieldMixin from './BaseFieldMixin';

export default {
	name: 'number-field',
	mixins: [BaseFieldMixin]
};
</script>