<?php

declare(strict_types=1);

namespace JsonI18n\Tests;

use JsonI18n\Translate;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

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

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new Translate('en-CA');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }
    
    /**
     * @covers \JsonI18n\Translate::__construct
     */
    public function testDefaultConstructor() {
        $obj2 = new Translate('en-CA');
        
        $this->assertEquals(Assert::readAttribute($obj2, 'lang'), 'en-CA');
    }
    
    /**
     * @covers \JsonI18n\Translate::__construct
     * @expectedException \TypeError
     */
    public function testConstructorFail() {
        new Translate();
    }
    
    /**
     * @covers \JsonI18n\Translate::setLanguage
     */
    public function testSetLanguage() {
        $this->object->setLanguage('fr-CA');
        $this->assertEquals($this->object->getLanguage(), 'fr-CA');
    }
    
    /**
     * @covers \JsonI18n\Translate::setLanguage
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid language
     */
    public function testSetEmptyLanguage() {
        $this->object->setLanguage('');
    }
    
    /**
     * @covers \JsonI18n\Translate::setLanguage
     * @expectedException \TypeError
     */
    public function testSetInvalidLanguage() {
        $this->object->setLanguage(1);
    }

    /**
     * @covers \JsonI18n\Translate::addResource
     * @covers \JsonI18n\Translate::addSubresource
     * @covers \JsonI18n\Translate::addResourceFile
     * @covers \JsonI18n\Translate::addResourceString
     * @covers \JsonI18n\Translate::addResourceArray
     */
    public function testAddResourceFile() {
        $this->object->addResource(dirname(__FILE__) . '/../resources/test.json');

        $data = Assert::readAttribute($this->object, 'data');
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('en-CA', $data);
        $this->assertArrayHasKey('fr-CA', $data);
        
        $this->assertNotEmpty(Assert::readAttribute($this->object, 'arrayGroups'));
    }
    
    /**
     * @covers \JsonI18n\Translate::addResource
     * @covers \JsonI18n\Translate::addResourceArray
     */
    public function testAddResourceArray() {
        $array = [
            'en-CA' => ['area' => 'area'],
            'fr-CA' => ['area' => 'zone']
        ];
        
        $this->object->addResource($array);
        
        $data = Assert::readAttribute($this->object, 'data');
        $this->assertArrayHasKey('en-CA', $data);
        $this->assertArrayHasKey('fr-CA', $data);
    }

    /**
     * @covers \JsonI18n\Translate::addResource
     * @covers \JsonI18n\Translate::addResourceString
     * @covers \JsonI18n\Translate::addResourceArray
     */
    public function testAddResourceJson() {
        $this->object->addResource($this->json, 'json');

        $data = Assert::readAttribute($this->object, 'data');
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('en-CA', $data);
        $this->assertArrayHasKey('fr-CA', $data);
        
        $this->assertNotEmpty(Assert::readAttribute($this->object, 'arrayGroups'));
    }
    
    /**
     * @covers \JsonI18n\Translate::addResource
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid resource type
     */
    public function testAddResourceInvalidType() {
        $this->object->addResource('foo', 'foo');
    }
    
    /**
     * @covers \JsonI18n\Translate::addResourceFile
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage foo is not a file
     */
    public function testAddResourceInvalidFile() {
        $this->object->addResource('foo');
    }
    
    /**
     * @covers \JsonI18n\Translate::addResourceString
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode \JSON_ERROR_SYNTAX
     */
    public function testAddResourceInvalidJson() {
        $this->object->addResource('foo', 'json');
    }
    
    /**
     * @covers \JsonI18n\Translate::addSubresource
     * @expectedException \InvalidArgumentException
     */
    public function testAddInvalidSubresource() {
        $this->object->addSubresource(null, 'en-CA');
    }
    
    /**
     * @covers \JsonI18n\Translate::addResource
     * @covers \JsonI18n\Translate::addSubresource
     * @covers \JsonI18n\Translate::addResourceFile
     * @covers \JsonI18n\Translate::addResourceString
     * @covers \JsonI18n\Translate::addResourceArray
     */
    public function testAddMultipleResources() {
        $this->object->addResource(dirname(__FILE__) . '/../resources/test.json');
        $this->object->addResource(dirname(__FILE__) . '/../resources/global.json');
        
        $this->assertEquals($this->object->__('documentTitleTag'), 'Routes and maps');
        $this->assertEquals($this->object->__('documentTitleTag', 'fr-CA'), 'Circuits et cartes');
        $this->assertEquals($this->object->__('goodbye'), 'Have a good day!');
        $this->assertEquals($this->object->__('goodbye', 'fr-CA'), 'Bonne journée!');
    }

    /**
     * @covers \JsonI18n\Translate::getLanguage
     */
    public function testGetLanguage() {
        $this->assertEquals($this->object->getLanguage(), 'en-CA');
    }

    /**
     * @covers \JsonI18n\Translate::__
     */
    public function test__() {
        $this->object->addResource(dirname(__FILE__) . '/../resources/test.json');

        $this->assertEquals($this->object->__('documentTitleTag'), 'Quick planner');
        $this->assertEquals($this->object->__('documentTitleTag', 'fr-CA'), 'Planificateur rapide');
    }
    
    /**
     * @covers \JsonI18n\Translate::__
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Invalid language: zh-CN
     */
    public function test__InvalidLanguage() {
        $this->object->addResource(dirname(__FILE__) . '/../resources/test.json');
        $this->object->__('documentTitleTag', 'zh-CN');
    }
    
    /**
     * @covers \JsonI18n\Translate::__
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Invalid key: null
     */
    public function test__InvalidKey() {
        $this->object->addResource(dirname(__FILE__) . '/../resources/test.json');
        $this->object->__('null');
    }

    /**
     * @covers \JsonI18n\Translate::_f
     */
    public function test_f() {
        $this->object->addResource(dirname(__FILE__) . '/../resources/test.json');

        $this->assertEquals($this->object->_f('destination', 'Kanata'), 'Destination: Kanata');
        $this->assertEquals($this->object->_f('numBuses', 2), 'You will need to transfer 2 time(s).');
        $this->assertEquals($this->object->_f('instructionTake', array(95, 'Campus Station', '18:01')), 'Board the 95 at Campus Station at 18:01.');

        $this->assertEquals($this->object->_f('destination', 'Kanata', 'fr-CA'), 'Destination : Kanata');
        $this->assertEquals($this->object->_f('numBuses', 2, 'fr-CA'), 'Vous devez transferer 2 fois.');
        $this->assertEquals($this->object->_f('instructionTake', array(95, 'Station Campus', '18h01'), 'fr-CA'), 'Embarquer le 95 à Station Campus à 18h01.');
        
        $this->assertEquals($this->object->_f('destination', NULL), 'Destination: ');
        $this->assertEquals($this->object->_f('numBuses', NULL), 'You will need to transfer 0 time(s).');
    }
    
    /**
     * @covers \JsonI18n\Translate::_f
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Invalid language: zh-CN
     */
    public function test_fInvalidLanguage() {
        $this->object->addResource(dirname(__FILE__) . '/../resources/test.json');
        $this->object->_f('numBuses', 0, 'zh-CN');
    }
    
    /**
     * @covers \JsonI18n\Translate::_f
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Invalid key: null
     */
    public function test_fInvalidKey() {
        $this->object->addResource(dirname(__FILE__) . '/../resources/test.json');
        $this->object->_f('null', 0);
    }
    
    /**
     * @covers \JsonI18n\Translate::_f
     * @expectedException \InvalidArgumentException
     */
    public function test_fInvalidStrings() {
        $this->object->addResource(dirname(__FILE__) . '/../resources/test.json');
        $this->object->_f('numBuses', true);
    }
    
    /**
     * @covers \JsonI18n\Translate::localizeDeepArray
     */
    public function testLocalizeNullArray() {
        $this->object->addResource(dirname(__FILE__) . '/../resources/test.json');

        $arr1 = $this->object->localizeDeepArray(null, 'headsign');
        
        $this->assertNull($arr1);
    }
    
    /**
     * @covers \JsonI18n\Translate::flatten
     * @covers \JsonI18n\Translate::localizeDeepArray
     */
    public function testLocalize1DArray() {
        $this->object->addResource(dirname(__FILE__) . '/../resources/test.json');
        
        $arr1 = $this->object->localizeDeepArray($this->arr1D, 'headsign', 0, 'en-CA');
        $arr1 = $this->object->localizeDeepArray($arr1, 'subtext', 0, 'fr-CA');
        
        $this->assertEquals($arr1, [
            'headsign' => 'Blackbird Mall',
            'subtext' => 'Rue Main'
        ]);
    }

    /**
     * @covers \JsonI18n\Translate::flatten
     * @covers \JsonI18n\Translate::parseMetadata
     * @covers \JsonI18n\Translate::localizeDeepArray
     */
    public function testLocalize2DArray() {
        $this->object->addResource(dirname(__FILE__) . '/../resources/test.json');

        $arr1 = $this->object->localizeDeepArray($this->arr2D, 'headsign', 1, 'en-CA');
        $arr2 = $this->object->localizeDeepArray($this->arr2D, 'headsign', 1, 'fr-CA');

        $this->assertEquals($arr1, [
            ['headsign' => 'University'],
            ['headsign' => 'College'],
            ['headsign' => 'Airport']
        ]);

        $this->assertEquals($arr2, [
            ['headsign' => 'Université'],
            ['headsign' => 'Collège'],
            ['headsign' => 'Aéroport']
        ]);
    }

    /**
     * @covers \JsonI18n\Translate::flatten
     * @covers \JsonI18n\Translate::localizeDeepArray
     */
    public function testLocalize3DArray() {
        $this->object->addResource(dirname(__FILE__) . '/../resources/test.json');
        
        $arr = $this->object->localizeDeepArray($this->arr3D, 'headsign', 2, 'en-CA');
        
        $this->assertEquals($arr, [
            'local' => [
                ['headsign' => 'Airport']
            ],
            'crosstown' => [
                ['headsign' => 'Blackbird Mall'],
                ['headsign' => 'University'],
                ['headsign' => 'College']
            ]]
        );
    }
    
    /**
     * @covers \JsonI18n\Translate::flatten
     * @covers \JsonI18n\Translate::localizeDeepArray
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Invalid group: endpoint
     */
    public function testLocalizeArrayInvalidGroup() {
        $this->object->addResource(dirname(__FILE__) . '/../resources/test.json');
        
        $this->object->localizeDeepArray($this->arr2D, 'endpoint', 1, 'en-CA');
    }
    
    /**
     * @covers \JsonI18n\Translate::flatten
     * @covers \JsonI18n\Translate::localizeDeepArray
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Invalid language: zh-CN
     */
    public function testLocalizeArrayInvalidLanguage() {
        $this->object->addResource(dirname(__FILE__) . '/../resources/test.json');
        
        $this->object->localizeDeepArray($this->arr2D, 'headsign', 1, 'zh-CN');
    }
    
    /**
     * @covers \JsonI18n\Translate::flatten
     * @covers \JsonI18n\Translate::localizeDeepArray
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Invalid array index: invalid
     */
    public function testLocalizeArrayInvalidIndex() {
        $this->object->addResource(dirname(__FILE__) . '/../resources/test.json');
        
        $this->object->localizeDeepArray($this->arr2D, 'headsign', 1, 'en-US');
    }

    /**
     * @covers \JsonI18n\Translate::localizeDeepArray
     * @expectedException \InvalidArgumentException
     */
    public function testLocalizeArrayInvalidDepth() {
        $this->object->localizeDeepArray($this->arr2D, 'headsign', -1);
    }

    /**
     * @covers \JsonI18n\Translate::localizeDeepArray
     * @expectedException \LengthException
     */
    public function testLocalizeArrayExceedDepth() {
        $this->object->localizeDeepArray($this->arr1D, 'headsign', PHP_INT_MAX);
}
}
