<template>
<el-dialog
	title="Add media"
	:visible.sync="visible"
	class="tabbed-dialog"
>
	<el-tabs type="border-card">
		<el-tab-pane v-if="canUser('image.add')" label="Upload media">
			<media-upload
				:multiple="false"
				:allow-types="false"
				:type="mediaPicker.mediaType"
				:on-success="mediaFromResponse"
			/>
		</el-tab-pane>
		<el-tab-pane label="Media manager">
			<div class="o-media__switch">
				<el-radio-group v-model="view" size="small">
					<el-radio-button label="Grid"></el-radio-button>
					<el-radio-button label="Details"></el-radio-button>
				</el-radio-group>
			</div>
			<paged-results
				:results="media"
				:view="view"
				picker-mode
				:pickerAction="setFieldMedia"
				:counts="[12, 20, 30]"
				:filter="(items) => items.filter(item => item.type === mediaPicker.mediaType)"
			/>
		</el-tab-pane>
		<!-- <el-tab-pane label="URL">
			<el-input placeholder="http://" />
			<span class="media-picker__form-caution">Please be mindful of copyright issues and attribution when linking to external media (this copies the media to our servers).</span>
		</el-tab-pane> -->
	</el-tabs>
	<span slot="footer" class="dialog-footer">
		<el-button @click="visible = false">Cancel</el-button>
	</span>
</el-dialog>
</template>

<script>
import { mapState, mapMutations, mapGetters } from 'vuex';
import PagedResults from 'components/media/PagedResults';
import MediaUpload from 'components/MediaUpload';

export default {

	components: {
		PagedResults,
		MediaUpload
	},

	created() {
		this.lastUpdated = 0;
	},

	data() {
		return {
			media: [],
			view: 'Grid'
		};
	},

	computed: {
		...mapState({
			mediaPicker: state => state.media.mediaPicker,
			currentBlockIndex: state => state.contenteditor.currentBlockIndex,
			currentRegionName: state => state.contenteditor.currentRegionName,
			siteId: state => state.site.site
		}),

		...mapGetters([
			'canUser',
			'currentSectionIndex'
		]),

		visible: {
			get() {
				return this.mediaPicker.visible;
			},
			set(value) {
				if(value) {
					this.showMediaPicker();
				}
				else {
					this.hideMediaPicker();
				}
			}
		},

		fieldPath() {
			return this.mediaPicker.fieldPath;
		}
	},

	watch: {
		visible(show) {
			// update media if it's not been fetched before or
			// if 10 minutes have elapsed since the last fetch
			if(show && (new Date() - this.lastUpdated) / 60000 > 10) {
				this.fetchMedia();
			}
		}
	},

	methods: {
		...mapMutations([
			'showMediaPicker',
			'hideMediaPicker',
			'updateFieldValue',
			'updateBlockMedia'
		]),

		mediaFromResponse({ data: json }) {
			this.setFieldMedia(json.data);
		},

		setFieldMedia(media) {
			this.updateFieldValue({
				name: this.fieldPath,
				value: { ...media, type: 'image' },
				index: this.currentBlockIndex,
				region: this.currentRegionName,
				section: this.currentSectionIndex
			});

			this.updateBlockMedia({
				region: this.currentRegionName,
				section: this.currentSectionIndex,
				index: this.currentBlockIndex,
				value: {
					...media,
					/* eslint-disable camelcase */
					associated_field: this.fieldPath
					/* eslint-enable camelcase */
				}
			});

			this.hideMediaPicker();
		},

		fetchMedia() {
			this.$api
				// TODO: if unmodified since
				.get(`media?order=id.desc&site_ids[]=${this.siteId}`)
				.then(({ headers, data: json }) => {
					this.lastUpdated = headers.date;
					this.media = json.data;
				});
		}
	}
};
</script>