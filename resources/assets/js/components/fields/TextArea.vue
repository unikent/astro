<template>
	<el-input type="textarea" v-model="value" :autosize="{ minRows: 2, maxRows: 4}" />
</template>

<script>
import BaseFieldMixin from './BaseFieldMixin';

export default {
	name: 'textarea-field',
	mixins: [BaseFieldMixin]
};
</script>