<template>
	<div v-if="(data && definition && !this.deleted)" class="card mb-1">
		<div class="card-header card-header-primary">
			{{definition[this.data.type].name}}
			<svg class="move" viewBox="0 0 128 128">
				<g fill="#000000">
					<rect height="24" width="24" y="7" x="49"/>
					<rect height="24" width="24" y="52" x="49"/>
					<rect height="24" width="24" y="97" x="49"/>
				</g>
			</svg>
			<i class="kf-close pull-right" @click="toggleDeleted"></i>
			<i class="kf-expand pull-right mx-1" @click="toggleExpanded"></i>

		</div>
		<div class="card-block" v-if="expanded">
			<field v-for="field in definition[this.data.type].fields" :data="data.fields[field.name]" :definition="field" v-on:update="update"></field>
		</div>
	</div>
</template>

<script>
	import Vue from 'vue'
	import Field from './Field.vue'

	export default {
		props: ['data', 'definition'],

		components: {
			Field
		},

		data() {
			return{
				expanded: true,
				deleted: false
			}
		},

		methods: {
			update(text, name) {
				this.data.fields[name] = text
				this.$emit('update', text);
			},

			toggleExpanded() {
				this.expanded = !this.expanded
			},

			toggleDeleted() {
				this.deleted = !this.deleted
			}
		}
	}
</script>

<style>


</style>