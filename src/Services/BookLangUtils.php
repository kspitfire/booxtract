<?php

namespace Booxtract\Services;

class BookLangUtils
{
    const LANG_RU = 'ru';
    const LANG_EN = 'en';
    const LANG_FR = 'fr';
    const LANG_UK = 'uk';
    const LANG_DE = 'de';

    const TITLE_EN = '[EN]';
    const TITLE_OLD_RU = '[Ѣ]';
    const TITLE_FR = '[FR]';
    const TITLE_DE = '[DE]';
    const TITLE_UK = '[UK]';

    const LANG_MAP = [
        self::LANG_RU => '',
        self::LANG_EN => self::TITLE_EN,
        self::LANG_FR => self::TITLE_FR,
        self::LANG_UK => self::TITLE_UK,
        self::LANG_DE => self::TITLE_DE,
    ];

    public static function checkIsOldRussian(string $sample): string
    {
        return (mb_substr_count(mb_strtolower($sample), 'ѣ') > 2) ? self::TITLE_OLD_RU : '';
    }
}