<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\traits\hasHealthCheckTrait;
use DateTime;
use Exception;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class healthDataController extends AbstractController
{

    use hasHealthCheckTrait;

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws Exception
     */
    #[Route('/health-data', name: 'health-data', methods: ['GET'])]
    public function __invoke(): Response
    {
        $services = json_decode($_ENV['SERVICE_API'], true, 512, JSON_THROW_ON_ERROR);
        $urlResponse = [];
        $servicesName = [];

        foreach ($services as $serviceApi => $service) {
            $servicesName[] = $serviceApi;

            foreach ($service as $api => $url) {
                // add extra execution time to avoid PHP time out
                set_time_limit(60*5);
                $urlResponse[$serviceApi][$api] = json_decode($this->fetchHealthCheck($url)->getContent());
            }
        }

        for ($i = 0; $i < count($servicesName); $i++) {
            $staging = new DateTime($urlResponse[$servicesName[$i]]['Staging']->lastCommitDate);
            $preProduction = new DateTime($urlResponse[$servicesName[$i]]['Pre-Production']->lastCommitDate);
            $production = new DateTime($urlResponse[$servicesName[$i]]['Production']->lastCommitDate);

            $stageVsPrProd = $staging->diff($preProduction)->days;
            $preProdVsProd = $preProduction->diff($production)->days;

            $urlResponse[$servicesName[$i]]['info'] = [
                'preProductionDiff' => $stageVsPrProd,
                'productionDiff' => $preProdVsProd,
            ];
        }

        // Sort by productionDiff descending
        uasort($urlResponse, function ($a, $b) {
            return $b['info']['productionDiff'] <=> $a['info']['productionDiff'];
        });

        return $this->json($urlResponse);
    }
}
