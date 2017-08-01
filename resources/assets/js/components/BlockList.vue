<template>
<div>
	<div class="block-list columns is-multiline">
		<div
			v-for="(block, key) in blocks"
			v-if="block"
			class="column is-one-quarter"
		>
			<div
				class="block-move"
				:class="[{ 'block-move--selected': selected.indexOf(key) !== -1 }]"
				@mousedown="handleMousedown(key)"
			>
				<h3>{{ block.label }}</h3>
				<p>{{ block.info }}</p>
			</div>
		</div>
	</div>
</div>
</template>

<script>
export default {

	props: {
		'selectedBlocks': {
			required: true
		}
	},

	data() {
		return {
			selected: this.selectedBlocks
		};
	},

	watch: {
		selectedBlocks(val) {
			if(val.length === 0) {
				this.selected = val;
			}
		}
	},

	computed: {
		blocks() {
			return this.$store.state.definition.blockDefinitions;
		},

		labels() {
			return this.selected.map(name => this.blocks[name].label);
		}
	},

	methods: {
		handleMousedown(name) {
			const current = this.selected.indexOf(name);

			if(current === -1) {
				this.selected.push(name);
			}
			else {
				this.selected.splice(current, 1);
			}
		}
	}
};
</script>
