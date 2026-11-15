<?php

namespace Modera\DirectBundle\Attribute;

/**
 * Exposes a controller action to the Ext Direct API (same as the "@Remote" docblock tag).
 *
 * @copyright 2026 Modera Foundation
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class Remote
{
}
