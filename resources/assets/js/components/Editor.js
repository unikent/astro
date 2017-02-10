import Vue from 'vue';
import Velocity from 'velocity-animate';
import eventBus from '../libs/event-bus.js';

// TODO: port to vue.js (should be easy enough)

export default class Editor {

	constructor() {
		this.overlay = null;
		this.editable = null;
		this.editableBlock = null;
		this.dragger = null;
		this.scaled = false;
		this.current = null;

		this.init();
		this.initEvents();
	}

	init() {
		this.overlay = document.createElement('div');
		this.overlay.setAttribute('id', 'b-overlay');

		this.editable = document.createElement('div');
		this.editable.setAttribute('class', 'b-editable');

		this.editableBlock = document.createElement('div');
		this.editableBlock.setAttribute('class', 'b-block');

		this.options = document.createElement('div');
		this.options.setAttribute('class', 'b-block-options');

		this.options.innerHTML = '⚙';

		this.moveEl = document.createElement('div');
		this.moveEl.setAttribute('class', 'b-block-move');

		this.moveEl.innerHTML = '⇅';

		this.editableBlock.appendChild(this.options);
		this.editableBlock.appendChild(this.moveEl);

		this.handle = document.createElement('div');
		this.handle.setAttribute('class', 'b-handle');

		let info = document.querySelector('html').getBoundingClientRect();

		this.handle.innerHTML = '⇅';

		this.wrapper = document.querySelector('#b-wrapper');
		document.body.appendChild(this.handle);
		this.wrapper.appendChild(this.editable);
		this.wrapper.appendChild(this.editableBlock);

		document.body.appendChild(this.overlay);
	}

	move(e) {
		this.handle.style.transform = 'translateY(' + (e.pageY - 22) + 'px)';
	}

	initEvents() {
		document.querySelectorAll('.b-block-container').forEach((block) => {
			block.setAttribute('data-block', true);
			block.addEventListener('mouseover', () => {
				this.positionOverlay(block, this.editableBlock, true)
			});
			block.addEventListener('mouseout', (e) => {
				if(
					!e.relatedTarget ||
					!e.relatedTarget.hasAttribute('class') ||
					e.relatedTarget.getAttribute('class').indexOf('b-block') === -1
				) {
					this.editableBlock.style.opacity = 0;
				}
			});
		});

		var links = document.querySelectorAll(`
			#main_content a,
			#main_content h3,
			#main_content h2,
			#main_content li,
			#main_content p
		`);

		links.forEach((el) => {
			el.setAttribute('contenteditable', true);
			el.addEventListener('mouseover', () => this.positionOverlay(el, this.editable));
			el.addEventListener('mouseout', (e) => {
				if(!e.relatedTarget || !e.relatedTarget.hasAttribute('class') ||
					e.relatedTarget.getAttribute('class').indexOf('b-editable') === -1) {
					this.editable.style.opacity = 0;
				}
			});
			el.addEventListener('focus', (e) => {
				if(!e.relatedTarget || !e.relatedTarget.hasAttribute('class') ||
					e.relatedTarget.getAttribute('class').indexOf('b-editable') === -1) {
					this.editable.style.opacity = 0;
				}
			});
		});

		const moveFunc = this.move.bind(this);

		document.addEventListener('mousedown', e => {
			switch(e.target) {
				case this.moveEl:
					this.wrapper.style.userSelect =  'none';
					this.overlay.style.pointerEvents =  'auto';

					if(e.button === 0) {
						this.handle.style.opacity = 1;
						this.drag(false, e.clientY);

						eventBus.$emit('drag', {
							event: e,
							el: this.current
						});

						this.editableBlock.classList.add('hide-drag');
						document.addEventListener('mousemove', moveFunc);
					}
					break;
				case this.options:
					if(e.button === 0) {
						eventBus.$emit('block:edit', {});
					}
					break;

				default:
			}
		});

		document.addEventListener('mouseup', e => {
			this.wrapper.style.userSelect =  'auto';

			if(this.scaled) {
				this.handle.style.opacity = 0;
				this.drag(true, e.clientY);

				this.editableBlock.classList.remove('hide-drag');
				document.removeEventListener('mousemove', moveFunc);
			}
		});
	}

	positionOverlay(el, box, setCurrent) {
		var
			pos = el.getBoundingClientRect(),
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

		box.style.top = (pos.top + window.scrollY - minusTop) + 'px';
		box.style.left = (pos.left + window.scrollX - minusLeft) + 'px';
		box.style.width = (pos.width + addWidth) + 'px';
		box.style.height = (pos.height + addHeight) + 'px';

		box.style.opacity = 1;

		if(setCurrent) {
			this.current = el;
		}
	}

	drag(revert, mouseY) {
		var scroll = window.scrollY;

		if(revert) {
			this.scaled = false;

			var
				scaledOffset = scroll * 2.5,
				offsetPlusScaled = (mouseY * 2.5) - mouseY;

			Velocity(
				document.body,
				'scroll', {
					offset: scaledOffset + offsetPlusScaled,
					queue: false,
					duration: 300,
					easing: 'swing'
				}
			);

			var self = this;

			Velocity(this.wrapper, {
				scale: 1,
				queue: false
			}, {
				duration: 300,
				easing: 'swing',
				complete: () => {
					this.overlay.style.pointerEvents =  'none';
				}
			});

		} else {
			this.scaled = true;

			var
				scaledOffset = scroll * 0.4,
				offsetMinusScaled = mouseY - (mouseY * 0.4);

			this.handle.style.transform = 'translateY(' + (((mouseY + window.scrollY) * 0.4) - 22) + 'px)';

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

			Velocity(this.wrapper, {
				scale: 0.4,
				queue: false
			}, {
				duration: 300,
				easing: 'swing'
			});
		}
	}

}