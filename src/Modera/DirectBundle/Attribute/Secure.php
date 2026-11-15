<?php

namespace Modera\DirectBundle\Attribute;

/**
 * Requires an authenticated session to call the remote action (same as the "@Secure" docblock tag).
 *
 * @copyright 2026 Modera Foundation
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class Secure
{
}
