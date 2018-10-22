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
				<el-row>
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
				</el-row>

				<el-row>
					<el-select
						v-if="selectPageAnchorLinks && selectPageAnchorLinks.length"
						placeholder="Select a page section to link to"
						class="add-link-modal__page-select"
						v-model="anchor"
						@change="() => setLink(selectValue)"
					>
						<el-option
							key="empty-anchor-link"
							label="No anchor link"
							:value="false"
						>
							<span class="add-link-modal__page-select-label">
								No anchor link
							</span>
						</el-option>
						<el-option
							v-for="item in selectPageAnchorLinks"
							:key="item.value"
							:label="item.value"
							:value="item.value"
						>
							<span class="add-link-modal__page-select-label">
								{{ item.value }}
							</span>
							<span class="add-link-modal__page-select-value">
								{{ item.label }}
							</span>
						</el-option>
					</el-select>
				</el-row>

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
				name="document"
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
						v-model="links.document.text"
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
import { Definition } from 'classes/helpers';
import { getPathsToItem } from 'helpers/collection';
import _ from 'lodash';

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
				document: {
					text: '',
					value: '',
					fileInfo: ''
				}
			},
			selectValue: null,
			anchor: null,
			media: [],
			site: null,
			pages: [],
			hideTextInputs: false,
			currentValue: null
		};
	},

	computed: {
		...mapState({
			siteId: state => state.site.site
		}),

		sitePages() {
			return this.pages.map(({ title, path, depth, blocks }) => {
				const anchorPaths = getPathsToItem(
					blocks,
					el => el && el.anchor_link,
					['errors']
				);

				return {
					label: title,
					value: path,
					depth,
					anchorPaths,
					blocks
				}
			});
		},

		selectPageAnchorLinks() {
			if(!this.selectValue) {
				return null;
			}

			const selectedPage = this.sitePages.find(
				page => page.value === this.selectValue
			);

			return selectedPage && selectedPage.anchorPaths.map(anchorPath => {
				const
					blockData = _.get(
						selectedPage.blocks,
						anchorPath.slice(0, 4)
					),
					blockDefinition = Definition.get(Definition.getType({
						name   : blockData.definition_name,
						version: blockData.definition_version
					})),
					field = _.get(selectedPage.blocks, anchorPath);

				return {
					label: field.title || `${blockDefinition.label} Block`,
					value: field.anchor_link
				};
			});
		}
	},

	watch: {
		visible(value) {
			if(value) {
				this.fetchData();
			}
		},

		selectValue(value, oldValue) {
			if(value !== oldValue) {
				this.anchor = null;
			}
		}
	},

	created() {
		this.$bus.$on('add-link-modal:show', this.showModal);
	},

	beforeDestroy() {
		this.$bus.$off('add-link-modal:show', this.showModal);
	},

	methods: {
		showModal({ callback, hideTextInputs, currentValue }) {
			this.currentValue = currentValue;
			this.visible = true;
			this.callback = callback;
			this.hideTextInputs = hideTextInputs;
		},

		setInitialLinkValue() {
			const
				[path, anchor] = (
					this.currentValue
						.replace(`https://${this.site.host}${this.site.path}`, '')
						.split('#')
				),
				hashIndex = this.currentValue.indexOf('#'),
				// grab the offset of the hash (or total length if no hash)
				hashOffset = hashIndex === -1 ? this.currentValue.length : hashIndex;

			// the path is external if it matches the original URL up to the hash
			if(path === this.currentValue.substr(0, hashOffset)) {
				this.links.external.value = this.currentValue;
			}
			else { // path is an internal link
				this.selectValue = path;
				// wait for our watcher to *possibly* set this.anchor to null first
				this.$nextTick(() => this.anchor = anchor);
			}
		},

		setLink(link) {
			let tmp;

			switch(this.activeTab) {
				case 'internal':
					tmp = this.sitePages.find(page => page.value === link);

					link = {
						text: tmp.label,
						value: `https://${this.site.host}${this.site.path}${tmp.value}`
					};
					break;
				case 'document':
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
				case 'internal':
					// add anchor to URL if one is selected
					if(value && this.anchor) {
						value += `#${this.anchor}`;
					}
					break;
				case 'external':
					// add HTTPS if no protocol is given
					if(value && !value.match(/^([A-Za-z]{3,9})?:(?:\/\/)?/)) {
						value = 'https://' + value;
					}
					break;
				case 'email':
					value = 'mailto:' + value;
					break;
				case 'document':
					// TODO: prepend media URL
					text += link.fileInfo;
					break;
			}

			this.callback({ text, value, type: this.activeTab });
			this.reset();
		},

		fetchData() {
			// TODO: catch errors
			this.$api
				.get(`media?order=id.desc&site_ids[]=${this.siteId}`)
				.then(({ data: json }) => {
					this.media = json.data;
				});

			this.$api
				.get(`sites/${this.siteId}?include=pages:full`)
				.then(({ data: json }) => {
					this.site = {
						host: json.data.host,
						path: json.data.path
					};
					this.pages = json.data.pages;
					this.setInitialLinkValue();
				});
		},

		reset() {
			Object.keys(this.links).forEach(tab => {
				// document links don't need to be reset
				if(tab !== 'document') {
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