<template>
	<div>
		<el-tooltip
			:disabled="!value"
			:content="value"
			placement="top"
		>
			<div class="link-field">
				{{ value || 'No link set' }}
			</div>
		</el-tooltip>
		<el-button
			class="link-field__add-link-button"
			@click="displayLinkPicker"
		>Edit link</el-button>
	</div>
</template>

<script>
import BlockField from 'components/BlockField';

export default {
	name: 'link-field',
	extends: BlockField,

	methods: {
		displayLinkPicker() {
			this.$bus.$emit('add-link-modal:show', {
				callback: ({ value }) => this.updateFieldValue(this.path, value),
				hideTextInputs: true,
				currentValue: this.value
			});
		}
	}
};
</script>