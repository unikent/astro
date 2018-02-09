<template>
	<div class="richtext-wrapper">
		<rich-text v-model="value" />
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
	}
};
</script>