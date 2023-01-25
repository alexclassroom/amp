## Method `AMP_Base_Embed_Handler::__construct()`

```php
public function __construct( $args = array() );
```

Constructor.

### Arguments

* `array $args` - Height and width for embed.

### Source

:link: [includes/embeds/class-amp-base-embed-handler.php:66](/includes/embeds/class-amp-base-embed-handler.php#L66-L74)

<details>
<summary>Show Code</summary>

```php
public function __construct( $args = [] ) {
	$this->args = wp_parse_args(
		$args,
		[
			'width'  => $this->DEFAULT_WIDTH,
			'height' => $this->DEFAULT_HEIGHT,
		]
	);
}
```

</details>
