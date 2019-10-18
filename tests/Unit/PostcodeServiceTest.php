<?php

declare(strict_types=1);

namespace JustSteveKing\LaravelPostcodes\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use JustSteveKing\LaravelPostcodes\TestCase;
use JustSteveKing\LaravelPostcodes\Service\PostcodeService;

class PostcodeServiceTest extends TestCase
{
    protected $postcode = 'N11 1QZ';
    protected $terminatedPostcode = 'AB1 0AA';

    public function testServiceIsCorrectType()
    {
        $this->assertInstanceOf(PostcodeService::class, $this->service(200));
    }

    public function testServiceCanValidatePostcode()
    {
        $serviceFail = $this->service(200, json_encode(['result' => false]));
        $this->assertEquals(false, $serviceFail->validate('test'));

        $serviceSuccess = $this->service(200, json_encode(['result' => true]));
        $this->assertTrue($serviceSuccess->validate($this->postcode));
    }

    public function testServiceCanGetPostcode()
    {
        $service = $this->service(200, json_encode(['result' => ['postcode' => $this->postcode]]));
        $result = $service->getPostcode($this->postcode);

        $this->assertEquals($result->postcode, $this->postcode);
    }

    public function testServiceCanGetOutwardCode()
    {
        $service = $this->service(200, json_encode(['result' => ['outcode' => substr($this->postcode, 0, 3)]]));
        $result = $service->getOutwardCode(substr($this->postcode, 0, 3));

        $this->assertEquals($result->outcode, substr($this->postcode, 0, 3));
    }

    public function testServiceCanGetRandomPostcode()
    {
        $service = $this->service(200, json_encode(['result' => ['postcode' => $this->postcode]]));
        $result = $service->getRandomPostcode();

        $this->assertNotNull($result->postcode);
    }

    public function testServiceCanQueryPostcode()
    {
        $serviceFound = $this->service(200, json_encode(['result' => [['postcode' => $this->postcode]]]));
        $resultFound = $serviceFound->query($this->postcode);

        $this->assertIsArray($resultFound);
        $this->assertCount(1, $resultFound);

        $serviceNull = $this->service(200, json_encode(['result' => null]));
        $resultNull = $serviceNull->query($this->postcode);

        $this->assertNull($resultNull);
    }

    public function testServiceCanGetTerminatedPostcode()
    {
        $service = $this->service(200, json_encode(['result' => ['postcode' => $this->terminatedPostcode, "year_terminated" => 1996, "month_terminated" => 6, "longitude" => -2.242851, "latitude" => 57.101474]]));
        $result = $service->getTerminatedPostcode($this->terminatedPostcode);

        $this->assertNotNull($result->postcode);
    }

    private function service(int $status, string $body = null): PostcodeService
    {
        $mock = new MockHandler([new Response($status, [], $body)]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        return new PostcodeService($client);
    }
}
