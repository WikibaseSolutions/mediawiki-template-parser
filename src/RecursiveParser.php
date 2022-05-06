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
	private const PARAM_START = '{{{';

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
            // Remove the braces around the template
            $source = substr($source, 2, -2);

            // Tokenize the template
            $templateParts = $this->splitArguments($source, '|');
            $templateName = trim(array_shift($templateParts));

            // We need to name our anonymous arguments using their numeric identifier
            $argPointer = 1;
            $arguments = [];

            foreach ($templateParts as $argument) {
                $argumentParts = explode('=', $argument, 2);
                $argumentName = count($argumentParts) === 1 ? strval($argPointer++) : trim(array_shift($argumentParts));

                $arguments[$argumentName] = $this->parse($argumentParts[0]);
            }

			$templates[$templateName][] = $arguments;
		}

		return $templates;
	}

    /**
     * Parses a template and splits it based on arguments, while respecting nested templates.
     *
     * @param string $template
     * @return string[] The arguments in the array
     */
	private function splitArguments(string $template): array {
        $numChars = strlen($template);

        $idx = 0;
        $arguments = [];
        $argument = '';

        while ($idx < $numChars) {
            if (substr($template, $idx, 3) === self::PARAM_START) {
                // We found a parameter on which we should not split
                $argument .= $this->matchBraces($template, $numChars, $idx, 3);
            } elseif (substr($template, $idx, 2) === self::TEMPLATE_START) {
                // We found a template on which we should not split
                $argument .= $this->matchBraces($template, $numChars, $idx, 2);
            } elseif ($template[$idx] === '|') {
                // Split when we encounter the delimiter
                $arguments[] = $argument;
                $argument = '';
            } else {
                // Add the character if we didn't encounter anything special
                $argument .= $template[$idx];
            }

            $idx++;
        }

        $arguments[] = $argument;

        return $arguments;
	}

    /**
     * @param string $source The source to find templates in
     * @return array
     */
    private function findTemplates(string $source): array {
        $numChars = strlen($source);

        $idx = 0;
        $templates = [];

        while ($idx < $numChars) {
            if (substr($source, $idx, 3) === self::PARAM_START) {
                // Skip the parameter (and possibly any nested templates inside it)
                $this->matchBraces($source, $numChars, $idx, 3);
            } elseif (substr($source, $idx, 2) === self::TEMPLATE_START) {
                // We are at the start of a template, consume it and continue
                $template = $this->matchBraces($source, $numChars, $idx, 2);

                // Check if it is actually a template and not something like a parser function
                if ($this->isTemplate($template)) {
                    $templates[] = $template;
                }
            }

            $idx++;
        }

        return $templates;
    }

    /**
     * Matches the number of braces and returns the result. This function expects the given index to start at the
     * specified number of braces in the given source.
     *
     * @param string $source The source to match braces in
     * @param int $numChars The number of characters in $source
     * @param int $idx The index to start matching
     * @param int $numBraces The number of braces to match
     * @return string
     */
    private function matchBraces(string $source, int $numChars, int &$idx, int $numBraces): string {
        $idx += $numBraces;

        $match = str_repeat('{', $numBraces);
        $matchEnd = str_repeat('}', $numBraces);

        while ($idx < $numChars) {
            if (substr($source, $idx, $numBraces) === $matchEnd) {
                // We're at the end of the braces, break
                break;
            }

            if (substr($source, $idx, 3) === self::PARAM_START) {
                // We found a parameter which we need to consume first
                $match .= $this->matchBraces($source, $numChars, $idx, 3);
            } elseif (substr($source, $idx, 2) === self::TEMPLATE_START) {
                // We found a template which we need to consume first
                $match .= $this->matchBraces($source, $numChars, $idx, 2);
            } else {
                // We didn't encounter anything special
                $match .= $source[$idx];
            }

            $idx++;
        }

        // End at the last brace
        $idx += $numBraces - 1;
        $match .= $matchEnd;

        return $match;
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

