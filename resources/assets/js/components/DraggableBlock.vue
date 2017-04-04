<style>
.block-move-container {
	transition: transform 0.2s ease-out;
}
</style>

<template>
	<div class="block-move-container" :style="offsetStyles">
		<div class="block-move" @mousedown="handleMousedown" :style="styles">
			{{ block.name }}
		</div>
	</div>
</template>

<script>
	export default {
		props: ['block'],

		data() {
			return {
				dragging: false,
				mouseStart: {
					x: 0,
					y: 0
				},
				mouseOffset: {
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
					transform: !this.dragging ? null : `translate3d(${ this.position.x }px, ${ this.position.y }px, 0)`,
					zIndex: 100,
					transition: this.transition
				}
			},
			offsetStyles() {
				return {
					transform: !this.dragging ? null : `translate3d(${ -this.offsetCenter.x }px, ${ -this.offsetCenter.y }px, 0)`
				}
			}
		},

		beforeDestroy() {
			this.cleanUp();
		},

		mounted() {
			this.iframe = document.querySelector('iframe');
		},

		methods: {
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

				this.mouseOffset = {
					x: this.mouseStart.x - x,
					y: this.mouseStart.y - y
				};

				this.offsetCenter = {
					x: (w / 2) - this.mouseOffset.x,
					y: (h / 2) - this.mouseOffset.y
				};

				this.iframePos = this.iframe.getBoundingClientRect();

				this.handleMove(e);

				document.addEventListener('mousemove', this.handleMove);
				document.addEventListener('mouseup', this.handleMouseUp);

				document.querySelector('.iframe-overlay').style.position = 'absolute';
				document.body.style.userSelect = 'none';
			},

			handleMove(e) {
				this.position = {
					x: e.clientX - this.mouseStart.x,
					y: e.clientY - this.mouseStart.y
				};

				let [x, y] = [
					Math.max(0, Math.min(e.clientX - this.iframePos.left, this.iframePos.width)),
					Math.max(0, Math.min(e.clientY - this.iframePos.top, this.iframePos.height))
				];

				if(x !== 0 && y !== 0 && x !== this.iframePos.width && y !== this.iframePos.height) {
					this.$store.dispatch('updateOver', {x, y});
				}
			},

			handleMouseUp() {
				this.cleanUp();
				document.querySelector('.iframe-overlay').style.position = 'static';

				this.transition = 'transform 0.3s ease-out';

				const onEnd = () => {
					this.transition = null;
					this.$el.removeEventListener('transitionend', onEnd);
				};

				this.$el.addEventListener('transitionend', onEnd);

				this.dragging = false;
			},

			cleanUp() {
				document.removeEventListener('mouseup', this.handleMouseUp);
				document.removeEventListener('mousemove', this.handleMove);
				document.body.style.userSelect = 'auto';
			}

		}
	}
</script>
