<?php

namespace App\Tests\Notifier;

use App\Notifier\WhatsAppClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class WhatsAppClientTest extends TestCase
{
    /**
     * @var list<array{method: string, url: string, options: array}>
     */
    private array $requests = [];

    public function testItPostsTheApprovedTemplateToTheGraphApi(): void
    {
        $client = $this->createClient('123456789', 'test-token');

        $client->sendBookingConfirmation('+919876543210', [
            'Mahesh',
            'BK-0001',
            'Inception',
            '14 Nov 2026, 06:00 PM',
        ]);

        self::assertCount(1, $this->requests);
        $request = $this->requests[0];

        self::assertSame('POST', $request['method']);
        self::assertSame('https://graph.facebook.com/v21.0/123456789/messages', $request['url']);
        self::assertSame(
            ['Authorization: Bearer test-token'],
            $request['options']['normalized_headers']['authorization'],
        );

        // The "json" option is encoded to the request body before the client
        // sees it, so decode it back to inspect the payload.
        $payload = json_decode($request['options']['body'], true, flags: \JSON_THROW_ON_ERROR);

        self::assertSame([
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => '+919876543210',
            'type' => 'template',
            'template' => [
                'name' => 'booking_confirmation',
                'language' => ['code' => 'en'],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => 'Mahesh'],
                            ['type' => 'text', 'text' => 'BK-0001'],
                            ['type' => 'text', 'text' => 'Inception'],
                            ['type' => 'text', 'text' => '14 Nov 2026, 06:00 PM'],
                        ],
                    ],
                ],
            ],
        ], $payload);
    }

    public function testItSkipsTheRequestWhenNotConfigured(): void
    {
        $logger = $this->createRecordingLogger();
        $client = $this->createClient('', '', $logger);

        $client->sendBookingConfirmation('+919876543210', ['Mahesh', 'BK-0001']);

        self::assertCount(0, $this->requests, 'No HTTP request should be made when WhatsApp is not configured.');
        self::assertCount(1, $logger->records);
        self::assertSame('warning', $logger->records[0]['level']);
        self::assertStringContainsString('not configured', $logger->records[0]['message']);
    }

    public function testItThrowsWhenTheGraphApiRejectsTheMessage(): void
    {
        $client = $this->createClient('123456789', 'test-token', response: new MockResponse(
            '{"error":{"message":"Invalid parameter"}}',
            ['http_code' => 400],
        ));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('WhatsApp API returned 400');

        $client->sendBookingConfirmation('+919876543210', ['Mahesh', 'BK-0001']);
    }

    private function createClient(
        string $phoneNumberId,
        string $token,
        ?AbstractLogger $logger = null,
        ?MockResponse $response = null,
    ): WhatsAppClient {
        $this->requests = [];

        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use ($response): MockResponse {
            $this->requests[] = ['method' => $method, 'url' => $url, 'options' => $options];

            return $response ?? new MockResponse('{"messages":[{"id":"wamid.TEST"}]}', ['http_code' => 200]);
        });

        return new WhatsAppClient(
            $httpClient,
            $logger ?? $this->createRecordingLogger(),
            'v21.0',
            $phoneNumberId,
            $token,
            'booking_confirmation',
            'en',
        );
    }

    /**
     * @return AbstractLogger&object{records: list<array{level: mixed, message: string, context: array}>}
     */
    private function createRecordingLogger(): AbstractLogger
    {
        return new class extends AbstractLogger {
            /**
             * @var list<array{level: mixed, message: string, context: array}>
             */
            public array $records = [];

            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->records[] = [
                    'level' => $level,
                    'message' => (string) $message,
                    'context' => $context,
                ];
            }
        };
    }
}
