<?php

namespace App\Notifier;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Minimal client for the WhatsApp Cloud API (Meta Graph API).
 *
 * WhatsApp only allows businesses to start a conversation with an approved
 * message template, so booking confirmations are sent as a "utility" template
 * whose body parameters are filled in below. The template name, language and
 * the order of the parameters must match the template approved in Meta's
 * WhatsApp Manager.
 *
 * Symfony's Notifier component has no WhatsApp bridge, which is why this calls
 * the Graph API directly through symfony/http-client.
 */
final readonly class WhatsAppClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        #[Autowire('%env(WHATSAPP_GRAPH_VERSION)%')]
        private string $graphVersion,
        #[Autowire('%env(WHATSAPP_PHONE_NUMBER_ID)%')]
        private string $phoneNumberId,
        #[Autowire('%env(WHATSAPP_TOKEN)%')]
        private string $token,
        #[Autowire('%env(WHATSAPP_TEMPLATE)%')]
        private string $template,
        #[Autowire('%env(WHATSAPP_TEMPLATE_LANG)%')]
        private string $templateLang,
    ) {
    }

    /**
     * @param list<string> $bodyParameters Ordered values for the template's
     *                                     {{1}}, {{2}}, ... placeholders.
     */
    public function sendBookingConfirmation(string $to, array $bodyParameters): void
    {
        if ('' === $this->phoneNumberId || '' === $this->token) {
            $this->logger->warning('WhatsApp is not configured; skipping confirmation for {to}.', ['to' => $to]);

            return;
        }

        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            $this->graphVersion,
            $this->phoneNumberId,
        );

        try {
            $response = $this->httpClient->request('POST', $url, [
                'auth_bearer' => $this->token,
                'json' => [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $to,
                    'type' => 'template',
                    'template' => [
                        'name' => $this->template,
                        'language' => ['code' => $this->templateLang],
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => array_map(
                                    static fn (string $text): array => [
                                        'type' => 'text',
                                        'text' => $text,
                                    ],
                                    $bodyParameters,
                                ),
                            ],
                        ],
                    ],
                ],
            ]);

            $status = $response->getStatusCode();

            if ($status >= 300) {
                throw new \RuntimeException(sprintf(
                    'WhatsApp API returned %d: %s',
                    $status,
                    $response->getContent(false),
                ));
            }
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('Could not reach the WhatsApp API.', 0, $e);
        }
    }
}
