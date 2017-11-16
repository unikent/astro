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
			activeMenuItem: state => state.menu.active
		}),

		...mapGetters([
			'getBlockMeta',
			'scaleUp'
		]),

		stylesOuter() {
			return {
				transform: this.offset === 0 ?
					'' : `translate3d(0, ${ this.offset + this.current.y }px, 0)`,
				zIndex: this.current.z,
				transition: 'none',
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

		blockMeta() {
			return false;
			// return this.getBlockMeta(this.index, this.region);
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


	methods: {
		...mapMutations([
			'updateBlockMeta',
			'setBlock',
			'collapseSidebar',
			'revealSidebar',
			'updateMenuActive',
			'changeBlock'
		]),

		editBlock() {
			this.$store.dispatch('changeBlock', {
				regionName: this.region,
				sectionName: this.section,
				blockIndex: this.index
			});
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
			}
			this.$bus.$emit('block:hideOverlay', this);
		},
	}
};
</script>
