<template>
	<div class="upload-progress-wrapper">
		<div @click="handleClick">
			<el-tooltip content="View status list" placement="top" :open-delay="500">
				<el-card class="upload-progress">
					<span>Upload progress</span>
					<el-progress :percentage="50"></el-progress>
					<div class="small" v-if="failed">
						<span class="dot" /> 1 Failed
					</div>
				</el-card>
			</el-tooltip>
		</div>
	</div>
</template>

<script>
export default {
	data() {
		return {
			failed: false
		};
	},

	mounted() {
		window.fail = (fail) => {
			this.failed = fail;
		}
	},

	methods: {
		handleClick() {
			console.log('open modal');
		}
	}
};
</script>