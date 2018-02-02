<template>
	<el-input type="textarea" v-model="value" :autosize="{ minRows: 3, maxRows: 5}" />
</template>

<script>
import baseFieldMixin from '../../mixins/baseFieldMixin';

export default {
	name: 'textarea-field',
	mixins: [baseFieldMixin]
};
</script>