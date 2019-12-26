<?php

declare(strict_types=1);

namespace JsonI18n\Tests;

use InvalidArgumentException;
use JsonI18n\Translate;
use LengthException;
use OutOfBoundsException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use TypeError;
use const JSON_ERROR_SYNTAX;

/**
 * @covers \JsonI18n\Translate
 * @coversDefaultClass \JsonI18n\Translate
 */
class TranslateTest extends TestCase
{
    /**
     * @var Translate
     */
    protected $object;
    
    /**
     * Some sample JSON input
     * @var string
     */
    protected $json = '{"@metadata":{"arrayGroups": {"station": {"en-CA": "station_en","fr-CA": "station_fr"}}},"en-CA": {"station": "Station","goodbye": "Have a good day!"},"fr-CA": {"station": "Station","goodbye": "Bonne journée!"}}';
    
    /**
     * Sample one dimensional array
     * @var string[]
     */
    protected $arr1D = [
        'headsign_en' => 'Blackbird Mall',
        'headsign_fr' => 'Centre commercial Blackbird',
        'subtext_en' => 'Main Street',
        'subtext_fr' => 'Rue Main'
    ];

    /**
     * Sample two-dimensional array
     * @var string[][]
     */
    protected $arr2D = [
        ['headsign_en' => 'University', 'headsign_fr' => 'Université'],
        ['headsign_en' => 'College', 'headsign_fr' => 'Collège'],
        ['headsign_en' => 'Airport', 'headsign_fr' => 'Aéroport']
    ];

    /**
     * Sample three-dimensional array
     * @var string[][][]
     */
    protected $arr3D = [
        'local' => [
            ['headsign_en' => 'Airport', 'headsign_fr' => 'Aéroport']
        ],
        'crosstown' => [
            ['headsign_en' => 'Blackbird Mall', 'headsign_fr' => 'Centre commercial Blackbird'],
            ['headsign_en' => 'University', 'headsign_fr' => 'Université'],
            ['headsign_en' => 'College', 'headsign_fr' => 'Collège']
        ]
    ];

    protected function setUp(): void
    {
        $this->object = new Translate('en-CA');
    }

    /**
     * @covers ::__construct
     */
    public function testDefaultConstructor(): void
    {
        $obj2 = new Translate('en-CA');
        
        static::assertSame('en-CA', Assert::readAttribute($obj2, 'lang'));
    }

    /**
     * @covers ::setLanguage
     */
    public function testSetLanguage(): void
    {
        $this->object->setLanguage('fr-CA');

        static::assertSame('fr-CA', $this->object->getLanguage());
    }
    
    /**
     * @covers ::setLanguage
     * @expectedExceptionMessage Invalid language
     */
    public function testSetEmptyLanguage(): void
    {
        static::expectException(InvalidArgumentException::class);

        $this->object->setLanguage('');
    }
    
    /**
     * @covers ::setLanguage
     */
    public function testSetInvalidLanguage(): void
    {
        static::expectException(TypeError::class);

        $this->object->setLanguage(1);
    }

    /**
     * @covers ::addResource
     * @covers ::addSubresource
     * @covers ::addResourceFile
     * @covers ::addResourceString
     * @covers ::addResourceArray
     */
    public function testAddResourceFile(): void
    {
        $this->object->addResource(__DIR__ . '/resources/test.json');

        $data = Assert::readAttribute($this->object, 'data');

        static::assertNotEmpty($data);
        static::assertArrayHasKey('en-CA', $data);
        static::assertArrayHasKey('fr-CA', $data);
        static::assertNotEmpty(Assert::readAttribute($this->object, 'arrayGroups'));
    }
    
    /**
     * @covers ::addResource
     * @covers ::addResourceArray
     */
    public function testAddResourceArray(): void
    {
        $array = [
            'en-CA' => ['area' => 'area'],
            'fr-CA' => ['area' => 'zone']
        ];
        
        $this->object->addResource($array);
        
        $data = Assert::readAttribute($this->object, 'data');

        static::assertArrayHasKey('en-CA', $data);
        static::assertArrayHasKey('fr-CA', $data);
    }

    /**
     * @covers ::addResource
     * @covers ::addResourceString
     * @covers ::addResourceArray
     */
    public function testAddResourceJson(): void
    {
        $this->object->addResource($this->json, 'json');

        $data = Assert::readAttribute($this->object, 'data');

        static::assertNotEmpty($data);
        static::assertArrayHasKey('en-CA', $data);
        static::assertArrayHasKey('fr-CA', $data);
        static::assertNotEmpty(Assert::readAttribute($this->object, 'arrayGroups'));
    }
    
    /**
     * @covers ::addResource
     * @expectedExceptionMessage Invalid resource type
     */
    public function testAddResourceInvalidType(): void
    {
        static::expectException(InvalidArgumentException::class);

        $this->object->addResource('foo', 'foo');
    }
    
    /**
     * @covers ::addResourceFile
     * @expectedExceptionMessage foo is not a file
     */
    public function testAddResourceInvalidFile(): void
    {
        static::expectException(InvalidArgumentException::class);

        $this->object->addResource('foo');
    }
    
    /**
     * @covers ::addResourceString
     */
    public function testAddResourceInvalidJson(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionCode(JSON_ERROR_SYNTAX);

        $this->object->addResource('foo', 'json');
    }
    
    /**
     * @covers ::addSubresource
     */
    public function testAddInvalidSubresource(): void
    {
        static::expectException(InvalidArgumentException::class);

        $this->object->addSubresource(null, 'en-CA');
    }
    
    /**
     * @covers ::addResource
     * @covers ::addSubresource
     * @covers ::addResourceFile
     * @covers ::addResourceString
     * @covers ::addResourceArray
     */
    public function testAddMultipleResources(): void
    {
        $this->object->addResource(__DIR__ . '/resources/test.json');
        $this->object->addResource(__DIR__ . '/resources/global.json');
        
        static::assertSame('Routes and maps', $this->object->__('documentTitleTag'));
        static::assertSame('Circuits et cartes', $this->object->__('documentTitleTag', 'fr-CA'));
        static::assertSame('Have a good day!', $this->object->__('goodbye'));
        static::assertSame('Bonne journée!', $this->object->__('goodbye', 'fr-CA'));
    }

    /**
     * @covers ::getLanguage
     */
    public function testGetLanguage(): void
    {
        static::assertSame('en-CA', $this->object->getLanguage());
    }

    /**
     * @covers ::__
     */
    public function test__(): void
    {
        $this->object->addResource(__DIR__ . '/resources/test.json');

        static::assertSame('Quick planner', $this->object->__('documentTitleTag'));
        static::assertSame('Planificateur rapide', $this->object->__('documentTitleTag', 'fr-CA'));
    }
    
    /**
     * @covers ::__
     * @expectedExceptionMessage Invalid language: zh-CN
     */
    public function test__InvalidLanguage(): void
    {
        static::expectException(OutOfBoundsException::class);

        $this->object->addResource(__DIR__ . '/resources/test.json');
        $this->object->__('documentTitleTag', 'zh-CN');
    }
    
    /**
     * @covers ::__
     * @expectedExceptionMessage Invalid key: null
     */
    public function test__InvalidKey(): void
    {
        static::expectException(OutOfBoundsException::class);

        $this->object->addResource(__DIR__ . '/resources/test.json');
        $this->object->__('null');
    }

    /**
     * @covers ::_f
     */
    public function test_f(): void
    {
        $this->object->addResource(__DIR__ . '/resources/test.json');

        static::assertSame('Destination: Kanata', $this->object->_f('destination', 'Kanata'));
        static::assertSame('You will need to transfer 2 time(s).', $this->object->_f('numBuses', 2));
        static::assertSame('Board the 95 at Campus Station at 18:01.', $this->object->_f('instructionTake', array(95, 'Campus Station', '18:01')));

        static::assertSame('Destination : Kanata', $this->object->_f('destination', 'Kanata', 'fr-CA'));
        static::assertSame('Vous devez transferer 2 fois.', $this->object->_f('numBuses', 2, 'fr-CA'));
        static::assertSame('Embarquer le 95 à Station Campus à 18h01.', $this->object->_f('instructionTake', array(95, 'Station Campus', '18h01'), 'fr-CA'));
        
        static::assertSame('Destination: ', $this->object->_f('destination', null));
        static::assertSame('You will need to transfer 0 time(s).', $this->object->_f('numBuses', null));
    }
    
    /**
     * @covers ::_f
     * @expectedExceptionMessage Invalid language: zh-CN
     */
    public function test_fInvalidLanguage(): void
    {
        static::expectException(OutOfBoundsException::class);

        $this->object->addResource(__DIR__ . '/resources/test.json');
        $this->object->_f('numBuses', 0, 'zh-CN');
    }
    
    /**
     * @covers ::_f
     * @expectedExceptionMessage Invalid key: null
     */
    public function test_fInvalidKey(): void
    {
        static::expectException(OutOfBoundsException::class);

        $this->object->addResource(__DIR__ . '/resources/test.json');
        $this->object->_f('null', 0);
    }
    
    /**
     * @covers ::_f
     */
    public function test_fInvalidStrings(): void
    {
        static::expectException(InvalidArgumentException::class);

        $this->object->addResource(__DIR__ . '/resources/test.json');
        $this->object->_f('numBuses', true);
    }
    
    /**
     * @covers ::localizeDeepArray
     */
    public function testLocalizeNullArray(): void
    {
        $this->object->addResource(__DIR__ . '/resources/test.json');

        $arr1 = $this->object->localizeDeepArray(null, 'headsign');
        
        static::assertNull($arr1);
    }
    
    /**
     * @covers ::flatten
     * @covers ::localizeDeepArray
     */
    public function testLocalize1DArray(): void
    {
        $this->object->addResource(__DIR__ . '/resources/test.json');
        
        $arr1 = $this->object->localizeDeepArray($this->arr1D, 'headsign', 0, 'en-CA');
        $arr1 = $this->object->localizeDeepArray($arr1, 'subtext', 0, 'fr-CA');
        
        static::assertEquals([
            'headsign' => 'Blackbird Mall',
            'subtext' => 'Rue Main'
        ], $arr1);
    }

    /**
     * @covers ::flatten
     * @covers ::parseMetadata
     * @covers ::localizeDeepArray
     */
    public function testLocalize2DArray(): void
    {
        $this->object->addResource(__DIR__ . '/resources/test.json');

        $arr1 = $this->object->localizeDeepArray($this->arr2D, 'headsign', 1, 'en-CA');
        $arr2 = $this->object->localizeDeepArray($this->arr2D, 'headsign', 1, 'fr-CA');

        static::assertEquals([
            ['headsign' => 'University'],
            ['headsign' => 'College'],
            ['headsign' => 'Airport']
        ], $arr1);

        static::assertEquals([
            ['headsign' => 'Université'],
            ['headsign' => 'Collège'],
            ['headsign' => 'Aéroport']
        ], $arr2);
    }

    /**
     * @covers ::flatten
     * @covers ::localizeDeepArray
     */
    public function testLocalize3DArray(): void
    {
        $this->object->addResource(__DIR__ . '/resources/test.json');
        
        $arr = $this->object->localizeDeepArray($this->arr3D, 'headsign', 2, 'en-CA');
        
        static::assertEquals([
            'local' => [
                ['headsign' => 'Airport']
            ],
            'crosstown' => [
                ['headsign' => 'Blackbird Mall'],
                ['headsign' => 'University'],
                ['headsign' => 'College']
            ]], $arr
        );
    }
    
    /**
     * @covers ::flatten
     * @covers ::localizeDeepArray
     * @expectedExceptionMessage Invalid group: endpoint
     */
    public function testLocalizeArrayInvalidGroup(): void
    {
        static::expectException(OutOfBoundsException::class);

        $this->object->addResource(__DIR__ . '/resources/test.json');
        $this->object->localizeDeepArray($this->arr2D, 'endpoint', 1, 'en-CA');
    }
    
    /**
     * @covers ::flatten
     * @covers ::localizeDeepArray
     * @expectedExceptionMessage Invalid language: zh-CN
     */
    public function testLocalizeArrayInvalidLanguage(): void
    {
        static::expectException(OutOfBoundsException::class);

        $this->object->addResource(__DIR__ . '/resources/test.json');
        $this->object->localizeDeepArray($this->arr2D, 'headsign', 1, 'zh-CN');
    }
    
    /**
     * @covers ::flatten
     * @covers ::localizeDeepArray
     * @expectedExceptionMessage Invalid array index: invalid
     */
    public function testLocalizeArrayInvalidIndex(): void
    {
        static::expectException(OutOfBoundsException::class);

        $this->object->addResource(__DIR__ . '/resources/test.json');
        $this->object->localizeDeepArray($this->arr2D, 'headsign', 1, 'en-US');
    }

    /**
     * @covers ::localizeDeepArray
     */
    public function testLocalizeArrayInvalidDepth(): void
    {
        static::expectException(InvalidArgumentException::class);

        $this->object->localizeDeepArray($this->arr2D, 'headsign', -1);
    }

    /**
     * @covers ::localizeDeepArray
     */
    public function testLocalizeArrayExceedDepth(): void
    {
        static::expectException(LengthException::class);

        $this->object->localizeDeepArray($this->arr1D, 'headsign', PHP_INT_MAX);
    }
}
