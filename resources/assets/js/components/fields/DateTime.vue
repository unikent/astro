<template>
	<el-date-picker type="datetime" v-model="value" />
</template>

<script>
import BaseFieldMixin from './BaseFieldMixin';

export default {
	name: 'DateTimeField',
	mixins: [BaseFieldMixin]
};
</script>