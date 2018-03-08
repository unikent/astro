<template>
	<div class="richtext-wrapper">
		<rich-text v-model="value" :options="opts" />
	</div>
</template>

<script>
import BlockField from 'components/BlockField';
import RichText from 'components/richtext';

export default {

	name: 'rich-text-field',

	extends: BlockField,

	components: {
		RichText
	},

	computed: {
		opts() {
			return this.options || {};
		}
	}
};
</script>