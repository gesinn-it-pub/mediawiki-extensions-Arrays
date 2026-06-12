<?php

/**
 * @covers \ExtArrays
 */
class ExtArraysTest extends MediaWikiUnitTestCase {

	private function callParseOptions( string $input ): array {
		$method = new ReflectionMethod( ExtArrays::class, 'parse_options' );
		$method->setAccessible( true );
		return $method->invoke( null, $input );
	}

	/**
	 * @dataProvider provideParseOptions
	 */
	public function testParseOptions( string $input, array $expected ): void {
		$result = $this->callParseOptions( $input );
		$this->assertSame( $expected, $result );
	}

	public static function provideParseOptions(): array {
		return [
			'empty string' => [
				'',
				[ '' => true ],
			],
			'single flag' => [
				'unique',
				[ 'unique' => true ],
			],
			'multiple flags' => [
				'unique, sort',
				[ 'unique' => true, 'sort' => true ],
			],
			'key=value pair' => [
				'sort=asc',
				[ 'sort' => 'asc' ],
			],
			'mixed flags and pairs' => [
				'unique, sort=desc',
				[ 'unique' => true, 'sort' => 'desc' ],
			],
			'options are lowercased' => [
				'UNIQUE, Sort=Asc',
				[ 'unique' => true, 'sort' => 'asc' ],
			],
			'spaces around = are ignored' => [
				'sort = desc',
				[ 'sort' => 'desc' ],
			],
			'spaces around , are ignored' => [
				'unique , sort',
				[ 'unique' => true, 'sort' => true ],
			],
		];
	}

	/**
	 * @dataProvider provideArrayUnique
	 */
	public function testArrayUnique( array $input, array $expected ): void {
		$result = ExtArrays::arrayUnique( $input );
		$this->assertSame( $expected, $result );
	}

	public static function provideArrayUnique(): array {
		return [
			'empty array' => [
				[],
				[],
			],
			'no duplicates' => [
				[ 'a', 'b', 'c' ],
				[ 'a', 'b', 'c' ],
			],
			'removes duplicates' => [
				[ 'a', 'b', 'a', 'c' ],
				[ 'a', 'b', 'c' ],
			],
			'removes empty strings' => [
				[ 'a', '', 'b' ],
				[ 'a', 'b' ],
			],
			'trims values before dedup' => [
				[ 'a', ' a', 'b ' ],
				[ 'a', 'b' ],
			],
			'all duplicates' => [
				[ 'x', 'x', 'x' ],
				[ 'x' ],
			],
			'all empty' => [
				[ '', '', '' ],
				[],
			],
		];
	}

	public function testArraySortAscending(): void {
		$result = ExtArrays::arraySort( [ 'banana', 'apple', 'cherry' ], 'asc nolocale' );
		$this->assertSame( [ 'apple', 'banana', 'cherry' ], $result );
	}

	public function testArraySortAscendingAlias(): void {
		$result = ExtArrays::arraySort( [ 'c', 'a', 'b' ], 'ascending nolocale' );
		$this->assertSame( [ 'a', 'b', 'c' ], $result );
	}

	public function testArraySortDescending(): void {
		$result = ExtArrays::arraySort( [ 'apple', 'banana', 'cherry' ], 'desc nolocale' );
		$this->assertSame( [ 'cherry', 'banana', 'apple' ], $result );
	}

	public function testArraySortDescendingAlias(): void {
		$result = ExtArrays::arraySort( [ 'a', 'c', 'b' ], 'descending nolocale' );
		$this->assertSame( [ 'c', 'b', 'a' ], $result );
	}

	public function testArraySortNatural(): void {
		$result = ExtArrays::arraySort( [ 'item10', 'item2', 'item1' ], 'natural' );
		$this->assertSame( [ 'item1', 'item2', 'item10' ], array_values( $result ) );
	}

	public function testArraySortNaturalAlias(): void {
		$result = ExtArrays::arraySort( [ 'z10', 'z2', 'z1' ], 'nat' );
		$this->assertSame( [ 'z1', 'z2', 'z10' ], array_values( $result ) );
	}

	public function testArraySortReverse(): void {
		$result = ExtArrays::arraySort( [ 'a', 'b', 'c' ], 'reverse' );
		$this->assertSame( [ 'c', 'b', 'a' ], $result );
	}

	public function testArraySortRandom(): void {
		$input = [ 'a', 'b', 'c', 'd', 'e' ];
		$result = ExtArrays::arraySort( $input, 'random' );
		$this->assertCount( 5, $result );
		$this->assertSame( [], array_diff( $input, $result ) );
	}

	public function testArraySortRandomAlias(): void {
		$input = [ '1', '2', '3', '4', '5' ];
		$result = ExtArrays::arraySort( $input, 'rand' );
		$this->assertCount( 5, $result );
		$this->assertSame( [], array_diff( $input, $result ) );
	}

	public function testArraySortUnknownModeReturnsArrayUnchanged(): void {
		$result = ExtArrays::arraySort( [ 'b', 'a', 'c' ], 'none' );
		$this->assertSame( [ 'b', 'a', 'c' ], $result );
	}

	public function testArraySortEmptyArray(): void {
		$result = ExtArrays::arraySort( [], 'asc' );
		$this->assertSame( [], $result );
	}

	public function testEscapeForExpansionWithNullTemplatesReturnsInputUnchanged(): void {
		global $egArraysExpansionEscapeTemplates;
		$original = $egArraysExpansionEscapeTemplates;
		try {
			$egArraysExpansionEscapeTemplates = null;
			$result = ExtArrays::escapeForExpansion( 'foo=bar|baz' );
			$this->assertSame( 'foo=bar|baz', $result );
		} finally {
			$egArraysExpansionEscapeTemplates = $original;
		}
	}

	public function testEscapeForExpansionReplacesSpecialCharacters(): void {
		global $egArraysExpansionEscapeTemplates;
		$original = $egArraysExpansionEscapeTemplates;
		try {
			$egArraysExpansionEscapeTemplates = [
				'=' => '{{=}}',
				'|' => '{{!}}',
			];
			$result = ExtArrays::escapeForExpansion( 'a=b|c' );
			$this->assertSame( 'a{{=}}b{{!}}c', $result );
		} finally {
			$egArraysExpansionEscapeTemplates = $original;
		}
	}

	public function testEscapeForExpansionWithNoSpecialCharsReturnsInputUnchanged(): void {
		global $egArraysExpansionEscapeTemplates;
		$original = $egArraysExpansionEscapeTemplates;
		try {
			$egArraysExpansionEscapeTemplates = [ '=' => '{{=}}' ];
			$result = ExtArrays::escapeForExpansion( 'hello world' );
			$this->assertSame( 'hello world', $result );
		} finally {
			$egArraysExpansionEscapeTemplates = $original;
		}
	}

	private function callIsValidRegEx( string $pattern, bool $forRegexFun = false ): bool {
		$method = new ReflectionMethod( ExtArrays::class, 'isValidRegEx' );
		$method->setAccessible( true );
		return $method->invoke( null, $pattern, $forRegexFun );
	}

	public function testIsValidRegExAcceptsSlashDelimiter(): void {
		$this->assertTrue( $this->callIsValidRegEx( '/foo/' ) );
	}

	public function testIsValidRegExAcceptsPipeDelimiter(): void {
		$this->assertTrue( $this->callIsValidRegEx( '|foo|' ) );
	}

	public function testIsValidRegExAcceptsPercentDelimiter(): void {
		$this->assertTrue( $this->callIsValidRegEx( '%foo%' ) );
	}

	public function testIsValidRegExAcceptsFlags(): void {
		$this->assertTrue( $this->callIsValidRegEx( '/foo/i' ) );
		$this->assertTrue( $this->callIsValidRegEx( '/foo/im' ) );
	}

	public function testIsValidRegExRejectsPlainString(): void {
		$this->assertFalse( $this->callIsValidRegEx( 'foo' ) );
	}

	public function testIsValidRegExRejectsInvalidPattern(): void {
		$this->assertFalse( $this->callIsValidRegEx( '/[unclosed/' ) );
	}

	public function testIsValidRegExRejectsEmptyString(): void {
		$this->assertFalse( $this->callIsValidRegEx( '' ) );
	}

	private function callValidateArrayIndex( ExtArrays $store, string $arrayId, &$index, bool $strict = false ): bool {
		$method = new ReflectionMethod( ExtArrays::class, 'validate_array_index' );
		$method->setAccessible( true );
		return $method->invokeArgs( $store, [ $arrayId, &$index, $strict ] );
	}

	private function makeStore( array $arrays ): ExtArrays {
		$store = new ExtArrays();
		foreach ( $arrays as $id => $arr ) {
			$store->mArrays[ $id ] = $arr;
		}
		return $store;
	}

	public function testValidateArrayIndexReturnsTrueForValidIndex(): void {
		$store = $this->makeStore( [ 'myArray' => [ 'a', 'b', 'c' ] ] );
		$index = 1;
		$this->assertTrue( $this->callValidateArrayIndex( $store, 'myArray', $index ) );
		$this->assertSame( 1, $index );
	}

	public function testValidateArrayIndexReturnsFalseForNonexistentArray(): void {
		$store = $this->makeStore( [] );
		$index = 0;
		$this->assertFalse( $this->callValidateArrayIndex( $store, 'missing', $index ) );
	}

	public function testValidateArrayIndexReturnsFalseForOutOfBoundsIndex(): void {
		$store = $this->makeStore( [ 'a' => [ 'x', 'y' ] ] );
		$index = 5;
		$this->assertFalse( $this->callValidateArrayIndex( $store, 'a', $index ) );
	}

	public function testValidateArrayIndexNormalizesNegativeIndex(): void {
		$store = $this->makeStore( [ 'a' => [ 'x', 'y', 'z' ] ] );
		$index = -1;
		$result = $this->callValidateArrayIndex( $store, 'a', $index );
		$this->assertTrue( $result );
		$this->assertSame( 2, $index );
	}

	public function testValidateArrayIndexLenientModeClampsNegativeIndexBeyondStartToZero(): void {
		$store = $this->makeStore( [ 'a' => [ 'x', 'y' ] ] );
		$index = -99;
		$result = $this->callValidateArrayIndex( $store, 'a', $index, false );
		$this->assertTrue( $result );
		$this->assertSame( 0, $index );
	}

	public function testValidateArrayIndexStrictModeRejectsNonNumeric(): void {
		$store = $this->makeStore( [ 'a' => [ 'x' ] ] );
		$index = 'notanumber';
		$this->assertFalse( $this->callValidateArrayIndex( $store, 'a', $index, true ) );
	}

	public function testValidateArrayIndexLenientModeNormalizesNonNumericToZero(): void {
		$store = $this->makeStore( [ 'a' => [ 'x', 'y' ] ] );
		$index = 'notanumber';
		$result = $this->callValidateArrayIndex( $store, 'a', $index, false );
		$this->assertTrue( $result );
		$this->assertSame( 0, $index );
	}
}
