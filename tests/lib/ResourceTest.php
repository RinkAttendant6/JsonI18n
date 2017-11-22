<?php

declare(strict_types=1);

namespace JsonI18n\Tests;

use JsonI18n\Resource;
use PHPUnit\Framework\TestCase;

class ResourceTest extends TestCase
{
    /**
     * @var Resource
     */
    protected $object;
    
    /**
     * Sample locale
     * @var string
     */
    protected $locale = 'en-CA';
    
    /**
     * Sample array data
     * @var string[]
     */
    protected $data = [
        'station' => "Station",
        'goodbye' => "Have a good day!"
    ];
    
    /**
     * JSON representation of sample data
     * @var string
     */
    protected $json;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->json = json_encode([$this->locale => $this->data]);
        
        $this->object = new Resource($this->locale, $this->data);
    }

    /**
     * @covers \JsonI18n\Resource::fromJson
     * @covers \JsonI18n\Resource::__construct
     * @covers \JsonI18n\Resource::setLocale
     */
    public function testFromJson() {
        $resource = Resource::fromJson($this->json);
        
        $this->assertSame($this->locale, $resource->getLocale());
        $this->assertSame($this->data, $this->readAttribute($this->object, 'data'));
    }

    /**
     * @covers \JsonI18n\Resource::getLocale
     */
    public function testGetLocale() {
        $this->assertSame($this->locale, $this->object->getLocale());
    }

    /**
     * @covers \JsonI18n\Resource::getLanguage
     */
    public function testGetLanguage() {
        $this->assertSame('en', $this->object->getLanguage());
    }

    /**
     * @covers \JsonI18n\Resource::addData
     */
    public function testAddData() {
        $new = [
            'station' => 'Terminal',
            'greeting' => 'Hello'
        ];
        
        $this->object->addData($new);
        
        $data = $this->readAttribute($this->object, 'data');
        
        $this->assertArrayHasKey('greeting', $data);
        $this->assertSame('Terminal', $data['station']);
    }
    
    /**
     * @covers \JsonI18n\Resource::addData
     */
    public function testAddDataNoOverwrite() {
        $new = [
            'station' => 'Terminal',
            'greeting' => 'Hello'
        ];
        
        $this->object->addData($new, false);
        
        $data = $this->readAttribute($this->object, 'data');
        $this->assertSame('Station', $data['station']);
    }

    /**
     * @covers \JsonI18n\Resource::getData
     */
    public function testGetData() {
        $this->assertSame($this->data, $this->object->getData());
    }

    /**
     * @covers \JsonI18n\Resource::merge
     */
    public function testMerge() {
        $new = [
            'station' => 'Terminal',
            'greeting' => 'Hello'
        ];
        
        $this->object->merge(new Resource($this->locale, $new));
        
        $data = $this->readAttribute($this->object, 'data');
        $this->assertSame('Terminal', $data['station']);
        $this->assertArrayHasKey('greeting', $new);
    }
    
    /**
     * @covers \JsonI18n\Resource::merge
     */
    public function testMergeNoOverwrite() {
        $new = [
            'station' => 'Terminal',
            'greeting' => 'Hello'
        ];
        
        $this->object->merge(new Resource($this->locale, $new), false);
        
        $data = $this->readAttribute($this->object, 'data');
        $this->assertSame('Station', $data['station']);
        $this->assertArrayHasKey('greeting', $new);
    }
    
    /**
     * @covers \JsonI18n\Resource::merge
     * @expectedException \PHPunit\Framework\Error\Notice
     */
    public function testMergeDifferentLocales() {
        $this->object->merge(new Resource('zh-CN'));
    }

    /**
     * @covers \JsonI18n\Resource::count
     */
    public function testCount() {
        $this->assertSame(sizeof($this->data), $this->object->count());
    }

    /**
     * @covers \JsonI18n\Resource::getIterator
     */
    public function testGetIterator() {
        $this->assertInstanceOf("\\Iterator", $this->object->getIterator());
    }

    /**
     * @covers \JsonI18n\Resource::offsetExists
     */
    public function testOffsetExists() {
        $this->assertTrue(isset($this->object['station']));
        $this->assertFalse(isset($this->object->offsetExists['foo']));
    }

    /**
     * @covers \JsonI18n\Resource::offsetGet
     */
    public function testOffsetGet() {
        $this->assertSame($this->data['station'], $this->object['station']);
    }
    
    /**
     * @covers \JsonI18n\Resource::offsetGet
     * @expectedException \PHPunit\Framework\Error\Error
     */
    public function testOffsetGetInexistent() {
        $this->object['foo'];
    }

    /**
     * @covers \JsonI18n\Resource::offsetSet
     */
    public function testOffsetSet() {
        $this->object['greeting'] = 'Hello';
        
        $this->assertArrayHasKey('greeting', $this->readAttribute($this->object, 'data'));
    }

    /**
     * @covers \JsonI18n\Resource::offsetUnset
     */
    public function testOffsetUnset() {
        unset($this->object['station']);
        
        $this->assertArrayNotHasKey('station', $this->readAttribute($this->object, 'data'));
    }

    /**
     * @covers \JsonI18n\Resource::jsonSerialize
     */
    public function testJsonSerialize() {
        $this->assertSame($this->json, $this->object->jsonSerialize());
    }

    /**
     * @covers \JsonI18n\Resource::serialize
     */
    public function testSerialize() {
        $this->assertSame($this->json, $this->object->serialize());
    }

    /**
     * @covers \JsonI18n\Resource::unserialize
     */
    public function testUnserialize() {
        $this->assertEquals($this->object, $this->object->unserialize($this->json));
    }
}
