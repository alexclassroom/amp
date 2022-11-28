## Method `AMP_Base_Sanitizer::set_layout()`

```php
public function set_layout( $attributes );
```

Sets the layout, and possibly the &#039;height&#039; and &#039;width&#039; attributes.

### Arguments

* `array $attributes` - {      Attributes.      @type string     $bottom      @type int|string $height      @type string     $layout      @type string     $left      @type string     $position      @type string     $right      @type string     $style      @type string     $top      @type int|string $width }

### Return value

`array` - Attributes.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:355](/includes/sanitizers/class-amp-base-sanitizer.php#L355-L462)

<details>
<summary>Show Code</summary>

```php
public function set_layout( $attributes ) {
	if ( isset( $attributes['layout'] ) && ( 'fill' === $attributes['layout'] || 'flex-item' !== $attributes['layout'] ) ) {
		return $attributes;
	}
	// Special-case handling for inline style that should be transformed into layout=fill.
	if ( ! empty( $attributes['style'] ) ) {
		$styles = $this->parse_style_string( $attributes['style'] );
		// Apply fill layout if top, left, bottom, right are used.
		if ( isset( $styles['position'], $styles['top'], $styles['left'], $styles['bottom'], $styles['right'] )
			&& 'absolute' === $styles['position']
			&& 0 === (int) $styles['top']
			&& 0 === (int) $styles['left']
			&& 0 === (int) $styles['bottom']
			&& 0 === (int) $styles['right']
			&& ( ! isset( $attributes['width'] ) || '100%' === $attributes['width'] )
			&& ( ! isset( $attributes['height'] ) || '100%' === $attributes['height'] )
		) {
			unset( $attributes['style'], $styles['position'], $styles['top'], $styles['left'], $styles['bottom'], $styles['right'] );
			if ( ! empty( $styles ) ) {
				$attributes['style'] = $this->reassemble_style_string( $styles );
			}
			$attributes['layout'] = 'fill';
			unset( $attributes['height'], $attributes['width'] );
			return $attributes;
		}
		// Apply fill layout if top, left, width, height are used.
		if ( isset( $styles['position'], $styles['top'], $styles['left'], $styles['width'], $styles['height'] )
			&& 'absolute' === $styles['position']
			&& 0 === (int) $styles['top']
			&& 0 === (int) $styles['left']
			&& '100%' === (string) $styles['width']
			&& '100%' === (string) $styles['height']
		) {
			unset( $attributes['style'], $styles['position'], $styles['top'], $styles['left'], $styles['width'], $styles['height'] );
			if ( ! empty( $styles ) ) {
				$attributes['style'] = $this->reassemble_style_string( $styles );
			}
			$attributes['layout'] = 'fill';
			unset( $attributes['height'], $attributes['width'] );
			return $attributes;
		}
		// Apply fill layout if width & height are 100%.
		if (
			( isset( $styles['position'] ) && 'absolute' === $styles['position'] )
			&& (
				( isset( $attributes['width'] ) && '100%' === $attributes['width'] )
				||
				( isset( $styles['width'] ) && '100%' === $styles['width'] )
			)
			&& (
				( isset( $attributes['height'] ) && '100%' === $attributes['height'] )
				||
				( isset( $styles['height'] ) && '100%' === $styles['height'] )
			)
		) {
			unset( $attributes['style'], $attributes['width'], $attributes['height'] );
			unset( $styles['position'], $styles['width'], $styles['height'] );
			if ( ! empty( $styles ) ) {
				$attributes['style'] = $this->reassemble_style_string( $styles );
			}
			$attributes['layout'] = 'fill';
			return $attributes;
		}
		// Make sure the width and height styles are copied to the width and height attributes since AMP will ultimately inline them as styles.
		$pending_attributes = [];
		$seen_units         = [];
		foreach ( wp_array_slice_assoc( $styles, [ 'height', 'width' ] ) as $dimension => $value ) {
			$value = $this->sanitize_dimension( $value, $dimension );
			if ( '' === $value ) {
				continue;
			}
			if ( 'auto' !== $value ) {
				$this_unit = preg_replace( '/^.*\d/', '', $value );
				if ( ! $this_unit ) {
					$this_unit = 'px';
				}
				$seen_units[ $this_unit ] = true;
			}
			$pending_attributes[ $dimension ] = $value;
		}
		if ( $pending_attributes && count( $seen_units ) <= 1 ) {
			$attributes = array_merge( $attributes, $pending_attributes );
		}
	}
	if ( isset( $attributes['width'], $attributes['height'] ) && '100%' === $attributes['width'] && '100%' === $attributes['height'] ) {
		unset( $attributes['width'], $attributes['height'] );
		$attributes['layout'] = 'fill';
	} else {
		if ( empty( $attributes['height'] ) ) {
			unset( $attributes['width'] );
			$attributes['height'] = self::FALLBACK_HEIGHT;
		}
		if ( empty( $attributes['width'] ) || '100%' === $attributes['width'] || 'auto' === $attributes['width'] ) {
			$attributes['layout'] = 'fixed-height';
			$attributes['width']  = 'auto';
		}
	}
	return $attributes;
}
```

</details>
