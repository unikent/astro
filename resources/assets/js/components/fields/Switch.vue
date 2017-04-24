<template>
	<el-switch on-text="" off-text="" v-model="value"></el-switch>
</template>

<script>
import BaseFieldMixin from './BaseFieldMixin';

export default {
	name: 'switch-field',
	mixins: [BaseFieldMixin]
};
</script>