<template>
	<el-time-select v-model="value" />
</template>

<script>
import BaseFieldMixin from './BaseFieldMixin';

export default {
	name: 'time-field',
	mixins: [BaseFieldMixin]
};
</script>