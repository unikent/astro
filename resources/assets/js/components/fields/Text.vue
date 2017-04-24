<template>
	<el-input v-model="value"></el-input>
</template>

<script>
import BaseFieldMixin from './BaseFieldMixin';

export default {
	name: 'text-field',
	mixins: [BaseFieldMixin]
};
</script>