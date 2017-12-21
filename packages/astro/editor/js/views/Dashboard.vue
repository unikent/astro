<template>
<el-card>
	<div slot="header" class="manage-table__header">
		<span class="main-header">Visits</span>
		<el-button-group class="dash-type-switch u-mla">
			<el-button @click="getWeek">Week</el-button>
			<el-button @click="getMonth">Month</el-button>
			<el-button @click="getYear">Year</el-button>
		</el-button-group>
	</div>
	<line-chart
		class="dash-chart"
		:chart-data="datacollection"
	/>
</el-card>
</template>

<script>
import LineChart from 'components/LineChart';

var getDaysArray = function(year, month) {
	var names = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
	var date = new Date(year, month - 1, 1);
	var result = [];

	while(date.getMonth() === month - 1) {
		result.push(names[date.getDay()] + ' ' + date.getDate());
		date.setDate(date.getDate() + 1);
	}

	return result;
};

const generateData = (length, maxValue) => {
	const result = [], result2 = [];

	for(let i = 0; i < length; i++) {
		result.push(Math.round(Math.random() * maxValue));
	}

	for(let i = 0; i < length; i++) {
		result2.push(Math.round(Math.random() * result[i] * .5));
	}

	return [
		result2, result
	];
}

export default {

	components: {
		LineChart
	},

	data() {
		return {
			datacollection: {
				labels: [
					'Jan', 'Feb', 'Mar', 'Apr',
					'May', 'Jun', 'Jul', 'Aug',
					'Sep', 'Oct', 'Nov', 'Dec'
				],
				datasets: [
					{
						label: 'Unique visits',
						pointBorderColor: '#2196f3',
						pointBackgroundColor: '#fff',
						pointHoverBackgroundColor: '#fff',
						pointBorderWidth: 2,
						pointRadius: 4,
						pointHoverRadius: 6,
						borderColor: '#2196f3',
						backgroundColor: 'rgba(33, 150, 243, 0.3)',
						data: [20, 16, 6, 22, 36, 50, 13, 12, 15, 33, 55, 39]
					},
					{
						label: 'Visits',
						pointBorderColor: '#b9c2c6',
						pointBackgroundColor: '#fff',
						pointHoverBackgroundColor: '#fff',
						pointBorderWidth: 2,
						pointRadius: 4,
						pointHoverRadius: 6,
						borderColor: '#b9c2c6',
						backgroundColor: 'rgba(185, 194, 198, 0.3)',
						data: [40, 39, 10, 40, 39, 80, 40, 34, 23, 46, 87, 89]
					}
				]
			}
		}
	},

	methods: {
		getWeek() {

			function getWeekArray(date) {
				var names = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
				var day = date.getDay() || 7, result = [];

				if(day !== 1) {
					date.setHours(-24 * (day - 1));
				}

				var count = 0;

				while(count < 7) {
					result.push(names[date.getDay()] + ' ' + date.getDate());
					date.setDate(date.getDate() + 1);
					count++;
				}

				return result;
			}

			const
				labels = getWeekArray(new Date()),
				data = generateData(labels.length, 100);

			const datasets = this.datacollection.datasets;

			datasets[0].data = data[0];
			datasets[1].data = data[1];

			this.datacollection = {
				...this.datacollection,
				labels,
				datasets
			};
		},

		getMonth() {
			const
				now = new Date(),
				labels = getDaysArray(now.getFullYear(), now.getMonth() + 1),
				data = generateData(labels.length, 100);

			const datasets = this.datacollection.datasets;

			datasets[0].data = data[0];
			datasets[1].data = data[1];

			this.datacollection = {
				...this.datacollection,
				labels,
				datasets
			};
		},

		getYear() {

			const
				labels = [
					'Jan', 'Feb', 'Mar', 'Apr',
					'May', 'Jun', 'Jul', 'Aug',
					'Sep', 'Oct', 'Nov', 'Dec'
				],
				datasets = this.datacollection.datasets;

			datasets[0].data = [20, 16, 6, 22, 36, 50, 13, 12, 15, 33, 55, 39];
			datasets[1].data = [40, 39, 10, 40, 39, 80, 40, 34, 23, 46, 87, 89];

			this.datacollection = {
				...this.datacollection,
				labels,
				datasets
			};
		}
	}

};
</script>