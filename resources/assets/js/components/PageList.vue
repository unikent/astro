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
			<PageListItem class="item" :site="site" :page="orderedHierarchy"></PageListItem>
		</ul>
	</div>
</template>

<script>
	import PageListItem from './PageListItem.vue';
	import axios from 'axios';

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
				site: pageListData.site_id,
				hierarchy: null,
				edit: null,
				order: 'title'
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
				axios
					.get(`/api/site/structure/${this.site}`)
					.then((response) => {
						this.hierarchy = response.data;
					});
			}
		},

		created() {
			this.fetchData();
		}
	}
</script>

<style>
	.search {
		width: 100%;
	}

	.page-list {
		user-select: none;
		font-size: 14px;
		width: 100%;
		border: 1px solid #e5e5e5;
	}

	.parent-page {
		padding: 0;
	}

	.parent-page > div {
		color: #03a9f4;
	}

	.is-site > div  {
		color: #41b883;
	}

	.parent-page > div,
	li {
		padding: 10px;
		transition: background 300ms ease;
		cursor: default;
	}

	ul {
		list-style: none;
		padding-left: 0;
	}

	li:not(.parent-page):hover,
	.parent-page > div:hover {
		background: #f6f6f6;
	}

	li.add:hover {
		background: none;
	}

	.chevron {
		cursor: pointer;
		width: 12px;
		margin-right: 2px;
		transition: transform .2s ease-out;
	}

	.chevron.open {
		transform: rotate(90deg);
	}

	.children {
		transition: max-height .2s ease-out;
	}

	.collapsed {
		display: none;
	}

	.name {
		display: inline-block;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		vertical-align: middle;
	}

	input {
		font-size: 14px;
	}

	.name,
	.edit-input {
		max-width: calc(100% - 85px);
	}

	.item span:first-child {
		padding: 2px;
	}

	.options {
		opacity: 0;
		display: inline-block;
		vertical-align: middle;
		margin-left: 4px;
	}

	.cog {
		width: 12px;
		display: inline-block;
		vertical-align: middle;
	}

	.options:hover path {
		fill: #555;
	}

	.move {
		cursor: move;
		width: 10px;
		opacity: 0;
	}

	.page-list .btn-group {
		cursor: move;
		opacity: 0;
	}

	.item:hover > div .move,
	.item:hover > div .options {
		opacity: 1;
	}

	.page-list .dropdown-menu {
		font-size: .9rem;
	}
</style>