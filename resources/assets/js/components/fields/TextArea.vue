<template>
	<el-input type="textarea" v-model="value" :autosize="{ minRows: 2, maxRows: 4}" />
</template>

<script>
import baseFieldMixin from '../../mixins/baseFieldMixin';

export default {
	name: 'textarea-field',
	mixins: [baseFieldMixin]
};
</script>