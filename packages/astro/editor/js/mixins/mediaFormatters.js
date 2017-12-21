// Euclid's algorithm (simple recursive version)
// gets the greatest common divisor
const gcd = (a, b) => {
	if(b === 0) {
		return a;
	}

	return gcd(b, a % b);
};

export default {

	methods: {
		/**
		 * Age old way of turning bytes into a nicer, human readable format.
		 *
		 * @param      {number}  size       The size
		 * @param      {number}  precision  The precision
		 *
		 * @return     {string}  Formatted file size.
		 */
		formatBytes(size, precision = 2) {
			const
				base = Math.log(size) / Math.log(1024),
				floored = Math.floor(base),
				unit = ['bytes', 'KB', 'MB', 'GB', 'TB'][floored],
				formattedBytes = Math.pow(1024, base - floored).toFixed(precision);

			return `${formattedBytes} ${unit}`;
		},

		/**
		 * Turn a (decimal) aspect ratio into the form "x:y".
		 *
		 * @param      {number}  decimal     The decimal eg. 1.5
		 * @param      {number}  multiplier  Multiplier is used to remove the
		 * digits after our decimal point and simplify the calculation / avoid rounding issues.
		 *
		 * @return     {string}  Aspect ratio.
		 */
		formatAspectRatioFromDecimal(decimal, multiplier = 1) {
			if(decimal === multiplier) {
				return '1:1';
			}

			const
				x = decimal * multiplier,
				y = multiplier,
				// make sure the first argument is the larger of the numbers
				divisor = gcd(
					y < x ? x : y,
					y < x ? y : x
				);

			return `${x / divisor}:${y / divisor}`;
		}
	}
};
