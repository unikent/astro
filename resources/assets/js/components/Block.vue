<template>
	<div
		class="b-block-container"
		:style="stylesOuter"
		:id="blockIdentifier"
		@mouseenter="showHoverOverlay"
		@mouseleave="hideHoverOverlay"
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
			/>
			<!-- placeholder element -->
			<div v-else class="placeholder-block" />
		</div>
	</div>
</template>

<script>
import { blocks } from 'helpers/themeExports';
import { mapState, mapGetters, mapMutations } from 'vuex';
import imagesLoaded from 'imagesloaded';
import { disableForms } from 'helpers/dom';

export default {

	name: 'block',

	props: {

		// The index of the block in its section
		index: {
			type: Number,
			required: true
		},

		// The name of the region containing the block
		region: {
			type: String,
			required: true
		},

		// The index in its region of the section containing the block
		section: {
			type: Number,
			required: true
		},

		// The type of the block
		type: {
			type: String
		},

		// The block data, including fields
		blockData: {
			type: Object,
			required: true
		},

		// The name of the section containing the block
		sectionName: {
			type: String,
			required: true
		}

	},
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
			'getBlockMeta'
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
				boxShadow: `rgba(0, 0, 0, 0.2) 0px ${this.current.shadow}px ${this.current.shadow * 2}px 0px`,
				transition: this.current.transition
			}
		},

		offset() {
			return 0;
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
				section: this.section,
				type: 'size',
				value: this.size.height
			});
		});

		disableForms(this.$el);
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
				sectionName: this.sectionName,
				blockIndex: this.index
			});

			this.$bus.$emit('block:showSelectedOverlay', this);
		},

		showHoverOverlay() {
			this.$bus.$emit('block:showHoverOverlay', this);
		},

		hideHoverOverlay(e) {
			if(
				e.relatedTarget !== null ||
				(
					e.relatedTarget && (
						!e.relatedTarget.hasAttribute('class') ||
						e.relatedTarget.getAttribute('class').indexOf('b-block') === -1
					)
				)
			) {
				this.$bus.$emit('block:hideHoverOverlay', this);
			}
		}
	}
};
</script>
