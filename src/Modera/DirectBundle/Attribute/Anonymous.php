<?php

namespace Modera\DirectBundle\Attribute;

/**
 * Allows calling the remote action without a session, also when secure access is the default (same as the "@Anonymous" docblock tag).
 *
 * @copyright 2026 Modera Foundation
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class Anonymous
{
}
