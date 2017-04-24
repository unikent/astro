<template>
<div>
	<div
		v-for="(region, name) in page.regions"
		:id="name"
	>
		<div id="b-wrapper">
			<block
				v-for="(blockData, index) in region"
				:key="`block-${blockData.id}`"
				:type="blockData.type"
				:index="index"
				:blockData="blockData"
				:scale="scale"
			>
			</block>
			<div class="b-block">
				<div class="b-block-options" @click="editBlock">⚙</div>
				<div class="b-block-move">⇅</div>
			</div>
		</div>
	</div>
	<div class="b-handle" :style="blockInfo.handleStyle">⇅</div>
	<div id="b-overlay"></div>
</div>
</template>

<script>
import Block from '../Block.vue';
import Velocity from 'velocity-animate';

import { mapState } from 'vuex';

/* global document, window */

const
	SCALE_DOWN = 0.4,
	SCALE_UP = 1 / SCALE_DOWN;

export default {
	name: 'wrapper',

	components: {
		Block
	},

	data() {
		return {
			scale: 1,
			handleStyle: {}
		};
	},

	computed: {
		...mapState([
			'page',
			'over',
			'blockInfo'
		])
	},

	created() {
		this.$bus.$on('block:move', index => {
			this.moved = index;
		});
	},

	mounted() {
		this.wrapper = document.querySelector('#b-wrapper');
		this.overlay = document.querySelector('#b-overlay');

		this.editable = document.querySelector('.b-editable');
		this.editableBlock = document.querySelector('.b-block');
		this.options = document.querySelector('.b-block-options');
		this.moveEl = document.querySelector('.b-block-move');

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
		editBlock() {
			this.$store.dispatch('editBlock', this.current);
		},

		move(e) {
			this.handleStyle.transform = `translateY(${e.pageY - 22}px)`;
		},

		showOverlay(block) {
			if(block !== this.current) {
				this.positionOverlay(block, this.editableBlock, true);
			}
		},

		hideOverlay(block) {
			if(block !== this.current) {
				this.editableBlock.style.opacity = 0;
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
					this.wrapper.style.userSelect =  'none';
					this.overlay.style.pointerEvents =  'auto';

					if(e.button === 0) {
						this.handleStyle.opacity = 1;
						this.drag(false, e.clientY);

						this.$bus.$emit('block:dragstart', {
							event: e,
							el: this.current.$el
						});

						this.editableBlock.classList.add('hide-drag');
						document.addEventListener('mousemove', this.move);
					}
					break;

				default:
			}
		},

		mouseUp(e) {
			if(this.scaled) {
				this.wrapper.style.userSelect =  'auto';
				this.handleStyle.opacity = 0;
				this.drag(true, e.clientY);

				this.editableBlock.classList.remove('hide-drag');
				document.removeEventListener('mousemove', this.move);
			}
		},

		repositionOverlay(data) {
			let size = 0;

			if(data.to > data.from) {
				for(let i = 0; i <= data.to; i++) {
					size += this.blockInfo.sizes[i];
				}
				size -= this.blockInfo.sizes[data.from];
			}
			else {
				for(let i = 0; i < data.to; i++) {
					size += this.blockInfo.sizes[i];
				}
			}

			// console.log(size, this.overlay);

			this.editableBlock.style.transform = `translateY(${(size)}px)`;
		},

		positionOverlay(block, box, setCurrent) {
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

			box.style.transform = `translateY(${(pos.top + window.scrollY - minusTop)}px)`;
			box.style.left = (pos.left + window.scrollX - minusLeft) + 'px';
			box.style.width = (pos.width + addWidth) + 'px';
			box.style.height = (pos.height + addHeight) + 'px';

			box.style.opacity = 1;

			if(setCurrent) {
				this.current = block;
			}
		},

		resetAfterDrag() {
			this.overlay.style.pointerEvents =  'none';

			this.$store.dispatch('reorderBlocks', {
				from:  this.moved.from,
				to:    this.moved.to,
				value: this.page.regions['main_content'][this.moved.from]
			});

			this.$store.dispatch('updateBlockDataOrder', {
				type:  'sizes',
				from:  this.moved.from,
				to:    this.moved.to,
				value: this.blockInfo.sizes[this.moved.from]
			});

			this.$store.dispatch('updateBlockDataOrder', {
				type:  'offsets',
				from:  this.moved.from,
				to:    this.moved.to,
				value: this.blockInfo.offsets[this.moved.from]
			});

			this.$bus.$emit('block:dragstop');
		},

		drag(revert, mouseY) {
			var scroll = window.scrollY;

			// TODO: don't actually scroll, just use transforms to scroll page?

			if(revert) {
				this.scaled = false;

				var
					scrollScaleUp = scroll * SCALE_UP,
					offsetPlusScaled = (mouseY * SCALE_UP) - mouseY;

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
					scrollScaleDown = scroll * SCALE_DOWN,
					offsetMinusScaled = mouseY - (mouseY * SCALE_DOWN);

				this.handleStyle.transform = 'translateY(' + (((mouseY + window.scrollY) * SCALE_DOWN) - 22) + 'px)';

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
						scale: SCALE_DOWN,
						queue: false
					},
					{
						duration: 300,
						easing: 'swing'
					}
				);
			}
		}
	}
};
</script>