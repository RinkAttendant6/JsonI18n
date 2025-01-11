<?php

declare(strict_types=1);

namespace JsonI18n\Tests;

use DateTime;
use DateTimeZone;
use IntlDateFormatter;
use InvalidArgumentException;
use JsonI18n\DateFormat;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JsonI18n\DateFormat
 * @coversDefaultClass \JsonI18n\DateFormat
 */
#[CoversClass("\JsonI18n\DateFormat")]
final class DateFormatTest extends TestCase
{
    /** @var string */
    private const TIMEZONE = 'America/Toronto';

    /** @var DateFormat */
    protected $object;

    protected function setUp(): void
    {
        date_default_timezone_set(self::TIMEZONE);
        $this->object = new DateFormat('fr-CA');
    }

    /**
     * @covers ::__construct
     */
    public function testConstructorInvalidLanguage(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Invalid locale.');

        new DateFormat('');
    }

    /**
     * @covers ::addResource
     * @covers ::processData
     */
    public function testAddResource(): void
    {
        $this->object->addResource(__DIR__ . '/resources/dateformats.json');
        
        $ref = $this->object->__debugInfo();
        
        static::assertArrayHasKey('day_date', $ref['en-CA']);
        static::assertArrayHasKey('day_date_time', $ref['fr-CA']);
        
        static::assertInstanceOf(IntlDateFormatter::class, $ref['en-CA']['day_date']);
        static::assertInstanceOf(IntlDateFormatter::class, $ref['fr-CA']['day_date_time']);
        
        static::assertSame('eeee d MMMM yyyy', $ref['en-CA']['day_date']->getPattern());
        static::assertSame('EEE d MMM, yyyy à HH:mm', $ref['fr-CA']['day_date_time']->getPattern());
    }

    /**
     * @covers ::format
     * @dataProvider formatDataProvider
     * @param string $expected Output
     * @param DateTime|string $input Value to be localized
     * @param string $locale Locale
     */
    #[DataProvider("formatDataProvider")]
    public function testFormat(string $expected, $input, string $locale): void
    {
        $this->object->addResource(__DIR__ . '/resources/dateformats.json');

        static::assertSame($expected, $this->object->format($input, 'day', $locale));
    }

    public static function formatDataProvider(): iterable
    {
        $date = new DateTime('2014-01-01', new DateTimeZone(self::TIMEZONE));
        $date2 = DateTime::createFromFormat('Y-m-d H:i:s', '2014-07-01 08:00:00');

        yield from [
            ['Wednesday', $date, 'en-CA'],
            ['mercredi', $date, 'fr-CA'],
            ['Tuesday', $date2, 'en-CA'],
            ['mardi', $date2, 'fr-CA'],
            ['Sunday', '2014-03-02', 'en-CA'],
            ['dimanche', '2014-03-02', 'fr-CA'],
        ];
    }
    
    /**
     * @covers ::format
     */
    public function testFormatInvalidLocale(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Locale data not found.');

        $this->object->addResource(__DIR__ . '/resources/dateformats.json');
        $this->object->format('2014-03-02', 'day', 'zh-CN');
    }
    
    /**
     * @covers ::format
     */
    public function testFormatInvalidFormat(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Formatter not found for specified locale.');

        $this->object->addResource(__DIR__ . '/resources/dateformats.json');
        $this->object->format('2014-03-02', 'invalid');
    }
    
    /**
     * @covers ::getFormatter()
     */
    public function testGetFormatter(): void
    {
        $this->object->addResource(__DIR__ . '/resources/dateformats.json');
        
        static::assertInstanceOf(IntlDateFormatter::class, $this->object->getFormatter('day'));
        static::assertSame('cccc', $this->object->getFormatter('day')->getPattern());
    }
    
    /**
     * @covers ::getFormatter
     */
    public function testGetFormatterInvalidLocale(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Locale data not found.');

        $this->object->addResource(__DIR__ . '/resources/dateformats.json');
        $this->object->getFormatter('day', 'zh-CN');
    }
    
    /**
     * @covers ::getFormatter
     */
    public function testGetFormatterInvalidFormatter(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Formatter not found for specified locale.');

        $this->object->addResource(__DIR__ . '/resources/dateformats.json');
        $this->object->getFormatter('invalid');
    }
}
