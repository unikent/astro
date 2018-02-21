/**
 * This webpack plugin adds support for HMR (hot module replacement) and cache
 * busting that's compatible with Laravel's mix manifest file.
 */

import fs from 'fs';

/* global process */

class MixManifestPlugin {

	static url;

	constructor({ filename, path, url, options = {} }) {
		this.filename = filename;
		MixManifestPlugin.url = url;
		this.manifest = {};
		this.distPath = path;
		this.options = options;
		this.manifestCache = null;
	}

	apply(compiler) {

		compiler.plugin('emit', (compilation, done) => {
			// grab webpack stats from this compilation run
			const { publicPath, assetsByChunkName: assets } = compilation
				.getStats().toJson({
					hash: true,
					publicPath: true,
					assets: true,
					chunks: false,
					modules: false,
					source: false,
					errorDetails: false,
					timings: false,

					...this.options
				});

			let cache;

			// If HMR is enabled, create a "hot" file compatible with Laravel's
			// "mix" helper that changes our asset URLs.
			if(this.hmrIsEnabled()) {
				const hotPath = `${this.distPath}/hot`;

				if(MixManifestPlugin.url) {
					fs.writeFileSync(hotPath, MixManifestPlugin.url);
				}
				else {
					fs.openSync(hotPath, 'w');
				}
			}
			// Otherwise delete the "hot" file
			else {
				if(fs.existsSync(`${this.distPath}/hot`)) {
					fs.unlinkSync(`${this.distPath}/hot`);
				}
			}

			// Loop over the assets webpack has output, removing any
			// special HMR files (ending in "hot-update.js") and adding
			// others to our manifest file in the correct format.
			Object.keys(assets).forEach(chunkName => {
				if(Array.isArray(assets[chunkName])) {
					assets[chunkName].forEach(path => {
						if(!path.endsWith('hot-update.js')) {
							this.add(publicPath, path);
						}
					});
				}
				else if(!assets[chunkName].endsWith('hot-update.js')) {
					this.add(publicPath, assets[chunkName])
				}
			});

			cache = JSON.stringify(this.manifest, null, '	');

			// Write the mix manifest file if this run created different JSON to the last.
			if(cache !== this.manifestCache) {
				fs.writeFileSync(`${this.distPath}/${this.filename}`, cache);
				this.manifestCache = cache;
			}

			done();
		});

	}

	add(publicPath, path) {
		this.manifest['/build/' + path.replace(/\?.+$/, '')] = publicPath + path;
	}

	hmrIsEnabled() {
		return process.argv.includes('--hot');
	}

	// Config for webpack dev server, that uses the URL supplied to this class.
	static devServerConfig({ url, options = {} } = {}) {
		const
			urlParts = (url || MixManifestPlugin.url).match(
				/(?:https?:)?(?:\/\/)([^:\n]+):?(\d+)?/
			),
			hostAndPort = {};

		if(urlParts[1]) {
			hostAndPort.host = urlParts[1];
		}

		if(urlParts[2]) {
			hostAndPort.port = urlParts[2]
		}

		return {
			...hostAndPort,
			headers: {
				'Access-Control-Allow-Origin': '*'
			},
			contentBase: 'public',
			historyApiFallback: true,
			compress: true,
			...options
		}
	}

}

export default MixManifestPlugin;
