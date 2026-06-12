<?php

/**
 * @covers \ExtArrays
 * @group Database
 */
class ExtArraysIntegrationTest extends MediaWikiIntegrationTestCase {

	private function parse( string $wikitext ): string {
		$out = $this->getServiceContainer()->getParser()->parse(
			$wikitext,
			\Title::makeTitle( NS_MAIN, 'Test' ),
			\ParserOptions::newFromAnon()
		);
		return \Parser::stripOuterParagraph( $out->getRawText() );
	}

	// -----------------------------------------------------------------------
	// #arraydefine / #arraysize
	// -----------------------------------------------------------------------

	/** @return array */
	public static function provideArraydefine(): array {
		return [
			'comma-separated values' => [
				'{{#arraydefine:a|x,y,z}}{{#arraysize:a}}',
				'3',
			],
			'empty value creates empty array' => [
				'{{#arraydefine:a|}}{{#arraysize:a}}',
				'0',
			],
			'singleempty option preserves single empty element' => [
				'{{#arraydefine:a||,|singleempty}}{{#arraysize:a}}',
				'1',
			],
			'unique option removes duplicates' => [
				'{{#arraydefine:a|x,y,x,z,y|,|unique}}{{#arraysize:a}}',
				'3',
			],
			'sort=asc option sorts ascending' => [
				'{{#arraydefine:a|c,a,b|,|sort=asc}}{{#arrayprint:a}}',
				'a, b, c',
			],
			'regex delimiter splits on whitespace' => [
				'{{#arraydefine:a|a b c|/\s+/}}{{#arraysize:a}}',
				'3',
			],
			'print=list returns comma-joined output' => [
				'{{#arraydefine:a|x,y,z|,|print=list}}',
				'x, y, z',
			],
			'non-existent array returns empty string' => [
				'{{#arraysize:doesnotexist}}',
				'',
			],
		];
	}

	/**
	 * @dataProvider provideArraydefine
	 */
	public function testArraydefine( string $wikitext, string $expected ): void {
		$this->assertSame( $expected, $this->parse( $wikitext ) );
	}

	// -----------------------------------------------------------------------
	// #arrayprint
	// -----------------------------------------------------------------------

	/** @return array */
	public static function provideArrayprint(): array {
		return [
			'default comma delimiter' => [
				'{{#arraydefine:a|x,y,z}}{{#arrayprint:a}}',
				'x, y, z',
			],
			'custom delimiter' => [
				'{{#arraydefine:a|x,y,z}}{{#arrayprint:a|;}}',
				'x;y;z',
			],
			'subject template replaces placeholder' => [
				'{{#arraydefine:a|1,2,3}}{{#arrayprint:a|,|@|[@]}}',
				'[1],[2],[3]',
			],
			'non-existent array returns empty string' => [
				'{{#arrayprint:nosucharray}}',
				'',
			],
		];
	}

	/**
	 * @dataProvider provideArrayprint
	 */
	public function testArrayprint( string $wikitext, string $expected ): void {
		$this->assertSame( $expected, $this->parse( $wikitext ) );
	}

	// -----------------------------------------------------------------------
	// #arrayindex
	// -----------------------------------------------------------------------

	/** @return array */
	public static function provideArrayindex(): array {
		return [
			'positive index returns element' => [
				'{{#arraydefine:a|x,y,z}}{{#arrayindex:a|1}}',
				'y',
			],
			'negative index counts from end' => [
				'{{#arraydefine:a|x,y,z}}{{#arrayindex:a|-1}}',
				'z',
			],
			'out-of-bounds returns default' => [
				'{{#arraydefine:a|x}}{{#arrayindex:a|99|fallback}}',
				'fallback',
			],
			'non-existent array returns default' => [
				'{{#arrayindex:nosucharray|0|mydefault}}',
				'mydefault',
			],
			'missing index argument returns empty string' => [
				'{{#arraydefine:a|x}}{{#arrayindex:a}}',
				'',
			],
		];
	}

	/**
	 * @dataProvider provideArrayindex
	 */
	public function testArrayindex( string $wikitext, string $expected ): void {
		$this->assertSame( $expected, $this->parse( $wikitext ) );
	}

	// -----------------------------------------------------------------------
	// #arraysearch
	// -----------------------------------------------------------------------

	/** @return array */
	public static function provideArraysearch(): array {
		return [
			'finds element and returns index' => [
				'{{#arraydefine:a|red,green,blue}}{{#arraysearch:a|green}}',
				'1',
			],
			'not found returns empty string' => [
				'{{#arraydefine:a|red,green,blue}}{{#arraysearch:a|yellow}}',
				'',
			],
			'yes param returned on match' => [
				'{{#arraydefine:a|x,y}}{{#arraysearch:a|x|0|yes|no}}',
				'yes',
			],
			'no param returned when not found' => [
				'{{#arraydefine:a|x,y}}{{#arraysearch:a|z|0|yes|no}}',
				'no',
			],
			'start index skips earlier occurrences' => [
				'{{#arraydefine:a|x,y,x}}{{#arraysearch:a|x|1}}',
				'2',
			],
			'regex needle matches by pattern' => [
				'{{#arraydefine:a|foo,123,bar}}{{#arraysearch:a|/\d+/}}',
				'1',
			],
			'non-existent array returns empty string' => [
				'{{#arraysearch:nosuch|x}}',
				'',
			],
		];
	}

	/**
	 * @dataProvider provideArraysearch
	 */
	public function testArraysearch( string $wikitext, string $expected ): void {
		$this->assertSame( $expected, $this->parse( $wikitext ) );
	}

	// -----------------------------------------------------------------------
	// #arraysearcharray
	// -----------------------------------------------------------------------

	/** @return array */
	public static function provideArraysearcharray(): array {
		return [
			'plain string match collects all occurrences' => [
				'{{#arraydefine:src|a,b,a,c}}{{#arraysearcharray:dst|src|a}}{{#arrayprint:dst}}',
				'a, a',
			],
			'regex match selects numeric elements' => [
				'{{#arraydefine:src|foo,123,bar,456}}{{#arraysearcharray:dst|src|/\d+/}}{{#arrayprint:dst}}',
				'123, 456',
			],
			'limit caps number of results' => [
				'{{#arraydefine:src|a,a,a,a}}{{#arraysearcharray:dst|src|a|0|2}}{{#arraysize:dst}}',
				'2',
			],
			'transform applies regex replacement' => [
				'{{#arraydefine:src|foo,bar}}{{#arraysearcharray:dst|src|/(\w+)/|0||[$1]}}{{#arrayprint:dst}}',
				'[foo], [bar]',
			],
			'missing source array creates empty destination' => [
				'{{#arraysearcharray:dst}}{{#arraysize:dst}}',
				'0',
			],
		];
	}

	/**
	 * @dataProvider provideArraysearcharray
	 */
	public function testArraysearcharray( string $wikitext, string $expected ): void {
		$this->assertSame( $expected, $this->parse( $wikitext ) );
	}

	// -----------------------------------------------------------------------
	// #arrayslice
	// -----------------------------------------------------------------------

	/** @return array */
	public static function provideArrayslice(): array {
		return [
			'offset and length select subrange' => [
				'{{#arraydefine:src|a,b,c,d,e}}{{#arrayslice:dst|src|1|2}}{{#arrayprint:dst}}',
				'b, c',
			],
			'negative offset counts from end' => [
				'{{#arraydefine:src|a,b,c,d,e}}{{#arrayslice:dst|src|-2}}{{#arrayprint:dst}}',
				'd, e',
			],
			'no offset clones the full array' => [
				'{{#arraydefine:src|a,b,c}}{{#arrayslice:dst|src}}{{#arrayprint:dst}}',
				'a, b, c',
			],
			'missing source creates empty destination' => [
				'{{#arrayslice:dst}}{{#arraysize:dst}}',
				'0',
			],
		];
	}

	/**
	 * @dataProvider provideArrayslice
	 */
	public function testArrayslice( string $wikitext, string $expected ): void {
		$this->assertSame( $expected, $this->parse( $wikitext ) );
	}

	// -----------------------------------------------------------------------
	// #arrayreset
	// -----------------------------------------------------------------------

	/** @return array */
	public static function provideArrayreset(): array {
		return [
			'reset all arrays leaves nothing defined' => [
				'{{#arraydefine:a|x}}{{#arraydefine:b|y}}{{#arrayreset:}}{{#arraysize:a}}{{#arraysize:b}}',
				'',
			],
			'reset specific array leaves others intact' => [
				'{{#arraydefine:a|x}}{{#arraydefine:b|y}}{{#arrayreset:a}}{{#arraysize:a}}{{#arraysize:b}}',
				'1',
			],
		];
	}

	/**
	 * @dataProvider provideArrayreset
	 */
	public function testArrayreset( string $wikitext, string $expected ): void {
		$this->assertSame( $expected, $this->parse( $wikitext ) );
	}

	// -----------------------------------------------------------------------
	// #arrayunique
	// -----------------------------------------------------------------------

	/** @return array */
	public static function provideArrayunique(): array {
		return [
			'removes duplicates and empty elements' => [
				'{{#arraydefine:a|x,y,x,,y}}{{#arrayunique:a}}{{#arrayprint:a}}',
				'x, y',
			],
			'non-existent array is a no-op' => [
				'{{#arrayunique:nosuch}}',
				'',
			],
		];
	}

	/**
	 * @dataProvider provideArrayunique
	 */
	public function testArrayunique( string $wikitext, string $expected ): void {
		$this->assertSame( $expected, $this->parse( $wikitext ) );
	}

	// -----------------------------------------------------------------------
	// #arraysort
	// -----------------------------------------------------------------------

	/** @return array */
	public static function provideArraysort(): array {
		return [
			'ascending order' => [
				'{{#arraydefine:a|c,a,b}}{{#arraysort:a|asce}}{{#arrayprint:a}}',
				'a, b, c',
			],
			'descending order' => [
				'{{#arraydefine:a|c,a,b}}{{#arraysort:a|desc}}{{#arrayprint:a}}',
				'c, b, a',
			],
			'reverse order' => [
				'{{#arraydefine:a|a,b,c}}{{#arraysort:a|reverse}}{{#arrayprint:a}}',
				'c, b, a',
			],
			'ascending with nolocale flag' => [
				'{{#arraydefine:a|c,a,b}}{{#arraysort:a|asc nolocale}}{{#arrayprint:a}}',
				'a, b, c',
			],
			'descending with nolocale flag' => [
				'{{#arraydefine:a|c,a,b}}{{#arraysort:a|desc nolocale}}{{#arrayprint:a}}',
				'c, b, a',
			],
			'non-existent array returns empty string' => [
				'{{#arraysort:nosuch|asc}}',
				'',
			],
		];
	}

	/**
	 * @dataProvider provideArraysort
	 */
	public function testArraysort( string $wikitext, string $expected ): void {
		$this->assertSame( $expected, $this->parse( $wikitext ) );
	}

	// -----------------------------------------------------------------------
	// #arraymerge
	// -----------------------------------------------------------------------

	/** @return array */
	public static function provideArraymerge(): array {
		return [
			'merges two arrays preserving duplicates' => [
				'{{#arraydefine:a|x,y}}{{#arraydefine:b|y,z}}{{#arraymerge:c|a|b}}{{#arrayprint:c}}',
				'x, y, y, z',
			],
			'merges three arrays in order' => [
				'{{#arraydefine:a|1}}{{#arraydefine:b|2}}{{#arraydefine:c|3}}{{#arraymerge:d|a|b|c}}{{#arrayprint:d}}',
				'1, 2, 3',
			],
			'single source array is copied' => [
				'{{#arraydefine:a|x,y}}{{#arraymerge:b|a}}{{#arraysize:b}}',
				'2',
			],
			'no source arrays creates empty destination' => [
				'{{#arraymerge:b}}{{#arraysize:b}}',
				'0',
			],
		];
	}

	/**
	 * @dataProvider provideArraymerge
	 */
	public function testArraymerge( string $wikitext, string $expected ): void {
		$this->assertSame( $expected, $this->parse( $wikitext ) );
	}

	// -----------------------------------------------------------------------
	// #arrayunion
	// -----------------------------------------------------------------------

	/** @return array */
	public static function provideArrayunion(): array {
		return [
			'merges two arrays without duplicates' => [
				'{{#arraydefine:a|x,y,z}}{{#arraydefine:b|y,z,w}}{{#arrayunion:c|a|b}}{{#arrayprint:c}}',
				'x, y, z, w',
			],
			'single source array is copied without dedup' => [
				'{{#arraydefine:a|x,y,x}}{{#arrayunion:b|a}}{{#arraysize:b}}',
				'3',
			],
		];
	}

	/**
	 * @dataProvider provideArrayunion
	 */
	public function testArrayunion( string $wikitext, string $expected ): void {
		$this->assertSame( $expected, $this->parse( $wikitext ) );
	}

	// -----------------------------------------------------------------------
	// #arraydiff
	// -----------------------------------------------------------------------

	/** @return array */
	public static function provideArraydiff(): array {
		return [
			'removes elements present in second array' => [
				'{{#arraydefine:a|a,b,c,d}}{{#arraydefine:b|b,d}}{{#arraydiff:c|a|b}}{{#arrayprint:c}}',
				'a, c',
			],
			'three arrays subtract successively' => [
				'{{#arraydefine:a|a,b,c,d,e}}{{#arraydefine:b|b,d}}{{#arraydefine:c|e}}{{#arraydiff:d|a|b|c}}{{#arrayprint:d}}',
				'a, c',
			],
			'single source array is copied unchanged' => [
				'{{#arraydefine:a|x,y,z}}{{#arraydiff:b|a}}{{#arraysize:b}}',
				'3',
			],
		];
	}

	/**
	 * @dataProvider provideArraydiff
	 */
	public function testArraydiff( string $wikitext, string $expected ): void {
		$this->assertSame( $expected, $this->parse( $wikitext ) );
	}

	// -----------------------------------------------------------------------
	// #arrayintersect
	// -----------------------------------------------------------------------

	/** @return array */
	public static function provideArrayintersect(): array {
		return [
			'returns common elements of two arrays' => [
				'{{#arraydefine:a|a,b,c}}{{#arraydefine:b|b,c,d}}{{#arrayintersect:c|a|b}}{{#arrayprint:c}}',
				'b, c',
			],
			'three-way intersection returns only shared elements' => [
				'{{#arraydefine:a|a,b,c,d}}{{#arraydefine:b|b,c,d,e}}{{#arraydefine:c|c,d,e,f}}{{#arrayintersect:d|a|b|c}}{{#arrayprint:d}}',
				'c, d',
			],
			'no overlap produces empty array' => [
				'{{#arraydefine:a|x,y}}{{#arraydefine:b|p,q}}{{#arrayintersect:c|a|b}}{{#arraysize:c}}',
				'0',
			],
			'single source array is copied unchanged' => [
				'{{#arraydefine:a|x,y,z}}{{#arrayintersect:b|a}}{{#arraysize:b}}',
				'3',
			],
		];
	}

	/**
	 * @dataProvider provideArrayintersect
	 */
	public function testArrayintersect( string $wikitext, string $expected ): void {
		$this->assertSame( $expected, $this->parse( $wikitext ) );
	}

	public function testCreateArraySanitizesValues(): void {
		$parser = $this->getServiceContainer()->getParser();
		$parser->parse( '', \Title::makeTitle( NS_MAIN, 'Test' ), \ParserOptions::newFromAnon() );

		$store = ExtArrays::get( $parser );
		$store->createArray( 'myArr', [ ' foo ', 'bar ', ' baz' ] );

		$this->assertSame( [ 'foo', 'bar', 'baz' ], $store->mArrays['myArr'] );
	}

	public function testUnsetArrayReturnsTrueWhenRemoved(): void {
		$parser = $this->getServiceContainer()->getParser();
		$parser->parse( '', \Title::makeTitle( NS_MAIN, 'Test' ), \ParserOptions::newFromAnon() );

		$store = ExtArrays::get( $parser );
		$store->createArray( 'myArr', [ 'x' ] );

		$this->assertTrue( $store->unsetArray( 'myArr' ) );
		$this->assertArrayNotHasKey( 'myArr', $store->mArrays );
	}

	public function testUnsetArrayReturnsFalseForMissingArray(): void {
		$parser = $this->getServiceContainer()->getParser();
		$parser->parse( '', \Title::makeTitle( NS_MAIN, 'Test' ), \ParserOptions::newFromAnon() );

		$store = ExtArrays::get( $parser );
		$this->assertFalse( $store->unsetArray( 'nonexistent' ) );
	}

	public function testOnParserClearStateResetsArrayStore(): void {
		$parser = $this->getServiceContainer()->getParser();
		$parser->parse(
			'{{#arraydefine:a|x,y,z}}',
			\Title::makeTitle( NS_MAIN, 'Test' ),
			\ParserOptions::newFromAnon()
		);

		ExtArrays::onParserClearState( $parser );

		$this->assertSame( [], $parser->mExtArrays->mArrays );
	}
}
