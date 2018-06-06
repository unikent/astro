<template>
<div v-if="view === 'Grid'">
	<div v-for="items in resultsInRows" class="columns">
		<div v-for="index in columnCount" class="column">
			<item-thumbnail
				v-if="items[index - 1]"
				:item="items[index - 1]"
				:selected="selected && selected.indexOf(1) !== -1"
				:enable-options="!pickerMode"
				:on-edit="editAction"
				:options="mediaOptions"
			>
				<div v-if="pickerMode" class="media-picker__overlay" @click="pickMedia(items[index - 1])">
					<icon
						name="plus"
						:width="20"
						:height="20"
						viewBox="0 0 20 20"
						style="fill: #fff; z-index: 1;"
					/>
				</div>
			</item-thumbnail>
		</div>
	</div>
</div>
<item-details
	v-else
	:results="results"
	:selected="selected"
	:on-click="pickMedia"
	:on-edit="editAction"
/>
</template>

<script>
import ItemThumbnail from './ItemThumbnail';
import ItemDetails from './ItemDetails';
import Icon from 'components/Icon';
import { mapGetters } from 'vuex';

export default {

	props: {
		columnCount: {
			default: 6
		},
		results: {
			type: Array,
			default: () => []
		},
		view: {
			type: String
		},
		pickerMode: {
			type: Boolean,
			default: false
		},
		allowMultiple: {
			type: Boolean,
			default: false
		},
		pickerAction: {},
		editAction: {}
	},

	components: {
		ItemThumbnail,
		ItemDetails,
		Icon
	},

	data() {
		return {
			selected: []
		};
	},

	computed: {
		...mapGetters([
			'canUser',
		]),

		resultsInRows() {
			let items = [];

			for(var i = 0; i < this.results.length; i += this.columnCount) {
				items.push(this.results.slice(i, i + this.columnCount));
			}

			return items;
		},

		mediaOptions() {
			return this.canUser('image.unlink') ? [
				{
					text: 'Delete',
					action: (item) => this.$store.dispatch('detachMediaFromSite', item),
					divided: true
				}
			] : [];
		}
	},

	methods: {
		pickMedia(item) {
			if(!this.pickerMode) {
				return;
			}

			if(this.allowMultiple) {
				this.selected.push(item.id);
			}
			else {
				this.selected = [item.id];
			}

			if(this.pickerAction) {
				this.pickerAction(item);
			}
		}
	}
};
</script>