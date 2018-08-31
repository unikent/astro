<template>
<div class="page"  v-if="canUser('page.edit')">

	<div class="editor-body">
		<div class="editor-wrapper" ref="editor">
			<iframe :src="getPreviewUrl" id="editor-content" class="editor-content" :style="dimensions" frameborder="0" />
			<div
				class="iframe-overlay"
				:style="{ 'position' : displayIframeOverlay ? 'absolute' : null }"
			/>
		</div>
		<sidebar />
	</div>

	<modal-container />
</div>
<div class="page" v-else v-show="showPermissionsError">
	<el-alert
		title="You don't have access to this site"
		type="error"
		description="You don't have permission to access this site. Please contact the site owner."
		:closable="false"
		show-icon
	>
	</el-alert>
</div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import { Loading } from 'element-ui';

import Config from 'classes/Config';
import Sidebar from 'components/sidebar';
import ModalContainer from 'components/ModalContainer';
import Icon from 'components/Icon';
import { undoStackInstance } from 'plugins/undo-redo';
import { onKeyDown, onKeyUp } from 'plugins/key-commands';
import { Definition } from 'classes/helpers';

export default {
	name: 'editor',

	components: {
		Sidebar,
		ModalContainer,
		Icon
	},

	provide: {
		fieldType: 'block'
	},

	data() {
		return {
			showPermissionsError: false
		}
	},

	created() {
		this.$store.commit('site/updateCurrentSiteID', this.$route.params.site_id);
		this.$store.dispatch('loadSiteRole', { siteId: this.$route.params.site_id, username: Config.get('username') })
			// TODO: catch errors
			.then(() => {
				if (this.canUser('page.edit')) {
					this.showLoader();
				}
				else {
					this.showPermissionsError = true;
				}
			});

		this.views = {
			desktop: {
				icon: 'desktop',
				label: 'Desktop',
				width: '100%',
				height: '100vh'
			},
			tablet: {
				icon: 'tablet',
				label: 'Tablet',
				width: '768px',
				height: '1024px'
			},
			mobile: {
				icon: 'mobile',
				label: 'Mobile',
				width: '320px',
				height: '568px'
			}
		};

		this.onKeyDown = onKeyDown(undoStackInstance);
		this.onKeyUp = onKeyUp(undoStackInstance);

		document.addEventListener('keydown', this.onKeyDown);
		document.addEventListener('keyup', this.onKeyUp);

		this.$bus.$on('global:validate', this.validate);
		this.$bus.$on('global:validateAll', this.validateAll);
	},

	destroyed() {
		// we have left the page editor so remove the snapeshot of the latest saved content
		this.$store.commit('resetCurrentSavedState');

		document.removeEventListener('keydown', this.onKeyDown);
		document.removeEventListener('keyup', this.onKeyUp);

		this.$bus.$off('global:validate', this.validate);
		this.$bus.$off('global:validateAll', this.validateAll);
	},

	computed: {

		...mapState([
			'displayIframeOverlay',
			'currentView'
		]),

		...mapState({
			pageLoaded: state => state.page.loaded
		}),

		...mapGetters([
			'canUser',
			'currentBlock',
			'currentDefinition'
		]),

		// get the URL for the route to show the editor preview page (not the external page preview)
		getPreviewUrl() {
			// TODO: Don't reload page when page_id changes, use state instead
			return `${Config.get('base_url', '')}/preview/${this.$route.params.page_id}`;
		},

		dimensions() {
			return {
				width: this.views[this.currentView].width,
				height: this.views[this.currentView].height
			};
		}

	},

	watch: {
		pageLoaded(hideLoader) {
			// update/set the current snapshot of the saved page content
			this.$store.commit('updateCurrentSavedState');
			if(hideLoader) {
				if(this.loader) {
					this.loader.close();
				}
			}
			else {
				this.showLoader();
			}
		}
	},

	methods: {

		showLoader() {
			this.loader = Loading.service({
				target: this.$refs.editor,
				text: 'Loading preview...',
				customClass: 'loading-overlay'
			});
		},

		// TODO: turn this into an action
		validate: _.debounce(
			function(blockInfo) {
				const
					block = blockInfo ?
						this.$store.getters.getBlock(
							blockInfo.regionName,
							blockInfo.sectionIndex,
							blockInfo.blockIndex
						) :
						this.currentBlock,
					definition = blockInfo ?
						{
							name: block.definition_name,
							version: block.definition_version
						} :
						this.currentDefinition;

				if(!block) {
					return;
				}

				const validator = Definition.getValidator(definition);

				if(validator) {
					this.$store.commit('resetFieldErrors', {
						blockId: `${block.id}`
					});

					validator.validate(block.fields, (errors, fields) => {
						if(errors) {
							errors.forEach(({ field, message }) => {
								this.$store.commit('addFieldError', {
									blockId: `${block.id}`,
									fieldName: field,
									errors: [message]
								});
							});
						}
					});
				}
			},
			100,
			{ trailing: true }
		),

		validateAll() {
			this.$store.dispatch('initialiseBlocksAndValidate', this.$store.state.page.pageData.blocks);
		}
	}
};
</script>
