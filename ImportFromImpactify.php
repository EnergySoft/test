<?php


namespace App\Models\partners;


use App\Console\tools\CpmCalculate;
use App\Console\tools\TimeAutomatic;
use App\Models\Partners;
use App\Models\PartnersSites;
use App\Models\Statistics;
use App\Models\statistics\StatisticsProcessor;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class ImportFromImpactify extends ImportFrom
{

    const BASE_URL = 'https://app.impactify.io/api/reporting';
    const TOKEN = '1812-c416a17977d2a21c6ada07f627528d31';
    const PARTNER_NAME = 'impactify';
    const IS_HB = false;


    public function __construct()
    {
        $this->client = new Client([
            'headers' => [
                'X-IMPACTIFY-APIKEY' => static::TOKEN,
                'content-type' => 'application/json'
            ],
            'base_uri' => static::BASE_URL,
        ]);
    }

    private function getData()
    {

        $datesClass = new TimeAutomatic();

        try {
            $cpmC = new CpmCalculate();
            $result = $this->client->request('GET', '',
                [
                    'query' => [
                        'report_date' => ['min' => date('Y-m-d', strtotime('yesterday')), 'max' => date('Y-m-d', strtotime('yesterday'))],
                    ]
                ]
            );

            $result = $result->getBody()->getContents();

            dump('impactify uri :'. $datesClass->frenchTime());

        } catch (\Throwable $e) {
            return ['error' => $e->getMessage(), 'data' => ''];
        }
        
        $responseData = \GuzzleHttp\json_decode($result, true);

        $statistics = [];

        foreach ($responseData as $response ) {

            foreach ($response as $data) {


                $cpm = $cpmC->calculate(intval($data['revenue']), intval($data['impressions']));

                $statistics[] = [
                    'external_site_id' => $data['app'],
                    'external_site_name' => $data['app'],
                    'impressions' => intval($data['impressions']),
                    'clicks' => intval($data['clicks']),
                    'gain' =>  intval($data['revenue']),
                    'revenue_float' => floatval($data['revenue']),
                    'cpm' => $cpm
                ];

            }
        }


        dump('impactify data :'. $datesClass->frenchTime());
        dump($statistics);

        return $this->insertData($statistics);

    }


    private function insertData($statistics)
    {
        $partner = Partners::where('name', '=', ImportFromImpactify::PARTNER_NAME)->first();
        $tA = TimeAutomatic::time();
        $yesterday = str_replace('/', '-', date('Y/m/d', $tA['yesterday']));
        $conversationUsdEuro = new StatisticsProcessor();
        $iteationForDataStatistics = 0;
        $numberofcreate = 0;
        $numberofupdate = 0;
        $timelog = new TimeAutomatic();

        foreach ($statistics as $data) {

            $iteationForDataStatistics++;
            dump($iteationForDataStatistics++);
            dump($data);

            $partnerSite = PartnersSites::where('id_partner', '=', $partner->id)->where('external_site_id', '=', $data['external_site_id'])->first();

            if ($partnerSite === null) {

                PartnersSites::create([
                    'id_partner' => $partner->id,
                    'id_site' => null,
                    'external_site_id' =>  $data['external_site_id'],
                    'external_site_name' => $data['external_site_name'],
                ]);
            }

            $partnerSite = DB::table('partners_sites')
                ->where('id_partner' , '=', $partner->id)
                ->where('external_site_id','=', $data['external_site_id'])
                ->select('id')
                ->get()->count();

            if($partnerSite != 0 ) {

                $getIdPartnerSite = DB::table('partners_sites')
                    ->where('id_partner', '=', $partner->id)
                    ->where('external_site_id', '=', $data['external_site_id'])
                    ->select('id')
                    ->first();

                $gain = intval($conversationUsdEuro->convertUsdToEuro(intval($data['gain'])));
                $floatConversion = $conversationUsdEuro->convertUsdToEuro(intval($data['revenue_float']));

                $detectStatistics = Statistics::where('id_partner_site', $getIdPartnerSite->id)->where('dates', $yesterday)->first();

                if ($detectStatistics === null) {

                    dump('create');
                    $numberofcreate++;

                    $insertStat = new Statistics;
                    $insertStat->clicks = 0;
                    $insertStat->impressions = $data['impressions'];
                    $insertStat->currency = 'EUR';
                    $insertStat->gain = $gain ;
                    $insertStat->date = $yesterday;
                    $insertStat->dates = $yesterday;
                    $insertStat->cpm =  $data['cpm'];
                    $insertStat->id_partner_site = $getIdPartnerSite->id;
                    $insertStat->id_external = $data['external_site_id'];
                    $insertStat->revenue_float = $floatConversion;
                    $insertStat->save();

                }else {

                    dump('update');
                    $numberofupdate++;

                    DB::table('statistics')

                        ->where('id_external', '=', $data['external_site_id'])
                        ->where('dates', $yesterday)
                        ->update([
                            'gain' => $gain,
                            'impressions'=>  $data['impressions'],
                            'cpm' => $data['cpm'],
                            'revenue_float' =>  $floatConversion,

                        ]);


                }

                dump('data register');

                $rStat = Statistics::where('id_partner_site', $getIdPartnerSite->id)->where('dates', $yesterday)->first();
                dump($rStat);


            }

        }

        $arrayResponseLogs = [
            'lenghtData' => $iteationForDataStatistics,
            'numberOfCreationInDatabase' => $numberofcreate,
            'numberOfUpdateInDatabase' => $numberofupdate,
            'lenghtArrayReport' => count($statistics),
            'SizeOfArrayReport' => sizeof($statistics),
            "memoryUsage" => memory_get_usage(),
            'endexuceution'=> $timelog->frenchTime()
        ];

        dump($arrayResponseLogs);

        return $statistics;

    }

    public function execute()
    {

        $partner = Partners::where('name', '=', ImportFromImpactify::PARTNER_NAME)->first();

        if ($partner === null) {

            Partners::create([
                'name' => ImportFromImpactify::PARTNER_NAME,
                'isHB' => ImportFromImpactify::IS_HB,
            ]);
        }

        return $this->getData();
    }
}