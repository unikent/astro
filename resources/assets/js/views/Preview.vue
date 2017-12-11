<template>
<div v-if="layoutErrors.length === 0">
	<div id="b-wrapper" ref="wrapper" :style="wrapperStyles">
		<component :is="layout" />
		<div
			class="block-overlay" :class="{
				'hide-drag': hideBlockOverlayControls,
				'block-overlay--hidden': overlayHidden
			}"
			:style="blockOverlayStyles"
		>

			<div class="block-overlay__buttons" v-if="sectionConstraints">

				<div v-if="sectionConstraints.canSwapBlocks">
					<el-button
						class="move-up"
						@click="showBlockList(0, true)"
					>
						Swap block
					</el-button>
				</div>
				<div v-else-if="canMove">
					<el-button-group>
						<el-button
							class="move-up"
							:disabled="currentBlockIsFirst"
							@click="moveBlock(-1)"
						>
							<icon name="angle-up" width="15" height="15" viewBox="0 0 15 15" /> Move up
						</el-button>

						<el-button
							class="move-down"
							:disabled="currentBlockIsLast"
							@click="moveBlock(1)"
						>
							Move down <icon name="angle-down" width="15" height="15" viewBox="0 0 15 15" />
						</el-button>
					</el-button-group>

					<el-dropdown
						class="block-overlay__delete-button"
						@command="removeBlock"
						v-if="sectionConstraints && sectionConstraints.canRemoveBlocks"
					>
						<el-button :plain="true" type="danger">
							<icon name="delete" width="15" height="15" viewBox="0 0 15 15" /> Delete
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
				<icon name="plus" width="15" height="15" viewBox="0 0 15 15" />
			</div>
			<div
				class="add-after"
				:class="{ 'add-after--last' : currentBlockIsLast }"
				@click="showBlockList(1)"
				v-if="sectionConstraints && sectionConstraints.canAddBlocks"
			>
				<icon name="plus" width="15" height="15" viewBox="0 0 15 15" />
			</div>
		</div>
	</div>
	<div class="b-handle" :style="handleStyles">
		<icon name="move" width="20" height="20" />
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
			blockOverlayStyles: {},
			hideBlockOverlayControls: false,
			overlayStyles: {},
			wrapperStyles: {},
			overlayHidden: true,
			/**
			 * @var {BlockComponent} - The Block.vue component which is currently hovered
			 */
			current: null,
			sectionDefinition: null,
			sectionConstraints: null,
			currentSectionBlocks: null,
			layoutDefinition: null,
			layoutErrors: []
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
			siteLayoutDefinitions: state => state.site.layouts
		}),

		...mapGetters([
			'getBlocks',
			'blocks'
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
			return this.current && this.current.index === 0;
		},

		currentBlockIsLast() {
			return this.current && this.current.index === this.blocks.length - 1;
		},

		canMove() {
			return this.currentSectionBlocks.length > 1;
		}
	},

	created() {
		var $this = this; // because 'this' isnt available to us in callback function after the api has returned

		this.fetchPage(this.$route.params.page_id || 1)// is this fallback necessary?
			.then(function () {

				$this.validateLayout();

			});

		this.onKeyDown = onKeyDown(undoStackInstance);
		this.onKeyUp = onKeyUp(undoStackInstance);

		this.cancelClicks = (e) => {
			if(!e.ctrlKey && findParent('a', e.target, false)) {
				e.preventDefault();
			}
		};

		this.onResize = _.throttle(() => {
			if(this.current) {
				this.positionOverlay(this.current);
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

			let $this = this;

			$this.layoutDefinition = $this.siteLayoutDefinitions[`${$this.currentLayout}-v${$this.layoutVersion}`];
			// check that we have the same number of regions in our data as we have defined
			if (Object.keys($this.pageData.blocks).length !== $this.layoutDefinition.regions.length) {
				$this.layoutErrors.push('The regions on this page do not match the expected regions it should have.');
			}


			_.each($this.layoutDefinition.regions, function (regionName) {

				// check that this defined region exist in the page's regions
				if ($this.pageData.blocks[regionName] === void 0) {
					$this.layoutErrors.push(`The region '${regionName}' was expected but not found on this page.`);
				}
				
				let regionDefinition = Definition.regionDefinitions[regionName];

				if (regionDefinition !== void 0) {
					_.each(regionDefinition.sections, function(sectionDefinition, index) {
						
						// check that this section is in the right part of the region
						if ($this.pageData.blocks[regionName][index].name !== sectionDefinition.name) {
							$this.layoutErrors.push(`The section '${sectionDefinition.name}' was expected in '${regionName}' region, but found '${$this.pageData.blocks[regionName][index].name}'.`);
							
						}

						// if the section is in the right part of the region, go ahead and check that it has the right blocks within it
						else {
							_.each($this.pageData.blocks[regionName][index].blocks, function (block) {
								let fullBlockName = block.definition_name + '-v' + block.definition_version;
								if (sectionDefinition.allowedBlocks.indexOf(fullBlockName) < 0) {
									$this.layoutErrors.push(`The block '${fullBlockName}' is now allowed in the '${sectionDefinition.name}' section of the '${regionName}' region.`);
								}
							});
						}
					});
				}

				// the defined region was not loaded in our region definitions
				else {
					$this.layoutErrors.push(`The defined region '${regionName}' was not found in our loaded region definitions`);
				}
			});
		},

		initEvents() {
			document.addEventListener('keydown', this.onKeyDown);
			document.addEventListener('keyup', this.onKeyUp);
			document.addEventListener('click', this.cancelClicks);
			document.addEventListener('mousedown', this.mouseDown);
			document.addEventListener('mouseup', this.mouseUp);
			win.addEventListener('resize', this.onResize);

			this.$bus.$on('block:showOverlay', this.showOverlay);
			this.$bus.$on('block:hideOverlay', this.hideOverlay);
			this.$bus.$on('block:updateOverlay', this.updateOverlay)
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
			const { index, region, section } = this.current;
			const blockToBeDeleted = this.$store.getters.getBlock(region, section, index);
			this.deleteBlockValidationIssue(blockToBeDeleted.id);
			this.deleteBlock({ index, region, section });
			this.hideOverlay();
			this.current = null;
			this.$message({
				message: 'Block removed',
				type: 'success'
			});
		},

		moveBlock(num) {
			const { index, region, section } = this.current;
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

		showOverlay(block) {
			if(block !== this.current) {
				// wait for images to load before displaying overlay
				imagesLoaded(block.$el, () => {
					this.positionOverlay(block, true);
				});
			}
		},

		hideOverlay(block) {
			if(block !== this.current) {
				this.overlayHidden = true;
				this.updateStyles('blockOverlay', 'transform', 'translateY(0)');
			}
		},

		updateOverlay(index = null) {
			// block is hovered and wasn't just deleted
			if(this.current && this.current.index !== index) {
				this.positionOverlay(this.current);
			}
			else if(this.current && this.current.index === index) {
				this.hideOverlay();
				this.current = null;
			}
			else {
				this.hideOverlay();
			}
		},

		repositionOverlay(data) {
			let
				offset = 0,
				to = data.to > data.from ? data.to : data.to - 1;

			for(let i = 0; i <= to; i++) {
				if(i !== data.from) {
					offset += this.blockMeta[i].size;
				}
			}

			this.overlayHidden = false;
			this.updateStyles('blockOverlay', 'transform', `translateY(${offset}px)`);
		},

		positionOverlay(block, setCurrent) {
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

			this.overlayHidden = false;

			this.updateStyles('blockOverlay', {
				transform: `translateY(${(pos.top + window.scrollY - minusTop)}px)`,
				left     : `${(pos.left + window.scrollX - minusLeft)}px`,
				width    : `${(pos.width + addWidth)}px`,
				height   : `${(pos.height + addHeight)}px`
			});


			if(setCurrent) {
				this.current = block;
			}

			if(this.current) {
				const section = this.$store.getters.getSection(this.current.region, this.current.section);
				this.sectionDefinition = section ? Definition.getRegionSectionDefinition(this.current.region, this.current.section) : null
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
			this.showBlockPicker({
				insertIndex: this.current.index + offset,
				sectionIndex: this.current.section,
				regionName: this.current.region,
				blocks: this.sectionConstraints ? this.sectionConstraints.allowedBlocks : [],
				maxSelectableBlocks: this.sectionConstraints.canSwapBlocks ? 1 : (this.sectionDefinition.max ? this.sectionDefinition.max - this.blocks.length : null),
				replaceBlocks: replaceBlocks
			});

		}
	}
};
</script>
