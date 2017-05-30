<template>
	<div
		class="snackbar-container"
		@mouseenter="clearTimer"
		@mouseleave="() => startCloseTimer()"
	>
		<transition name="snackbar-toggle">
			<div v-show="isOpen" class="snackbar">
				<div class="snackbar__text" v-html="message" />
				<div
					v-if="action && action.text && action.run"
					class="snackbar__action"
					@click="action.run"
				>
					<button>{{ action.text }}</button>
				</div>
			</div>
		</transition>
	</div>
</template>

<script>
/* global setTimeout, clearTimeout */

export default {

	data() {
		return {
			isOpen: false,
			action: null,
			message: ''
		};
	},

	methods: {
		open({ message = '', duration = null, action = null }) {
			const newDuration = duration ?
				// average reading speed (CPM) + half a second to notice snackbar
				duration : Math.ceil((message.length * 60000) / 863) + 500;

			this.timer = setTimeout(
				() => {
					this.isOpen = true;
					this.message = message.trim().split('\n').join('<br>');
					this.action = action;
					this.startCloseTimer(newDuration);
				},
				this.isOpen && this.close() ? 500 : 0
			);
		},

		close() {
			this.clearTimer();
			this.isOpen = false;
			return true;
		},

		clearTimer() {
			if(this.timer) {
				clearTimeout(this.timer);
			}
		},

		startCloseTimer(duration = 800) {
			this.timer = setTimeout(this.close, duration);
		}
	}

};
</script>