<?php

namespace Amp;

use DOMElement;
use DOMNode;

/**
 * Central helper functionality for all Amp-related PHP code.
 *
 * @package amp/common
 */
final class Amp
{

    /**
     * List of Amp attribute tags that can be appended to the <html> element.
     *
     * @var string[]
     */
    const TAGS = ['amp', '⚡', '⚡4ads', 'amp4ads', '⚡4email', 'amp4email'];

    /**
     * Host and scheme of the Amp cache.
     *
     * @var string
     */
    const CACHE_HOST = 'https://cdn.ampproject.org';

    /**
     * URL of the Amp cache.
     *
     * @var string
     */
    const CACHE_ROOT_URL = self::CACHE_HOST . '/';

    /**
     * List of valid Amp formats.
     *
     * @var string[]
     */
    const FORMATS = ['AMP', 'AMP4EMAIL', 'AMP4ADS'];

    /**
     * List of dynamic components
     *
     * This list should be kept in sync with the list of dynamic components at:
     *
     * @see https://github.com/ampproject/amphtml/blob/master/spec/amp-cache-guidelines.md#guidelines-adding-a-new-cache-to-the-amp-ecosystem
     *
     * @var array[]
     */
    const DYNAMIC_COMPONENTS = [
        Attribute::CUSTOM_ELEMENT  => [Extension::GEO],
        Attribute::CUSTOM_TEMPLATE => [],
    ];

    /**
     * Array of custom element names that delay rendering.
     *
     * @var string[]
     */
    const RENDER_DELAYING_EXTENSIONS = [
        Extension::DYNAMIC_CSS_CLASSES,
        Extension::EXPERIMENT,
        Extension::STORY,
    ];

    /**
     * Check if a given node is the Amp runtime script.
     *
     * The Amp runtime script node is of the form '<script async src="https://cdn.ampproject.org...v0.js"></script>'.
     *
     * @param DOMNode $node Node to check.
     * @return bool Whether the given node is the Amp runtime script.
     */
    public static function isRuntimeScript(DOMNode $node)
    {
        if (! self::isAsyncScript($node)
            || self::isExtension($node)) {
            return false;
        }

        $src = $node->getAttribute(Attribute::SRC);

        if (strpos($src, self::CACHE_ROOT_URL) !== 0) {
            return false;
        }

        if (substr($src, -6) !== '/v0.js'
            && substr($src, -14) !== '/amp4ads-v0.js') {
            return false;
        }

        return true;
    }

    /**
     * Check if a given node is the Amp viewer script.
     *
     * The Amp viewer script node is of the form '<script async
     * src="https://cdn.ampproject.org/v0/amp-viewer-integration-...js>"</script>'.
     *
     * @param DOMNode $node Node to check.
     * @return bool Whether the given node is the Amp runtime script.
     */
    public static function isViewerScript(DOMNode $node)
    {
        if (! self::isAsyncScript($node)
            || self::isExtension($node)) {
            return false;
        }

        $src = $node->getAttribute(Attribute::SRC);

        if (strpos($src, self::CACHE_HOST . '/v0/amp-viewer-integration-') !== 0) {
            return false;
        }

        if (substr($src, -3) !== '.js') {
            return false;
        }

        return true;
    }

    /**
     * Check if a given node is an Amp extension.
     *
     * @param DOMNode $node Node to check.
     * @return bool Whether the given node is the Amp runtime script.
     */
    public static function isExtension(DOMNode $node)
    {
        return ! empty(self::getExtensionName($node));
    }

    /**
     * Get the name of the extension.
     *
     * Returns an empty string if the name of the extension could not be retrieved.
     *
     * @param DOMNode $node Node to get the name of.
     * @return string Name of the custom node or template. Empty string if none found.
     */
    public static function getExtensionName(DOMNode $node)
    {
        if (! $node instanceof DOMElement || $node->tagName !== Tag::SCRIPT) {
            return '';
        }

        if ($node->hasAttribute(Attribute::CUSTOM_ELEMENT)) {
            return $node->getAttribute(Attribute::CUSTOM_ELEMENT);
        }

        if ($node->hasAttribute(Attribute::CUSTOM_TEMPLATE)) {
            return $node->getAttribute(Attribute::CUSTOM_TEMPLATE);
        }

        if ($node->hasAttribute(Attribute::HOST_SERVICE)) {
            return $node->getAttribute(Attribute::HOST_SERVICE);
        }

        return '';
    }

    /**
     * Check whether a given node is a script for a render-delaying extension.
     *
     * @param DOMNode $node Node to check.
     * @return bool Whether the node is a script for a render-delaying extension.
     */
    public static function isRenderDelayingExtension(DOMNode $node)
    {
        $extensionName = self::getExtensionName($node);

        if (empty($extensionName)) {
            return false;
        }

        return in_array($extensionName, self::RENDER_DELAYING_EXTENSIONS, true);
    }

    /**
     * Check whether a given DOM node is an Amp custom element.
     *
     * @param DOMNode $node DOM node to check.
     * @return bool Whether the checked DOM node is an Amp custom element.
     */
    public static function isCustomElement(DOMNode $node)
    {
        return $node instanceof DOMElement && strpos($node->tagName, Extension::PREFIX) === 0;
    }

    /**
     * Check whether a given node is an async <script> element.
     *
     * @param DOMNode $node Node to check.
     * @return bool Whether the given node is an async <script> element.
     */
    private static function isAsyncScript(DOMNode $node)
    {
        if (! $node instanceof DOMElement
            || $node->tagName !== Tag::SCRIPT) {
            return false;
        }

        if (! $node->hasAttribute(Attribute::SRC)
            || ! $node->hasAttribute(Attribute::ASYNC)) {
            return false;
        }

        return true;
    }
}
