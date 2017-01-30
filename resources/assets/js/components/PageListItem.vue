<template>
	<li v-if="this.page" :class="{'parent-page': hasChildren, 'is-site': this.page.type && this.page.type.indexOf('site') !== -1}">
		<div :style="leftPadding">

			<svg v-if="this.getLevel !== 0" class="move" viewBox="0 0 128 128">
				<g fill="#bbbbbf">
					<rect height="24" width="24" y="7" x="49"/>
					<rect height="24" width="24" y="52" x="49"/>
					<rect height="24" width="24" y="97" x="49"/>
				</g>
			</svg>

			<svg style="width: 12px" v-if="this.getLevel !== 0 && this.page.type && this.page.type.indexOf('site') !== -1" viewBox="0 0 128 128">
				<path v-if="this.page.type && this.page.type.indexOf('locked') !== -1" d="m100 112h-72c-3.6 0-6-2.4-6-6v-48c0-3.6 2.4-6 6-6 0-19.8 16.2-36 36-36s36 16.2 36 36c3.6 0 6 2.4 6 6v48c0 3.6-2.4 6-6 6zm-36-84c-13.2 0-24 10.8-24 24h48c0-13.2-10.8-24-24-24zm30 36h-60v36h60v-36zm-30 6c3.6 0 6 2.4 6 6v12c0 3.6-2.4 6-6 6s-6-2.4-6-6v-12c0-3.6 2.4-6 6-6z" fill="#999" />
				<path v-else d="m100 52c3.6 0 6 2.4 6 6v48c0 3.6-2.4 6-6 6h-72c-3.6 0-6-2.4-6-6v-48c0-3.6 2.11-5.67 6.5-5.74-2.6-20 2.6-35.5 20.8-43.4 18-7.76 39.1 0.49 47.1 18.4l-11 4.8c-5.4-11.9-19.4-17.3-31.4-12.2-12.1 5.3-16.4 21.2-13.8 31.6zm-6 12h-60v36h60zm-30 6c3.6 0 6 2.4 6 6v12c0 3.6-2.4 6-6 6s-6-2.4-6-6v-12c0-3.6 2.4-6 6-6z" fill="#41b883"/>
			</svg>

			<svg v-if="this.getLevel !== 0 && hasChildren" class="chevron" :class="{'open': open}" @click="toggle" viewBox="0 0 100 100">
				<path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" fill="#444" class="arrow" transform="translate(100, 100) rotate(180)"></path>
			</svg>

			<span class="name" ref="name" v-show="this.$root.edit !== page.id" @click="waitAndEdit" @dblclick="rename">{{page.title}}</span>

			<span v-show="this.$root.edit === page.id">
				<input class="edit-input" ref="input" type="text" @blur="saveEdit" @keyup.13="saveEdit" :value="page.title">
			</span>

			<div class="btn-group options">
				<button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<div class="cog">
						<svg viewBox="0 0 128 128">
							<path d="m55.5 0l-2.8 19.7c-4.2 1.1-8.2 2.8-12 5l-15.9-12-12.1 12.1 12 15.9c-2.2 3.8-3.9 7.8-4.9 12l-19.8 2.8v17l19.7 2.8c1.1 4.2 2.8 8.2 5 12l-12 15.7 12.1 12 15.9-12c3.8 2 7.8 4 12 5l2.8 20h17l2.8-20c4.2-1 8.2-3 12-5l15.7 12 12-12-12-15.7c2-3.8 4-7.8 5-12l20-2.8v-17l-20-2.8c-1-4.2-3-8.2-5-12l12-15.9-12-12.1-15.7 12c-3.8-2.2-7.8-3.9-12-4.9l-2.8-19.8h-17zm8.5 41a23 23 0 0 1 23 23 23 23 0 0 1 -23 23 23 23 0 0 1 -23 -23 23 23 0 0 1 23 -23z" fill="#8e8e8e"/>
						</svg>
					</div>
				</button>
				<div class="dropdown-menu">
					<a class="dropdown-item" href="#" @click="edit">Edit</a>
					<a v-show="isParent && this.getLevel <= 2" class="dropdown-item" href="#" @click="addChild">Add page</a>
					<a v-show="this.getLevel !== 0" class="dropdown-item" href="#" @click="rename">Rename</a>
					<div v-show="this.getLevel !== 0" class="dropdown-divider"></div>
					<a v-show="this.getLevel !== 0" class="dropdown-item" href="#" @click="remove(page)" data-toggle="modal" data-target="#exampleModal">Delete</a>
				</div>
			</div>
		</div>
		<ul class="children" :class="{'collapsed': !open && page.depth !== 0}" v-if="isParent">
			<subpages
				class="item"
				:key="`page-${page.id}`"
				v-for="page in page.children"
				:page="page"
				:site="site">
			</subpages>
		</ul>
	</li>
	<div v-else>Loading..</div>
</template>

<script>
	import Vue from 'vue';

	// https://gist.github.com/jed/982883
	function uuid(a) {
		return a ?
			(a^Math.random() * 16 >> a / 4).toString(16) :
			([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, uuid);
	}

	var timer = null

	export default {
		name: 'subpages',

		props: ['page', 'site'],

		data() {
			return {
				open: false
			}
		},

		computed: {
			isParent() {
				return this.page.children
			},

			hasChildren() {
				return this.page.children && this.page.children.length
			},

			getLevel() {
				var level = -2, parent = this;

				while(parent) {
					level++;
					parent = parent.$parent
				}

				return level;
			},

			leftPadding() {
				return {
					paddingLeft: (this.getLevel * 20) + (this.isParent ? 10 : 0) + 'px'
				}
			}
		},

		methods: {
			toggle() {
				if(this.isParent) {
					this.open = !this.open;
				}
			},

			waitAndEdit() {
				clearTimeout(timer);
				timer = setTimeout(this.edit, 400);
			},

			edit() {
				clearTimeout(timer);
				window.location = `/site/${this.site}/page/${this.page.id}`;
			},

			rename() {
				clearTimeout(timer);

				this.$root.edit = this.page.id;

				var nameWidth = Math.max(50, this.$refs.name.offsetWidth);

				Vue.nextTick(() => {
					var input = this.$refs.input;
					input.style.width = (nameWidth + 4) + 'px';
					input.focus()
				});
			},

			saveEdit() {
				if(this.page.title !== this.$refs.input.value) {
					this.page.title = this.$refs.input.value;

					// update page title in DB
					console.log(this.page.id, this.page.title);
				}

				this.$root.edit = null;
			},

			remove(page) {
				var children = this.$parent.page.children;
				children.splice(children.indexOf(page), 1);
			},

			addChild() {
				var
					id = uuid(),
					newLength = this.page.children.push({
						title: id.substring(0, 8),
						id: id,
						children: []
					});

				this.open = true;

				Vue.nextTick(() => {
					this.$children[newLength - 1].rename();
				});
			}
		}
	}
</script>