<?php

declare(strict_types=1);

namespace JsonI18n\Tests;

use Iterator;
use JsonI18n\Resource;
use PHPUnit\Framework\Error\Error;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JsonI18n\Resource
 * @coversDefaultClass \JsonI18n\Resource
 */
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

    protected function setUp(): void
    {
        $this->json = json_encode([$this->locale => $this->data]);
        $this->object = new Resource($this->locale, $this->data);
    }

    /**
     * @covers ::fromJson
     * @covers ::__construct
     * @covers ::setLocale
     */
    public function testFromJson(): void
    {
        $resource = Resource::fromJson($this->json);
        
        static::assertSame($this->locale, $resource->getLocale());
        static::assertSame($this->data, $this->readAttribute($this->object, 'data'));
    }

    /**
     * @covers ::getLocale
     */
    public function testGetLocale(): void
    {
        static::assertSame($this->locale, $this->object->getLocale());
    }

    /**
     * @covers ::getLanguage
     */
    public function testGetLanguage(): void
    {
        static::assertSame('en', $this->object->getLanguage());
    }

    /**
     * @covers ::addData
     */
    public function testAddData(): void
    {
        $new = [
            'station' => 'Terminal',
            'greeting' => 'Hello'
        ];
        
        $this->object->addData($new);
        
        $data = $this->readAttribute($this->object, 'data');
        
        static::assertArrayHasKey('greeting', $data);
        static::assertSame('Terminal', $data['station']);
    }
    
    /**
     * @covers ::addData
     */
    public function testAddDataNoOverwrite(): void
    {
        $new = [
            'station' => 'Terminal',
            'greeting' => 'Hello'
        ];
        
        $this->object->addData($new, false);
        
        $data = $this->readAttribute($this->object, 'data');
        static::assertSame('Station', $data['station']);
    }

    /**
     * @covers ::getData
     */
    public function testGetData(): void
    {
        static::assertSame($this->data, $this->object->getData());
    }

    /**
     * @covers ::merge
     */
    public function testMerge(): void
    {
        $new = [
            'station' => 'Terminal',
            'greeting' => 'Hello'
        ];
        
        $this->object->merge(new Resource($this->locale, $new));
        
        $data = $this->readAttribute($this->object, 'data');
        static::assertSame('Terminal', $data['station']);
        static::assertArrayHasKey('greeting', $new);
    }
    
    /**
     * @covers ::merge
     */
    public function testMergeNoOverwrite(): void
    {
        $new = [
            'station' => 'Terminal',
            'greeting' => 'Hello'
        ];
        
        $this->object->merge(new Resource($this->locale, $new), false);
        
        $data = $this->readAttribute($this->object, 'data');
        static::assertSame('Station', $data['station']);
        static::assertArrayHasKey('greeting', $new);
    }
    
    /**
     * @covers ::merge
     */
    public function testMergeDifferentLocales(): void
    {
        static::expectException(Notice::class);

        $this->object->merge(new Resource('zh-CN'));
    }

    /**
     * @covers ::count
     */
    public function testCount(): void
    {
        static::assertSame(sizeof($this->data), $this->object->count());
    }

    /**
     * @covers ::getIterator
     */
    public function testGetIterator(): void
    {
        static::assertInstanceOf(Iterator::class, $this->object->getIterator());
    }

    /**
     * @covers ::offsetExists
     */
    public function testOffsetExists(): void
    {
        static::assertTrue(isset($this->object['station']));
        static::assertFalse(isset($this->object->offsetExists['foo']));
    }

    /**
     * @covers ::offsetGet
     */
    public function testOffsetGet(): void
    {
        static::assertSame($this->data['station'], $this->object['station']);
    }
    
    /**
     * @covers ::offsetGet
     */
    public function testOffsetGetInexistent(): void
    {
        static::expectException(Error::class);

        $this->object['foo'];
    }

    /**
     * @covers ::offsetSet
     */
    public function testOffsetSet(): void
    {
        $this->object['greeting'] = 'Hello';
        
        static::assertArrayHasKey('greeting', $this->readAttribute($this->object, 'data'));
    }

    /**
     * @covers ::offsetUnset
     */
    public function testOffsetUnset(): void
    {
        unset($this->object['station']);
        
        static::assertArrayNotHasKey('station', $this->readAttribute($this->object, 'data'));
    }

    /**
     * @covers ::jsonSerialize
     */
    public function testJsonSerialize(): void
    {
        static::assertSame($this->json, $this->object->jsonSerialize());
    }

    /**
     * @covers ::serialize
     */
    public function testSerialize(): void
    {
        static::assertSame($this->json, $this->object->serialize());
    }

    /**
     * @covers ::unserialize
     */
    public function testUnserialize(): void
    {
        static::assertEquals($this->object, $this->object->unserialize($this->json));
    }
}
