<template>
	<span></span>
</template>

<script>
import Vue from 'vue';

export default {

	mounted() {
		console.log(this.$el);

		$(this.$el).redactor({
			air: true,
			callbacks: {
				change() {
					console.log(this.code.get());
				}
			}
		});
	}
}
</script>