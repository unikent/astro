<template>
	<el-dialog
		title="Insert link"
		:visible.sync="visible"
		class="tabbed-dialog"
	>
		<el-tabs
			v-model="activeTab"
			type="border-card"
		>
			<el-tab-pane
				name="internal"
				label="Internal page"
				class="add-link-modal__internal"
			>
				<el-select
					v-model="selectValue"
					@change="setLink"
					placeholder="Select a page"
					class="add-link-modal__page-select"
				>
					<el-option
						v-for="item in sitePages"
						:key="item.value"
						:label="item.label"
						:value="item.value"
					>
						<span class="add-link-modal__page-select-label">
							{{ item.label }}
						</span>
						<span class="add-link-modal__page-select-value">
							{{ item.value }}
						</span>
					</el-option>
				</el-select>
			</el-tab-pane>
			<el-tab-pane
				name="external"
				label="External page"
			>
				<div class="columns">
					<div class="column" v-if="!hideTextInputs">
						<label class="el-form-item__label">Link text</label>
						<el-input
							v-model="links.external.text"
							placeholder="The Kent website"
						/>
					</div>
					<div class="column">
						<label class="el-form-item__label">URL</label>
						<el-input
							v-model="links.external.value"
							placeholder="http://kent.ac.uk"
						/>
					</div>
				</div>
			</el-tab-pane>
			<el-tab-pane
				name="email"
				label="Email"
			>
				<div class="columns">
					<div class="column" v-if="!hideTextInputs">
						<label class="el-form-item__label">Email link text</label>
						<el-input
							v-model="links.email.text"
							placeholder="A person's name"
						/>
					</div>
					<div class="column">
						<label class="el-form-item__label">Email address</label>
						<el-input
							v-model="links.email.value"
							placeholder="person@example.com"
						/>
					</div>
				</div>
			</el-tab-pane>
			<el-tab-pane
				name="doc"
				label="Document"
			>
				<paged-results
					:results="media"
					view="Details"
					picker-mode
					:pickerAction="setLink"
					:counts="[12, 20, 30]"
					:filter="(items) => items.filter(item => item.type === 'document')"
					class="hide-url-column"
				/>
				<template v-if="!hideTextInputs">
					<label
						class="el-form-item__label add-link-modal__doc-link-text"
					>Link text</label>
					<el-input
						v-model="links.doc.text"
						placeholder="Document name"
					/>
				</template>
			</el-tab-pane>
		</el-tabs>
		<span slot="footer" class="dialog-footer">
			<el-button @click="reset">Cancel</el-button>
			<el-button @click="addLink" type="primary">Insert</el-button>
		</span>
	</el-dialog>
</template>

<script>
import { mapState } from 'vuex';

import PagedResults from 'components/media/PagedResults';
import mediaFormatters from 'mixins/mediaFormatters';

export default {

	components: {
		PagedResults
	},

	mixins: [mediaFormatters],

	data() {
		return {
			activeTab: 'internal',
			visible: false,
			links: {
				internal: {
					text: '',
					value: ''
				},
				external: {
					text: '',
					value: ''
				},
				email: {
					text: '',
					value: ''
				},
				doc: {
					text: '',
					value: '',
					fileInfo: ''
				}
			},
			selectValue: null,
			media: [],
			pages: [],
			hideTextInputs: false
		};
	},

	computed: {
		...mapState({
			siteId: state => state.site.site
		}),

		currentPage() {
			const { id, path, depth } = this.$store.state.page.pageData;
			return { id, path, depth };
		},

		sitePages() {
			return this.pages.map(
				({ title, path, depth }) => ({ label: title, value: path, depth })
			);
		}
	},

	watch: {
		visible(value) {
			if(value) {
				this.fetchData();
			}
		}
	},

	// TODO: replace these events with a single event and associated methods
	created() {
		this.$bus.$on('richtext:showAddLinkModal', this.showModalRichtext);
		this.$bus.$on('block:showAddLinkModal', this.showModalBlock);
	},

	beforeDestroy() {
		this.$bus.$off('richtext:showAddLinkModal', this.showModalRichtext);
		this.$bus.$off('block:showAddLinkModal', this.showModalBlock);
	},

	methods: {
		showModalBlock({ callback, hideTextInputs }) {
			this.visible = true;
			this.callback = callback;
			this.hideTextInputs = hideTextInputs;
		},

		showModalRichtext({ callback, hideTextInputs }) {
			this.visible = true;
			this.callback = callback;
			this.hideTextInputs = hideTextInputs;
		},

		setLink(link) {
			let tmp, val = './';

			switch(this.activeTab) {
				case 'internal':
					tmp = this.sitePages.find(page => page.value === link);
					// TODO: this logic doesn't work for homepages (depth 0), needs fixing.
					for(let i = 0; i < this.currentPage.depth - 1; i++) {
						val += '../';
					}

					link = {
						text: tmp.label,
						value: val + tmp.value.replace('/', '')
					};
					break;
				case 'doc':
					tmp = ` (${
						link.filename.split('.').pop().toUpperCase()
					} ${
						this.formatBytes(link.filesize)
					})`;

					link = {
						text: `${link.filename}`,
						value: link.url,
						fileInfo: tmp
					};
					break;
			}

			this.links[this.activeTab] = link;
		},

		addLink() {
			const link = this.links[this.activeTab];
			let { text, value } = link;

			switch(this.activeTab) {
				case 'external':
					// replace all protocols with HTTPS ¯\_(ツ)_/¯
					value = 'https://' + value.replace(/^([A-Za-z]{3,9})?:(?:\/\/)?/, '');
					break;
				case 'email':
					value = 'mailto:' + value;
					break;
				case 'doc':
					// TODO: prepend media URL
					text += link.fileInfo;
					break;
			}

			this.callback({ text, value });
			this.reset();
		},

		fetchData() {
			this.$api
				.get(`media?order=id.desc&site_ids[]=${this.siteId}`)
				.then(({ data: json }) => {
					this.media = json.data;
				});

			this.$api
				.get(`sites/${this.siteId}?include=pages`)
				.then(({ data: json }) => {
					this.pages = json.data.pages;
				});
		},

		reset() {
			Object.keys(this.links).forEach(tab => {
				if(tab !== 'doc') {
					this.links[tab] = {
						text: '',
						value: ''
					};
				}
			});
			this.selectValue = null;
			this.hideTextInputs = false;
			this.visible = false;
		}
	}
};
</script>