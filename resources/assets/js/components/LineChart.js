import { Line, mixins } from 'vue-chartjs';

export default {

	extends: Line,
	mixins: [mixins.reactiveProp],

	mounted() {
		this.renderChart(
			this.chartData,
			{
				responsive: true,
				maintainAspectRatio: false,

				legend: {
					display: false
				},

				hover: {
					intersect: false,
					mode: 'x',
					animationDuration: 0
				},

				tooltips: {
					mode: 'x',
					position: 'average',
					intersect: false
				},

				scales: {
					yAxes: [{
						gridLines: {
							display: true
						}
					}],
					xAxes: [{
						gridLines: {
							display: false
						}
					}]
				}
			}
		);
	}
};
