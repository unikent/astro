<template>
	<div class="block-move-container" :style="offsetStyles">
		<div class="block-move" @mousedown="handleMousedown" :style="styles">
			{{ block.label }}
		</div>
	</div>
</template>

<script>
import { mapMutations } from 'vuex';

/* global document */

export default {
	props: ['block'],

	data() {
		return {
			dragging: false,
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
					)`
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
			'updateWrapperStyle'
		]),

		handleMousedown(e) {
			this.dragging = true;

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

			// fire before mouse moves
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
				Math.max(0, Math.min(e.clientX - this.iframePos.left, this.iframePos.width)),
				Math.max(0, Math.min(e.clientY - this.iframePos.top, this.iframePos.height))
			];

			if(x !== 0 && y !== 0 && x !== this.iframePos.width && y !== this.iframePos.height) {
				this.$store.commit('updateOver', {x, y});
			}
		},

		handleMouseUp() {
			this.destroy();

			this.showIframeOverlay(false);

			this.transition = 'transform 0.3s ease-out';

			const onEnd = () => {
				this.transition = null;
				this.$el.removeEventListener('transitionend', onEnd);
			};

			this.$el.addEventListener('transitionend', onEnd);

			this.dragging = false;
		},

		destroy() {
			document.removeEventListener('mouseup', this.handleMouseUp);
			document.removeEventListener('mousemove', this.handleMouseMove);

			this.updateWrapperStyle({ prop: 'userSelect', value: 'auto' });
		}

	}
};
</script>
