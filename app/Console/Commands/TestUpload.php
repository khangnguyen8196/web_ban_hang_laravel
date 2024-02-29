<?php

namespace App\Console\Commands;

use League\Csv\Writer;
use Aws\CommandPool;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use SimpleXMLElement;


class TestUpload extends Command
{
    protected $s3Instance;
    protected $isRunSuccess = true;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */

     public function handle()
     {
         $fromDate = Carbon::parse('2024-01-01 00:00:00')->format('Y-m-d');
         $toDate = Carbon::parse('2024-01-31 00:00:00')->format('Y-m-d');
        //  iss-ndl-opac
        //zassaku
         try {
//             $apiLink = 'http://iss.ndl.go.jp/api/oaipmh?verb=ListRecords';
//             $apiLink .= '&metadataPrefix=dcndl';
//             $apiLink .= '&set=iss-ndl-opac';
//             $apiLink .= '&from='.$fromDate.'';
//             $apiLink .= '&until='.$toDate.'';
//             $response = Http::get($apiLink);
//             $recordContent = $response->body();
             $folderPath = storage_path('app/public/tmpXml2/rawFile');
             $now = Carbon::now();
//             $fileName = $folderPath . '/rawFile_' . $now->format('Y_m_d_H_i_s') . '.xml';
             $fileName = $folderPath . '/rawFile_2024_02_29_16_02_32.xml';
//             if (!File::isDirectory($folderPath)) {
//                 File::makeDirectory($folderPath, 0755, true, true);
//             }
//             file_put_contents($fileName, $recordContent);
             $this->extractTitlesFromXML($fileName);
//             Log::info('XML file created and saved successfully.');
         } catch (\Exception $exception) {
             Log::error($exception->getMessage());
         }
     }

    //  function extractTitlesFromXML($filePath){
    //     if (!File::exists($filePath)) {
    //         return [];
    //     }

    //     $recordContent = File::get($filePath);
    //     $record = new SimpleXMLElement($recordContent);
    //     $titleNodes = $record->xpath('.//*[local-name()="title"]/*[local-name()="Description"]/*[local-name()="value"]/text()');
    //     $titles = [];
    //     foreach ($titleNodes as $title) {
    //         $titles[] = (string)$title;
    //     }
    //     log::info($titles);
    // // return $titles;
    // }

//     function extractTitlesFromXML($filePath){
//     if (!File::exists($filePath)) {
//         return [];
//     }
//     $recordContent = File::get($filePath);
//     $record = new SimpleXMLElement($recordContent);
//     $ListRecords = $record->ListRecords;
//     $result = [];
//     foreach ($ListRecords as $item) {
//         $record = $item->record;
//         $record->registerXPathNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
//         $record->registerXPathNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
//         $record->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
//         $record->registerXPathNamespace('dcndl', 'http://ndl.go.jp/dcndl/terms/');
//         $record->registerXPathNamespace('foaf', 'http://xmlns.com/foaf/0.1/');
//         $record->registerXPathNamespace('dcterms', 'http://purl.org/dc/terms/');

//         $TITLE = $record->xpath('.//dc:title/rdf:Description/rdf:value/text()');
//         $SERIESTITLE = $record->xpath('.//dcndl:seriesTitle/rdf:Description/rdf:value/text()');
//         // $VOLUMETITLE = $record->xpath('') ;
//         $ALTERNATIVE =  $record->xpath('.//dcndl:alternative/rdf:Description/rdf:value/text()');
//         // $VOLUME = $record->xpath('');
//         $CREATOR = $record->xpath('.//dcterms:creator/foaf:Agent/foaf:name/text()');
//         if ($CREATOR !== false && count($CREATOR) > 0) {
//             $CREATOR_VALUE = implode('/', array_map(fn($element) => $element->__toString(), $CREATOR));
//             Log::info($CREATOR_VALUE);
//         } else {
//             $CREATOR_VALUE ='';
//             Log::info( $CREATOR_VALUE);
//         }
//         $result[] = $CREATOR_VALUE;  
//         // $DIGITIZEDPUBLISHER= $record->xpath('');
//         $PUBLICYEAR = $record->xpath('.//dcterms:issued[@rdf:datatype="http://purl.org/dc/terms/W3CDTF"]/text()');
//         // $NDLC = $record->xpath('');
//         // $NDC = $record->xpath('');
//         // $NDC8 = $record->xpath('');
//         // $NDC9 = $record->xpath('');
//         // $GHQSCAP = $record->xpath('');
//         // $UDC = $record->xpath('');
//         // $DDC = $record->xpath('');
//         // $NDLSH = $record->xpath('');
//         $PAGERANGE = $record->xpath('.//dcndl:pageRange/text()');
//         // $ABSTRACT1 = $record->xpath('');
//         // $ABSTRACT2 = $record->xpath('');
//         $METARIALTPE = $record->xpath('.//dcndl:materialType/@rdfs:label');
//         // $METARIALID = $record->xpath('');
//         // $IMTFORMAT = $record->xpath('');
//         $PUBLISHER = $record->xpath('.//dcterms:publisher/foaf:Agent/foaf:name/text()');
//         $LANGUAGE = $record->xpath('.//dcterms:language/text()');
//         $ISOLANGUAGE = $record->xpath('.//dcterms:language/text()');
//         // $EDITION = $record->xpath('');
//         $PUBNAME = $record->xpath('.//dcndl:publicationName/text()');
//         $PUBPLACECD = $record->xpath('.//dcndl:publicationPlace[@rdf:datatype="http://purl.org/dc/terms/ISO3166"]/text()');
//         $PUBPLACENAME = $record->xpath('.//dcndl:publicationName/text()');
//         $PUBVOLUME = $record->xpath('.//dcndl:publicationVolume/text()');
//         $PUBDATE = $record->xpath('.//dcterms:date/text()');
//         // $TABLECONTENTS = $record->xpath('');
//         // $PARTTITLE = $record->xpath('');
//         $NUMBER = $record->xpath('.//dcndl:number/text()');
//         // $SPATIAL = $record->xpath('');
//         $DESCRIPTION = $record->xpath('.//dcndl:BibResource/dcterms:description/text()');
//         // $EXTENT = '';
//         // $PRICE = $record->xpath('');
//         // $SERIESCREATOR = $record->xpath('');
//         // $JISX0402 = $record->xpath('');
//         // $NCNO = $record->xpath('');
//         // $UTMNO = $record->xpath('');
//         $JPNO = $record->xpath('.//dcterms:identifier[@xsi:type="dcndl:JPNO"]/text()');
//         $ISBN = $record->xpath('.//dcterms:identifier[@xsi:type="dcndl:ISBN"]/text()');
//         $ISSN = $record->xpath('.//dcterms:identifier[rdf:datatype="http://ndl.go.jp/dcndl/terms/ISSN"]/text()');
//         $ISSNL = $record->xpath('.//dcterms:identifier[@xsi:type="dcndl:ISSNL"]/text()');
//         $INCORRECTISSN = $record->xpath('.//dcterms:identifier[@xsi:type="dcndl:IncorrectISSN"]/text()');
//         $INCORRECTISSNL = $record->xpath('.//dcterms:identifier[@xsi:type="dcndl:IncorrectISSNL"]/text()');
//         $ISBNSET = $record->xpath('.//dcterms:identifier[@xsi:type="dcndl:SetISBN"]/text()');
//         $BRNO = $record->xpath('.//dcterms:identifier[@xsi:type="dcndl:BRNO"]/text()');
//         $DOI = $record->xpath('.//dcterms:identifier[@xsi:type="dcndl:DOI"]/text()');
//         // $NDLBIDID = $record->xpath('');
//         // $STANDARDNO = $record->xpath('');
//         // $TOHANMARCNO = $record->xpath('');
//         // $USMARCNO = $record->xpath('');
//         // $NSMARCNO = $record->xpath('');
//         // $UKMARCNO = $record->xpath('');
//         // $RIS502 = $record->xpath('');
//         // $OCLCNOS = $record->xpath('');
//         // $RLINNO = $record->xpath('');
//         // $KAKENHINO = $record->xpath('');
//         // $TRNO = $record->xpath('');
//         // $GPOBIBNO = $record->xpath('');
//         // $NIIBIBNO = $record->xpath('');
//         // $UNDS = $record->xpath('');
//         // $UNSN = $record->xpath('');
//         // $CHECKSUM = '';
//         $INSERTTIMES = $record->$record->xpath('header/datestamp/text()');
//         // $CODE = '';
//         // $CODE2 = '';
//         // $CODE3 = '';
//         // $CRTDATE = '';
//         // $UPDDATETIME ='';
//         $TITLETRANS = $record->xpath('.//dc:title/rdf:Description/dcndl:transcription/text()');
//         // $ALTERNATIVETRANS = $record->xpath('');
//         // $VOLUMETRANS =$record->xpath('');
//         // $VOLUMETITLETRANS = $record->xpath('');
//         // $SERIESTITLETRANS = $record->xpath('');
//         // $PARTTITLETRANS = $record->xpath('');
//         // $CREATORTRANS = $record->xpath('');
//         // $DATEDIGITIZED = $record->xpath('');
//         // $USCAR =  $record->xpath('');
//         // $MCJ = $record->xpath('');
//         // $NDLBIBID = $record->xpath('');
//         // $NDLJP = $record->xpath('');
//         // $TRCMARCNO = $record->xpath('');
//         // $OPLMARCNO = $record->xpath('');
//         // $KNMARCNO = $record->xpath('');
//         // $CODEN = $record->xpath('');
//         // $ISRN = $record->xpath('');
//         // $ISMN = $record->xpath('');
//         // $PBNO = $record->xpath('');
//         // $PLNO = $record->xpath('');
//         // $GPOCN = $record->xpath('');
//         // $SUPTDOC = $record->xpath('');
//         // $SICI = $record->xpath('');
//         // $ICNO = $record->xpath('');
//         // $TEMPORAL = $record->xpath('');
//         // $PERIOD = $record->xpath('');
//         // $ACCESSRIGHTS = $record->xpath('');
//         // $RIGHTS = $record->xpath('');
//         // $RIGHTSHOLDER = $record->xpath('');
//         // $URI = $record->xpath('');
//         // $NDC10 = $record->xpath('');
//         // $PARTCREATOR = $record->xpath('');
//         // $LCC = $record->xpath('');
//         // $ErrorISBN = $record->xpath('');
//         // $EXTENT = $record->xpath('');
//         // $ISSUE = $record->xpath('');
//         // $W3CDTF = $record->xpath('');
//         // $result = implode('/', array_map(fn($element) => $element->__toString(), $CREATOR));
//         // log::info($result);
//     }
//     log::info($result);
//     die();
// }

function extractTitlesFromXML($filePath){
    if (!File::exists($filePath)) {
        return [];
    }
    $recordContent = File::get($filePath);
    $record = new SimpleXMLElement($recordContent);
    $ListRecords = $record->ListRecords;
    $result = [];
    foreach ($ListRecords as $item) {
        // $record = $item->record;
        $item->registerXPathNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
        $item->registerXPathNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
        $item->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
        $item->registerXPathNamespace('dcndl', 'http://ndl.go.jp/dcndl/terms/');
        $item->registerXPathNamespace('foaf', 'http://xmlns.com/foaf/0.1/');
        $item->registerXPathNamespace('dcterms', 'http://purl.org/dc/terms/');
        
        $CREATOR = $item->xpath('.//dcterms:creator/foaf:Agent/foaf:name/text()');
        
        if ($CREATOR !== false && count($CREATOR) > 0) {
            $CREATOR_VALUE = implode('/', array_map(fn($element) => $element->__toString(), $CREATOR));
        } else {
            $CREATOR_VALUE ='';
        }
        $result[] = $CREATOR_VALUE;
       
    }
    $res= $result;
    Log::info($res);
}







}
