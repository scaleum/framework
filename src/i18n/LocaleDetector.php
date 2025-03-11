<?php
declare (strict_types = 1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\i18n;

/**
 * LocaleDetector
 *
 * @author Maxim Kirichenko <kirichenko.maxim@gmail.com>
 */
class LocaleDetector extends LocaleIdentityAbstract {
    private static array $defaultCountries = [
        'af'  => 'ZA', // Африкаанс - ЮАР
        'ar'  => 'SA', // Арабский - Саудовская Аравия
        'az'  => 'AZ', // Азербайджанский - Азербайджан
        'be'  => 'BY', // Белорусский - Беларусь
        'bg'  => 'BG', // Болгарский - Болгария
        'bn'  => 'BD', // Бенгальский - Бангладеш
        'bs'  => 'BA', // Боснийский - Босния и Герцеговина
        'ca'  => 'ES', // Каталонский - Испания
        'cs'  => 'CZ', // Чешский - Чехия
        'cy'  => 'GB', // Валлийский - Великобритания
        'da'  => 'DK', // Датский - Дания
        'de'  => 'DE', // Немецкий - Германия
        'el'  => 'GR', // Греческий - Греция
        'en'  => 'US', // Английский - США
        'es'  => 'ES', // Испанский - Испания
        'et'  => 'EE', // Эстонский - Эстония
        'eu'  => 'ES', // Баскский - Испания
        'fa'  => 'IR', // Персидский - Иран
        'fi'  => 'FI', // Финский - Финляндия
        'fil' => 'PH', // Филиппинский - Филиппины
        'fr'  => 'FR', // Французский - Франция
        'ga'  => 'IE', // Ирландский - Ирландия
        'gl'  => 'ES', // Галисийский - Испания
        'gu'  => 'IN', // Гуджарати - Индия
        'he'  => 'IL', // Иврит - Израиль
        'hi'  => 'IN', // Хинди - Индия
        'hr'  => 'HR', // Хорватский - Хорватия
        'hu'  => 'HU', // Венгерский - Венгрия
        'hy'  => 'AM', // Армянский - Армения
        'id'  => 'ID', // Индонезийский - Индонезия
        'is'  => 'IS', // Исландский - Исландия
        'it'  => 'IT', // Итальянский - Италия
        'ja'  => 'JP', // Японский - Япония
        'ka'  => 'GE', // Грузинский - Грузия
        'kk'  => 'KZ', // Казахский - Казахстан
        'km'  => 'KH', // Кхмерский - Камбоджа
        'kn'  => 'IN', // Каннада - Индия
        'ko'  => 'KR', // Корейский - Южная Корея
        'ky'  => 'KG', // Киргизский - Киргизия
        'lt'  => 'LT', // Литовский - Литва
        'lv'  => 'LV', // Латышский - Латвия
        'mk'  => 'MK', // Македонский - Северная Македония
        'ml'  => 'IN', // Малаялам - Индия
        'mn'  => 'MN', // Монгольский - Монголия
        'mr'  => 'IN', // Маратхи - Индия
        'ms'  => 'MY', // Малайский - Малайзия
        'mt'  => 'MT', // Мальтийский - Мальта
        'my'  => 'MM', // Бирманский - Мьянма
        'nb'  => 'NO', // Норвежский букмол - Норвегия
        'ne'  => 'NP', // Непальский - Непал
        'nl'  => 'NL', // Голландский - Нидерланды
        'nn'  => 'NO', // Нюнорск (норвежский) - Норвегия
        'pa'  => 'IN', // Панджаби - Индия
        'pl'  => 'PL', // Польский - Польша
        'ps'  => 'AF', // Пушту - Афганистан
        'pt'  => 'PT', // Португальский - Португалия
        'ro'  => 'RO', // Румынский - Румыния
        'ru'  => 'RU', // Русский - Россия
        'si'  => 'LK', // Сингальский - Шри-Ланка
        'sk'  => 'SK', // Словацкий - Словакия
        'sl'  => 'SI', // Словенский - Словения
        'sq'  => 'AL', // Албанский - Албания
        'sr'  => 'RS', // Сербский - Сербия
        'sv'  => 'SE', // Шведский - Швеция
        'sw'  => 'TZ', // Суахили - Танзания
        'ta'  => 'IN', // Тамильский - Индия
        'te'  => 'IN', // Телугу - Индия
        'th'  => 'TH', // Тайский - Таиланд
        'tr'  => 'TR', // Турецкий - Турция
        'uk'  => 'UA', // Украинский - Украина
        'ur'  => 'PK', // Урду - Пакистан
        'uz'  => 'UZ', // Узбекский - Узбекистан
        'vi'  => 'VN', // Вьетнамский - Вьетнам
        'zh'  => 'CN', // Китайский - Китай
        'zu'  => 'ZA', // Зулу - ЮАР
    ];

    protected function identify(): bool | string {
        // Проверяем переменные окружения, используемые в CLI
        $locale = getenv('LANG') ?: getenv('LC_ALL') ?: getenv('LC_CTYPE');

        // Если запущено в веб-среде, пробуем заголовки HTTP
        if (! $locale && (extension_loaded('intl') && isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))) {
            $locale = locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }

        // Если локаль не найдена, пытаемся определить по системным настройкам
        if (! $locale) {
            $locale = setlocale(LC_ALL, "0");
        }

        return self::normalizeLocale($locale ?: 'en_US'); // Если ничего не найдено, используем локаль по умолчанию
    }

    private static function normalizeLocale(string $locale): string {
        // Убираем кодировку (".UTF-8" и т. д.)
        $locale = preg_replace('/\..*$/', '', $locale);

        // Если локаль задана только как язык (например, "en"), добавляем дефолтную страну
        if (preg_match('/^[a-z]{2}$/', $locale)) {
            return strtolower($locale) . '_' . strtoupper(self::getDefaultCountry($locale));
        }

        // Приводим к формату xx_XX
        if (preg_match('/^([a-z]{2})[_-]([A-Z]{2})$/i', $locale, $matches)) {
            return strtolower($matches[1]) . '_' . strtoupper($matches[2]);
        }

        return 'en_US'; // Если формат неизвестен
    }

    private static function getDefaultCountry(string $language): string {
        return self::$defaultCountries[$language] ?? 'US'; // Дефолт - США
    }
}
/** End of LocaleDetector **/