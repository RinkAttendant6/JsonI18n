<?php

namespace Tests;

use JsonI18n\NumberFormat;

require_once dirname(__FILE__) . '/../../lib/NumberFormat.php';

class NumberFormatTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var NumberFormat
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new NumberFormat('en-CA');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }
    
    /**
     * @covers JsonI18n\NumberFormat::__construct
     */
    public function testDefaultConstructor() {
        $obj2 = new NumberFormat('en-CA');
        
        $this->assertEquals(\PHPUnit_Framework_Assert::readAttribute($obj2, 'locale'), 'en-CA');
    }
    
    /**
     * @covers JsonI18n\NumberFormat::__construct
     * @expectedException \PHPUnit_Framework_Error_Warning
     */
    public function testConstructorFail() {
        new NumberFormat();
    }
    
    /**
     * @covers JsonI18n\NumberFormat::__construct
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid locale.
     */
    public function testConstructorInvalidLanguage() {
        new NumberFormat('');
    }

    /**
     * @covers JsonI18n\NumberFormat::addResource
     * @covers JsonI18n\NumberFormat::processData
     */
    public function testAddResource() {
        $this->object->addResource(__DIR__ . '/../resources/numberformats.json');
        
        $ref = \PHPUnit_Framework_Assert::readAttribute($this->object, 'formatters');
        
        $this->assertArrayHasKey('decimal', $ref['en-CA']);
        $this->assertArrayHasKey('currency', $ref['en-CA']);
        $this->assertArrayHasKey('percent', $ref['en-CA']);
        $this->assertArrayHasKey('scientific', $ref['en-CA']);
        $this->assertArrayHasKey('spellout', $ref['en-CA']);
        $this->assertArrayHasKey('ordinal', $ref['en-CA']);
        
        $this->assertInstanceOf('NumberFormatter', $ref['en-CA']['decimal']);
        $this->assertInstanceOf('NumberFormatter', $ref['en-CA']['currency']);
        $this->assertInstanceOf('NumberFormatter', $ref['en-CA']['percent']);
        $this->assertInstanceOf('NumberFormatter', $ref['en-CA']['scientific']);
        $this->assertInstanceOf('NumberFormatter', $ref['en-CA']['spellout']);
        $this->assertInstanceOf('NumberFormatter', $ref['en-CA']['ordinal']);
    }
    
    /**
     * @covers JsonI18n\NumberFormat::format()
     */
    public function testFormat() {
        $this->object->addResource(__DIR__ . '/../resources/numberformats.json');
        
        $this->assertEquals($this->object->format(12345670.89, 'decimal'), "12,345,670.89");
        $this->assertEquals($this->object->format(12345670.89, 'scientific'), "1.234567089E7");
        $this->assertEquals($this->object->format(12345670.89, 'currency'), "$12,345,670.89");
        $this->assertEquals($this->object->format(12345670.89, 'spellout'), "twelve million three hundred forty-five thousand six hundred seventy point eight nine");
        $this->assertEquals($this->object->format(12345670.89, 'percent'), "1,234,567,089%");
        
        $this->assertEquals($this->object->format(-0.20060, 'decimal'), "-0.201");
        $this->assertEquals($this->object->format(-0.20060, 'scientific'), "-2.006E-1");
        $this->assertEquals($this->object->format(-0.20060, 'currency'), "($0.20)");
        $this->assertEquals($this->object->format(-0.20060, 'spellout'), "minus zero point two zero zero six");
        $this->assertEquals($this->object->format(-0.20060, 'percent'), "-20%");
    }
    
    public function testFormatForeignCurrency() {
        
        $this->object->addResource(__DIR__ . '/../resources/numberformats.json');
        
        $this->assertEquals($this->object->formatForeignCurrency(12345670.89, 'CAD'), '$12,345,670.89');
        $this->assertEquals($this->object->formatForeignCurrency(12345670.89, 'USD'), 'US$12,345,670.89');
    }
    
    /**
     * @covers JsonI18n\NumberFormat::getFormatter()
     */
    public function testGetFormatter() {
        $this->object->addResource(__DIR__ . '/../resources/numberformats.json');
        
        $this->assertEquals($this->object->getFormatter('decimal')->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL), '.');
    }
    
    /**
     * @covers JsonI18n\NumberFormat::getFormatter()
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Locale data not found.
     */
    public function testGetFormatterInvalidLocale() {
        $this->object->addResource(__DIR__ . '/../resources/numberformats.json');
        
        $this->object->getFormatter('decimal', 'zh-CN');
    }
    
    /**
     * @covers JsonI18n\NumberFormat::getFormatter()
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Formatter not found for specified locale.
     */
    public function testGetFormatterInvalidFormatter() {
        $this->object->addResource(__DIR__ . '/../resources/numberformats.json');
        
        $this->object->getFormatter('invalid');
    }
}