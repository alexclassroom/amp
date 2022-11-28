## Method `AMP_Base_Embed_Handler::create_overflow_button_markup()`

```php
protected function create_overflow_button_markup( $text = null );
```

Create overflow button markup.

### Arguments

* `string $text` - Button text (optional).

### Return value

`string` - Button markup.

### Source

:link: [includes/embeds/class-amp-base-embed-handler.php:234](/includes/embeds/class-amp-base-embed-handler.php#L234-L239)

<details>
<summary>Show Code</summary>

```php
protected function create_overflow_button_markup( $text = null ) {
	if ( ! $text ) {
		$text = __( 'See more', 'amp' );
	}
	return sprintf( '<button overflow type="button">%s</button>', esc_html( $text ) );
}
```

</details>
