<template>
<div v-if="!pageHasLayoutErrors">
	<div id="b-wrapper" ref="wrapper" :style="wrapperStyles">
		<component :is="layout" />
		<div
			class="block-overlay-hovered" :class="{
				'hide-drag': hideBlockHoverOverlayControls,
				'block-overlay-hovered--hidden': hoverOverlayHidden
			}"
			:style="blockHoverOverlayStyles"
		>

			<div class="block-overlay-hovered__buttons" v-if="sectionConstraints">

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
							:disabled="currentBlockIsFirst"
							@click="moveBlock(-1)"
							size="mini"
						>
						<i class="el-icon-arrow-up el-icon--left"></i>Move up
						</el-button>

						<el-button
							class="move-down"
							:disabled="currentBlockIsLast"
							@click="moveBlock(1)"
							size="mini"
						>
						Move down<i class="el-icon-arrow-down el-icon--right"></i>
						</el-button>
					</el-button-group>

					<el-dropdown
						v-if="sectionConstraints && sectionConstraints.canRemoveBlocks"
						class="block-overlay-hovered__-button"
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
				:class="{ 'add-before--first' : currentBlockIsFirst }"
				@click="showBlockList()"
				v-if="sectionConstraints && sectionConstraints.canAddBlocks"
			>
				<icon name="plus" width="16" height="16" viewBox="0 0 16 16" />
			</div>
			<div
				class="add-after"
				:class="{ 'add-after--last' : currentBlockIsLast }"
				@click="showBlockList(1)"
				v-if="sectionConstraints && sectionConstraints.canAddBlocks"
			>
				<icon name="plus" width="16" height="16" viewBox="0 0 16 16" />
			</div>
		</div>
	</div>
	<div class="b-handle" :style="handleStyles">
		<icon name="move" width="20" height="20" />
	</div>
	<div id="b-overlay" :style="hoverOverlayStyles"></div>
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
import { win, findParent } from 'classes/helpers';
import { undoStackInstance } from 'plugins/undo-redo';
import { onKeyDown, onKeyUp } from 'plugins/key-commands';
import { layouts } from 'cms-prototype-blocks/layouts';
import { allowedOperations } from 'classes/SectionConstraints';
import { Definition } from 'classes/helpers';

/* global document, window, console */
/* eslint-disable no-console */

export default {
	name: 'preview-wrapper',

	components: {
		Icon,
		ResizeShim
	},

	data() {
		return {
			handleStyles: {
				fill: '#fff'
			},
			blockHoverOverlayStyles: {},
			hideBlockHoverOverlayControls: false,
			hoverOverlayStyles: {},
			wrapperStyles: {},
			hoverOverlayHidden: true,
			/**
			 * @var {BlockComponent} - The Block.vue component which is currently hovered
			 */
			currentlyHoveredBlock: null,
			sectionDefinition: null,
			sectionConstraints: null,
			currentSectionBlocks: null,
			layoutDefinition: null
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
			// currentBlockIndex: => state.contenteditor.currentBlockIndex,
			// currentRegionName: => state.contenteditor.currentRegionName,
			// currentSectionName: => state.contenteditor.currentSectionName
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

		currentBlockIsFirst() {
			return this.currentlyHoveredBlock && this.currentlyHoveredBlock.index === 0;
		},

		currentBlockIsLast() {
			return this.currentlyHoveredBlock && this.currentlyHoveredBlock.index === this.blocks.length - 1;
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
			.then(() => {

				this.validateLayout();

			});

		this.onKeyDown = onKeyDown(undoStackInstance);
		this.onKeyUp = onKeyUp(undoStackInstance);

		this.cancelClicks = (e) => {
			if(!e.ctrlKey && findParent('a', e.target, false)) {
				e.preventDefault();
			}
		};

		this.onResize = _.throttle(() => {
			if(this.currentlyHoveredBlock) {
				this.positionHoverOverlay(this.currentlyHoveredBlock);
			}
		}, 16, { trailing: true });
	},

	destroyed() {
		document.removeEventListener('keydown', this.onKeyDown);
		document.removeEventListener('keyup', this.onKeyUp);
		document.removeEventListener('click', this.cancelClicks);
		win.removeEventListener('resize', this.onResize);
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

		validateLayout(){

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

		initEvents() {
			document.addEventListener('keydown', this.onKeyDown);
			document.addEventListener('keyup', this.onKeyUp);
			document.addEventListener('click', this.cancelClicks);
			document.addEventListener('mousedown', this.mouseDown);
			document.addEventListener('mouseup', this.mouseUp);
			win.addEventListener('resize', this.onResize);

			this.$bus.$on('block:showHoverOverlay', this.showHoverOverlay);
			this.$bus.$on('block:hideHoverOverlay', this.hideHoverOverlay);
			this.$bus.$on('block:updateHoverOverlay', this.updateHoverOverlay)
		},

		removeDialog(done) {
			this
				.$confirm('Are you sure you want to remove this block?')
				.then(() => {
					done();
				})
				.catch(() => {});
		},

		removeBlock() {
			// remove block but before we do so remove any validation issues it owns
			const { index, region, section } = this.currentlyHoveredBlock;
			const blockToBeDeleted = this.$store.getters.getBlock(region, section, index);
			this.deleteBlockValidationIssue(blockToBeDeleted.id);
			this.deleteBlock({ index, region, section });
			this.hideHoverOverlay();
			this.currentlyHoveredBlock = null;
			this.$message({
				message: 'Block removed',
				type: 'success'
			});
		},

		moveBlock(num) {
			const { index, region, section } = this.currentlyHoveredBlock;
			this.reorderBlocks({
				from: index,
				to: index + num,
				region,
				section
			});
		},

		handlerMove(e) {
			this.updateStyles(
				'handle',
				'transform',
				`translateY(${e.pageY - 22}px)`
			);
		},

		showHoverOverlay(block) {
			if(block !== this.currentlyHoveredBlock) {
				// wait for images to load before displaying overlay
				imagesLoaded(block.$el, () => {
					this.positionHoverOverlay(block, true);
				});
			}
		},

		hideHoverOverlay(block) {
			if(block !== this.currentlyHoveredBlock) {
				this.hoverOverlayHidden = true;
				this.updateStyles('blockHoverOverlay', 'transform', 'translateY(0)');
			}
		},

		updateHoverOverlay(index = null) {
			// block is hovered and wasn't just deleted
			if(this.currentlyHoveredBlock && this.currentlyHoveredBlock.index !== index) {
				this.positionHoverOverlay(this.currentlyHoveredBlock);
			}
			else if(this.currentlyHoveredBlock && this.currentlyHoveredBlock.index === index) {
				this.hideHoverOverlay();
				this.currentlyHoveredBlock = null;
			}
			else {
				this.hideHoverOverlay();
			}
		},

		repositionHoverOverlay(data) {
			let
				offset = 0,
				to = data.to > data.from ? data.to : data.to - 1;

			for(let i = 0; i <= to; i++) {
				if(i !== data.from) {
					offset += this.blockMeta[i].size;
				}
			}

			this.hoverOverlayHidden = false;
			this.updateStyles('blockHoverOverlay', 'transform', `translateY(${offset}px)`);
		},

		positionHoverOverlay(block, setCurrent) {
			var
				pos = block.$el.getBoundingClientRect(),
				heightDiff = Math.round(pos.height - 30),
				widthDiff = Math.round(pos.width - 30),
				minusTop = 0,
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

			this.hoverOverlayHidden = false;

			this.updateStyles('blockHoverOverlay', {
				transform: `translateY(${(pos.top + window.scrollY - minusTop)}px)`,
				left     : `${(pos.left + window.scrollX - minusLeft)}px`,
				width    : `${(pos.width + addWidth)}px`,
				height   : `${(pos.height + addHeight)}px`
			});


			if(setCurrent) {
				this.currentlyHoveredBlock = block;
			}


			if(this.currentlyHoveredBlock) {
				const section = this.$store.getters.getSection(this.currentlyHoveredBlock.region, this.currentlyHoveredBlock.section);
				this.sectionDefinition = section ? Definition.getRegionSectionDefinition(this.currentlyHoveredBlock.region, this.currentlyHoveredBlock.section) : null
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
				insertIndex: this.currentlyHoveredBlock.index + offset,
				sectionIndex: this.currentlyHoveredBlock.section,
				regionName: this.currentlyHoveredBlock.region,
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
