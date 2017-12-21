import Ajv from 'ajv';
import path from 'path';
import fs from 'fs';
import dotenv from 'dotenv';
import chalk from 'chalk';
import _ from 'lodash';

/* global __dirname, console, process */
/* eslint-disable no-console */

const envPath = path.resolve(`${__dirname}/../../../../../.env`);

dotenv.config({ path: envPath });

const
	schemas = ['layout', 'region', 'block', 'site'],
	ajv = new Ajv({ $data: true }),
	validators = schemas.reduce((validators, schemaName) => {
		const schemaPath = path.resolve(
			`${__dirname}/../../test/schemas/${schemaName}.spec.json`
		);
		if(fs.existsSync(schemaPath)) {
			const schema = JSON.parse(fs.readFileSync(schemaPath).toString());
			return { ...validators, [schemaName]: ajv.compile(schema) };
		}
		else {
			console.error(`Can't find "${schemaPath}" schema.`);
		}
	}, {}),
	getDefinitions = (themeFolder, types = []) => {
		let definitions = {};

		types.forEach(type => {
			definitions[type] = walkDir(
				`${themeFolder}/${type}s`, (file) => file === 'definition.json'
			);
		});

		return definitions;
	},
	walkDir = (dir, shouldAddFile = () => true, filelist = []) => {
		fs.readdirSync(dir).forEach(filename => {
			const filepath = path.join(dir, filename);

			if(fs.statSync(filepath).isDirectory()) {
				return walkDir(filepath, shouldAddFile, filelist);
			}

			if(shouldAddFile(filename)) {
				filelist.push(filepath);
			}
		});

		return filelist;
	},
	definitionsPath = process.env.DEFINITIONS_PATH;

let definitionCount = 0, invalidDefinitionCount = 0, errors = {};

if(definitionsPath) {

	schemas.forEach(defType => {
		getDefinitions(definitionsPath, schemas)[defType].forEach(path => {
			if(!validators) {
				return;
			}

			let definition, jsonString;

			try {
				jsonString = fs.readFileSync(path).toString()
				definition = JSON.parse(jsonString);
			}
			catch(e) {
				if(e instanceof SyntaxError) {
					const
						positionMatch = /\d+/.exec(e.stack),
						position = positionMatch[0] && parseInt(positionMatch[0]),
						lineNumber = jsonString && position ?
							`${jsonString.substr(0, position).substr().split('\n').length}` :
							'';

					console.log(
						chalk`{redBright ${e.toString()} in file (line number ${lineNumber}) "${path}"}`
					);
				}
				else {
					console.log(chalk`{redBright An error occured}`);
				}
				return;
			}

			const valid = validators[defType](definition);

			if(!valid) {
				validators[defType].errors.forEach(error => {
					const
						schemaPath = error.schemaPath.substr(2).replace(/\/(\d+)/g, '[$1]').replace(/\//g, '.'),
						dataPath = error.dataPath ? error.dataPath.substr(1) : 'root',
						defId = `${definition.name}-v${definition.version}`;

					if(!errors[defType]) {
						errors[defType] = {};
					}

					if(!errors[defType][defId]) {
						errors[defType][defId] = {};
					}

					if(!errors[defType][defId][dataPath]) {
						errors[defType][defId][dataPath] = [];
					}

					errors[defType][defId][dataPath].push(
						`${
							dataPath !== 'root' && _.has(definition, dataPath) ?
								` ${JSON.stringify(_.get(definition, dataPath))}` :
								''
						} ${error.message} - as defined within "${schemaPath}" in JSON schema`
					);
				});

				invalidDefinitionCount++;
			}

			definitionCount++;
		});
	});

	if(errors) {
		Object.keys(errors).forEach(defType => {
			console.log(chalk`{whiteBright \n${defType.charAt(0).toUpperCase() + defType.slice(1)} errors:}`);

			Object.keys(errors[defType]).forEach(def => {
				console.log(`\n  ${chalk`{yellow ${def}}`}`);

				Object.keys(errors[defType][def]).forEach(dataPath => {
					console.log(
						chalk`{greenBright     ${dataPath}\n}` +
						chalk`{redBright     ${errors[defType][def][dataPath].join(',\n    ')}}`
					);
				});
			});
		});
	}

	console.log(chalk.whiteBright(`\nValidated ${definitionCount} definition files. ${invalidDefinitionCount} are invalid.\n`));
}
else {
	console.log(chalk.redBright(`DEFINITIONS_PATH not set in ${envPath}`));
}
