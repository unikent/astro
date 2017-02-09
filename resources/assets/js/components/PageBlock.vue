<template>
	<div
		ref="wrap"
		class="b-block-container"
		style="user-select: none;"
		:style="stylesOuter"
	>
		<div
			class="block"
			:style="stylesInner"
		>
			<component :is="currentView" :data="blockData.fields"></component>
		</div>
	</div>
</template>

<script>
	import fieldMarkup from '../stubs/block-markup';
	import fields from 'cms-prototype-blocks';

	var fieldKeys = Object.keys(fieldMarkup);

	function rand(arr) {
		return arr[Math.floor(Math.random() * arr.length)];
	}

	export default {

		name: 'PageBlock',

		props: ['blockData', 'scale', 'sizes', 'index'],

		data() {

			let startValues = {
				y: 0,
				z: 0,
				scale: 1,
				pointer: 'auto',
				shadow: 10,
				transition: 'none',
				scroll: 0
			};

			console.log(this.blockData.type, fields)

			return {
				zoomed: true,
				dragging: false,
				start: Object.assign({}, startValues),
				current: Object.assign({}, startValues),
				size: null,
				prevOver: null,
				currentView: fields[this.blockData.type] ?
					fields[this.blockData.type] :
					{
						template: fieldMarkup[rand(fieldKeys)]
					}
			}
		},

		computed: {
			stylesOuter() {
				return {
					transform: `translate3d(0, ${ this.current.y }px, 0)`,
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

			startDrag(e) {
				if(e.button === 0) {
					this.size = this.$el.getBoundingClientRect();

					this.addTransition(true);
					this.start.y = this.size.top;

					this.dragging = true;
					this.current.y = this.getY(e);

					this.current.z = 20;
					this.current.scale = 1.05;
					this.current.pointer = 'none';
					this.current.shadow = 10;

					this.start.scroll = window.scrollY * 0.4;

					window.addEventListener('scroll', this.addScroll);

					window.addEventListener('mousemove', this.onDrag);
					window.addEventListener('mouseup', this.stopDrag);
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

			addScroll(e) {
				this.current.scroll = window.scrollY - this.start.scroll;
			},

			getY(e) {
				var scale = (1 / 0.4);
				return ((e.clientY - this.start.y - this.size.height / 2) * scale) + this.current.scroll;
			},

			onDrag(e) {
				if(this.dragging) {
					this.current.y = this.getY(e);
				}

				this.onDragOver(e);

				if(e.target === this.prevOver) {
					return;
				}

				if(e.target.classList.contains('block')) {
					this.prevOver = e.target;
				}
			},

			onDragOver(e) {
				if(this.prevOver) {
					var rect = this.prevOver.parentNode.__vue__.size;
					console.dir(next ? 'move' : 'nope');
					var next = (e.clientY - rect.top) / (rect.bottom - rect.top) > .5;
				}
			},

			stopDrag() {
				if(this.dragging) {
					this.dragging = false;
					this.size = this.$el.getBoundingClientRect();
					this.addTransition();

					this.current.scale = this.start.scale;
					this.current.pointer = this.start.pointer;
					this.current.y = 0;
					this.current.shadow = 1;
					this.current.scroll = 0;
					this.prevOver = null;

					window.removeEventListener('mousemove', this.onDrag);
					window.removeEventListener('scroll', this.addScroll);
				}
			}
		},

		mounted() {
			this.size = this.$el.getBoundingClientRect();

			window.emitter.$on('drag', info => {
				if(info.el === this.$el) {
					this.startDrag(info.event, info.el);
				}
			});
		}
	}
</script>