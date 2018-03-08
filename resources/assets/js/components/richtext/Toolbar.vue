<template>
<div class="richtext-toolbar flex-container">

	<span
		v-for="item in toolbarItems"
		v-if="item.tag === '*' || allowedTags.includes(item.tag)"
		class="richtext-toolbar__button"
	>
		<button
			type="button"
			class="richtext-toolbar__tooltip"
			:aria-label="item.label"
			:data-command-name="item.command || null"
		>
			<icon
				:name="item.icon"
				:width="14"
				:height="14"
				viewBox="0 0 14 14"
			/>
		</button>

		<ul v-if="item.menu" class="richtext-toolbar__options">
			<component
				v-if="!Array.isArray(item.menu) && item.menu.component"
				ref="table"
				:is="item.menu.component"
			/>
			<li v-else v-for="subItem in item.menu">
				<button
					type="button" class="elem"
					:data-command-name="subItem.command || null"
					v-html="subItem.label"
				/>
			</li>
		</ul>
	</span>

</div>
</template>

<script>
import Icon from '../Icon';

/* global document, console */
/* eslint-disable no-console */

// TODO: add table creation/editig and finish UI

export default {

	name: 'richtext-toolbar',

	props: ['allowedTags'],

	components: {
		Icon
	},

	created() {
		this.toolbarItems = [
			{
				tag: 'b',
				command: 'bold',
				label: 'Bold',
				icon: 'bold'
			},
			{
				tag: 'i',
				command: 'italic',
				label: 'Italicise',
				icon: 'italic'
			},
			{
				tag: 'h3',
				command: 'h3',
				label: 'Sub-heading'
			},
			{
				tag: 'ul',
				command: 'insertUnorderedList',
				label: 'Unordered list',
				icon: 'list'
			},
			{
				tag: 'ol',
				command: 'insertOrderedList',
				label: 'Ordered list',
				icon: 'list-ol'
			},
			{
				tag: 'a',
				label: 'Link',
				icon: 'link',

				menu: [
					{
						command: 'linkPrompt',
						icon: 'link',
						label: 'Add link'
					},
					{
						command: 'unlink',
						label: 'Remove link'
					}
				]
			},
			{
				tag: '*',
				command: 'removeFormat',
				label: 'Remove Formatting',
				icon: 'cleartext'
			},
			// {
			// 	label: 'Table',
			// 	icon: 'table',

			// 	menu: {
			// 		command: 'table',
			// 		component: {
			// 			template: `
			// 				<canvas ref="canvas" style="height: 168px; width: 168px; margin: 10px;" />
			// 			`
			// 		}
			// 	}
			// }
		];
	},

	mounted() {
		// this.setupTable();
		// this.initEvents();
	},

	methods: {
		setupTable() {
			const
				canvas = this.$refs.table[0].$refs.canvas,
				ctx = canvas.getContext('2d'),
				gutter = 4,
				offset = .5,
				size = 13;

			canvas.width = 168;
			canvas.height = 168;

			ctx.strokeStyle = '#a6a6a6';
			ctx.fillStyle = '#cdeffe';
			ctx.lineWidth = 1;
			draw();

			function draw() {

				for(let y = 0; y < 10; y++) {
					for(let x = 0; x < 10; x++) {
						ctx.strokeRect(
							x * size + (x * gutter) + offset,
							y * size + (y * gutter) + offset,
							size,
							size
						);

						ctx.fillRect(
							x * size + (x * gutter) + 1,
							y * size + (y * gutter) + 1,
							size - 1,
							size - 1
						);
					}
				}

			}
		},

		initEvents() {
			const menuItems = document.querySelectorAll('.richtext-toolbar__button');

			Array.from(menuItems).forEach(topLevel => {

				const
					button = topLevel.querySelector('button'),
					subItems = topLevel.querySelectorAll('.richtext-toolbar__options .elem');

				Array.from(subItems).forEach(item => {
					item.addEventListener('focus', (e) => {
						e.preventDefault();
						console.log('focus');
						button.classList.add('active-item');
					});

					item.addEventListener('blur', (e) => {
						e.preventDefault();
						console.log('blur');
						button.classList.remove('active-item');
					});
				});
			});
		}
	}

};
</script>