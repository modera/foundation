<?php

namespace Modera\ServerCrudBundle\DataMapping;

/**
 * @copyright 2024 Modera Foundation
 */
interface PreferencesAwareUserInterface
{
    public const SETTINGS_DATE_FORMAT = 'dateFormat';
    public const SETTINGS_DATETIME_FORMAT = 'datetimeFormat';

    /**
     * @return array<string, string>
     */
    public function getPreferences(): array;
}
