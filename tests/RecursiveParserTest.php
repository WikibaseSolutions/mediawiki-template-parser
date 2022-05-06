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
 * @covers \WikibaseSolutions\MediaWikiTemplateParser\RecursiveParser
 * @package WikibaseSolutions\MediaWikiTemplateParser\Tests
 */
class RecursiveParserTest extends TestCase
{
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
	 * @dataProvider provideParserData
	 * @param string $source
	 * @param array $expected
	 */
	public function testParser(string $source, array $expected): void
	{
		$this->assertSame($expected, $this->parser->parse($source));
	}

	public function provideParserData(): array
	{
		return [
			'empty string' => [
				'',
				['_text' => '']
			],
			'no closing braces' => [
				'{{',
				['_text' => '{{']
			],
			'no name' => [
				'{{}}',
				['_text' => '{{}}']
			],
			'empty name' => [
				' {{ }} ',
				[
					'' => [[]],
					'_text' => '{{ }}'
				]
			],
			'simple template' => [
				'{{Example}}',
				[
					'Example' => [[]],
					'_text' => '{{Example}}'
				]
			],
			'simple template with whitespace' => [
				'{{ Example }}',
				[
					'Example' => [[]],
					'_text' => '{{ Example }}'
				]
			],
			'simple template with anonymous argument' => [
				'{{Example|Arg1}}',
				[
					'Example' => [
						[
							'1' => [
								'_text' => 'Arg1'
							]
						]
					],
					'_text' => '{{Example|Arg1}}'
				]
			],
			'simple template with anonymous argument with whitespace' => [
				'{{Example| Arg1 }}',
				[
					'Example' => [
						[
							'1' => [
								'_text' => 'Arg1'
							]
						]
					],
					'_text' => '{{Example| Arg1 }}'
				]
			],
			'simple template with named argument' => [
				'{{Example|Name1=Arg1}}',
				[
					'Example' => [
						[
							'Name1' => [
								'_text' => 'Arg1'
							]
						]
					],
					'_text' => '{{Example|Name1=Arg1}}'
				]
			],
			'simple template with named argument with whitespace 1' => [
				'{{Example| Name1 =Arg1}}',
				[
					'Example' => [
						[
							'Name1' => [
								'_text' => 'Arg1'
							]
						]
					],
					'_text' => '{{Example| Name1 =Arg1}}'
				]
			],
			'simple template with named argument with whitespace 2' => [
				'{{Example| Name1 = Arg1 }}',
				[
					'Example' => [
						[
							'Name1' => [
								'_text' => 'Arg1'
							]
						]
					],
					'_text' => '{{Example| Name1 = Arg1 }}'
				]
			],
			'simple template with multiple anonymous arguments' => [
				'{{Example|Arg1|Arg2}}',
				[
					'Example' => [
						[
							'1' => [
								'_text' => 'Arg1'
							],
							'2' => [
								'_text' => 'Arg2'
							]
						]
					],
					'_text' => '{{Example|Arg1|Arg2}}'
				]
			],
			'simple template with mixed arguments 1' => [
				'{{Example|Name1=Arg1|Arg2}}',
				[
					'Example' => [
						[
							'Name1' => [
								'_text' => 'Arg1'
							],
							'1' => [
								'_text' => 'Arg2'
							]
						]
					],
					'_text' => '{{Example|Name1=Arg1|Arg2}}'
				]
			],
			'simple template with mixed arguments 2' => [
				'{{Example|Arg1|Name1=Arg2}}',
				[
					'Example' => [
						[
							'1' => [
								'_text' => 'Arg1'
							],
							'Name1' => [
								'_text' => 'Arg2'
							]
						]
					],
					'_text' => '{{Example|Arg1|Name1=Arg2}}'
				]
			],
			'simple template with mixed arguments 3' => [
				'{{Example|Arg1|Name1=Arg2|Arg3}}',
				[
					'Example' => [
						[
							'1' => [
								'_text' => 'Arg1'
							],
							'Name1' => [
								'_text' => 'Arg2'
							],
							'2' => [
								'_text' => 'Arg3'
							]
						]
					],
					'_text' => '{{Example|Arg1|Name1=Arg2|Arg3}}'
				]
			],
			'simple template with named arguments' => [
				'{{Example|Name1=Arg1|Name1=Arg2}}',
				[
					'Example' => [
						[
							'Name1' => [
								'_text' => 'Arg2'
							]
						]
					],
					'_text' => '{{Example|Name1=Arg1|Name1=Arg2}}'
				]
			],
			'single nested template with anonymous argument' => [
				'{{Example|{{Example2}}}}',
				[
					'Example' => [
						[
							'1' => [
								'Example2' => [[]],
								'_text' => '{{Example2}}'
							]
						]
					],
					'_text' => '{{Example|{{Example2}}}}'
				]
			],
			'single nested template with anonymous arguments' => [
				'{{Example|{{Example2}}|{{Example3}}}}',
				[
					'Example' => [
						[
							'1' => [
								'Example2' => [[]],
								'_text' => '{{Example2}}'
							],
							'2' => [
								'Example3' => [[]],
								'_text' => '{{Example3}}'
							]
						]
					],
					'_text' => '{{Example|{{Example2}}|{{Example3}}}}'
				]
			],
			'single nested template with named argument' => [
				'{{Example|Name1={{Example2}}}}',
				[
					'Example' => [
						[
							'Name1' => [
								'Example2' => [[]],
								'_text' => '{{Example2}}'
							]
						]
					],
					'_text' => '{{Example|Name1={{Example2}}}}'
				]
			],
			'single nested template with named arguments' => [
				'{{Example|Name1={{Example2}}|Name2={{Example3}}}}',
				[
					'Example' => [
						[
							'Name1' => [
								'Example2' => [[]],
								'_text' => '{{Example2}}'
							],
							'Name2' => [
								'Example3' => [[]],
								'_text' => '{{Example3}}'
							]
						]
					],
					'_text' => '{{Example|Name1={{Example2}}|Name2={{Example3}}}}'
				]
			],
			'multiple templates with different names' => [
				'{{Example}}{{Example2}}',
				[
					'Example' => [[]],
					'Example2' => [[]],
					'_text' => '{{Example}}{{Example2}}'
				]
			],
			'multiple templates with same name 1' => [
				'{{Example}}{{Example}}',
				[
					'Example' => [[], []],
					'_text' => '{{Example}}{{Example}}'
				]
			],
			'multiple templates with same name 2' => [
				'{{Example|Arg1}}{{Example|Arg2}}',
				[
					'Example' => [
						[
							'1' => [
								'_text' => 'Arg1'
							]
						],
						[
							'1' => [
								'_text' => 'Arg2'
							]
						]
					],
					'_text' => '{{Example|Arg1}}{{Example|Arg2}}'
				]
			],
			'lone parameter' => [
				'{{{Param1}}}',
				[
					'_text' => '{{{Param1}}}'
				]
			],
			'parser function' => [
				'{{#Example}}',
				[
					'_text' => '{{#Example}}'
				]
			],
			'parameter as parameter' => [
				'{{Example|{{{Param1}}}}}',
				[
					'Example' => [
						[
							'1' => [
								'_text' => '{{{Param1}}}'
							]
						]
					],
					'_text' => '{{Example|{{{Param1}}}}}'
				]
			],
			'parameters in text' => [
				'{{Example}} Lorem ipsum {{{Param1}}}',
				[
					'Example' => [[]],
					'_text' => '{{Example}} Lorem ipsum {{{Param1}}}'
				]
			],
			'nested multiple instance templates' => [
				<<<'TEMPLATE'
				{{Example
				|Arg1
				|Name1=Arg2
				|Name2={{Example2
				|Arg1
				|Name1=Arg2
				|Name2={{Example3
				|Arg1
				}}
				}}
				{{Example2|Arg1}}
				}}
				TEMPLATE,
				[
					'Example' => [
						[
							'1' => [
								'_text' => 'Arg1'
							],
							'Name1' => [
								'_text' => 'Arg2'
							],
							'Name2' => [
								'Example2' => [
									[
										'1' => [
											'_text' => 'Arg1'
										],
										'Name1' => [
											'_text' => 'Arg2'
										],
										'Name2' => [
											'Example3' => [
												[
													'1' => [
														'_text' => 'Arg1'
													]
												]
											],
											'_text' => <<<'TEMPLATE'
											{{Example3
											|Arg1
											}}
											TEMPLATE
										]
									],
									[
										'1' => [
											'_text' => 'Arg1'
										]
									]
								],
								'_text' => <<<'TEMPLATE'
								{{Example2
								|Arg1
								|Name1=Arg2
								|Name2={{Example3
								|Arg1
								}}
								}}
								{{Example2|Arg1}}
								TEMPLATE
							]
						]
					],
					'_text' => <<<'TEMPLATE'
					{{Example
					|Arg1
					|Name1=Arg2
					|Name2={{Example2
					|Arg1
					|Name1=Arg2
					|Name2={{Example3
					|Arg1
					}}
					}}
					{{Example2|Arg1}}
					}}
					TEMPLATE
				]
			],
			'nested multiple instance templates with parameters' => [
				<<<'TEMPLATE'
				{{Example
				|Arg1
				|Name1=Arg2
				|Name2={{Example2
				|{{{Param1}}}
				|Name1={{{Param1}}}Arg2
				|Name2={{Example3
				|{{{Param2}}}
				}}
				}}
				{{Example2|{{{Param3}}}{{{Param4}}}}}
				}}
				TEMPLATE,
				[
					'Example' => [
						[
							'1' => [
								'_text' => 'Arg1'
							],
							'Name1' => [
								'_text' => 'Arg2'
							],
							'Name2' => [
								'Example2' => [
									[
										'1' => [
											'_text' => '{{{Param1}}}'
										],
										'Name1' => [
											'_text' => '{{{Param1}}}Arg2'
										],
										'Name2' => [
											'Example3' => [
												[
													'1' => [
														'_text' => '{{{Param2}}}'
													]
												]
											],
											'_text' => <<<'TEMPLATE'
											{{Example3
											|{{{Param2}}}
											}}
											TEMPLATE
										]
									],
									[
										'1' => [
											'_text' => '{{{Param3}}}{{{Param4}}}'
										]
									]
								],
								'_text' => <<<'TEMPLATE'
								{{Example2
								|{{{Param1}}}
								|Name1={{{Param1}}}Arg2
								|Name2={{Example3
								|{{{Param2}}}
								}}
								}}
								{{Example2|{{{Param3}}}{{{Param4}}}}}
								TEMPLATE
							]
						]
					],
					'_text' => <<<'TEMPLATE'
					{{Example
					|Arg1
					|Name1=Arg2
					|Name2={{Example2
					|{{{Param1}}}
					|Name1={{{Param1}}}Arg2
					|Name2={{Example3
					|{{{Param2}}}
					}}
					}}
					{{Example2|{{{Param3}}}{{{Param4}}}}}
					}}
					TEMPLATE
				]
			],
			'full example' => [
				<<<'TEMPLATE'
				{{Example
				|Arg1
				|Name1=Arg2
				|Name2={{Example2
				|{{{Param1}}}
				|Name1={{{Param1}}}Arg2
				|Name2={{Example3
				|{{{Param2}}}
				}}
				}}
				{{Example2|{{{Param3}}}{{{Param4}}}}}
				}}
				{{Example
				|Arg1
				|Name1=Arg2
				|Name2={{Example2
				|{{{Param1}}}
				|Name1={{{Param1}}}Arg2
				|Name2={{Example3
				|{{{Param2}}}
				}}
				}}
				{{Example2|{{{Param3}}}{{{Param4}}}}}
				}}{{{Param1}}}
				
				Lorem ipsum dolor et
				
				{{Example
				|Arg1
				|Name1=Arg2
				|Name2={{Example2
				|{{{Param1}}}
				|Name1={{{Param1}}}Arg2
				|Name2={{Example3
				|{{{Param2}}}
				}}
				}}
				{{Example2|{{{Param3}}}{{{Param4}}}}}
				}}
				TEMPLATE,
				[
					'Example' => [
						[
							'1' => [
								'_text' => 'Arg1'
							],
							'Name1' => [
								'_text' => 'Arg2'
							],
							'Name2' => [
								'Example2' => [
									[
										'1' => [
											'_text' => '{{{Param1}}}'
										],
										'Name1' => [
											'_text' => '{{{Param1}}}Arg2'
										],
										'Name2' => [
											'Example3' => [
												[
													'1' => [
														'_text' => '{{{Param2}}}'
													]
												]
											],
											'_text' => <<<'TEMPLATE'
											{{Example3
											|{{{Param2}}}
											}}
											TEMPLATE
										]
									],
									[
										'1' => [
											'_text' => '{{{Param3}}}{{{Param4}}}'
										]
									]
								],
								'_text' => <<<'TEMPLATE'
								{{Example2
								|{{{Param1}}}
								|Name1={{{Param1}}}Arg2
								|Name2={{Example3
								|{{{Param2}}}
								}}
								}}
								{{Example2|{{{Param3}}}{{{Param4}}}}}
								TEMPLATE
							]
						],
						[
							'1' => [
								'_text' => 'Arg1'
							],
							'Name1' => [
								'_text' => 'Arg2'
							],
							'Name2' => [
								'Example2' => [
									[
										'1' => [
											'_text' => '{{{Param1}}}'
										],
										'Name1' => [
											'_text' => '{{{Param1}}}Arg2'
										],
										'Name2' => [
											'Example3' => [
												[
													'1' => [
														'_text' => '{{{Param2}}}'
													]
												]
											],
											'_text' => <<<'TEMPLATE'
											{{Example3
											|{{{Param2}}}
											}}
											TEMPLATE
										]
									],
									[
										'1' => [
											'_text' => '{{{Param3}}}{{{Param4}}}'
										]
									]
								],
								'_text' => <<<'TEMPLATE'
								{{Example2
								|{{{Param1}}}
								|Name1={{{Param1}}}Arg2
								|Name2={{Example3
								|{{{Param2}}}
								}}
								}}
								{{Example2|{{{Param3}}}{{{Param4}}}}}
								TEMPLATE
							]
						],
						[
							'1' => [
								'_text' => 'Arg1'
							],
							'Name1' => [
								'_text' => 'Arg2'
							],
							'Name2' => [
								'Example2' => [
									[
										'1' => [
											'_text' => '{{{Param1}}}'
										],
										'Name1' => [
											'_text' => '{{{Param1}}}Arg2'
										],
										'Name2' => [
											'Example3' => [
												[
													'1' => [
														'_text' => '{{{Param2}}}'
													]
												]
											],
											'_text' => <<<'TEMPLATE'
											{{Example3
											|{{{Param2}}}
											}}
											TEMPLATE
										]
									],
									[
										'1' => [
											'_text' => '{{{Param3}}}{{{Param4}}}'
										]
									]
								],
								'_text' => <<<'TEMPLATE'
								{{Example2
								|{{{Param1}}}
								|Name1={{{Param1}}}Arg2
								|Name2={{Example3
								|{{{Param2}}}
								}}
								}}
								{{Example2|{{{Param3}}}{{{Param4}}}}}
								TEMPLATE
							]
						]
					],
					'_text' => <<<'TEMPLATE'
					{{Example
					|Arg1
					|Name1=Arg2
					|Name2={{Example2
					|{{{Param1}}}
					|Name1={{{Param1}}}Arg2
					|Name2={{Example3
					|{{{Param2}}}
					}}
					}}
					{{Example2|{{{Param3}}}{{{Param4}}}}}
					}}
					{{Example
					|Arg1
					|Name1=Arg2
					|Name2={{Example2
					|{{{Param1}}}
					|Name1={{{Param1}}}Arg2
					|Name2={{Example3
					|{{{Param2}}}
					}}
					}}
					{{Example2|{{{Param3}}}{{{Param4}}}}}
					}}{{{Param1}}}
					
					Lorem ipsum dolor et
					
					{{Example
					|Arg1
					|Name1=Arg2
					|Name2={{Example2
					|{{{Param1}}}
					|Name1={{{Param1}}}Arg2
					|Name2={{Example3
					|{{{Param2}}}
					}}
					}}
					{{Example2|{{{Param3}}}{{{Param4}}}}}
					}}
					TEMPLATE
				]
			],
            'parameter with default' => [
                '{{Example|{{{Param1|Default1}}}|Arg1}}',
                [
                    'Example' => [
                        [
                            '1' => [
                                '_text' => '{{{Param1|Default1}}}'
                            ],
                            '2' => [
                                '_text' => 'Arg1'
                            ]
                        ]
                    ],
                    '_text' => '{{Example|{{{Param1|Default1}}}|Arg1}}'
                ]
            ],
            'parameter with template as default' => [
                '{{Example|{{{Param1|{{Example2|Arg1}}}}}|Arg1}}',
                [
                    'Example' => [
                        [
                            '1' => [
                                '_text' => '{{{Param1|{{Example2|Arg1}}}}}'
                            ],
                            '2' => [
                                '_text' => 'Arg1'
                            ]
                        ]
                    ],
                    '_text' => '{{Example|{{{Param1|{{Example2|Arg1}}}}}|Arg1}}'
                ]
            ]
		];
	}
}