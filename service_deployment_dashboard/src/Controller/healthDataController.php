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

            $now = new DateTime();
            $stagingCommit = new DateTime($urlResponse[$servicesName[$i]]['Staging']->lastCommitDate);
            $stagingVersion = $urlResponse[$servicesName[$i]]['Staging']->version;

            $preProductionCommit = new DateTime($urlResponse[$servicesName[$i]]['Pre-Production']->lastCommitDate);
            $preProductionVersion = $urlResponse[$servicesName[$i]]['Pre-Production']->version;

            $productionCommit = new DateTime($urlResponse[$servicesName[$i]]['Production']->lastCommitDate);
            $productionVersion = $urlResponse[$servicesName[$i]]['Production']->version;

            $stageVsPrProd = 0;
            if(substr($stagingVersion, 0,6) !== substr($preProductionVersion, 0,6)){
                $stageVsPrProd = $preProductionCommit->diff($now)->days;
            }

            $preProdVsProd = 0;
            if(substr($preProductionVersion, 0,6) !== substr($productionVersion, 0,6)){
                $preProdVsProd = $productionCommit->diff($now)->days;
            }

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
