<template>
	<div class="redactorWrapper">
		<textarea class="redactor" ref="redactor">{{ content }}</textarea>
	</div>
</template>

<script>
	import { mapActions } from 'vuex';

	export default {

		name: 'RichTextField',

		props: ['name'],

		data() {
			return {
				synced: false
			};
		},

		computed: {
			content() {
				return this.$store.getters.getCurrentFieldValue(this.name);
			}
		},

		methods: {
			...mapActions([
				'updateValue'
			]),

			cleanText(txt) {
				this.tempHTML.innerHTML = txt;
				return this.tempHTML.children.length === 1 ?
					this.tempHTML.firstChild.innerHTML : txt;
			}
		},

		watch: {
			// when content is updated outside of redactor we need to keep redactor in sync with our textarea.
			// and only update state again if content is different (this.synced)
			content() {
				if(this.editor) {
					const val = this.editor.redactor('code.get');
					if(this.content !== this.cleanText(val)) {
						this.editor.redactor('code.set', this.content);
						this.synced = true;
					}
				}
			}
		},

		created() {
			this.tempHTML = document.createElement('div');
		},

		mounted() {
			let self = this;

			this.editor = $(this.$refs.redactor);

			this.editor.redactor({
				callbacks: {
					change() {
						if(self.synced) {
							self.synced = false;
						} else {
							self.updateValue({
								name: self.name,
								value: self.cleanText(this.code.get())
							});
						}
					}
				}
			});
		}
	}
</script>