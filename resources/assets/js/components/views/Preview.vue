<template>
<div>
	<div
		id="main_content"
	>
		<div id="b-wrapper" ref="wrapper" :style="wrapperStyles">
			<template v-if="page.blocks">
				<block
					v-for="(blockData, index) in page.blocks['main']"
					v-if="blockData"
					:key="`block-${blockData.id}`"
					:type="getBlockType(blockData)"
					:index="index"
					:blockData="blockData"
					:scale="scale"
				/>
			</template>
			<div v-if="loadedBlocks && page.blocks && !Object.keys(page.blocks).length" class="empty-page">
				This page is empty.<br>
				To get started drag some blocks in...
			</div>
			<div
				class="block-overlay" :class="{ 'hide-drag': showBlockOverlayControls }"
				:style="blockOverlayStyles"
			>
				<div class="block-overlay__options" @click="editBlock">
					<Icon :glyph="editIcon" width="20" height="20" />
				</div>
				<div class="block-overlay__delete" @click="removeBlock">
					<Icon :glyph="deleteIcon" width="20" height="20" />
				</div>
				<div ref="move" class="block-overlay__move">
					<Icon :glyph="moveIcon" width="20" height="20" />
				</div>
			</div>
		</div>
	</div>
	<div class="b-handle" :style="handleStyles">
		<Icon :glyph="editIcon" width="20" height="20" />
	</div>
	<div id="b-overlay" :style="overlayStyles"></div>
</div>
</template>

<script>
import { mapState, mapActions, mapMutations } from 'vuex';
import Velocity from 'velocity-animate';

import Block from '../Block';
import Icon from '../Icon';
import editIcon from 'IconPath/pencil.svg';
import deleteIcon from 'IconPath/trash.svg';
import moveIcon from 'IconPath/arrows-vertical.svg';

/* global document, window */

export default {
	name: 'preview-wrapper',

	components: {
		Icon,
		Block
	},

	data() {
		return {
			scale: 1,
			handleStyles: {},
			blockOverlayStyles: {},
			showBlockOverlayControls: false,
			overlayStyles: {},
			wrapperStyles: {}
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
			loadedBlocks: state => state.page.loaded
		})
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
	},

	mounted() {
		this.wrapper = this.$refs.wrapper;
		this.moveEl = this.$refs.move;

		this.scaled = false;
		this.current = null;

		this.initEvents();
	},

	beforeDestroy() {
		document.removeEventListener('mousedown', this.mouseDown);
		document.removeEventListener('mouseup', this.mouseUp);
		document.removeEventListener('mousemove', this.move);
	},

	methods: {
		...mapActions([
			'fetchPage'
		]),

		...mapMutations([
			'setBlock',
			'reorderBlocks',
			'updateBlockPositionsOrder',
			'deleteBlock',
		]),

		editBlock() {
			const { index, type } = this.current;
			this.setBlock({ index, type });
		},

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
				this.positionOverlay(block, true);
			}
		},

		hideOverlay(block) {
			if(block !== this.current) {
				this.updateStyles('blockOverlay', 'opacity', 0);
			}
		},

		initEvents() {
			this.$bus.$on('block:showOverlay', this.showOverlay);
			this.$bus.$on('block:hideOverlay', this.hideOverlay);

			this.$bus.$on('block:move', this.repositionOverlay);

			document.addEventListener('mousedown', this.mouseDown);
			document.addEventListener('mouseup', this.mouseUp);
		},

		mouseDown(e) {
			switch(e.target) {
				case this.moveEl:
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
					break;

				default:
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

			this.updateStyles('blockOverlay', 'transform', `translateY(${(pos.top + window.scrollY - minusTop)}px)`);
			this.updateStyles('blockOverlay', 'left', (pos.left + window.scrollX - minusLeft) + 'px');
			this.updateStyles('blockOverlay', 'width', (pos.width + addWidth) + 'px');
			this.updateStyles('blockOverlay', 'height', (pos.height + addHeight) + 'px');
			this.updateStyles('blockOverlay', 'opacity', 1);

			if(setCurrent) {
				this.current = block;
			}
		},

		resetAfterDrag() {
			this.updateStyles('overlay', 'pointerEvents', 'none');

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

			this.$bus.$emit('block:dragstop');
		},

		drag(revert, mouseY) {
			var scroll = window.scrollY;

			// TODO: Use transforms to scroll page?
			if(revert) {
				this.scaled = false;

				var
					scrollScaleUp = scroll * this.SCALE_UP,
					offsetPlusScaled = (mouseY * this.SCALE_UP) - mouseY;

				Velocity(
					document.body,
					'scroll',
					{
						offset: scrollScaleUp + offsetPlusScaled,
						queue: false,
						duration: 300,
						easing: 'swing'
					}
				);

				Velocity(
					this.wrapper,
					{
						scale: 1,
						queue: false
					},
					{
						duration: 300,
						easing: 'swing',
						complete: this.resetAfterDrag
					}
				);

			}
			else {
				this.scaled = true;

				var
					scrollScaleDown = scroll * this.SCALE_DOWN,
					offsetMinusScaled = mouseY - (mouseY * this.SCALE_DOWN);

				this.updateStyles(
					'handle',
					'transform',
					'translateY(' + (((mouseY + window.scrollY) * this.SCALE_DOWN) - 22) + 'px)'
				);

				Velocity(
					document.body,
					'scroll',
					{
						offset: scrollScaleDown - offsetMinusScaled,
						queue: false,
						duration: 300,
						easing: 'swing'
					}
				);

				Velocity(
					this.wrapper,
					{
						scale: this.SCALE_DOWN,
						queue: false
					},
					{
						duration: 300,
						easing: 'swing'
					}
				);
			}
		},

		updateStyles(dataName, prop, value) {
			this[`${dataName}Styles`] = {
				...this[`${dataName}Styles`],
				[prop]: value
			};
		},

		getBlockType(block) {
			return (
				Object.keys(block).length === 0 ?
				'placeholder' :
				`${block.definition_name}-v${block.definition_version}`
			);
		}
	}
};
</script>