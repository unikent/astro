<template>
	<div id="js-app">
		<!-- <div class="row">
			<div class="col-5 offset-7">
				<label class="">Sort by</label>
				<select class="form-control form-control-sm">
					<option>Title</option>
					<option>Date modified</option>
					<option>Date created</option>
				</select>
			</div>
		</div>

		<div class="form-group search">
			<label for="search" class="sr-only">Search</label>
			<div class="input-group input-group-md">
				<input type="search" class="form-control" placeholder="Search... " autocomplete="off" value="">
				<span class="input-group-btn">
					<button type="submit" class="btn btn-accent btn-icon kf-search active" aria-label="Search"><span class="sr-only">Search</span></button>
				</span>
			</div>
		</div> -->

		<ul class="page-list">
			<PageListItem class="item" :site="site" :page="orderedHierarchy" :editing="edit"></PageListItem>
		</ul>
	</div>
</template>

<script>
import PageListItem from './PageListItem.vue';
import { Loading } from 'element-ui';

import pageStructure from '../tests/stubs/page-structure.json';

const order = {

	title(hierarchy) {
		return hierarchy;
	},

	modified(hierarchy) {
		return null;
	},

	created(hierarchy) {
		return null;
	}
}

export default {

	data() {
		return {
			site: 1,
			hierarchy: null,
			edit: null,
			order: 'title',
			loading: true
		};
	},

	components: {
		PageListItem
	},

	computed: {
		orderedHierarchy() {
			return order[this.order](this.hierarchy)
		}
	},

	methods: {
		fetchData() {
			// this.$api
			// 	.get(`sites/structure/${this.site}`)
			// 	.then((response) => {
			// 		this.hierarchy = response.data;
			// 		this.loading = false;
			// 		if(this.loadingInstance) {
			// 			this.loadingInstance.close();
			// 		}
			// 	});

			this.hierarchy = pageStructure;

			this.loading = false;
			if(this.loadingInstance) {
				this.loadingInstance.close();
			}
		}
	},

	created() {
		this.fetchData();
		this.$bus.$on('rename-page', (id) => this.edit = id);
	},

	mounted() {
		if(this.loading) {
			this.loadingInstance = Loading.service({
				target: this.$el,
				text: 'Loading...',
				customClass: 'kkjdhgs'
			});
		}
	}
};
</script>