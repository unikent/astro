<template>
	<el-checkbox-group v-model="value">
		<el-checkbox
				v-for="option in options"
				:label="option.value"
			>
			{{ option.label }}
		</el-checkbox>
	</el-checkbox-group>
</template>

<script>
import BaseFieldMixin from './BaseFieldMixin';

export default {
	name: 'checkbox-field',
	mixins: [BaseFieldMixin]
};
</script>