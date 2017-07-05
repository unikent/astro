<template>
	<div class="redactorWrapper">
		<rich-text v-model="value" />
	</div>
</template>

<script>
import baseFieldMixin from '../../mixins/baseFieldMixin';
import RichText from 'components/RichText';

/* global document */

export default {

	name: 'rich-text-field',

	mixins: [baseFieldMixin],

	components: {
		RichText
	}
};
</script>