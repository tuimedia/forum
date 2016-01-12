<?php
namespace AppBundle\Service;

use AppBundle\Service\JWTCoder;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class DisplayNameListener
{
    protected $jwtCoder;
    protected $profileEndpoint;

    public function __construct(JWTCoder $jwtCoder, $profileEndpoint)
    {
        $this->jwtCoder = $jwtCoder;
        $this->profileEndpoint = $profileEndpoint;
    }

    public function getUsernames(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (strpos($request->query->get('include'), 'displayName') === false) {
            return;
        }

        $data = $response->getContent();
        preg_match_all('/"createdBy":\s*"([^\"]+)"/', $data, $matches, PREG_PATTERN_ORDER);
        if (!isset($matches[1]) || !count($matches[1])) {
            return;
        }

        $matches[0] = array_unique($matches[0]);
        $matches[1] = array_unique($matches[1]);

        $displayNames = $this->lookupUsernames($matches[1]);
        foreach ($matches[0] as $idx => $originalString) {
            $data = str_replace($originalString, vsprintf('%s, "displayName": %s', [
                $originalString,
                json_encode($displayNames[ $matches[1][$idx] ]),
            ]), $data);
        }

        $response->setContent($data);
    }

    protected function lookupUsernames($data)
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->profileEndpoint,
        ]);
        $token = $this->jwtCoder->encode([
            'username' => 'tui_forum',
        ]);

        $queries = array_chunk($data, 255);
        $displayNames = [];
        foreach ($queries as $names) {
            $response = $client->post('profile/names/', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
                'json' => [
                    'users' => $names,
                ],
            ]);

            $displayNames = array_merge($displayNames, json_decode((string) $response->getBody(), true));
        }

        return $displayNames;
    }
}
