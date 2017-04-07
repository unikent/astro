<template>
	<el-input v-model="value"></el-input>
</template>

<script>
import BaseFieldMixin from './BaseFieldMixin';

export default {
	name: 'TextField',
	mixins: [BaseFieldMixin]
};
</script>