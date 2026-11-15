<?php

namespace Modera\DirectBundle\Attribute;

/**
 * Marks a remote action as a form handler, so Ext Direct submits forms and files to it (same as the "@Form" docblock tag).
 *
 * @copyright 2026 Modera Foundation
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class Form
{
}
