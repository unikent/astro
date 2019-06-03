<template>
<div>
	<br>
	<el-input v-model="filter" placeholder="Search blocks"></el-input>
	<div class="block-list columns is-multiline">
		<div
			v-for="(item, key) in filteredOptions"
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
			selected: this.selectedOptions ? this.selectedOptions : [],
			filter: '',
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
		},
		filteredOptions() {
			const filter = this.filter ? this.filter.toLowerCase() : '';
			if(!filter) {
				return this.options;
			}
			const filteredObject = {};
			for(let e in this.options) {
				if (this.options.hasOwnProperty(e)) {
					const item = this.options[e];
					if ((item.info && item.info.toLowerCase().includes(filter)) ||
						(item.label && item.label.toLowerCase().includes(filter)) ||
						(item.name && item.name.toLowerCase().includes(filter))) {
						filteredObject[e] = item;
					}
				}
			}
			return filteredObject;
		},
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
