import fs from 'fs';

/* global process */

class MixManifestPlugin {

	static url;

	constructor({ filename, path, url, options = {} }) {
		this.output = filename;
		this.options = options;
		MixManifestPlugin.url = url;
		this.manifest = {};
		this.distPath = path;
		this.manifestCache = null;
	}

	apply(compiler) {
		const
			{ output, options } = this,
			hmrEnabled = this.isHMR(),
			ctx = this;

		compiler.plugin('emit', (compilation, done) => {

			const { publicPath, assetsByChunkName: assets } =
				compilation.getStats().toJson({
					hash: true,
					publicPath: true,
					assets: true,
					chunks: false,
					modules: false,
					source: false,
					errorDetails: false,
					timings: false,

					...options
				});

			let cache;

			if(hmrEnabled) {
				const hotPath = `${this.distPath}/hot`;

				if(MixManifestPlugin.url) {
					fs.writeFileSync(hotPath, MixManifestPlugin.url);
				}
				else {
					fs.openSync(hotPath, 'w');
				}
			}
			else {
				if(fs.existsSync(`${this.distPath}/hot`)) {
					fs.unlinkSync(`${this.distPath}/hot`);
				}
			}

			Object.keys(assets).forEach(chunkName => {
				if(Array.isArray(assets[chunkName])) {
					assets[chunkName].forEach(path => {
						if(!path.endsWith('hot-update.js')) {
							ctx.add(publicPath, path);
						}
					});
				}
				else if(!assets[chunkName].endsWith('hot-update.js')) {
					ctx.add(publicPath, assets[chunkName])
				}
			});

			cache = JSON.stringify(ctx.manifest, null, '	');

			if(cache !== this.manifestCache) {
				fs.writeFileSync(`${this.distPath}/${output}`, cache);
				this.manifestCache = cache;
			}

			done();
		});
	}

	add(publicPath, path) {
		this.manifest['/build/' + path.replace(/\?.+$/, '')] = publicPath + path;
	}

	isHMR() {
		return process.argv.includes('--hot');
	}

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
