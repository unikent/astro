<template>
<div>
	<div id="main_content">
		<div id="b-wrapper" ref="container">
			<block
				v-for="(blockData, index) in page.blocks"
				:key="`block-${blockData.id}`"
				:type="blockData.type"
				:index="index"

				:blockData="blockData"

				:scale="meta.scale"
				:sizes="meta.sizes"
				:offsets="meta.offsets"
			>
			</block>
			<div class="b-editable"></div>
			<div class="b-block" style="top: 0; opacity: 0">
				<div class="b-block-options" @click="editBlock">⚙</div>
				<div class="b-block-move">⇅</div>
			</div>
		</div>
	</div>
	<div class="b-handle" :style="meta.handleStyle">⇅</div>
	<div id="b-overlay"></div>
</div>
</template>

<script>
	import Vue from 'vue';
	import Block from './Block.vue';
	import page from '../stubs/page';
	import eventBus from '../libs/event-bus.js';
	import Velocity from 'velocity-animate';

	import { mapState } from 'vuex';

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
				meta : {
					scale: 1,
					sizes: [],
					offsets: [],
					handleStyle: ''
				}
			};
		},

		computed: {
			...mapState([
				'page'
			])
		},

		created() {
			eventBus.$on('block:set_info', block => {
				this.meta.sizes[block.idx] = block.height;

				// all blocks have loaded
				if(this.meta.sizes.length === page.blocks.length) {
					console.log('done');
				}
			});

			eventBus.$on('block:move', index => {
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
			this.handle = document.querySelector('.b-handle');

			this.scaled = false;
			this.current = null;

			this.initEvents();
		},

		methods: {
			editBlock(e) {
				this.$store.dispatch('editBlock', this.current);
			},

			move(e) {
				this.handleStyle = `transform: translateY(${e.pageY - 22}px)`;
			},

			showOverlay(block) {
				this.positionOverlay(block, this.editableBlock, true);
			},

			hideOverlay() {
				this.editableBlock.style.opacity = 0;
			},

			initEvents() {
				eventBus.$on('block:showOverlay', this.showOverlay);
				eventBus.$on('block:hideOverlay', this.hideOverlay);

				document.addEventListener('mousedown', e => {
					switch(e.target) {
						case this.moveEl:
							this.wrapper.style.userSelect =  'none';
							this.overlay.style.pointerEvents =  'auto';

							if(e.button === 0) {
								this.handle.style.opacity = 1;
								this.drag(false, e.clientY);

								eventBus.$emit('block:dragstart', {
									event: e,
									el: this.current
								});

								this.editableBlock.classList.add('hide-drag');
								document.addEventListener('mousemove', this.move);
							}
							break;
						// case this.options:
						// 	if(e.button === 0) {
						// 		eventBus.$emit('block:edit', {});
						// 	}
						// 	break;

						default:
					}
				});

				document.addEventListener('mouseup', e => {
					if(this.scaled) {
						this.wrapper.style.userSelect =  'auto';
						this.handle.style.opacity = 0;
						this.drag(true, e.clientY);

						this.editableBlock.classList.remove('hide-drag');
						document.removeEventListener('mousemove', this.move);
					}
				});
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
				// box.style.left = (pos.left + window.scrollX - minusLeft) + 'px';
				box.style.width = (pos.width + addWidth) + 'px';
				box.style.height = (pos.height + addHeight) + 'px';

				box.style.opacity = 1;

				if(setCurrent) {
					this.current = block;
				}
			},

			resetAfterDrag() {
				this.overlay.style.pointerEvents =  'none';

				const element = page.blocks[this.moved.from];
				page.blocks.splice(this.moved.from, 1);
				page.blocks.splice(this.moved.to, 0, element);

				const size = this.meta.sizes[this.moved.from];
				this.meta.sizes.splice(this.moved.from, 1);
				this.meta.sizes.splice(this.moved.to, 0, size);

				const offset = this.meta.offsets[this.moved.from];
				this.meta.offsets.splice(this.moved.from, 1);
				this.meta.offsets.splice(this.moved.to, 0, offset);

				eventBus.$emit('block:dragstop');
			},

			drag(revert, mouseY) {
				var scroll = window.scrollY;

				if(revert) {
					this.scaled = false;

					var
						scaledOffset = scroll * SCALE_UP,
						offsetPlusScaled = (mouseY * SCALE_UP) - mouseY;

					Velocity(
						document.body,
						'scroll',
						{
							offset: scaledOffset + offsetPlusScaled,
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

				} else {
					this.scaled = true;

					var
						scaledOffset = scroll * SCALE_DOWN,
						offsetMinusScaled = mouseY - (mouseY * SCALE_DOWN);

					this.handle.style.transform = 'translateY(' + (((mouseY + window.scrollY) * SCALE_DOWN) - 22) + 'px)';

					Velocity(
						document.body,
						'scroll',
						{
							offset: scaledOffset - offsetMinusScaled,
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
	}
</script>