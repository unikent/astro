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
						class="block-overlay__-button"
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
			>
				<icon name="plus" :width="16" :height="16" viewBox="0 0 16 16" />
			</div>
			<div
				class="add-after"
				:class="{ 'add-after--last' : hoveredBlockIsLast }"
				@click="showBlockList(1)"
				v-if="sectionConstraints && sectionConstraints.canAddBlocks"
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
import { disableLinks } from 'helpers/dom';

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
			sectionDefinition: null,
			sectionConstraints: null,
			currentSectionBlocks: null,
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

		...mapGetters([
			'getBlocks',
			'blocks'
		]),

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

		hoveredBlockIsFirst() {
			return this.hoveredBlock && this.hoveredBlock.index === 0;
		},

		hoveredBlockIsLast() {
			return this.hoveredBlock && this.hoveredBlock.index === this.blocks.length - 1;
		},

		canMove() {
			return this.currentSectionBlocks.length > 1;
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

		this.onResize = _.throttle(() => {
			this.positionOverlay(this.hoveredBlock);
			this.positionOverlay(this.selectedBlock, 'selected');
		}, 16, { trailing: true });
	},

	destroyed() {
		document.removeEventListener('keydown', this.onKeyDown);
		document.removeEventListener('keyup', this.onKeyUp);
		document.removeEventListener('click', disableLinks);
		win.removeEventListener('resize', this.onResize);

		this.$bus.$off('block:updateBlockOverlays', this.updateOverlays);

		this.$bus.$on('block:showHoverOverlay', this.showOverlay);
		this.$bus.$off('block:hideHoverOverlay', this.hideOverlay);

		this.$bus.$off('block:showSelectedOverlay', this.showSelectedOverlay);
		this.$bus.$off('block:hideSelectedOverlay', this.hideSelectedOverlay);
	},

	mounted() {
		this.wrapper = this.$refs.wrapper;
		this.moveEl = this.$refs.move;
		this.initEvents();
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

			this.$bus.$on('block:showHoverOverlay', this.showOverlay);
			this.$bus.$on('block:hideHoverOverlay', this.hideOverlay);

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
			const { index, region, section } = this.hoveredBlock;
			const blockToBeDeleted = this.$store.getters.getBlock(region, section, index);
			this.deleteBlockValidationIssue(blockToBeDeleted.id);
			this.deleteBlock({ index, region, section });
			this.hideOverlay();
			this.hideSelectedOverlay();
			this.hoveredBlock = null;
			this.$message({
				message: 'Block removed',
				type: 'success'
			});
		},

		moveBlock(num) {
			const { index, region, section } = this.hoveredBlock;

			this.reorderBlocks({
				from: index,
				to: index + num,
				region,
				section
			});
		},

		showSelectedOverlay(block) {
			this.selectedBlock = block;
			// wait for images to load before displaying overlay
			imagesLoaded(block.$el, () => {
				this.positionOverlay(block, 'selected');
			});
		},

		hideSelectedOverlay() {
			this.selectedOverlayHidden = true;
			this.updateStyles('selectedOverlay', 'transform', 'translateY(0)');
		},

		showOverlay(block) {
			if(block !== this.hoveredBlock) {
				this.hoveredBlock = block;
				// wait for images to load before displaying overlay
				imagesLoaded(block.$el, () => {
					this.positionOverlay(block);
				});
			}
		},

		hideOverlay(block) {
			if(block !== this.hoveredBlock) {
				this.overlayHidden = true;
				this.updateStyles('blockOverlay', 'transform', 'translateY(0)');
			}
		},

		updateOverlays(index = null) {
			if(this.hoveredBlock) {
				if(this.hoveredBlock.index !== index) {
					this.positionOverlay(this.hoveredBlock);
				}
				else {
					this.hideOverlay();
					this.hoveredBlock = null;
				}
			}
			else {
				this.hideOverlay();
			}

			if(this.selectedBlock) {
				this.positionOverlay(this.selectedBlock, 'selected');
			}
			else {
				this.hideSelectedOverlay();
			}
		},

		positionOverlay(block, type = 'hover') {
			if(!block) {
				return;
			}

			const
				pos = block.$el.getBoundingClientRect(),
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

			// this is for the currently hovered block
			if(this.hoveredBlock) {
				const section = this.$store.getters.getSection(this.hoveredBlock.region, this.hoveredBlock.section);
				this.sectionDefinition = section ? Definition.getRegionSectionDefinition(this.hoveredBlock.region, this.hoveredBlock.section) : null
				this.currentSectionBlocks = section.blocks;
				this.sectionConstraints = section ? allowedOperations(section.blocks, this.sectionDefinition) : null;
			}
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
			const maxBlocks = this.sectionDefinition.max || this.sectionDefinition.size;

			this.showBlockPicker({
				insertIndex: this.hoveredBlock.index + offset,
				sectionIndex: this.hoveredBlock.section,
				regionName: this.hoveredBlock.region,
				blocks: this.sectionConstraints ?
					this.sectionConstraints.allowedBlocks : [],
				maxSelectableBlocks: this.sectionConstraints.canSwapBlocks ?
					1 : (maxBlocks ? maxBlocks - this.blocks.length : null),
				replaceBlocks: replaceBlocks
			});
		}
	}
};
</script>
