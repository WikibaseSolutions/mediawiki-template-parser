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
use WikibaseSolutions\MediaWikiTemplateParser\Parser;

/**
 * Class ParserTest
 *
 * @covers  \WikibaseSolutions\MediaWikiTemplateParser\Parser
 * @package WikibaseSolutions\MediaWikiTemplateParser\Tests
 * @group   Unit
 */
class ParserTest extends TestCase
{
    /**
     * @var Parser
     */
    private Parser $parser;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->parser = new Parser();
    }

    /**
     * @dataProvider provideParserData
     * @param        string $source
     * @param        array  $expected
     */
    public function testParser(string $source, array $expected): void
    {
        $this->assertSame($expected, $this->parser->parseArticle($source));
    }

    public function provideParserData(): array
    {
        return [
            ['', []],
            ['{{', []],
            ['{{}}', []],
            ['{{Foo}}', ['Foo' => []]],
            ['{{Foo|Foo=Bar}}', ['Foo' => ['Foo' => 'Bar']]],
            ['{{Foo|Bar}}', ['Foo' => ['1' => 'Bar']]],
            ['{{Foo|Bar}}{{Boo|Far}}', ['Foo' => ['1' => 'Bar'], 'Boo' => ['1' => 'Far']]],
            ['{{Foo}}{{Bar}}', ['Foo' => [], 'Bar' => []]],
            ['{{Article|Content={{Subtemplate|Foo}}}}', ['Article' => ['Content' => '{{Subtemplate|Foo}}']]],
            ['{{Article|{{Subtemplate|Foo}}}}', ['Article' => ['1' => '{{Subtemplate|Foo}}']]],
            ['{{}}{{Foo}}', ['Foo' => []]],
            ['{{F{{}}}}', ['F{{}}' => []]],
            ['{{{{Foo}}}}', ['{{Foo}}' => []]],
            ['{{Foo|Bar|Quz|Foo=Bar}}', ['Foo' => ['1' => 'Bar', '2' => 'Quz', 'Foo' => 'Bar']]],
            ['Lorem ipsum doler et... <br/> {{Foo|Bar|Quz|Foo=Bar}} <br/> {{#expr: 1 > 1|t|f}}{{Bar|Bar|Quz|Foo=Bar}}', ['Foo' => ['1' => 'Bar', '2' => 'Quz', 'Foo' => 'Bar'], 'Bar' => ['1' => 'Bar', '2' => 'Quz', 'Foo' => 'Bar']]],
            ['{{Foo=Bar}}', ['Foo=Bar' => []]],
            ['{{Foo|Bar=}}', ['Foo' => ['Bar' => '']]]
        ];
    }
}