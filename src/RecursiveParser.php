<?php declare(strict_types=1);
/*
 * This file is part of wikibase-solutions/mediawiki-template-parser.
 *
 * (c) Marijn van Wezel <marijnvanwezel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WikibaseSolutions\MediaWikiTemplateParser;

/**
 * A blazing fast recursive parser for parsing MediaWiki articles.
 *
 * @package WikibaseSolutions\MediaWikiTemplateParser
 */
class RecursiveParser {
	public const TEXT_KEY = '_text';

	private const TEMPLATE_START = '{{';
	private const TEMPLATE_END = '}}';

	private const PARAM_START = '{{{';
	private const PARAM_END = '}}}';

	/**
     * Parses the given wikitext.
     *
     * @param string $source
     * @return array
     */
    public function parse(string $source): array {
        $templates = $this->parseTemplates($this->findTemplates($source));
		$templates[self::TEXT_KEY] = trim($source);

		return $templates;
    }

	/**
	 * Parses the given templates into an associative array.
	 *
	 * @param string[] $sources The sources of the templates to parse
	 * @return array
	 */
	private function parseTemplates(array $sources): array {
		$templates = [];

		foreach ($sources as $source) {
			list($name, $arguments) = $this->parseTemplate($source);
			$templates[$name][] = $arguments;
		}

		return $templates;
	}

	/**
	 * Parses the given template.
	 *
	 * @param string $template
	 * @return array
	 */
	private function parseTemplate(string $template): array {
		// Remove the braces around the template
		$template = substr($template, 2, -2);
		$templateParts = $this->tokenizeTemplate($template);
		$templateName = trim(array_shift($templateParts));

		// We need to name our anonymous arguments using their numeric identifier
		$anonymousArgumentPointer = 1;
		$templateArguments = [];

		foreach ($templateParts as $argument) {
			$argumentParts = explode('=', $argument, 2);
			$argumentName = count($argumentParts) === 1 ?
				strval($anonymousArgumentPointer++) : trim(array_shift($argumentParts));
			$templateArguments[$argumentName] = $this->parse($argumentParts[0]);
		}

		return [$templateName, $templateArguments];
	}

	/**
	 * Parses a template and splits it based on arguments, while respecting nested templates.
	 *
	 * @param string $template
	 * @return string[] The arguments in the array
	 */
	private function tokenizeTemplate(string $template): array {
		$template = mb_str_split($template);
		$arguments = [];

		$openBrackets = 0;
		$argument = '';

		foreach ($template as $index => $char) {
			if ($openBrackets === 0 && $char === "|") {
				$arguments[] = $argument;
				$argument = '';

				continue;
			} elseif ($char === "{" && $template[$index + 1] === "{") {
				// Check if a template starts
				$openBrackets++;
			} elseif ($openBrackets > 0 && $char === "}" && $template[$index + 1] === "}") {
				// Check if a template ends
				$openBrackets--;
			}

			$argument .= $char;
		}

		$arguments[] = $argument;

		return $arguments;
	}

    /**
     * Finds all templates in the given wikitext and returns it as an array of wikitext, where each item is the
     * wikitext corresponding to a single template on the given source.
     *
     * @param string $source
     * @return void
     */
    private function findTemplates(string $source): array {
        $templates = [];
        $chars = mb_str_split($source);
        $numChars = count($chars);

        $inParameter = false;
        $openBrackets = 0;
        $template = '';

        for ($idx = 0; $idx < $numChars; $idx++) {
            // We need to lookahead at least three characters, since parameters use three braces
            $current = $chars[$idx];
            $second = $chars[$idx + 1] ?? "\0";
            $third = $chars[$idx + 2] ?? "\0";

            if (!$inParameter && $current . $second . $third === self::PARAM_START) {
                // We're at the start of a parameter
                $inParameter = true;
                $idx += 2;

                if ($openBrackets > 0) {
					// Only add if we're parsing a template
                    $template .= self::PARAM_START;
                }
            } elseif ($inParameter && $current . $second . $third === self::PARAM_END) {
                // We're at the end of a parameter
                $inParameter = false;
                $idx += 2;

                if ($openBrackets > 0) {
					// Only add if we're parsing a template
                    $template .= self::PARAM_END;
                }
            } elseif ($current . $second === self::TEMPLATE_START) {
                // We're at the start of a template
                $openBrackets++;
                $idx++;
                $template .= self::TEMPLATE_START;
            } elseif ($current . $second === self::TEMPLATE_END) {
                // We're at the end of a template
                $openBrackets--;
                $idx++;
                $template .= self::TEMPLATE_END;

                if ($openBrackets === 0) {
                    // We are done parsing a template
                    if ($this->isTemplate($template)) {
                        $templates[] = $template;
                    }

                    // Reset the template source
                    $template = '';
                }
            } elseif ($openBrackets > 0) {
                // We're inside a template and not at any special tokens
                $template .= $current;
            }
        }

        return $templates;
    }

    /**
     * Checks if the given source is a valid template.
     *
     * @param string $source
     * @return bool
     */
    private function isTemplate(string $source): bool {
        return strlen($source) >= 5 && // Check if it as least five characters ({{<identifier>}})
            $source[0] === '{' && $source[1] === '{' && // Check if it starts with "{{"
            $source[-1] === '}' && $source[-2] === '}' && // Check if it ends with "}}"
            $source[2] !== "#"; // Check if it is not a parser function
    }
}
