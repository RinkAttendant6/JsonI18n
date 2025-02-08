<?php

declare(strict_types=1);

namespace JsonI18n\Tests;

use Iterator;
use JsonI18n\Resource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JsonI18n\Resource
 * @coversDefaultClass \JsonI18n\Resource
 */
#[CoversClass("\JsonI18n\Resource")]
class ResourceTest extends TestCase
{
    /**
     * @var Resource
     */
    protected $object;
    
    /**
     * Sample locale
     * @var non-empty-string
     */
    protected $locale = 'en-CA';
    
    /**
     * Sample array data
     * @var array<string, string>
     */
    protected $data = [
        'station' => "Station",
        'goodbye' => "Have a good day!",
    ];
    
    /**
     * JSON representation of sample data
     * @var string
     */
    protected $json;

    protected function setUp(): void
    {
        error_reporting(\E_ALL);
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
        static::assertSame($this->data, $this->object->getData());
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
            'greeting' => 'Hello',
        ];
        
        $this->object->addData($new);
        
        $data = $this->object->getData();
        
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
            'greeting' => 'Hello',
        ];
        
        $this->object->addData($new, false);
        
        $data = $this->object->getData();
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
            'greeting' => 'Hello',
        ];
        
        $this->object->merge(new Resource($this->locale, $new));
        
        $data = $this->object->getData();
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
            'greeting' => 'Hello',
        ];
        
        $this->object->merge(new Resource($this->locale, $new), false);
        
        $data = $this->object->getData();
        static::assertSame('Station', $data['station']);
        static::assertArrayHasKey('greeting', $new);
    }
    
    /**
     * @covers ::merge
     */
    public function testMergeDifferentLocales(): void
    {
        set_error_handler(
            static function ($errno, $errstr): void {
                restore_error_handler();
                throw new \Exception($errstr, $errno);
            },
            E_ALL
        );

        static::expectException(\Exception::class);
        static::expectExceptionCode(\E_USER_NOTICE);

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
        static::assertNull($this->object['foo']);
    }

    /**
     * @covers ::offsetSet
     */
    public function testOffsetSet(): void
    {
        $this->object['greeting'] = 'Hello';
        
        static::assertArrayHasKey('greeting', $this->object->getData());
    }

    /**
     * @covers ::offsetUnset
     */
    public function testOffsetUnset(): void
    {
        unset($this->object['station']);
        
        static::assertArrayNotHasKey('station', $this->object->getData());
    }

    /**
     * @covers ::jsonSerialize
     */
    public function testJsonSerialize(): void
    {
        static::assertSame($this->json, $this->object->jsonSerialize());
    }
}
