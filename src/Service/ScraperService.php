<?php


namespace App\Service;


use App\Entity\Kenteken;
use App\Repository\KentekenRepository;
use Carbon\Carbon;
use Exception;
use Goutte\Client;

class ScraperService
{
    private array $relevant = [
        'Merk',
        'Modelomschrijving',
        'Model',
        'Nieuwprijs',
        'Brandstof',
        'Carrosserie',
        'Schakeling',
        'Datum eerste toelating',
        'Datum laatste tenaamstelling',
        'Parallel import',
        'Aantal eigenaren',
        'Max. vermogen',
        'Brandstofverbruik gecombineerd (WLTP)',
        'Massa leeg',
        'Aanhangermassa ongeremd',
        'Aanhangermassa geremd',
        'Topsnelheid',
        'Acceleratie 0-100 km/h',
    ];

    private array $textKeys = [
        'Merk',
        'Omschrijving',
        'Jaar',
        'Nieuwprijs',
        'Max. vermogen',
        'Brandstof',
        'Schakeling',
        'Topsnelheid',
        'Acceleratie 0-100 km/h',
        'Massa leeg',
        'Aantal eigenaren',
        'In bezit eigenaar',
    ];

    private array $rawData = [];
    private KentekenRepository $kentekenRepository;

    public function __construct(KentekenRepository $kentekenRepository)
    {
        $this->kentekenRepository = $kentekenRepository;
    }

    public function getLicensePlateDetails(string $licensePlate): string
    {
        $entity = new Kenteken();
        $entity->setKenteken($licensePlate);

        try {
            $text = $this->requestLicensePlateDetails($licensePlate);
            $entity->setOutput($text);
        } catch (Exception $e) {
            $entity->setOutput($e->getMessage());
            $entity->setConnection(false);
            return $e->getMessage();
        } finally {
            $this->kentekenRepository->save($entity);
        }

        return $text;
    }

    /**
     * @throws Exception
     */
    public function requestLicensePlateDetails(string $licensePlate): string
    {
        $filteredRows = $this->scrapeData($licensePlate);

        $this->rawData = $this->createKeyValueMap($filteredRows);

        $this->rawData['In bezit eigenaar'] = $this->calculateTimeInOwnership();
        $this->rawData['Omschrijving'] = $this->rawData['Modelomschrijving'] ?? $this->rawData['Model'];
        $this->rawData['Jaar'] = Carbon::parseFromLocale($this->rawData['Datum eerste toelating'], 'nl_NL')->year;

        return $this->buildString();
    }

    /**
     * @param string $licensePlate
     * @return array
     * @throws Exception
     */
    private function scrapeData(string $licensePlate): array
    {
        $client = new Client();
        $response = $client->request('GET', 'https://www.autoweek.nl/kentekencheck/' . $licensePlate);

        if ($response === null) {
            throw new Exception('Er is iets fout gegaan');
        }

        $check = $response->filter('div.inner-l-contents')->text();

        if (str_starts_with($check, 'Het kenteken bestaat niet')) {
            throw new Exception('Kenteken bestaat niet of is foutief');
        } else if (str_starts_with($check, 'Het kenteken-formaat klopt niet')) {
            throw new Exception('Het kenteken-formaat klopt niet');
        }

        return $response->filter('td')->each(static fn($row) => $row->text());
    }

    private function createKeyValueMap(array $filteredRows): array
    {
        $unevenRows = array_filter($filteredRows, static fn($index) => $index % 2 !== 0, ARRAY_FILTER_USE_KEY);
        $evenRows = array_filter($filteredRows, static fn($index) => $index % 2 === 0, ARRAY_FILTER_USE_KEY);
        return array_intersect_key(array_combine($evenRows, $unevenRows), array_flip($this->relevant));
    }

    private function calculateTimeInOwnership(): int
    {
        $date = Carbon::parseFromLocale($this->rawData['Datum laatste tenaamstelling'], 'nl_NL');
        return $date->diffInDays(Carbon::now());
    }

    private function buildString(): string
    {
        $data = array_intersect_key($this->rawData, array_flip($this->textKeys));

        /**
         * Deze QUATTRO AUDI RS6 is uit 2008. Hij kost €174.347. Hij heeft 579pk op Benzine. Het schakelen
         * gaat automatisch met een top van 250 km/h. Dit doet hij in 4,6 seconden met een gewicht van 2.000 kg. De 4e
         * eigenaar heeft hem 1211 dagen in bezit.
         */

        $text = "Deze ${data['Merk']} ${data['Omschrijving']} is uit ${data['Jaar']}. ";
        $text .= "Hij kost ${data['Nieuwprijs']}. Hij heeft ${data['Max. vermogen']} op ${data['Brandstof']}. ";
        $text .= "Het schakelen gaat ${data['Schakeling']} met een top van ${data['Topsnelheid']}. ";
        $text .= "Dit doet hij in ${data['Acceleratie 0-100 km/h']} met een gewicht van ${data['Massa leeg']}. ";
        $text .= "De ${data['Aantal eigenaren']}e eigenaar heeft hem ${data['In bezit eigenaar']} dagen in bezit.";

        return $text;
    }
}