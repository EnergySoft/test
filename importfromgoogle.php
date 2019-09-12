<?php

namespace App\Models\partners;

use App\Console\tools\TimeAutomatic;
use App\Models\Partners;
use App\Models\PartnersSites;
use App\Models\Statistics;
use Google\AdsApi\AdManager\AdManagerSession;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\Util\v201908\ReportDownloader;
use Google\AdsApi\AdManager\Util\v201908\StatementBuilder;
use Google\AdsApi\AdManager\v201908\Column;
use Google\AdsApi\AdManager\v201908\DateRangeType;
use Google\AdsApi\AdManager\v201908\Dimension;
use Google\AdsApi\AdManager\v201908\ExportFormat;
use Google\AdsApi\AdManager\v201908\ReportJob;
use Google\AdsApi\AdManager\v201908\ReportQuery;
use Google\AdsApi\AdManager\v201908\ServiceFactory;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use http\Exception\UnexpectedValueException;
use Illuminate\Support\Facades\DB;


class ImportFromGoogle extends ImportFrom
{
    const PARTNER_NAME = 'google';
    const IS_HB = false;


    // constant  for google
    const SAVED_QUERY_ID = '11352168226';
    const ORDER_ID = '11351975154';

    private function auth ()
    {

        $partner = Partners::where('name', '=', ImportFromGoogle::PARTNER_NAME)->first();

        if ($partner === null) {
                 Partners::create(array(
                    'name' => ImportFromGoogle::PARTNER_NAME,
                    'isHB' => ImportFromGoogle::IS_HB,
            ));
        }



        /** TODO:  https://developers.google.com/ad-manager/api/start */
        // Generate a refreshable OAuth2 credential for authentication.

        $oAuth2Credential = (new OAuth2TokenBuilder())
            ->fromFile()
            ->build();

        $session = (new AdManagerSessionBuilder())
            ->fromFile()
            ->withOAuth2Credential($oAuth2Credential)
            ->build();

        self::generateReporting( new ServiceFactory(), $session);

        // varaible global pour générer des rapport créer dans DFP
        //intval(self::SAVED_QUERY_ID)

    }



    public function generateReporting( ServiceFactory $serviceFactory, AdManagerSession $session)
    {
        $dates     = TimeAutomatic::time();
        $yesterday = str_replace('/', '-', date('Y/m/d', $dates['yesterday']));

        $row = 1;
        $reportService = $serviceFactory->createReportService($session);
        // Create report query.
        $reportQuery = new ReportQuery();
        $reportQuery->setDimensions(
            [
                Dimension::DATE,
                Dimension::AD_UNIT_ID,
                Dimension::AD_UNIT_NAME,

            ]
        );
        $reportQuery->setColumns(
            [
                Column::AD_EXCHANGE_LINE_ITEM_LEVEL_IMPRESSIONS,
                Column::AD_EXCHANGE_LINE_ITEM_LEVEL_CLICKS,
                Column::AD_EXCHANGE_LINE_ITEM_LEVEL_REVENUE,

            ]
        );


        $reportQuery->setDateRangeType(DateRangeType::YESTERDAY);
        $reportJob = new ReportJob();
        $reportJob->setReportQuery($reportQuery);
        $reportJob = $reportService->runReportJob($reportJob);
        $reportDownloader = new ReportDownloader(
            $reportService,
            $reportJob->getId()
        );

        if ($reportDownloader->waitForReportToFinish()) {

            // Write to system temp directory by default.
            $filePath = sprintf('%s.csv.gz', tempnam(storage_path('app/public/google/'), 'report-'.$yesterday.'-') );

            // Download the report.
            $reportDownloader->downloadReport(ExportFormat::CSV_DUMP, $filePath);

        } else {
            print "Report failed.\n";
        }



        $pathIntern =  storage_path('app/public/google/');
        $fileToConvert = '.gz';
        $yesterday = '.';
        $pathCompress = null;

        $detectFileCompress = scandir($pathIntern);

        foreach ( $detectFileCompress as $val ) {

            if (strpos($val, $fileToConvert) !== false) {

                if (strpos($val, $yesterday) !== false) {

                    $pathCompress =  storage_path('app/public/google/'.$val);
                }
            }
        }



        // unzip file
        $file_name = $pathCompress ;
        $buffer_size = 4096;
        $out_file_name = str_replace('.gz', '', $file_name);
        $file = gzopen($file_name, 'rb');
        $out_file = fopen($out_file_name, 'wb');

        while(!gzeof($file)) {

            fwrite($out_file, gzread($file, $buffer_size));


        }

        $googleCsv = [];

        $secondPathIntern =  storage_path('app/public/google/');
        $fileToConvert = '.csv';
        $yesterday = '.';
        $pathCsv = null;
        $goodUrl = null;

        $detectCsv = scandir($secondPathIntern);

        foreach ( $detectCsv as $val ) {

            if (strpos($val, $fileToConvert) !== false) {

                if (strpos($val, $yesterday) !== false) {

                    $pathCsv =  storage_path('app/public/google/'.$val);
                    $goodUrl = str_replace('.gz', '', $pathCsv);
                }
            }
        }

        $csvGoogle = [];

        $file = fopen($goodUrl, 'r');
        while (($line = fgetcsv($file)) !== FALSE) {
            //$line is an array of the csv elements
            $csvGoogle[] = $line;

        }
        fclose($file);

        $partner = Partners::where('name',ImportFromGoogle::PARTNER_NAME)->get()->first();

        unset($csvGoogle[0]);

        foreach ($csvGoogle as  $data ) {

            $getPartnerSite = PartnersSites::where('external_site_id', $data[1])->get()->first();

            if ($getPartnerSite === null) {


                PartnersSites::create([
                    'id_partner' => $partner->id,
                    'id_site' => null,
                    'external_site_id' => $data[1],
                    'external_site_name' => $data[2],
                ]);
            }


            if (Statistics::where('id_external', $getPartnerSite->external_site_id)->where('dates', $yesterday)->get()->first() === null) {

                $statisticGoogle[] = [
                    'clicks' => intval($data[4]),
                    'cpm' => 0,
                    'currency' => 'EUR',
                    'gain' => $this->getNumber($data[5]),
                    'impressions' => intval($data[3]),
                    'date' => $yesterday,
                    'dates' => $yesterday,
                    'id_external' => $getPartnerSite->external_site_id,
                    'id_partner_site' => $getPartnerSite->id


                ];

            } else {

                DB::table('statistics')
                    ->where('id_external', '=', $getPartnerSite->external_site_id)
                    ->where('dates', $yesterday)
                    ->update([
                        'clicks' => intval($data[4]),
                        'cpm' => 0,
                        'currency' => 'EUR',
                        'gain' => $this->getNumber($data[5]),
                        'impressions' => intval($data[3]),
                    ]);


            }
        }






    }


    public function unzip()
    {
        $dates     = TimeAutomatic::time();
        $yesterday = str_replace('/', '-', date('Y/m/d', $dates['yesterday']));

        $partner = Partners::where('name', '=', ImportFromGoogle::PARTNER_NAME)->first();

        if ($partner === null) {
            Partners::create(array(
                'name' => ImportFromGoogle::PARTNER_NAME,
                'isHB' => ImportFromGoogle::IS_HB,
            ));
        }






         $path = storage_path('app/public/unzip/report.csv.gz');


         // unzip file
         $file_name = $path ;
         $buffer_size = 4096;
         $out_file_name = str_replace('.gz', '', $file_name);
         $file = gzopen($file_name, 'rb');
         $out_file = fopen($out_file_name, 'wb');

         while(!gzeof($file)) {

             fwrite($out_file, gzread($file, $buffer_size));


         }



        // read csv and transform to array

        $partner = Partners::where('name',ImportFromGoogle::PARTNER_NAME)->get()->first();

        $csvGoogle = [];

        $csv = storage_path('app/public/google/test.csv');

        $file = fopen($csv, 'r');
        while (($line = fgetcsv($file)) !== FALSE) {
            $csvGoogle[] = $line;
        }
        fclose($file);


        unset($csvGoogle[0]);
        array_unique($csvGoogle, SORT_REGULAR);


        $statisticGoogle = [];


        foreach ($csvGoogle as  $data ) {


            $getPartnerSite = PartnersSites::where('external_site_id',$data[1])->get()->first();


            if ($getPartnerSite === null) {


                PartnersSites::create([
                    'id_partner' => $partner->id,
                    'id_site'=> null,
                    'external_site_id' => $data[1],
                    'external_site_name'=> $data[2],
                ]);
            }
            if ($data[2] === $getPartnerSite->external_site_name) {


                $propertyStatistic = Statistics::where('id_external',$getPartnerSite->external_site_id)
                    ->where('dates',$yesterday)
                    ->get()
                    ->first();


                if ($propertyStatistic === null ) {

                   // dd($getPartnerSite->id);

                    $statisticGoogle = [
                        'clicks' => intval($data[4]),
                        'cpm' => 0,
                        'currency' => 'EUR',
                        'gain' =>  $this->getNumber($data[5]),
                        'impressions' => intval($data[3]),
                        'id_partner_site' => $getPartnerSite->id,
                        'date' => $yesterday,
                        'dates' => $yesterday,
                        'id_external' => $getPartnerSite->external_site_id,

                    ];

                    Statistics::create($statisticGoogle);
                } else {

                    DB::table('statistics')
                        ->where('id_external', '=',  $getPartnerSite->external_site_id)
                        ->where('dates', $yesterday)
                        ->update([
                            'gain' => $this->getNumber($data[5]),
                            'impressions' => intval($data[3]),
                            'clicks' => intval($data[4]),
                        ]);

                }

            }

        }





    }

    public function getNumber(string $numberStatistic)
    {


        if(strlen($numberStatistic) > 9) {

            $wordMap = wordwrap($numberStatistic, 4, ".", true);
            $split = explode('.', $wordMap);

            return intval($split[0]);

        }

        elseif ( strlen($numberStatistic) === 9 ){

           $wordMap = wordwrap($numberStatistic, 3, ".", true);
           $split = explode('.', $wordMap);

           return intval($split[0]);

        } elseif (strlen($numberStatistic) === 8 ) {


            $wordMap = wordwrap($numberStatistic, 2, ".", true);
            $split = explode('.', $wordMap);

            return intval($split[0]);

        } elseif( strlen($numberStatistic) === 7 ) {

            $wordMap = wordwrap($numberStatistic, 1, ".", true);
            $split = explode('.', $wordMap);
            $result = $split[0] .'.'.  $split[1].$split[2] ;
            $float = (float)$result;


            return $float;

        }

        else {

            $numberString  ='0' . $numberStatistic;

            $wordMap = wordwrap($numberString, 1, ".", true);
            $split = explode('.', $wordMap);
            $result = $split[0] .'.'.  $split[1].$split[2] ;
            $float = (float)$result;


            return $float;

        }



    }


    public function execute() {

        return $this->unzip();
    }



 /*   public function getStatisticById(ServiceFactory $serviceFactory, AdManagerSession $session, $savedQueryId)
    {
        //ici c est sensé fonctionnée voir pk sa ne marche pas

        $reportService = $serviceFactory->createReportService($session);
        // Create statement to retrieve the saved query.
        $statementBuilder = (new StatementBuilder())->where('id = :id')
            ->orderBy('id ASC')
            ->limit(1)
            ->withBindVariableValue('id', $savedQueryId);
        $savedQueryPage = $reportService->getSavedQueriesByStatement(
            $statementBuilder->toStatement()
        );


        $savedQuery = $savedQueryPage->getResults()[0];

        dd($savedQuery);

        if ($savedQuery->getIsCompatibleWithApiVersion() === false) {
            throw new UnexpectedValueException(
                'The saved query is not compatible with this API version.'
            );
        }
        $reportQuery = $savedQuery->getReportQuery();
        //  dd($reportQuery);
        // Optionally modify the query.
        $reportQuery = $savedQuery->getReportQuery();
        $reportQuery->setAdUnitView(ReportQueryAdUnitView::HIERARCHICAL);
        // Create report job using the saved query.


        dd($reportQuery);
        $reportJob = new ReportJob();
        $reportJob->setReportQuery($reportQuery);
        $reportJob = $reportService->runReportJob($reportJob);

    }

    public function run (ServiceFactory $serviceFactory, AdManagerSession $session)
    {

        $reportService = $serviceFactory->createReportService($session);
        $networkService = $serviceFactory->createNetworkService($session);


        $statementBuilder = (new StatementBuilder())->where('id = :id')
            ->orderBy('id ASC')
            ->limit(1);
        //   ->withBindVariableValue('id', $savedQueryId);

        dd($statementBuilder);

        $savedQueryPage = $reportService->getSavedQueriesByStatement(
            $statementBuilder->toStatement()
        );
        $savedQuery = $savedQueryPage->getResults()[0];

        if ($savedQuery->getIsCompatibleWithApiVersion() === false) {

            return  'The saved query is not compatible with this API version.';
        }

        $reportQuery = $savedQuery->getReportQuery();


    }
*/

}