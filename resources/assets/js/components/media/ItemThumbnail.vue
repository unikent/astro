<template>
<div
	class="image-grid__item"
	:title="item.title"
>
	<lazy-img
		v-if="item.type === 'image'"
		class="img-grid"
		:bg="true"
		:src="(item.variants && item.variants['400x400']) || item.url"
		:smallSrc="item.variants && item.variants.base64 || null"
		:on-load="onLoad"
		:on-start="onStart"
	/>
	<item-icon v-else />
	<div class="image-grid__item-overlay"><slot></slot></div>
	<div v-if="enableOptions" class="item-grid__edit">
		<i class="el-icon-info item-grid__edit-button" @click="onEdit(item)"></i>
		<el-dropdown trigger="click" placement="bottom-start">
			<i class="el-icon-more"></i>
			<el-dropdown-menu slot="dropdown">
				<el-dropdown-item>
					<a :href="item.url" target="_blank" class="media-item__download-link">Download</a>
				</el-dropdown-item>
				<el-dropdown-item
					v-for="option in options"
					:key="option.text"
					:divided="option.divided"
					@click.native="() => option.action(item)"
				>
					<div >{{ option.text }}</div>
				</el-dropdown-item>
			</el-dropdown-menu>
		</el-dropdown>
	</div>
</div>
</template>

<script>
import LazyImg from 'components/LazyImage';
import ItemIcon from './ItemIcon';
import Icon from 'components/Icon';

export default {
	name: 'ItemThumbnail',

	props: {
		item: {
			type: Object,
			required: true
		},
		enableOptions: {
			type: Boolean,
			default: true
		},
		options: {
			default: () => []
		},
		onEdit: {
			default: () => {}
		}
	},

	components: {
		LazyImg,
		ItemIcon,
		Icon
	},

	data() {
		return {
			hideImg: false
		}
	},

	methods: {
		onStart() {
			this.hideImg = false;
		},
		onLoad() {
			this.hideImg = true;
		}
	}
};
</script>