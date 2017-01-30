<template>
	<div class="redactorWrapper">
		<textarea class="redactor" ref="redactor">{{localContent}}</textarea>
	</div>
</template>

<script>
	import Vue from 'vue'

	export default {
		props: ['content'],

		data() {
			return {
				localContent: this.content
			};
		},

		mounted() {
			var that = this;
			$(this.$refs.redactor).redactor({
				callbacks: {
					change() {
						that.localContent = this.code.get();
						that.$emit('update', that.localContent);
					}
				}
			});

		}
	}
</script>