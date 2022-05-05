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
 * A blazing fast recursive descent parser for parsing MediaWiki articles.
 *
 * @package WikibaseSolutions\MediaWikiTemplateParser
 */
class RecursiveParser {
    /**
     * @var string[] The list of tokens to generate templates sourc
     */
    private array $tokens;

    public function __construct(string $source) {
        $this->tokens = mb_str_split($source);
    }

    /**
     * Parses the given wikitext.
     *
     * @param string $source
     * @return array
     */
    public function parse(string $source): array {
        $templates = $this->findTemplates($source);

        var_dump($templates); die();
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
        $templateSource = '';

        for ($idx = 0; $idx < $numChars; $idx++) {
            // We need to lookahead at least three characters, since parameters use three braces
            $current = $chars[$idx];
            $next = $chars[$idx + 1] ?? "\0";
            $nnext = $chars[$idx + 2] ?? "\0";

            if (!$inParameter && $current === '{' && $next === '{' && $nnext === '{') {
                // We're at the start of a parameter
                $inParameter = true;
                $idx += 2;

                if ($openBrackets > 0) {
                    $templateSource .= '{{{';
                }
            } elseif ($inParameter && $current === '}' && $next === '}' && $nnext === '}') {
                // We're at the end of a parameter
                $inParameter = false;
                $idx += 2;

                if ($openBrackets > 0) {
                    $templateSource .= '}}}';
                }
            } elseif ($current === '{' && $next === '{') {
                // We're at the start of a template
                $openBrackets++;
                $idx++;

                $templateSource .= '{{';
            } elseif ($current === '}' && $next === '}') {
                // We're at the end of a template
                $openBrackets--;
                $idx++;

                $templateSource .= '}}';

                if ($openBrackets === 0) {
                    // We are done parsing a template
                    if ($this->isValidTemplate($templateSource)) {
                        $templates[] = $templateSource;
                    }

                    // Reset the template source
                    $templateSource = '';
                }
            } elseif ($openBrackets > 0) {
                // We're inside a template
                $templateSource .= $current;
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
    private function isValidTemplate(string $source): bool {
        return strlen($source) >= 5 && // Check if it as least five characters ({{<identifier>}})
            $source[0] === '{' && $source[1] === '{' && // Check if it starts with "{{"
            $source[-1] === '}' && $source[-2] === '}' && // Check if it ends with "}}"
            $source[2] !== "#"; // Check if it is not a parser function
    }
}

$parser = new RecursiveParser();

$parser->parse("{{Foo|Bar={{Foo}}{{fsdfa}}}}fsdfasdf{{Foo}}{{{foo}}}{{Foo|bar={{{Qux}}}}}{{}{}}}}{{{{{{}}{}{}{F{DF{S{}F}SDF{S}}}}DF{{{{F{F{{FF{{}}}}{{{FFFF}}}F{{{F}}F{{{{FFF}}}}}FF|F|F|F|D|D|D|{D{D{D|{|{|{F{|{|{|{|||{{{{{||F{{{FFF||{{FF}}");

