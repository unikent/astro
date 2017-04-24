<template>
	<el-date-picker v-model="value" />
</template>

<script>
import BaseFieldMixin from './BaseFieldMixin';

export default {
	name: 'date-field',
	mixins: [BaseFieldMixin]
};
</script>