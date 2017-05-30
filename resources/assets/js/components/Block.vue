<template>
	<div
		class="b-block-container"
		:style="stylesOuter"
		@mouseenter="showOverlay"
		@mouseleave="hideOverlay"
		@click="editBlock"
	>
		<div
			class="block"
			:style="stylesInner"
		>
			<component
				v-if="type !== 'placeholder'"
				:is="currentView"
				:fields="blockData.fields"
				:other="this.getData()"
			/>
			<!-- placeholder element -->
			<div v-else class="placeholder-block" />
		</div>
	</div>
</template>

<script>
import blocks from 'cms-prototype-blocks';
import { mapState, mapMutations } from 'vuex';

/* global document */

export default {

	name: 'block',

	props: ['scale', 'index', 'type', 'blockData'],

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
			start: { ...startValues },
			current: { ...startValues },
			size: null,
			prevOver: null,
			currentView: blocks[this.type] ? blocks[this.type] : {
				template: `
					<div class="missing-definition-warning">
						Missing "${this.type}" block type
					</div>
				`
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
			// TODO: animate opacity, not box-shadow, for buttery smooth animation
			return {
				transform: `scale(${this.current.scale})`,
				boxShadow: `rgba(0, 0, 0, 0.2) 0px ${this.current.shadow * (1 / this.scale)}px ${this.current.shadow * (2 / this.scale)}px 0px`,
				transition: this.current.transition
			}
		},

		...mapState({
			blockMeta: state => state.page.blockMeta,
			currentBlockIndex: state => state.definition.currentBlockIndex
		})
	},

	methods: {
		...mapMutations([
			'updateBlockPositions',
			'setBlock'
		]),

		listen() {
			this.$bus.$on('block:dragstart', info => {
				if(info.el === this.$el) {
					this.startDrag(info.event, info.el);
				}
			});

			this.$bus.$on('block:dragstart', () => {
				this.dragging = true;
			});

			this.$bus.$on('block:move', index => {
				const after = index.from <= index.to;

				if(after && this.index > index.from && this.index <= index.to) {
					this.offset = -this.blockMeta.sizes[index.from];
				}
				else if(!after && this.index >= index.to && this.index < index.from) {
					this.offset = this.blockMeta.sizes[index.from];
				}
				else {
					this.offset = 0;
				}

				this.updateBlockPositions({
					type: 'offsets',
					index: this.index,
					value: this.offset
				});
			});

			this.$bus.$on('block:dragstop', () => {
				this.dragging = false;
				this.updateBlockPositions({
					type: 'offsets',
					index: this.index,
					value: 0
				});
			});
		},

		editBlock() {
			if(this.currentBlockIndex !== this.index) {
				this.setBlock({ index: this.index, type: this.type });
			}
		},

		getData() {
			const {_type, _fields, ...other} = this.blockData;
			return other;
		},

		showOverlay() {
			this.$bus.$emit('block:showOverlay', this);
		},

		hideOverlay(e) {
			if(
				!e.relatedTarget ||
				!e.relatedTarget.hasAttribute('class') ||
				e.relatedTarget.getAttribute('class').indexOf('b-block') === -1
			) {
				this.$bus.$emit('block:hideOverlay', this);
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
			};

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

				this.current = {
					...this.current,
					y: 0,
					z: 20,
					scale: 1.05,
					pointer: 'none',
					shadow: 10
				};

				let top = 0;
				// this.sizeCacheAll = [];
				this.centerPoints = {};
				this.lastHover = this.index;

				for(var i = 0; i < this.blockMeta.sizes.length; i++) {
					// this.sizeCacheAll[i] = {
					// 	top,
					// 	height: this.blockMeta.sizes[i],
					// 	mid: top + (this.blockMeta.sizes[i] / 2),
					// 	bottom: top + this.blockMeta.sizes[i]
					// };

					this.centerPoints[
						(top + this.blockMeta.sizes[i] / 2).toFixed(3)
					] = i;

					top += this.blockMeta.sizes[i];
				}

				document.addEventListener('mousemove', this.onDrag);
				document.addEventListener('mouseup', this.stopDrag)
			}
		},

		onDrag(e) {
			this.updateY(e);

			let
				currentIndex = 0,
				offset = this.lastHover && this.blockMeta.offsets[this.lastHover] ?
					this.blockMeta.offsets[this.lastHover] + this.blockMeta.sizes[this.index] : 0;

			for(let size in this.centerPoints) {
				if(this.mouseY - offset > size) {
					currentIndex = this.centerPoints[size];
				}
			}

			if(currentIndex < this.lastHover) {
				offset = this.lastHover && this.blockMeta.offsets[this.lastHover] ?
					this.blockMeta.offsets[this.lastHover] : 0;

				for(let size in this.centerPoints) {
					if(this.mouseY - offset > size) {
						currentIndex = this.centerPoints[size];
					}
				}
			}

			if(this.lastHover !== currentIndex) {
				this.onDragOver(this.mouseY, currentIndex);
			}

			this.lastHover = currentIndex;
		},

		onDragOver(mouseY, idx) {
			// console.log('drag over', {
			// 	from: this.index,
			// 	to: idx
			// });

			this.$bus.$emit('block:move', {
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

		this.updateBlockPositions({
			type: 'sizes',
			index: this.index,
			value: this.size.height
		});

		this.listen();
	},

	beforeDestroy() {
		document.removeEventListener('mousemove', this.onDrag);
		document.removeEventListener('mouseup', this.stopDrag);
	}
};
</script>