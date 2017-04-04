<style lang="scss">
	.snackbar-container {
		position: fixed;
		overflow: hidden;

		bottom: 0;
		left: 50%;

		transform: translateX(-50%);

		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
		letter-spacing: .06em;

		z-index: 3000;
	}

	.snackbar {
		display: flex;
		align-items: center;

		min-width: 200px;
		max-width: 600px;
		min-height: 48px;

		padding: 14px 24px;
		margin: 4px 4px 8px 4px;

		border-radius: 2px;
		background-color: #3a424a;

		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);

		font-size: 14px;
		text-align: center;
	}

	.snackbar__text {
		color: white;
		line-height: 1.5em;
	}

	.snackbar__action {
		margin-left: auto;
		padding-left: 40px;

		button {
			border: none;
			background: none;

			text-transform: uppercase;

			color: #50bfff;
		}
	}

	.snackbar-toggle-enter-active,
	.snackbar-toggle-leave-active {
		transition: transform .3s ease-out;

		.snackbar__text,
		.snackbar__action {
			opacity: 1;
			transition: opacity .3s ease-out;
		}
	}

	.snackbar-toggle-enter,
	.snackbar-toggle-leave-to {
		transform: translateY(110%);

		.snackbar__text,
		.snackbar__action {
			opacity: 0;
		}
	}
</style>

<template>
	<div
		class="snackbar-container"
		@mouseover="clearTimer"
		@mouseout="startCloseTimer"
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
export default {

	data() {
		return {
			isOpen: false,
			action: null,
			message: ''
		};
	},

	created() {
		this.timer = null;
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