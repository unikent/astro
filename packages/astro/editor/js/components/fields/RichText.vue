<template>
	<div class="richtext-wrapper">
		<rich-text v-model="value" />
	</div>
</template>

<script>
import baseFieldMixin from '../../mixins/baseFieldMixin';
import RichText from 'components/richtext';

export default {

	name: 'rich-text-field',

	mixins: [baseFieldMixin],

	components: {
		RichText
	}
};
</script>