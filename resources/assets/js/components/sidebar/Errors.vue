<template>
	<div>
		<back-bar :title="title" />
		<template v-if="blocks" v-for="region in blocks">
			<!-- we could put the region title here when we use more than one region -->			
			<ul class="validation-errors" v-if="region">
				<template  v-for="block in region">
					<template v-if="errors.indexOf(block.id) === -1">
						<li class="validation-errors__item success">
							<i class="el-icon-circle-check"></i>
							<a href="#" class="validation-errors__link">{{ block.definition_name }}</a>
						</li>
					</template>
					<template v-else>
						<li class="validation-errors__item warning">
							<i class="el-icon-warning"></i>
							<a href="#" class="validation-errors__link">{{ block.definition_name }}</a>
						</li>
					</template>
				</template>
			</ul>		
		</template>
	</div>
</template>

<script>
import { mapState } from 'vuex';
import BackBar from 'components/BackBar';

export default {
	name: 'errors',

	props: ['title'],

	computed: {
		errors() {
			return this.$store.state.page.invalidBlocks;
		}, 

		blocks() {
			return this.$store.state.page.pageData.blocks;
		},

		region() {
			return this.$store.state.page.currentRegion;	
		}
	},

	components: {
		BackBar
	},
};
</script>
