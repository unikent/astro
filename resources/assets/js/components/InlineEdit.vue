<template>
	<span></span>
</template>

<script>
/* global $ */

export default {
	mounted() {
		$(this.$el).redactor({
			air: true,
			callbacks: {
				change() {
					console.log(this.code.get());
				}
			}
		});
	}
};
</script>