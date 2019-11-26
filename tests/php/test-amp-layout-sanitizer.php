<?php
/**
 * Class AMP_Layout_Sanitizer_Test
 *
 * @package AMP
 */

/**
 * Test AMP_Layout_Sanitizer_Test
 *
 * @covers AMP_Layout_Sanitizer_Test
 */
class AMP_Layout_Sanitizer_Test extends WP_UnitTestCase {

	/**
	 * Get body data.
	 *
	 * @return array Content.
	 */
	public function get_body_data() {
		return [
			'no_width_or_height'                          => [
				'<p data-amp-layout="fill"></p>',
				'<p layout="fill"></p>',
			],

			'no_layout_attr'                              => [
				'<p width="10"></p>',
			],

			'no_data_layout_attr'                         => [
				'<p width="10"></p>',
			],

			'data_layout_attr'                            => [
				'<p width="10" data-amp-layout="fill"></p>',
				'<p width="10" layout="fill"></p>',
			],

			'data_layout_attr_with_100%_width'            => [
				'<p width="100%" data-amp-layout="fill"></p>',
				'<p width="auto" layout="fixed-height"></p>',
			],

			'data_layout_attr_with_100%_width_and_height' => [
				'<p width="100%" height="100%" data-amp-layout="fill"></p>',
				'<p layout="fill"></p>',
			],

			'100%_width_with_layout_attr'                 => [
				'<p width="100%" layout="fill"></p>',
			],

			'100%_width_and_height_with_layout_attr'      => [
				'<p width="100%" height="100%" layout="fill"></p>',
			],
		];
	}

	/**
	 * @param string $source  Content.
	 * @param string $expected Expected content.
	 * @dataProvider get_body_data
	 * @covers AMP_Layout_Sanitizer::sanitize()
	 */
	public function test_sanitize( $source, $expected = null ) {
		$expected  = isset( $expected ) ? $expected : $source;
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Layout_Sanitizer( $dom );

		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEqualMarkup( $expected, $content );
	}

	/**
	 * Assert markup is equal.
	 *
	 * @param string $expected Expected markup.
	 * @param string $actual   Actual markup.
	 */
	public function assertEqualMarkup( $expected, $actual ) {
		$actual   = preg_replace( '/\s+/', ' ', $actual );
		$expected = preg_replace( '/\s+/', ' ', $expected );
		$actual   = preg_replace( '/(?<=>)\s+(?=<)/', '', trim( $actual ) );
		$expected = preg_replace( '/(?<=>)\s+(?=<)/', '', trim( $expected ) );

		$this->assertEquals(
			array_filter( preg_split( '#(<[^>]+>|[^<>]+)#', $expected, -1, PREG_SPLIT_DELIM_CAPTURE ) ),
			array_filter( preg_split( '#(<[^>]+>|[^<>]+)#', $actual, -1, PREG_SPLIT_DELIM_CAPTURE ) )
		);
	}
}
