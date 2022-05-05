<?php declare(strict_types=1);
/*
 * This file is part of wikibase-solutions/mediawiki-template-parser.
 *
 * (c) Marijn van Wezel <marijnvanwezel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WikibaseSolutions\MediaWikiTemplateParser\Utils;

/**
 * This class analyses the given wikitext and extracts the templates out of it.
 */
class TemplateFinder
{
    /**
     * @var string[] The tokens to analyze
     */
    private array $tokens;

    /**
     * @var int The number of tokens
     */
    private int $numTokens;

    /**
     * @var array The templates that have been found so far
     */
    private array $templates = [];

    /**
     * @var int The index we are analyzing
     */
    private int $current = 0;

    /**
     * @var string The current template buffer
     */
    private string $templateBuffer = '';

    /**
     * @param string $source The source to analyze
     */
    public function __construct(string $source) {
        $this->tokens = mb_str_split($source);
        $this->numTokens = count($this->tokens);

        $this->findTemplates();
    }

    /**
     * Returns the templates that have been found.
     *
     * @return array
     */
    public function getTemplates(): array {
        return $this->templates;
    }

    /**
     * Parses the source and finds the templates.
     *
     * @return void
     */
    private function findTemplates(): void {
        while (!$this->atEnd()) {
            if ($this->current() === '{' && $this->peek() === '{' && $this->peek(2) === '{') {
                $this->consumeParameter();
            } elseif ($this->current() === '{' && $this->peek() === '{') {
                $this->consumeTemplate();
            }
        }
    }

    /**
     * @return string
     */
    private function consume(): string {
        return $this->tokens[$this->current++];
    }

    /**
     * @return string
     */
    private function current(): string {
        return $this->tokens[$this->current];
    }

    /**
     * @param int $amount
     * @return string
     */
    private function peek(int $amount = 1): string {
        return $this->tokens[$this->current + $amount] ?? "\0";
    }

    /**
     * @return bool
     */
    private function atEnd(): bool {
        return $this->current >= $this->numTokens;
    }
}