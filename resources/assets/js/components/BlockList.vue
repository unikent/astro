<template>
<div>
	<div class="block-list columns is-multiline">
		<div
			v-for="(block, key) in blocks"
			v-if="block && isAllowed(key)"
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
		},
		'blocks': {
			required: true
		},
		'allowedBlocks': {
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
		labels() {
			return this.selected.map(name => this.blocks[name].label);
		}
	},

	methods: {
		isAllowed(key) {
			return key &&
				(
					this.allowedBlocks.indexOf(key) !== -1 ||
					this.allowedBlocks.indexOf(key.replace(/-v[0-9]+$/, '')) !== -1
				);
		},
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
