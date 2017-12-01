<template>
<div>
	<div class="block-list columns is-multiline">
		<div
			v-for="(item, key) in options"
			class="column is-one-quarter"
		>
			<div
				class="block-move"
				:class="[{ 'block-move--selected': selected.indexOf(key) !== -1 }]"
				@mousedown="handleMousedown(key)"
			>
				<h3>{{ item.label }}</h3>
				<p>{{ item.info }}</p>
			</div>
		</div>
	</div>
</div>
</template>

<script>
export default {

	props: {

		/**
		 * The items which the user can select from.
		 * { item_id => { item-definition }, ... }
		 */
		'options': {
			required: true,
			type: Object
		},
		/**
		 * The items which are selected by default.
		 * An array of strings
		 */
		'selectedOptions': {
			required: false,
			type: Array
		},
		/*
		 * The maximum number of items that can be selected by this picker.
		 */
		'maxSelectableOptions': {
			required: false,
			type: Number,
			default: null
		}
	},

	data() {
		return {
			selected: this.selectedOptions ? this.selectedOptions : []
		};
	},

	watch: {
		selectedOptions(val) {
			if(val.length === 0) {
				this.selected = val;
			}
		}
	},

	computed: {
		labels() {
			return this.selected.map(name => this.options[name].label);
		}
	},

	methods: {
		handleMousedown(name) {
			const current = this.selected.indexOf(name);

			if(current === -1) {
				if (this.maxSelectableOptions !== null && this.selected.length >= this.maxSelectableOptions) {
					this.$message({
						type: 'warning',
						message: 'Sorry you cannot select any more items.'
					});
				}
				else {
					this.selected.push(name);
				}
			}
			else {
				this.selected.splice(current, 1);
			}
		}
	}
};
</script>
