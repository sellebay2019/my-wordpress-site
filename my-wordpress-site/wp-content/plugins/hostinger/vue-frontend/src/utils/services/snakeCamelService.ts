import { isArray, isObject } from "lodash";

export const snakeToCamel = (string: string, pascal?: boolean) => {
	const converter = (matches: string) => matches[1]?.toUpperCase();

	let result = string?.toString().replace(/(_\w)/g, converter);

	if (pascal) {
		result = result?.charAt(0)?.toUpperCase() + result?.slice(1);
	}

	return result;
};

export const kebabToCamel = (string: string, pascal?: boolean) => {
	const converter = (matches: string) => matches[1]?.toUpperCase();

	let result = string?.toString().replace(/(-\w)/g, converter);

	if (pascal) {
		result = result?.charAt(0)?.toUpperCase() + result?.slice(1);
	}

	return result;
};
export const snakeToPascal = (string: string) =>
	string
		.split("/")
		.map((snake: string) =>
			snake
				.split("_")
				.map(
					(substr: string) =>
						substr.charAt(0).toUpperCase() + substr.slice(1)
				)
				.join("")
		)
		.join("/");

export const stringToPascal = (string: string) =>
	string
		.replace(
			/(\w)(\w*)/g,
			(w: string) => w[0].toUpperCase() + w.slice(1).toLowerCase()
		)
		.split(" ")
		.join("")
		.replace(/(\w)(\w*)/g, (w: string) => w[0].toLowerCase() + w.slice(1));

export const camelToSnake = (str: string) =>
	str
		.replace(/(^[A-Z])/, ([first]) => first.toLowerCase())
		.replace(/([A-Z])/g, ([letter]) => `_${letter.toLowerCase()}`);

export const camelToReadable = (str: string) =>
	camelToSnake(str).replace("_", " ");

export const snakeToCamelObj = (obj: unknown) => {
	if (isObject(obj) && !isArray(obj)) {
		const n: Record<string, unknown> = {};
		Object.keys(obj as Record<string, unknown>).forEach((k) => {
			n[snakeToCamel(k)] = snakeToCamelObj(
				(obj as Record<string, unknown>)[k]
			);
		});

		return n;
	}

	if (isArray(obj)) {
		const n: unknown[] = [];
		(obj as unknown[]).forEach((k: unknown) => n.push(snakeToCamelObj(k)));

		return n;
	}

	return obj;
};

export const camelToSnakeObj = (obj: unknown) => {
	if (isObject(obj) && !isArray(obj)) {
		const n: Record<string, unknown> = {};
		Object.keys(obj as Record<string, unknown>).forEach(
			(k) =>
				(n[camelToSnake(k)] = camelToSnakeObj(
					(obj as Record<string, unknown>)[k]
				))
		);

		return n;
	}

	if (isArray(obj)) {
		const n: unknown[] = [];
		(obj as unknown[]).forEach((k: unknown) => n.push(camelToSnakeObj(k)));

		return n;
	}

	return obj;
};

export const kebabToCamelObj = (obj: unknown) => {
	if (isObject(obj) && !isArray(obj)) {
		const n: Record<string, unknown> = {};
		Object.keys(obj as Record<string, unknown>).forEach((k) => {
			n[kebabToCamel(k)] = kebabToCamelObj(
				(obj as Record<string, unknown>)[k]
			);
		});

		return n;
	}

	if (isArray(obj)) {
		const n: unknown[] = [];
		(obj as unknown[]).forEach((k: unknown) => n.push(kebabToCamelObj(k)));

		return n;
	}

	return obj;
};

export const snakeToReadable = (str: string) => str?.replace(/_/g, " ");
