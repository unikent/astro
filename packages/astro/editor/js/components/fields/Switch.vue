<template>
	<el-switch on-text="" off-text="" v-model="value"></el-switch>
</template>

<script>
import baseFieldMixin from '../../mixins/baseFieldMixin';

export default {
	name: 'switch-field',
	mixins: [baseFieldMixin]
};
</script>