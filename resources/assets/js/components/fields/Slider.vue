<template>
	<el-slider v-model="value" />
</template>

<script>
import BaseFieldMixin from './BaseFieldMixin';

export default {
	name: 'slider-field',
	mixins: [BaseFieldMixin]
};
</script>