<template>
<div v-if="value" class="image-field">
	<item-thumbnail
		:item="item"
		:enable-options="value.filename !== 'placeholder.jpg'"
		:on-edit="showMediaOverlay"
	>
		<div class="image-field__overlay">
			<el-button @click="showPicker">Change image</el-button>
		</div>
	</item-thumbnail>
</div>
<div v-else class="image-field__add-button">
	<el-button @click="showPicker">Add image</el-button>
</div>
</template>

<script>
import { mapMutations, mapState, mapGetters, mapActions } from 'vuex';

import MediaPicker from 'components/MediaPicker';
import ItemThumbnail from 'components/media/ItemThumbnail';

export default {

	name: 'image-field',

	components: {
		MediaPicker,
		ItemThumbnail
	},

	props: [
		'path',
		'accept',
		'multiple',
		'field'
	],

	computed: {
		...mapState({
			currentBlockIndex: state => state.contenteditor.currentBlockIndex,
			currentRegionName: state => state.contenteditor.currentRegionName
		}),

		...mapGetters([
			'currentSectionIndex',
			'getCurrentFieldValue'
		]),

		value: {
			get() {
				return this.getCurrentFieldValue(this.path);
			},
			set(value) {
				this.updateFieldValue({
					name: this.path,
					index: this.currentBlockIndex,
					region: this.currentRegionName,
					section: this.currentSectionIndex,
					value: { ...value, type: 'image' }
				});

				this.updateBlockMedia({
					index: this.currentBlockIndex,
					region: this.currentRegionName,
					section: this.currentSectionIndex,
					value: {
						...value,
						/* eslint-disable camelcase */
						associated_field: this.path
  						/* eslint-enable camelcase */
					}
				});
			}
		},

		item() {
			return { ...this.value, type: 'image' };
		}
	},

	methods: {
		...mapMutations([
			'updateFieldValue',
			'updateBlockMedia',
			'setMediaType',
			'updateMediafieldPath',
			'showMediaPicker',
		]),

		...mapActions([
			'showMediaOverlay'
		]),

		showPicker() {
			this.setMediaType('image');
			this.updateMediafieldPath(this.path);
			this.showMediaPicker();
		}
	}
};
</script>
