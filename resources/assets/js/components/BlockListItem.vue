<template>
	<div class="block-move-container" :style="offsetStyles">
		<div class="block-move" @mousedown="handleMousedown" :style="styles">
			{{ block.label }}
			<span v-if="canDrop" style="float: right">Drop to add</span>
		</div>
	</div>
</template>

<script>
import { mapMutations } from 'vuex';
import { uuid } from 'classes/helpers';

/* global document */

export default {
	props: ['block'],

	data() {
		return {
			dragging: false,
			canDrop: false,
			dropped: false,
			mouseStart: {
				x: 0,
				y: 0
			},
			offsetCenter: {
				x: 0,
				y: 0
			},
			position: {
				x: 0,
				y: 0
			},
			transition: null
		}
	},

	computed: {
		styles() {
			return {
				transform: !this.dragging ?
					null : `translate3d(
						${ this.position.x }px,
						${ this.position.y }px,
						0
					)`,
				transition: this.transition
			};
		},
		offsetStyles() {
			return {
				transform: !this.dragging ?
					null : `translate3d(
						${ -this.offsetCenter.x }px,
						${ -this.offsetCenter.y }px,
						0
					)`,
				transitionDuration: `${this.dropped ? 0 : 0.2}s`
			};
		}
	},

	beforeDestroy() {
		this.destroy();
	},

	mounted() {
		this.iframe = document.querySelector('iframe');
	},

	methods: {
		...mapMutations([
			'showIframeOverlay',
			'updateWrapperStyle',
			'addBlock'
		]),

		handleMousedown(e) {
			this.dragging = true;
			this.dropped = false;

			const {
				left: x,
				top: y,
				width: w,
				height: h
			} = this.$el.getBoundingClientRect();

			this.mouseStart = {
				x: e.clientX,
				y: e.clientY
			};

			this.offsetCenter = {
				x: (w / 2) - (this.mouseStart.x - x),
				y: (h / 2) - (this.mouseStart.y - y)
			};

			this.iframePos = this.iframe.getBoundingClientRect();

			// fire once before mouse moves
			this.handleMouseMove(e);

			document.addEventListener('mousemove', this.handleMouseMove);
			document.addEventListener('mouseup', this.handleMouseUp);

			this.showIframeOverlay(true);
			this.updateWrapperStyle({ prop: 'userSelect', value: 'none' });
		},

		handleMouseMove(e) {
			this.position = {
				x: e.clientX - this.mouseStart.x,
				y: e.clientY - this.mouseStart.y
			};

			let [x, y] = [
				e.clientX - this.iframePos.left,
				e.clientY - this.iframePos.top,
			];

			let [normX, normY] = [
				Math.max(0, Math.min(x, this.iframePos.width)),
				Math.max(0, Math.min(y, this.iframePos.height))
			];

			if(x >= 0 && y >= 0 && x <= this.iframePos.width && y <= this.iframePos.height) {
				this.$store.commit('updateOver', { x: normX, y: normY });
				this.canDrop = true;
			}
			else {
				this.canDrop = false;
			}
		},

		handleMouseUp() {
			this.destroy();

			this.showIframeOverlay(false);

			if(this.canDrop) {
				this.addThisBlockType(this.block);
				this.dropped = true;
			}

			this.transition = `transform ${this.dropped ? 0 : 0.3}s ease-out`;

			this.canDrop = false;

			const onEnd = () => {
				this.transition = null;
				this.dropped = false;
				this.$el.removeEventListener('transitionend', onEnd);
			};

			this.$el.addEventListener('transitionend', onEnd);

			this.dragging = false;
		},

		destroy() {
			document.removeEventListener('mouseup', this.handleMouseUp);
			document.removeEventListener('mousemove', this.handleMouseMove);

			this.updateWrapperStyle({ prop: 'userSelect', value: 'auto' });
		},

		addThisBlockType({ name, version }) {

			const block = {
				definition_name: name,
				definition_version: version,
				id: uuid(),
				fields: {}
			};

			this.addBlock({ block });
		}

	}
};
</script>
