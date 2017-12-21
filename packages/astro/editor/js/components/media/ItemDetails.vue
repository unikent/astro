<template>
<div class="el-table el-table--fit el-table--enable-row-hover el-table--enable-row-transition el-table--astro">
	<table cellspacing="0" cellpadding="0" border="0">
		<thead>
			<tr>
				<th v-for="option in options" class="is-leaf" :width="option.size">
					<div class="cell">
						{{ option.label }}
					</div>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr
				v-for="(row, index) in rows"
				class="el-table__row"
				:class="{ 'el-table__row--selected': selected && selected.indexOf(row.id) !== -1 }"
				@click="onClick(results[index])"
			>
				<td v-for="(option, key) in options">
					<div class="cell">
						{{ option.format ? option.format(row[key]) : row[key] }}
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<!-- <i class="el-icon-edit item-grid__edit-button" @click="showMediaDetails = true; media = getThumbnailIndex(rowIndex, colIndex)" />
	<el-dropdown trigger="click" placement="start">
		<i class="el-icon-more"></i>
		<el-dropdown-menu slot="dropdown">
			<el-dropdown-item>Refresh Thumbnails</el-dropdown-item>
			<el-dropdown-item>Download</el-dropdown-item>
			<el-dropdown-item>Delete</el-dropdown-item>
		</el-dropdown-menu>
	</el-dropdown> -->
</template>

<script>
import _ from 'lodash';
import mediaFormatters from 'mixins/mediaFormatters';

export default {

	name: 'media-item-details',

	mixins: [mediaFormatters],

	props: {
		results: {
			type: Array,
			default: () => []
		},
		selected: {},
		onClick: {
			type: Function,
			default: () => {}
		}
	},

	data() {
		return {
			options: {
				filename: {
					label: 'File name'
				},
				type: {
					label: 'Type',
					size: 100
				},
				url: {
					label: 'URL'
				},
				filesize: {
					label: 'File size',
					format: this.formatBytes,
					size: 120
				}
			}
		};
	},

	computed: {
		rows() {
			return this.results.map(
				(item) => _.pick(item, ['id', ...Object.keys(this.options)])
			);
		}
	}
};
</script>