<template>
	<el-time-select v-model="value" />
</template>

<script>
import BaseFieldMixin from './BaseFieldMixin';

export default {
	name: 'TimeField',
	mixins: [BaseFieldMixin]
};
</script>