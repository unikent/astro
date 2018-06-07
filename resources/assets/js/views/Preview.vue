<template>
<div v-if="!pageHasLayoutErrors">
	<div id="b-wrapper" ref="wrapper" :style="wrapperStyles">
		<component :is="layout" />
		<div
			class="block-overlay block-overlay--selected" :class="{
				'block-overlay--hidden': selectedOverlayHidden
			}"
			:style="selectedOverlayStyles"
		></div>
		<div
			class="block-overlay" :class="{
				'block-overlay--hidden': overlayHidden
			}"
			:style="blockOverlayStyles"
		>

			<div class="block-overlay__buttons" v-if="sectionConstraints">

				<div v-if="sectionConstraints.canSwapBlocks">
					<el-button
						class="move-up"
						@click="showBlockList(0, true)"
						size="mini"
					>
						Swap block
					</el-button>
				</div>
				<div v-else>
					<el-button-group v-if="canMove">
						<el-button
							class="move-up"
							:disabled="hoveredBlockIsFirst"
							@click="moveBlock(-1)"
							size="mini"
						>
							<i class="el-icon-arrow-up el-icon--left"></i>Move up
						</el-button>

						<el-button
							class="move-down"
							:disabled="hoveredBlockIsLast"
							@click="moveBlock(1)"
							size="mini"
						>
							Move down<i class="el-icon-arrow-down el-icon--right"></i>
						</el-button>
					</el-button-group>

					<el-dropdown
						v-if="sectionConstraints && sectionConstraints.canRemoveBlocks"
						class="block-overlay__delete-button"
						@command="removeBlock"
						size="mini"
					>
						<el-button :plain="true" type="danger" size="mini">Delete<i class="el-icon-caret-bottom el-icon--right"></i>
						</el-button>
						<el-dropdown-menu slot="dropdown">
							<el-dropdown-item command="delete">Confirm</el-dropdown-item>
						</el-dropdown-menu>
					</el-dropdown>
				</div>

			</div>

			<div
				class="add-before"
				:class="{ 'add-before--first' : hoveredBlockIsFirst }"
				@click="showBlockList()"
				v-if="sectionConstraints && sectionConstraints.canAddBlocks"
				@mouseleave="possiblyHideHoverOverlay"
			>
				<icon name="plus" :width="16" :height="16" viewBox="0 0 16 16" />
			</div>
			<div
				class="add-after"
				:class="{ 'add-after--last' : hoveredBlockIsLast }"
				@click="showBlockList(1)"
				v-if="sectionConstraints && sectionConstraints.canAddBlocks"
				@mouseleave="possiblyHideHoverOverlay"
			>
				<icon name="plus" :width="16" :height="16" viewBox="0 0 16 16" />
			</div>
		</div>
	</div>
	<div id="b-overlay" :style="overlayStyles"></div>
	<resize-shim :onResize="onResize" />
</div>
<el-card v-else class="box-card error-card">
	<div slot="header" class="clearfix">
		<i class="error-icon el-icon-error is-big"></i>
		<span class="error-title is-bold">A layout error occured</span>
	</div>
	<p>Please report the following errors to your administrator</p>
	<ul>
		<li v-for="layoutError in layoutErrors" class="text item">
			{{ layoutError }}
		</li>
	</ul>
	<p>The current page's path is: <strong>/site/{{ siteId }}/page/{{ $route.params.page_id }}</strong>.</p>
</el-card>
</template>

<script>
import { mapState, mapActions, mapMutations, mapGetters } from 'vuex';
import _ from 'lodash';
import imagesLoaded from 'imagesloaded';

import Icon from 'components/Icon';
import ResizeShim from 'components/ResizeShim';
import { win, Definition } from 'classes/helpers';
import { undoStackInstance } from 'plugins/undo-redo';
import { onKeyDown, onKeyUp } from 'plugins/key-commands';
import { layouts } from 'helpers/themeExports';
import { allowedOperations } from 'classes/SectionConstraints';
import { disableLinks, findParent } from 'helpers/dom';

/* global document, console */
/* eslint-disable no-console */

export default {
	name: 'preview-wrapper',

	components: {
		Icon,
		ResizeShim
	},

	data() {
		return {
			overlayStyles: {},
			wrapperStyles: {},
			overlayHidden: true,
			blockOverlayStyles: {},
			selectedOverlayHidden: true,
			selectedOverlayStyles: {},
			layoutDefinition: null,
			selectedBlock: null,
			hoveredBlock: null
		};
	},

	computed: {
		...mapState([
			'over'
		]),

		...mapState({
			loadedBlocks: state => state.page.loaded,
			pageData: state => state.page.pageData,
			currentLayout: state => state.page.currentLayout,
			currentRegion: state => state.contenteditor.currentRegionName,
			layoutVersion: state => state.page.currentLayoutVersion,
			blockMeta: state => state.page.blockMeta.blocks[state.page.currentRegion],
			siteLayoutDefinitions: state => state.site.layouts,
			siteId: state => parseInt(state.site.site),
			layoutErrors: state => state.page.layoutErrors
		}),

		...mapMutations([
			'setLayoutErrors'
		]),

		layout() {
			if(!this.currentLayout) {
				return null;
			}

			const
				layoutName = `${this.currentLayout}-v${this.layoutVersion}`,
				layout = layouts[layoutName];

			if(!layout) {
				console.warn(`"${layoutName}" layout not found.`)
			}

			return layout || null;
		},

		hoveredBlockSection() {
			return (
				this.hoveredBlock ?
					this.$store.getters.getSection(
						this.hoveredBlock.regionName,
						this.hoveredBlock.sectionIndex
					) :
					null
			);
		},

		hoveredBlockSectionLength() {
			return this.hoveredBlockSection ? this.hoveredBlockSection.blocks.length : 0;
		},

		hoveredBlockIsFirst() {
			return this.hoveredBlock && this.hoveredBlock.blockIndex === 0;
		},

		hoveredBlockIsLast() {
			return this.hoveredBlock && this.hoveredBlock.blockIndex === this.hoveredBlockSectionLength - 1;
		},

		sectionDefinition() {
			return (
				this.hoveredBlock ?
					Definition.getRegionSectionDefinition(
						this.hoveredBlock.regionName,
						this.hoveredBlock.sectionIndex
					) : null
			);
		},

		sectionConstraints() {
			return (
				this.hoveredBlockSection ?
					allowedOperations(this.hoveredBlockSection.blocks, this.sectionDefinition) :
					null
			);
		},

		canMove() {
			return this.hoveredBlockSectionLength > 1;
		},

		pageHasLayoutErrors() {
			return this.layoutErrors.length !== 0;
		}
	},

	created() {
		this.fetchPage(this.$route.params.page_id)
			.then(() => this.validateLayout());

		this.onKeyDown = onKeyDown(undoStackInstance);
		this.onKeyUp = onKeyUp(undoStackInstance);

		this.windowWidth = win.innerWidth;

		this.onResize = _.throttle(() => {
			if(this.windowWidth !== win.innerWidth) {
				this.updateOverlays(
					this.hoveredBlock ? this.hoveredBlock.blockIndex : null
				);
				this.windowWidth = win.innerWidth;
			}
		}, 16, { trailing: true });
	},

	mounted() {
		this.wrapper = this.$refs.wrapper;
		this.moveEl = this.$refs.move;
		this.initEvents();
	},

	destroyed() {
		document.removeEventListener('keydown', this.onKeyDown);
		document.removeEventListener('keyup', this.onKeyUp);
		document.removeEventListener('click', disableLinks);
		win.removeEventListener('resize', this.onResize);

		this.$bus.$off('block:updateBlockOverlays', this.updateOverlays);

		this.$bus.$on('block:showHoverOverlay', this.showHoverOverlay);
		this.$bus.$off('block:hideHoverOverlay', this.hideHoverOverlay);

		this.$bus.$off('block:showSelectedOverlay', this.showSelectedOverlay);
		this.$bus.$off('block:hideSelectedOverlay', this.hideSelectedOverlay);
	},

	methods: {
		...mapActions([
			'fetchPage'
		]),

		...mapMutations([
			'reorderBlocks',
			'deleteBlock',
			'setScale',
			'showBlockPicker',
			'updateInsertIndex',
			'updateInsertRegion',
			'deleteBlockValidationIssue'
		]),

		initEvents() {
			document.addEventListener('keydown', this.onKeyDown);
			document.addEventListener('keyup', this.onKeyUp);
			document.addEventListener('click', disableLinks);
			document.addEventListener('mousedown', this.mouseDown);
			document.addEventListener('mouseup', this.mouseUp);
			win.addEventListener('resize', this.onResize);

			this.$bus.$on('block:updateBlockOverlays', this.updateOverlays);

			this.$bus.$on('block:showHoverOverlay', this.showHoverOverlay);
			this.$bus.$on('block:hideHoverOverlay', this.hideHoverOverlay);

			this.$bus.$on('block:showSelectedOverlay', this.showSelectedOverlay);
			this.$bus.$on('block:hideSelectedOverlay', this.hideSelectedOverlay);
		},

		validateLayout() {

			let layoutName = `${this.currentLayout}-v${this.layoutVersion}`;
			let layoutErrors = [];

			this.layoutDefinition = this.siteLayoutDefinitions[layoutName];
			// check that we have the same number of regions in our data as we have defined
			if (Object.keys(this.pageData.blocks).length !== this.layoutDefinition.regions.length) {
				layoutErrors.push(`The regions on this page do not match the expected regions it should have in layout '${layoutName}'.`);
			}


			this.layoutDefinition.regions.forEach((regionDefinitionName) => {

				// check that this defined region exist in the page's regions
				if (this.pageData.blocks[regionDefinitionName] === void 0) {
					layoutErrors.push(`The region '${regionDefinitionName}' was expected but not found on this page. Layout is '${layoutName}'.`);
				}

				// we have the region in our data
				else {

					let regionDefinition = Definition.regionDefinitions[regionDefinitionName];

					if (regionDefinition !== void 0) {
						regionDefinition.sections.forEach((sectionDefinition, index) => {

							// check that the section is present
							if (this.pageData.blocks[regionDefinitionName][index] === void 0) {
								layoutErrors.push(`The section '${sectionDefinition.name}' was expected in '${regionDefinitionName}' region, but found none.`);
							}

							// check that this section is in the right part of the region
							else if(this.pageData.blocks[regionDefinitionName][index].name !== sectionDefinition.name) {
								layoutErrors.push(`The section '${sectionDefinition.name}' was expected in '${regionDefinitionName}' region, but found '${this.pageData.blocks[regionDefinitionName][index].name}'.`);

							}

							// if the section is in the right part of the region, go ahead and check that it has the right blocks within it
							else {
								this.pageData.blocks[regionDefinitionName][index].blocks.forEach((block) => {
									let fullBlockName = block.definition_name + '-v' + block.definition_version;
									if (sectionDefinition.allowedBlocks.indexOf(fullBlockName) < 0) {
										layoutErrors.push(`The block '${fullBlockName}' is not allowed in the '${sectionDefinition.name}' section of the '${regionDefinitionName}' region.`);
									}
								});
							}
						});

						// check to see if there are more sections than defined
						if (this.pageData.blocks[regionDefinitionName].length > regionDefinition.sections.length) {
							for (var i = regionDefinition.sections.length; i < this.pageData.blocks[regionDefinitionName].length; i++) {
								layoutErrors.push(`Page contains an additional section '${this.pageData.blocks[regionDefinitionName][i].name}' at position ${i+1}. ${regionDefinition.sections.length} section(s) were expected in region '${regionDefinitionName}' of layout '${layoutName}'.`);
							}
						}

					}

					// the defined region was not loaded in our region definitions
					else {
						layoutErrors.push(`The defined region '${regionDefinitionName}' was not found in our loaded region definitions.`);
					}

				}
			});

			// another loop through the data regions to alert the user if there are regions that are not defined
			Object.keys(this.pageData.blocks).forEach((regionDataName) => {
				// check that this defined region exist in the page's regions
				if (this.layoutDefinition.regions.indexOf(regionDataName) < 0) {
					layoutErrors.push(`Page contains region '${regionDataName}' which is not allowed in layout '${layoutName}'.`);
				}
			});

			this.$store.commit('setLayoutErrors', layoutErrors);
		},

		removeBlock() {
			// remove block but before we do so remove any validation issues it owns
			const {
				blockIndex: index,
				regionName: region,
				sectionIndex: section,
				blockId
			} = this.hoveredBlock;

			this.deleteBlockValidationIssue(blockId);
			this.deleteBlock({region, section, index });

			this.hideHoverOverlay();

			if(this.selectedBlock && this.selectedBlock.id === blockId) {
				this.hideSelectedOverlay();
			}
		},

		moveBlock(num) {
			const { blockIndex, regionName, sectionIndex } = this.hoveredBlock;

			this.reorderBlocks({
				from: blockIndex,
				to: blockIndex + num,
				region: regionName,
				section: sectionIndex
			});
		},

		showHoverOverlay(blockInfo) {
			if(
				this.hoveredBlock === null ||
				blockInfo.blockId !== this.hoveredBlock.blockId
			) {
				this.hoveredBlock = blockInfo;
				this.hoveredBlockEl = this.$el.querySelector(`#block_${this.hoveredBlock.blockId}`);

				// wait for images to load before displaying overlay
				imagesLoaded(this.hoveredBlockEl, () => {
					this.positionOverlay('hover');
				});
			}
		},

		showSelectedOverlay(blockInfo) {
			this.selectedBlock = blockInfo;
			this.selectedBlockEl = this.$el.querySelector(`#block_${this.selectedBlock.id}`);

			// wait for images to load before displaying overlay
			imagesLoaded(this.selectedBlockEl, () => {
				this.positionOverlay('select');
			});
		},

		possiblyHideHoverOverlay(e) {
			// only hide overlay if the related target isn't a block
			if(
				e.relatedTarget &&
				!findParent({
					el: e.relatedTarget,
					match: 'class',
					search: 'b-block-container'
				})
			) {
				this.hideHoverOverlay();
			}
		},

		hideHoverOverlay() {
			this.overlayHidden = true;
			this.updateStyles('blockOverlay', 'transform', 'translateY(0)');
			this.hoveredBlock = null;
		},

		hideSelectedOverlay() {
			this.selectedOverlayHidden = true;
			this.updateStyles('selectedOverlay', 'transform', 'translateY(0)');
			this.selectedBlock = null;
		},

		updateOverlays(index = null) {
			if(this.hoveredBlock) {
				if(index) {
					this.hoveredBlock = {
						...this.hoveredBlock,
						blockIndex: index
					};
					this.positionOverlay('hover');
				}
				else {
					this.hideHoverOverlay();
				}
			}

			if(this.selectedBlock) {
				this.positionOverlay('select');
			}
			else {
				this.hideSelectedOverlay();
			}
		},

		positionOverlay(type = 'hover') {
			const blockElement = this[`${type}edBlockEl`];

			if(!blockElement) {
				return;
			}

			const
				pos = blockElement.getBoundingClientRect(),
				heightDiff = Math.round(pos.height - 30),
				widthDiff = Math.round(pos.width - 30);

			let minusTop = 0,
				minusLeft = 0,
				addHeight = 0,
				addWidth = 0;

			if(heightDiff < 0) {
				addHeight = -heightDiff;
				minusTop = addHeight / 2;
			}

			if(widthDiff < 0) {
				addWidth = -widthDiff;
				minusLeft = addWidth / 2;
			}

			if(type === 'hover') {
				this.overlayHidden = false;
			}
			else {
				this.selectedOverlayHidden = false;
			}

			this.updateStyles(type === 'hover' ? 'blockOverlay' : 'selectedOverlay', {
				transform: `translateY(${(pos.top + win.scrollY - minusTop)}px)`,
				left     : `${(pos.left + win.scrollX - minusLeft)}px`,
				width    : `${(pos.width + addWidth)}px`,
				height   : `${(pos.height + addHeight)}px`
			});
		},

		updateStyles(dataName, prop, value) {
			this[`${dataName}Styles`] = {
				...this[`${dataName}Styles`],
				...(
					typeof prop === 'object' ?
						prop : { [prop]: value }
				)
			};
		},

		showBlockList(offset = 0, replaceBlocks = false) {
			const 
				deprecatedBlocks = this.sectionDefinition.deprecatedBlocks ? this.sectionDefinition.deprecatedBlocks : [],
				maxBlocks = this.sectionDefinition.max || this.sectionDefinition.size;

			this.showBlockPicker({
				insertIndex: this.hoveredBlock.blockIndex + offset,
				sectionIndex: this.hoveredBlock.sectionIndex,
				regionName: this.hoveredBlock.regionName,
				blocks: this.sectionConstraints ?
					this.sectionConstraints.allowedBlocks : [],
				deprecatedBlocks: deprecatedBlocks,
				maxSelectableBlocks: this.sectionConstraints.canSwapBlocks ?
					1 : (maxBlocks ? maxBlocks - this.hoveredBlockSectionLength : null),
				replaceBlocks: replaceBlocks
			});
		}
	}
};
</script>
