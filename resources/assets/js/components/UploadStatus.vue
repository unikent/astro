<style lang="scss">
.upload-progress-wrapper {
	font-size: 13px;
	position: fixed;
	transform: translateZ(0);
	bottom: 0;
	right: 0;
	overflow: hidden;
}
.upload-progress {
	position: relative;
	font-size: 13px;
	width: 200px;
	margin: 20px;
	/* transform: translateY(140%); */
	transition: transform .3s ease-out;
	cursor: pointer;
}
.upload-progress .el-card__body {
	padding: 10px;
	text-align: center;
}
.small {
	font-size: 11px;
	text-align: left;
	margin-left: 10px;
}
.dot {
	background-color: #de5050;
	border-radius: 6px;
	color: #fff;
	display: inline-block;
	height: 6px;
	width: 6px;
}
.iconnn {
	position: absolute;
	top: 8px;
	right: 10px;
	transform: scale(-1, 1);
	fill: #888;
	cursor: pointer;
}
</style>
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