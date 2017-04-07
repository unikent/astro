<template>
	<div>
		<el-radio-group size="small">
			<el-radio
				v-for="option in options"
				:label="option.value"
			>
				{{ option.label }}
			</el-radio>
		</el-radio-group>
	</div>
</template>

<script>
import BaseFieldMixin from './BaseFieldMixin';

export default {
	name: 'RadioField',
	mixins: [BaseFieldMixin]
};
</script>