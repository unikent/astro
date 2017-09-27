<template>
	<div>
		<back-bar :title="title" />
		<template v-if="blocks" v-for="region in blocks">
			<!-- we could put the region title here when we use more than one region -->
			<ul class="validation-errors" v-if="region">
				<template v-for="(block, key) in region">
					<template v-if="errors.indexOf(block.id) !== -1">
						<li class="validation-errors__item warning">
							<i class="el-icon-warning"></i>
							<a href="#" @click="scrollTo(key)" class="validation-errors__link">{{ label(block.definition_name + "-v" + block.definition_version) }}</a>
						</li>
					</template>
				</template>
			</ul>
		</template>
	</div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
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


	methods: {

		...mapMutations([
			'setBlock',
			'updateMenuActive'
		]),

		/**
		@TODO - implement smooth scrolling?
		scrollTo() - simple jump to the specified block in the iframe 'editor-content'
		- note that blocks are identified by their block position and not unique api id.
		- this means as blocks are reordered on the page, the error markers automatically reposition accordingly
		*/
		scrollTo(block_id) {
			var el = document.getElementById('editor-content');
			var block = el.contentWindow.document.getElementById('block_' + block_id);
			// position of the block in the iframe
			var pos = block.getBoundingClientRect();
			// add on Y scroll position to pos.top to make sure the position for the next jump is relative to the current scroll position
			el.contentWindow.scrollTo(0, pos.top + el.contentWindow.scrollY);

			// set the current block
			this.setBlock({ index: block_id, type: 'type' });

			// set the block menu item as active
			this.updateMenuActive('blocks');
		},

		// return the correct human-readable label for the specified block definition
		label: function(name) {
			for (var block in this.$store.state.definition.blockDefinitions) {
				if (block == name) {
					return this.$store.state.definition.blockDefinitions[block].label;
				}
			}
		}
	}

};
</script>
