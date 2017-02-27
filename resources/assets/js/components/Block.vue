<template>
	<div
		class="b-block-container"
		style="user-select: none;"
		:style="stylesOuter"
		@mouseover="showOverlay"
		@mouseout="hideOverlay"
	>
		<div
			class="block"
			:style="stylesInner"
		>
			<component :is="currentView" :index="index" :fields="blockData.fields"></component>
		</div>
	</div>
</template>

<script>
	import fieldMarkup from '../stubs/block-markup';
	import fields from 'cms-prototype-blocks';
	import eventBus from '../libs/event-bus.js';

	var fieldKeys = Object.keys(fieldMarkup);

	function rand(arr) {
		return arr[Math.floor(Math.random() * arr.length)];
	}

	export default {

		props: ['scale', 'sizes', 'index', 'type', 'blockData'],

		data() {

			let startValues = {
				y: 0,
				z: 0,
				scale: 1,
				pointer: 'auto',
				shadow: 0,
				transition: 'transform 0.2s ease-out',
				scroll: 0
			};

			return {
				dragging: false,
				start: Object.assign({}, startValues),
				current: Object.assign({}, startValues),
				size: null,
				prevOver: null,
				currentView: fields[this.type] ? fields[this.type] : {
					template: fieldMarkup[rand(fieldKeys)]
				},
				offset: 0
			}
		},

		computed: {
			stylesOuter() {
				return {
					transform: !this.dragging ?
						'' : `translate3d(0, ${ this.offset + this.current.y }px, 0)`,
					zIndex: this.current.z,
					transition: this.current.y === 0 ? this.current.transition : 'none',
					pointerEvents: this.current.pointer
				}
			},

			stylesInner() {
				return {
					transform: `scale(${this.current.scale})`,
					boxShadow: `rgba(0, 0, 0, 0.2) 0px ${this.current.shadow * (1 / this.scale)}px ${this.current.shadow * (2 / this.scale)}px 0px`,
					transition: this.current.transition
				}
			}
		},

		methods: {
			showOverlay() {
				eventBus.$emit('block:showOverlay', this);
			},

			hideOverlay(e) {
				if(
					!e.relatedTarget ||
					!e.relatedTarget.hasAttribute('class') ||
					e.relatedTarget.getAttribute('class').indexOf('b-block') === -1
				) {
					eventBus.$emit('block:hideOverlay', this.$el);
				}
			},

			addTransition(dragstart) {
				this.current.transition = `
					${dragstart ? '.1s': ''} box-shadow 0.3s ease-out,
					${dragstart ? '.1s': ''} transform 0.3s ease-out
				`;

				const onEnd = () => {
					this.current.transition = 'none';
					this.$el.removeEventListener('transitionend', onEnd);

					if(!dragstart) {
						this.current.z = this.start.z;
					}
				}

				this.$el.addEventListener('transitionend', onEnd);
			},

			updateY(e) {
				const
					scale = (1 / 0.4),
					offset = e.pageY * scale;

				this.mouseY = offset;
				this.current.y = offset - (this.size.height / 2) - this.start.y;
			},

			startDrag(e) {
				if(e.button === 0) {
					this.addTransition(true);

					this.size = this.$el.getBoundingClientRect();
					this.start.y = this.$el.offsetTop;

					this.dragging = true;
					this.current.y = 0;

					this.current.z = 20;
					this.current.scale = 1.05;
					this.current.pointer = 'none';
					this.current.shadow = 10;

					let top = 0;
					this.sizeCache = [];
					this.sizeCache2 = {};
					this.lastHover = null;

					this.sizeCache3 = {};

					for(var i = 0; i < this.sizes.length; i++) {
						this.sizeCache[i] = {
							top,
							height: this.sizes[i],
							mid: top + (this.sizes[i] / 2),
							bottom: top + this.sizes[i]
						};

						this.sizeCache2[(top + this.sizes[i] / 2).toFixed(3)] = i;

						this.sizeCache3[i] = this.sizes[i].toFixed(3);

						top += this.sizes[i];
					}

					// console.log(this.sizeCache3, this.sizeCache2);

					document.addEventListener('mousemove', this.onDrag);
					document.addEventListener('mouseup', this.stopDrag)
				}
			},

			onDrag(e) {
				this.updateY(e);

				let
					currentIndex = 0,
					offset = this.lastHover && this.$parent.meta.offsets[this.lastHover] ?
						this.$parent.meta.offsets[this.lastHover] : 0;

				for(let size in this.sizeCache2) {
					if(this.mouseY - offset > size) {
						currentIndex = this.sizeCache2[size];
					}
				}

				if(this.lastHover !== currentIndex) {
					this.onDragOver(this.mouseY, currentIndex);
				}

				this.lastHover = currentIndex;
			},

			onDragOver(mouseY, idx) {
				eventBus.$emit('block:move', {
					from: this.index,
					to: idx
				});
			},

			stopDrag() {
				this.size = this.$el.getBoundingClientRect();
				this.addTransition();

				this.current.scale = this.start.scale;
				this.current.pointer = this.start.pointer;
				this.current.y = 0;
				this.current.shadow = 1;
				this.current.scroll = 0;
				this.prevOver = null;

				document.removeEventListener('mousemove', this.onDrag);
				document.removeEventListener('mouseup', this.stopDrag);
			}
		},

		mounted() {
			this.size = this.$el.getBoundingClientRect();

			eventBus.$emit('block:set_info', {
				idx: this.index,
				height: this.size.height,
				offset: this.offset
			});

			eventBus.$on('block:dragstart', info => {
				if(info.el === this.$el) {
					this.startDrag(info.event, info.el);
				}
			});

			eventBus.$on('block:dragstart', () => {
				this.dragging = true;
			});

			eventBus.$on('block:move', index => {
				const after = index.from <= index.to;

				if(after && this.index > index.from && this.index <= index.to) {
					this.offset = -this.$parent.meta.sizes[index.from];
				}
				else if(!after && this.index >= index.to && this.index < index.from) {
					this.offset = this.$parent.meta.sizes[index.from];
				}
				else {
					this.offset = 0;
				}

				this.$parent.meta.offsets[this.index] = this.offset;
			});

			eventBus.$on('block:dragstop', () => {
				this.dragging = false;
				this.$parent.meta.offsets[this.index] = 0;
			});
		}
	}
</script>