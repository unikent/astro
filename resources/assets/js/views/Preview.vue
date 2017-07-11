<template>
<div>
	<div id="b-wrapper" ref="wrapper" :style="wrapperStyles">
		<component :is="layout" />
		<div
			class="block-overlay" :class="{
				'hide-drag': hideBlockOverlayControls,
				'block-overlay--hidden': overlayHidden
			}"
			:style="blockOverlayStyles"
		>
			<div class="block-overlay__delete" @click="removeBlock"> <!-- removeDialog(removeBlock) -->
				<Icon name="delete" width="20" height="20" />
			</div>
			<div ref="move" class="block-overlay__move" v-show="blocks.length > 1">
				<Icon name="move" width="20" height="20" />
			</div>
			<div
				class="add-before"
				:class="{ 'add-before--first' : currentBlockIsFirst }"
				@click="showBlockList()"
			>
				<icon name="plus" width="15" height="15" viewBox="0 0 15 15" />
			</div>
			<div
				class="add-after"
				:class="{ 'add-after--last' : currentBlockIsLast }"
				@click="showBlockList(1)"
			>
				<icon name="plus" width="15" height="15" viewBox="0 0 15 15" />
			</div>
		</div>
	</div>
	<div class="b-handle" :style="handleStyles">
		<Icon name="move" width="20" height="20" />
	</div>
	<div id="b-overlay" :style="overlayStyles"></div>
	<resize-shim :onResize="onResize" />
</div>
</template>

<script>
import { mapState, mapActions, mapMutations, mapGetters } from 'vuex';
import _ from 'lodash';
import imagesLoaded from 'imagesloaded';

import Icon from 'components/Icon';
import ResizeShim from 'components/ResizeShim';

import { win, findParent, smoothScrollTo } from 'classes/helpers';

import { undoStackInstance } from 'plugins/undo-redo';
import { onKeyDown, onKeyUp } from 'plugins/key-commands';

import { layouts } from 'cms-prototype-blocks/layouts';

/* global document, window */

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
			overlayHidden: true
		};
	},

	computed: {
		...mapState([
			'over'
		]),

		...mapState({
			loadedBlocks: state => state.page.loaded,
			currentLayout: state => state.page.currentLayout,
			currentRegion: state => state.page.currentRegion,
			layoutVersion: state => state.page.currentLayoutVersion,
			blockMeta: state => state.page.blockMeta.blocks[state.page.currentRegion],
			blocks: state => state.page.pageData.blocks[state.page.currentRegion]
		}),

		...mapGetters([
			'scaleDown',
			'scaleUp'
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

		dragging: {
			get() {
				return this.$store.state.page.dragging;
			},

			set(val) {
				return this.$store.commit('setDragging', val);
			}
		},

		currentBlockIsFirst() {
			return this.current && this.current.index === 0;
		},

		currentBlockIsLast() {
			return this.current && this.current.index === this.blocks.length - 1;
		}
	},

	created() {
		this.fetchPage(this.$route.params.site_id);

		this.$bus.$on('block:move', index => {
			this.moved = index;
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
		document.removeEventListener('mousedown', this.mouseDown);
		document.removeEventListener('mouseup', this.mouseUp);
		document.removeEventListener('mousemove', this.handlerMove);
		win.removeEventListener('resize', this.onResize);
	},

	mounted() {
		this.wrapper = this.$refs.wrapper;
		this.moveEl = this.$refs.move;
		this.current = null;
		this.initEvents();
	},

	methods: {
		...mapActions([
			'fetchPage'
		]),

		...mapMutations([
			'reorderBlocks',
			'deleteBlock',
			'updateBlockMeta',
			'setScale',
			'addBlock',
			'showBlockPicker',
			'updateInsertIndex',
			'updateInsertRegion'
		]),

		initEvents() {
			document.addEventListener('keydown', this.onKeyDown);
			document.addEventListener('keyup', this.onKeyUp);
			document.addEventListener('click', this.cancelClicks);
			document.addEventListener('mousedown', this.mouseDown);
			document.addEventListener('mouseup', this.mouseUp);
			win.addEventListener('resize', this.onResize);

			this.$bus.$on('block:showOverlay', this.showOverlay);
			this.$bus.$on('block:hideOverlay', this.hideOverlay);
			this.$bus.$on('block:updateOverlay', this.updateOverlay);
			this.$bus.$on('block:move', this.repositionOverlay);
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
			const { index, region } = this.current;
			this.deleteBlock({ index, region });
			this.hideOverlay();
			this.current = null;
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

		mouseDown(e) {
			if(e.button === 0 && findParent(this.moveEl, e.target, true)) {
				this.updateStyles('wrapper', 'userSelect', 'none');
				this.updateStyles('overlay', 'pointerEvents', 'auto');
				this.updateStyles('handle', 'opacity', 1);

				const
					thirdOfScreen = window.innerHeight / 3,
					scale = Math.min(
						Math.round(
							(thirdOfScreen / this.current.$el.getBoundingClientRect().height) * 100
						) / 100,
						.6
					);

				this.setScale(scale);

				if(this.scaleDown() < 1) {
					this.scale(false, e.clientY);
				}

				this.updateBlockMeta({
					index: this.current.index,
					region: this.current.region,
					type: 'dragging',
					value: true
				});

				this.hideBlockOverlayControls = true;
				document.addEventListener('mousemove', this.handlerMove);

				this.dragging = true;
			}
		},

		mouseUp(e) {
			if(this.dragging) {
				this.updateStyles('wrapper', 'userSelect', 'auto');
				this.updateStyles('handle', 'opacity', 0);

				if(this.scaleDown() < 1) {
					this.scale(true, e.clientY);
				}
				else {
					this.resetAfterDrag();
				}

				this.hideBlockOverlayControls = false;
				document.removeEventListener('mousemove', this.handlerMove);
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
			if(this.dragging) {
				return;
			}

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
		},

		resetAfterDrag() {
			this.updateStyles('overlay', 'pointerEvents', 'none');

			if(this.moved) {
				this.reorderBlocks({
					from:  this.moved.from,
					to:    this.moved.to,
					value: this.blocks[this.moved.from]
				});
			}

			this.dragging = false;

			for(var i = 0; i < this.blocks.length; i++) {
				this.updateBlockMeta({
					type: 'offset',
					region: this.current.region,
					index: i,
					value: 0
				});
			}

			this.updateBlockMeta({
				index: this.moved ? this.moved.to : this.current.index,
				region: this.current.region,
				type: 'dragging',
				value: false
			});

			this.moved = false;
		},

		scale(revert, mouseY) {
			const scroll = window.scrollY;

			if(revert) {
				const
					scrollScaleUp = scroll * this.scaleUp(),
					offsetPlusScaled = (mouseY * this.scaleUp()) - mouseY;

				smoothScrollTo({ y: scrollScaleUp + offsetPlusScaled });

				this.wrapperStyles = Object.assign(this.wrapperStyles, {
					transform: null,
					transition: 'transform 0.3s ease-out'
				});

				const onEnd = () => {
					this.resetAfterDrag();
					this.wrapper.removeEventListener('transitionend', onEnd);
				};

				this.wrapper.addEventListener('transitionend', onEnd);
			}
			else {
				const
					scrollScaleDown = scroll * this.scaleDown(),
					offsetMinusScaled = mouseY - (mouseY * this.scaleDown());

				this.updateStyles(
					'handle',
					'transform',
					'translateY(' + (((mouseY + window.scrollY) * this.scaleDown()) - 22) + 'px)'
				);

				smoothScrollTo({ y: scrollScaleDown - offsetMinusScaled });

				this.wrapperStyles = Object.assign(this.wrapperStyles, {
					transform: `scale(${this.scaleDown()})`,
					transition: 'transform 0.3s ease-out'
				});
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

		showBlockList(offset = 0) {
			const { index, region } = this.current;
			this.updateInsertIndex(index + offset);
			this.updateInsertRegion(region);
			this.showBlockPicker();
		}
	}
};
</script>