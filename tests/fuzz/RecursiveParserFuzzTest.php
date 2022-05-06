<?php declare(strict_types=1);
/*
 * This file is part of wikibase-solutions/mediawiki-template-parser.
 *
 * (c) Marijn van Wezel <marijnvanwezel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WikibaseSolutions\MediaWikiTemplateParser\Tests;

use PHPUnit\Framework\TestCase;
use WikibaseSolutions\MediaWikiTemplateParser\RecursiveParser;

/**
 * Class RecursiveParserTest
 *
 * @covers  \WikibaseSolutions\MediaWikiTemplateParser\RecursiveParser
 * @package WikibaseSolutions\MediaWikiTemplateParser\Tests
 * @group   Fuzz
 */
class RecursiveParserFuzzTest extends TestCase
{
    const NUM_FUZZ_TESTS = 5000;

    /**
     * @var RecursiveParser
     */
    private RecursiveParser $parser;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->parser = new RecursiveParser();
    }

    /**
     * This function performs a fuzz test on the parser, which makes sure that the parser does not get stuck for certain
     * data. It does not perform assertions about the result.
     *
     * @doesNotPerformAssertions
     */
    public function testParserFuzz(): void
    {
        for ($i = 0; $i < self::NUM_FUZZ_TESTS; $i++) {
            $this->parser->parse($this->generateFuzzData());
        }
    }

    /**
     * Generate some fuzz data.
     *
     * @return string
     */
    private function generateFuzzData(): string
    {
        $tokens = ['{{', '{', '}', '}}', '#', '{{{', '}}}', 'Example', 'Example2', 'Arg1', 'Arg2', "\n", ' '];
        $numTokens = rand(1, 1500);

        $fuzz = '';

        for ($i = 0; $i < $numTokens; $i++) {
            $fuzz .= $tokens[array_rand($tokens)];
        }

        return $fuzz;
    }
}