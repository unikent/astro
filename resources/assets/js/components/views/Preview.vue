<template>
<div>
	<div id="b-wrapper" ref="wrapper" :style="wrapperStyles">
		<component :is="layout" />
		<div
			class="block-overlay" :class="{
				'hide-drag': showBlockOverlayControls,
				'block-overlay--hidden': overlayHidden
			}"
			:style="blockOverlayStyles"
		>
			<div class="block-overlay__delete" @click="removeBlock">
				<Icon :glyph="deleteIcon" width="20" height="20" />
			</div>
			<div ref="move" class="block-overlay__move">
				<Icon :glyph="moveIcon" width="20" height="20" />
			</div>
		</div>
	</div>
	<div class="b-handle" :style="handleStyles">
		<Icon :glyph="moveIcon" width="20" height="20" />
	</div>
	<div id="b-overlay" :style="overlayStyles"></div>
	<resize-shim :onResize="onResize" />
</div>
</template>

<script>
import { mapState, mapActions, mapMutations } from 'vuex';
import _ from 'lodash';
import imagesLoaded from 'imagesLoaded';

import Icon from '../Icon';
import editIcon from 'IconPath/pencil.svg';
import deleteIcon from 'IconPath/trash.svg';
import moveIcon from 'IconPath/arrows-vertical.svg';
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
			showBlockOverlayControls: false,
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
			page: state => state.page.pageData,
			blockMeta: state => state.page.blockMeta,
			pageScale: state => state.page.pageScale,
			loadedBlocks: state => state.page.loaded,
			currentLayout: state => state.page.currentLayout,
			layoutVersion: state => state.page.currentLayoutVersion
		}),

		layout() {
			return this.currentLayout ?
				layouts[`${this.currentLayout}-v${this.layoutVersion}`] : null;
		}
	},

	created() {
		this.fetchPage(this.$route.params.site_id);

		this.$bus.$on('block:move', index => {
			this.moved = index;
		});

		this.SCALE_DOWN = this.pageScale;
		this.SCALE_UP = 1 / this.SCALE_DOWN;

		this.editIcon = editIcon;
		this.deleteIcon = deleteIcon;
		this.moveIcon = moveIcon;

		this.onKeyDown = onKeyDown(undoStackInstance);
		this.onKeyUp = onKeyUp(undoStackInstance);

		document.addEventListener('keydown', this.onKeyDown);
		document.addEventListener('keyup', this.onKeyUp);

		this.cancelClicks = (e) => {
			if(findParent('a', e.target, false, e.ctrlKey)) {
				e.preventDefault();
			}
		};

		this.onResize = _.throttle(() => {
			if(this.current) {
				this.positionOverlay(this.current);
			}
		}, 50, { trailing: true });

		document.addEventListener('click', this.cancelClicks);
	},

	destroyed() {
		document.removeEventListener('keydown', this.onKeyDown);
		document.removeEventListener('keyup', this.onKeyUp);
		document.removeEventListener('click', this.cancelClicks);
		document.removeEventListener('mousedown', this.mouseDown);
		document.removeEventListener('mouseup', this.mouseUp);
		document.removeEventListener('mousemove', this.move);
		win.removeEventListener('resize', this.onResize);
	},

	mounted() {
		this.wrapper = this.$refs.wrapper;
		this.moveEl = this.$refs.move;

		this.scaled = false;
		this.current = null;

		this.initEvents();
	},

	methods: {
		...mapActions([
			'fetchPage'
		]),

		...mapMutations([
			'reorderBlocks',
			'updateBlockPositionsOrder',
			'deleteBlock',
		]),

		removeBlock() {
			const { index } = this.current;
			this.deleteBlock({ index });
			this.hideOverlay();
		},

		move(e) {
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

		initEvents() {
			this.$bus.$on('block:showOverlay', this.showOverlay);
			this.$bus.$on('block:hideOverlay', this.hideOverlay);
			this.$bus.$on('block:move', this.repositionOverlay);

			this.$bus.$on('block:updateOverlay', this.updateOverlay);

			document.addEventListener('mousedown', this.mouseDown);
			document.addEventListener('mouseup', this.mouseUp);

			win.addEventListener('resize', this.onResize);
		},

		mouseDown(e) {
			if(findParent(this.moveEl, e.target, true)) {
				this.updateStyles('wrapper', 'userSelect', 'none');
				this.updateStyles('overlay', 'pointerEvents', 'auto');

				if(e.button === 0) {
					this.updateStyles('handle', 'opacity', 1);
					this.drag(false, e.clientY);

					this.$bus.$emit('block:dragstart', {
						event: e,
						el: this.current.$el
					});

					this.showBlockOverlayControls = true;
					document.addEventListener('mousemove', this.move);
				}
			}
		},

		mouseUp(e) {
			if(this.scaled) {
				this.updateStyles('wrapper', 'userSelect', 'auto');
				this.updateStyles('handle', 'opacity', 0);
				this.drag(true, e.clientY);

				this.showBlockOverlayControls = false;
				document.removeEventListener('mousemove', this.move);
			}
		},

		repositionOverlay(data) {
			let size = 0;

			if(data.to > data.from) {
				for(let i = 0; i <= data.to; i++) {
					size += this.blockMeta.sizes[i];
				}
				size -= this.blockMeta.sizes[data.from];
			}
			else {
				for(let i = 0; i < data.to; i++) {
					size += this.blockMeta.sizes[i];
				}
			}

			this.overlayHidden = false;
			this.updateStyles('blockOverlay', 'transform', `translateY(${(size)}px)`);
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

			this.updateStyles('blockOverlay', 'transform', `translateY(${(pos.top + window.scrollY - minusTop)}px)`);
			this.updateStyles('blockOverlay', 'left', (pos.left + window.scrollX - minusLeft) + 'px');
			this.updateStyles('blockOverlay', 'width', (pos.width + addWidth) + 'px');
			this.updateStyles('blockOverlay', 'height', (pos.height + addHeight) + 'px');

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
					value: this.page.blocks['main'][this.moved.from]
				});

				this.updateBlockPositionsOrder({
					type:  'sizes',
					from:  this.moved.from,
					to:    this.moved.to,
					value: this.blockMeta.sizes[this.moved.from]
				});

				this.updateBlockPositionsOrder({
					type:  'offsets',
					from:  this.moved.from,
					to:    this.moved.to,
					value: this.blockMeta.offsets[this.moved.from]
				});
			}

			this.$bus.$emit('block:dragstop');
		},

		drag(revert, mouseY) {
			var scroll = window.scrollY;

			if(revert) {
				this.scaled = false;

				const
					scrollScaleUp = scroll * this.SCALE_UP,
					offsetPlusScaled = (mouseY * this.SCALE_UP) - mouseY;

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
				this.scaled = true;

				const
					scrollScaleDown = scroll * this.SCALE_DOWN,
					offsetMinusScaled = mouseY - (mouseY * this.SCALE_DOWN);

				this.updateStyles(
					'handle',
					'transform',
					'translateY(' + (((mouseY + window.scrollY) * this.SCALE_DOWN) - 22) + 'px)'
				);

				smoothScrollTo({ y: scrollScaleDown - offsetMinusScaled });

				this.wrapperStyles = Object.assign(this.wrapperStyles, {
					transform: `scale(${this.SCALE_DOWN})`,
					transition: 'transform 0.3s ease-out'
				});
			}
		},

		updateStyles(dataName, prop, value) {
			this[`${dataName}Styles`] = {
				...this[`${dataName}Styles`],
				[prop]: value
			};
		}
	}
};
</script>