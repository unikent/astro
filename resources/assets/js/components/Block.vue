<template>
	<div
		class="b-block-container"
		:style="stylesOuter"
		:id="blockIdentifier"
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
				:index="this.index"
			/>
			<!-- placeholder element -->
			<div v-else class="placeholder-block" />
		</div>
	</div>
</template>

<script>
import _ from 'lodash';
import blocks from 'cms-prototype-blocks';
import { mapState, mapGetters, mapMutations } from 'vuex';
import imagesLoaded from 'imagesloaded';

/* global document */

export default {

	name: 'block',

	props: ['index', 'region', 'section', 'type', 'blockData'],

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
			}
		}
	},

	computed: {

		...mapState({
			currentRegion: state => state.page.currentRegion,
			currentBlockIndex: state => state.definition.currentBlockIndex,
			draggingBlocks: state => state.page.dragging,
			allBlockMeta: state => state.page.blockMeta.blocks,
			activeMenuItem: state => state.menu.active
		}),

		...mapGetters([
			'getBlockMeta',
			'scaleUp'
		]),

		stylesOuter() {
			return {
				transform: !this.draggingBlocks && this.offset === 0 ?
					'' : `translate3d(0, ${ this.offset + this.current.y }px, 0)`,
				zIndex: this.current.z,
				transition: this.current.y === 0 && this.draggingBlocks ? this.current.transition : 'none',
				pointerEvents: this.current.pointer
			}
		},

		stylesInner() {
			// TODO: animate opacity, not box-shadow, for buttery smooth animation
			return {
				transform: `scale(${this.current.scale})`,
				boxShadow: `rgba(0, 0, 0, 0.2) 0px ${this.current.shadow * this.scaleUp()}px ${this.current.shadow * this.scaleUp() * 2}px 0px`,
				transition: this.current.transition
			}
		},

		isDragging() {
			return this.getBlockMeta(this.index, this.region, 'dragging');
		},

		blockMeta() {
			return this.getBlockMeta(this.index, this.region);
		},

		blockSizes() {
			return this.allBlockMeta[this.region].map(block => block.size);
		},

		offset() {
			return this.blockMeta.offset;
		},

		blockIdentifier() {
			return 'block_' + this.index;
		}
	},

	watch: {
		isDragging(dragging) {
			dragging ? this.startDrag() : this.endDrag();
		}
	},

	mounted() {
		imagesLoaded(this.$el, () => {
			this.size = this.$el.getBoundingClientRect();

			this.updateBlockMeta({
				index: this.index,
				region: this.region,
				type: 'size',
				value: this.size.height
			});
		});
	},

	beforeDestroy() {
		document.removeEventListener('mousemove', this.onDrag);
	},

	methods: {
		...mapMutations([
			'updateBlockMeta',
			'setBlock',
			'collapseSidebar',
			'revealSidebar',
			'updateMenuActive'
		]),

		editBlock() {
			// set the block in the sidebar if the user clicks on a different block (including one with the same index but in a different region)
			if(this.currentBlockIndex !== this.index || this.currentRegion !== this.region) {
				this.collapseSidebar();
				this.setBlock({ index: this.index, type: this.type });
			}
			// make sure we get to see the block menu if we're currently seeing the pages menu and a user clicks on any block
			if(this.activeMenuItem!=='blocks') {
				this.updateMenuActive('blocks');
			}
		},

		showOverlay() {
			this.$bus.$emit('block:showOverlay', this);
		},

		hideOverlay(e) {
			if(
				e.relatedTarget !== null ||
				(
					e.relatedTarget && (
						!e.relatedTarget.hasAttribute('class') ||
						e.relatedTarget.getAttribute('class').indexOf('b-block') === -1
					)
				)
			) {
				this.$bus.$emit('block:hideOverlay', this);
			}
		},

		addTransition(dragstart = false) {
			this.current.transition = `
				${dragstart ? '.1s': ''} box-shadow 0.3s ease-out,
				${dragstart ? '.1s': ''} transform 0.3s ease-out
			`;

			const onEnd = () => {
				this.current.transition = this.start.transition;
				this.$el.removeEventListener('transitionend', onEnd);

				if(!dragstart) {
					this.current.z = this.start.z;
				}
			};

			this.$el.addEventListener('transitionend', onEnd);
		},

		updateY(e) {
			const offset = e.pageY * this.scaleUp();

			this.mouseY = offset;
			this.current.y = offset - (this.size.height / 2) - this.start.y;
		},

		startDrag() {
			this.addTransition(true);

			this.start.y = this.$el.offsetTop;
			this.size = this.$el.getBoundingClientRect();

			this.updateBlockMeta({
				index: this.index,
				region: this.region,
				type: 'size',
				value: this.size.height
			});

			this.current = {
				...this.current,
				y: 0,
				z: 20,
				scale: 1.05,
				pointer: 'none',
				shadow: 10
			};

			let top = 0;
			this.centerPoints = [];
			this.lastOver = this.index;
			this.sizes = [...this.blockSizes];

			for(var i = 0; i < this.sizes.length; i++) {
				this.centerPoints[i] = parseFloat((top + this.sizes[i] / 2).toFixed(3));
				top += this.sizes[i];
			}

			document.addEventListener('mousemove', this.onDrag);
			document.addEventListener('mouseup', this.stopDrag);
		},

		onDrag(e) {
			if(this.isDragging) {
				this.updateY(e);

				let currentIndex = 0;

				for(var c = 0; c < this.centerPoints.length; c++) {
					if(this.mouseY > this.centerPoints[c]) {
						currentIndex = c;
					}
				}

				// if we're moving the block backwards and we're beyond the first element
				if(currentIndex < this.lastOver && this.mouseY > this.centerPoints[0]) {
					currentIndex++;
				}

				if(this.lastOver !== currentIndex && currentIndex < this.centerPoints.length) {
					this.onDragOver(this.index, currentIndex)
				}
			}
		},

		onDragOver(from, to) {
			const size = this.sizes.splice(this.lastOver, 1);
			this.sizes.splice(to, 0, size[0]);

			let top = 0;

			this.centerPoints = [];

			for(var i = 0; i < this.sizes.length; i++) {
				this.centerPoints[i] = parseFloat((top + this.sizes[i] / 2).toFixed(3));

				if(i === to) {
					this.offsetY = top - this.start.y;
				}

				top += this.sizes[i];

				let offset = 0;

				const size = this.getBlockMeta(from, this.region).size;

				if(i > from && i <= to) {
					offset = -size;
				}
				else if(i >= to && i < from) {
					offset = size;
				}

				this.updateBlockMeta({
					type: 'offset',
					region: this.region,
					index: i,
					value: offset
				});
			}

			this.lastOver = to;
			this.$bus.$emit('block:move', { from, to });
		},

		stopDrag() {
			document.removeEventListener('mousemove', this.onDrag);
			document.removeEventListener('mouseup', this.stopDrag);

			this.addTransition();

			this.current = {
				..._.cloneDeep(this.start),
				y: this.offsetY
			};

			this.offsetY = 0;
			this.start.y = 0;
			this.prevOver = null;
		},

		endDrag() {
			this.current.y = 0;
		}
	}
};
</script>
