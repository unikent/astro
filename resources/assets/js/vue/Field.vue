<template>
	<div>
		<h3>{{definition.label}}</h3>
		<redactor class="pb-1" v-if="isRichField" :content="data" v-on:update="update"></redactor>
		<div v-if="isPlainField">
				<input v-model="contents" @input="updateText" class="form-control mb-1">
		</div>
	</div>
</template>

<script>
	import Redactor from './Redactor.vue'
	import VueResource from 'vue';

	export default {
		props: ['data', 'definition'],

		components: {
			Redactor
		},

		data() {
			return {
				contents: this.data
			}
		},

		computed: {
			isRichField(block) {
				return this.definition.type == "rich"
			},

			isPlainField(block) {
				return this.definition.type == "text"
			}
		},

		methods: {
			update(text) {
				this.contents = text
				this.$emit('update', text, this.definition.name);
			},

			updateText() {
				this.$emit('update', this.contents, this.definition.name);
			}
		}

	}


</script>

<style>


</style>