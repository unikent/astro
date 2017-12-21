<template>
	<div>
		<el-radio-group v-model="value" size="small">
			<el-radio
				v-for="option in options"
				:label="option.value"
				:key="option.value"
			>
				{{ option.label }}
			</el-radio>
		</el-radio-group>
	</div>
</template>

<script>
import baseFieldMixin from '../../mixins/baseFieldMixin';

export default {
	name: 'radio-field',
	mixins: [baseFieldMixin]
};
</script>