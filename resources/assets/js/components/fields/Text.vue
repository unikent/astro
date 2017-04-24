<template>
	<el-input v-model="value"></el-input>
</template>

<script>
import baseFieldMixin from '../../mixins/baseFieldMixin';

export default {
	name: 'text-field',
	mixins: [baseFieldMixin]
};
</script>