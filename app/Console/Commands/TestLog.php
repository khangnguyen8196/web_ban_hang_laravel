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


class TestLog extends Command
{
    protected $s3Instance;
    protected $isRunSuccess = true;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:crona';

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
         try {
             $apiLink = 'http://iss.ndl.go.jp/api/oaipmh?verb=ListRecords';
             $apiLink .= '&metadataPrefix=dcndl';
             $apiLink .= '&set=iss-ndl-opac';
             $apiLink .= '&from='.$fromDate.'';
             $apiLink .= '&until='.$toDate.'';
             $response = Http::get($apiLink);
             $xmlContent = $response->body(); 
             $folderPath = storage_path('app/public/tmpXml2/rawFile');
             $now = Carbon::now();
             $fileName = $folderPath . '/rawFile_' . $now->format('Y_m_d_H_i_s') . '.xml';
             if (!File::isDirectory($folderPath)) {
                 File::makeDirectory($folderPath, 0755, true, true);
             }
             file_put_contents($fileName, $xmlContent);
             $this->extractTitlesFromXML($fileName);
             Log::info('XML file created and saved successfully.');
         } catch (\Exception $exception) {
             Log::error($exception->getMessage());
         }
     }
     function extractTitlesFromXML($filePath){
        if (!File::exists($filePath)) {
            return [];
        }
        $xmlContent = File::get($filePath);
        $xml = new SimpleXMLElement($xmlContent);
        $ListRecords = $xml->ListRecords;
    
        foreach ($ListRecords as $item) {
            $record = $item->record;
            $record->registerXPathNamespace('dcterms', 'http://purl.org/dc/terms/');
            $record->registerXPathNamespace('foaf', 'http://xmlns.com/foaf/0.1/');
            $PUBLISHER = $record->xpath('.//dcterms:publisher/foaf:Agent/foaf:name/text()');
            $CREATOR = $record->xpath('.//dcterms:creator/foaf:Agent/foaf:name/text()');
    
            log::info($PUBLISHER);
            dd($CREATOR);
        }
    }
//     function extractTitlesFromXML($filePath)
// {
//     if (!File::exists($filePath)) {
//         return [];
//     }
//     $xmlContent = File::get($filePath);
//     $xml = new SimpleXMLElement($xmlContent);
//     $xml->registerXPathNamespace('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
//     $xml->registerXPathNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
//     $xml->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
//     $xml->registerXPathNamespace('dcndl', 'http://ndl.go.jp/dcndl/terms/');
//     $xml->registerXPathNamespace('foaf', 'http://xmlns.com/foaf/0.1/');
//     $xml->registerXPathNamespace('dcterms', 'http://purl.org/dc/terms/');

//     $TITLE = $xml->xpath('//dc:title/rdf:Description/rdf:value/text()');
//     $SERIESTITLE = $xml->xpath('//dcndl:seriesTitle/rdf:Description/rdf:value/text()');
//     // $VOLUMETITLE = $xml->xpath('') ;
//     $ALTERNATIVE =  $xml->xpath('//dcndl:alternative/rdf:Description/rdf:value/text()');
//     // $VOLUME = $xml->xpath('');
//     $CREATOR = $xml->xpath('//dcterms:creator/foaf:Agent/foaf:name/text()');
//     // $DIGITIZEDPUBLISHER= $xml->xpath('');
//     $PUBLICYEAR = $xml->xpath('//dcterms:issued[@rdf:datatype="http://purl.org/dc/terms/W3CDTF"]/text()');
//     // $NDLC = $xml->xpath('');
//     // $NDC = $xml->xpath('');
//     // $NDC8 = $xml->xpath('');
//     // $NDC9 = $xml->xpath('');
//     // $GHQSCAP = $xml->xpath('');
//     // $UDC = $xml->xpath('');
//     // $DDC = $xml->xpath('');
//     // $NDLSH = $xml->xpath('');
//     $PAGERANGE = $xml->xpath('//dcndl:pageRange/text()');
//     // $ABSTRACT1 = $xml->xpath('');
//     // $ABSTRACT2 = $xml->xpath('');
//     $METARIALTPE = $xml->xpath('//dcndl:materialType/@rdfs:label');
//     // $METARIALID = $xml->xpath('');
//     // $IMTFORMAT = $xml->xpath('');
//     $PUBLISHER = $xml->xpath('//dcterms:publisher/foaf:Agent/foaf:name/text()');
//     $LANGUAGE = $xml->xpath('//dcterms:language/text()');
//     $ISOLANGUAGE = $xml->xpath('//dcterms:language/text()');
//     // $EDITION = $xml->xpath('');
//     $PUBNAME = $xml->xpath('//dcndl:publicationName/text()');
//     $PUBPLACECD = $xml->xpath('//dcndl:publicationPlace[@rdf:datatype="http://purl.org/dc/terms/ISO3166"]/text()');
//     $PUBPLACENAME = $xml->xpath('//dcndl:publicationName/text()');
//     $PUBVOLUME = $xml->xpath('//dcndl:publicationVolume/text()');
//     $PUBDATE = $xml->xpath('//dcterms:date/text()');
//     // $TABLECONTENTS = $xml->xpath('');
//     // $PARTTITLE = $xml->xpath('');
//     $NUMBER = $xml->xpath('//dcndl:number/text()');
//     // $SPATIAL = $xml->xpath('');
//     $DESCRIPTION = $xml->xpath('//dcndl:BibResource/dcterms:description/text()');
//     // $EXTENT = '';
//     // $PRICE = $xml->xpath('');
//     // $SERIESCREATOR = $xml->xpath('');
//     // $JISX0402 = $xml->xpath('');
//     // $NCNO = $xml->xpath('');
//     // $UTMNO = $xml->xpath('');
//     $JPNO = $xml->xpath('//dcterms:identifier[@xsi:type="dcndl:JPNO"]/text()');
//     $ISBN = $xml->xpath('//dcterms:identifier[@xsi:type="dcndl:ISBN"]/text()');
//     $ISSN = $xml->xpath('//dcterms:identifier[rdf:datatype="http://ndl.go.jp/dcndl/terms/ISSN"]/text()');
//     $ISSNL = $xml->xpath('//dcterms:identifier[@xsi:type="dcndl:ISSNL"]/text()');
//     $INCORRECTISSN = $xml->xpath('//dcterms:identifier[@xsi:type="dcndl:IncorrectISSN"]/text()');
//     $INCORRECTISSNL = $xml->xpath('//dcterms:identifier[@xsi:type="dcndl:IncorrectISSNL"]/text()');
//     $ISBNSET = $xml->xpath('//dcterms:identifier[@xsi:type="dcndl:SetISBN"]/text()');
//     $BRNO = $xml->xpath('//dcterms:identifier[@xsi:type="dcndl:BRNO"]/text()');
//     $DOI = $xml->xpath('//dcterms:identifier[@xsi:type="dcndl:DOI"]/text()');
//     // $NDLBIDID = $xml->xpath('');
//     // $STANDARDNO = $xml->xpath('');
//     // $TOHANMARCNO = $xml->xpath('');
//     // $USMARCNO = $xml->xpath('');
//     // $NSMARCNO = $xml->xpath('');
//     // $UKMARCNO = $xml->xpath('');
//     // $RIS502 = $xml->xpath('');
//     // $OCLCNOS = $xml->xpath('');
//     // $RLINNO = $xml->xpath('');
//     // $KAKENHINO = $xml->xpath('');
//     // $TRNO = $xml->xpath('');
//     // $GPOBIBNO = $xml->xpath('');
//     // $NIIBIBNO = $xml->xpath('');
//     // $UNDS = $xml->xpath('');
//     // $UNSN = $xml->xpath('');
//     // $CHECKSUM = '';
//     $INSERTTIMES = $xml->$xml->xpath('header/datestamp/text()');
//     // $CODE = '';
//     // $CODE2 = '';
//     // $CODE3 = '';
//     // $CRTDATE = '';
//     // $UPDDATETIME ='';
//     $TITLETRANS = $xml->xpath('//dc:title/rdf:Description/dcndl:transcription/text()');
//     // $ALTERNATIVETRANS = $xml->xpath('');
//     // $VOLUMETRANS =$xml->xpath('');
//     // $VOLUMETITLETRANS = $xml->xpath('');
//     // $SERIESTITLETRANS = $xml->xpath('');
//     // $PARTTITLETRANS = $xml->xpath('');
//     // $CREATORTRANS = $xml->xpath('');
//     // $DATEDIGITIZED = $xml->xpath('');
//     // $USCAR =  $xml->xpath('');
//     // $MCJ = $xml->xpath('');
//     // $NDLBIBID = $xml->xpath('');
//     // $NDLJP = $xml->xpath('');
//     // $TRCMARCNO = $xml->xpath('');
//     // $OPLMARCNO = $xml->xpath('');
//     // $KNMARCNO = $xml->xpath('');
//     // $CODEN = $xml->xpath('');
//     // $ISRN = $xml->xpath('');
//     // $ISMN = $xml->xpath('');
//     // $PBNO = $xml->xpath('');
//     // $PLNO = $xml->xpath('');
//     // $GPOCN = $xml->xpath('');
//     // $SUPTDOC = $xml->xpath('');
//     // $SICI = $xml->xpath('');
//     // $ICNO = $xml->xpath('');
//     // $TEMPORAL = $xml->xpath('');
//     // $PERIOD = $xml->xpath('');
//     // $ACCESSRIGHTS = $xml->xpath('');
//     // $RIGHTS = $xml->xpath('');
//     // $RIGHTSHOLDER = $xml->xpath('');
//     // $URI = $xml->xpath('');
//     // $NDC10 = $xml->xpath('');
//     // $PARTCREATOR = $xml->xpath('');
//     // $LCC = $xml->xpath('');
//     // $ErrorISBN = $xml->xpath('');
//     // $EXTENT = $xml->xpath('');
//     // $ISSUE = $xml->xpath('');
//     // $W3CDTF = $xml->xpath('');
//     $results = [];
//     foreach ($TITLE as $INDEX => $VALUE) {
//         $record = [
//             'TITLE' => (string)$VALUE,
//             'SERIESTITLE' => isset($SERIESTITLE[$INDEX]) ? (string)$SERIESTITLE[$INDEX] : null,
//             'VOLUMETITLE' => isset($VOLUMETITLE[$INDEX]) ? (string)$VOLUMETITLE[$INDEX] : null,
//             'ALTERNATIVE' => isset($ALTERNATIVE[$INDEX]) ? (string)$ALTERNATIVE[$INDEX] : null,
//             'VOLUME' => isset($VOLUME[$INDEX]) ? (string)$VOLUME[$INDEX] : null,
//             'CREATOR' => isset($CREATOR[$INDEX]) ? (string)$CREATOR[$INDEX] : null,
//             'DIGITIZEDPUBLISHER'=>isset($DIGITIZEDPUBLISHER[$INDEX]) ? (string)$DIGITIZEDPUBLISHER[$INDEX] : null,
//             'PUBLICYEAR'=>isset($PUBLICYEAR[$INDEX]) ? (string)$PUBLICYEAR[$INDEX] : null,
//             'NDLC' =>isset($NDLC[$INDEX]) ? (string)$NDLC[$INDEX] : null,
//             'NDC' =>isset($NDC[$INDEX]) ? (string)$NDC[$INDEX] : null,
//             'NDC8' =>isset($NDC8[$INDEX]) ? (string)$NDC8[$INDEX] : null,
//             'NDC9' => isset($NDC9[$INDEX]) ? (string)$NDC9[$INDEX] : null,
//             'GHQSCAP' => isset($GHQSCAP[$INDEX]) ? (string)$GHQSCAP[$INDEX] : null,
//             'UDC' => isset($UDC[$INDEX]) ? (string)$UDC[$INDEX] : null,
//             'DDC' =>isset($DDC[$INDEX]) ? (string)$DDC[$INDEX] : null,
//             'NDLSH' => isset($NDLSH[$INDEX]) ? (string)$NDLSH[$INDEX] : null,
//             'PAGERANGE' => isset($PAGERANGE[$INDEX]) ? (string)$PAGERANGE[$INDEX] : null,
//             'ABSTRACT1' => isset($ABSTRACT1[$INDEX]) ? (string)$ABSTRACT1[$INDEX] : null,
//             'ABSTRACT2' => isset($ABSTRACT2[$INDEX]) ? (string)$ABSTRACT2[$INDEX] : null,
//             'METARIALTPE' => isset($METARIALTPE[$INDEX]) ? (string)$METARIALTPE[$INDEX] : null,
//             'METARIALID' => isset($METARIALID[$INDEX]) ? (string)$METARIALID[$INDEX] : null,
//             'IMTFORMAT' => isset($IMTFORMAT[$INDEX]) ? (string)$IMTFORMAT[$INDEX] : null,
//             'PUBLISHER' => isset($PUBLISHER[$INDEX]) ? (string)$PUBLISHER[$INDEX] : null,
//             'LANGUAGE' =>    isset($LANGUAGE[$INDEX]) ? (string)$LANGUAGE[$INDEX] : null,
//             'ISOLANGUAGE' => isset($ISOLANGUAGE[$INDEX]) ? (string)$ISOLANGUAGE[$INDEX] : null,
//             'EDITION' => isset($EDITION[$INDEX]) ? (string)$EDITION[$INDEX] : null,
//             'PUBNAME' => isset($PUBNAME[$INDEX]) ? (string)$PUBNAME[$INDEX] : null,
//             'PUBPLACECD' => isset($PUBPLACECD[$INDEX]) ? (string)$PUBPLACECD[$INDEX] : null,
//             'PUBPLACENAME' => isset($PUBPLACENAME[$INDEX]) ? (string)$PUBPLACENAME[$INDEX] : null,
//             'PUBVOLUME' => isset($PUBVOLUME[$INDEX]) ? (string)$PUBVOLUME[$INDEX] : null,
//             'PUBDATE' => isset($PUBDATE[$INDEX]) ? (string)$PUBDATE[$INDEX] : null,
//             'TABLECONTENTS' => isset($TABLECONTENTS[$INDEX]) ? (string)$TABLECONTENTS[$INDEX] : null,
//             'PARTTITLE' => isset($PARTTITLE[$INDEX]) ? (string)$PARTTITLE[$INDEX] : null,
//             'NUMBER' => isset($NUMBER[$INDEX]) ? (string)$NUMBER[$INDEX] : null,
//             'SPATIAL'=> isset($SPATIAL[$INDEX]) ? (string)$SPATIAL[$INDEX] : null,
//             'DESCRIPTION' => isset($DESCRIPTION[$INDEX]) ? (string)$DESCRIPTION[$INDEX] : null,
//             'EXTENT' => isset($EXTENT[$INDEX]) ? (string)$EXTENT[$INDEX] : null,
//             'PRICE' => isset($PRICE[$INDEX]) ? (string)$PRICE[$INDEX] : null,
//             'SERIESCREATOR' => isset($SERIESCREATOR[$INDEX]) ? (string)$SERIESCREATOR[$INDEX] : null,
//             'JISX0402' => isset($JISX0402[$INDEX]) ? (string)$JISX0402[$INDEX] : null,
//             'NCNO' => isset($JISX0402[$INDEX]) ? (string)$JISX0402[$INDEX] : null,
//             'UTMNO'  => isset($UTMNO[$INDEX]) ? (string)$UTMNO[$INDEX] : null,
//             'JPNO' => isset($JPNO[$INDEX]) ? (string)$JPNO[$INDEX] : null,
//             'ISBN' => isset($ISBN[$INDEX]) ? (string)$ISBN[$INDEX] : null,
//             'ISSN' => isset($ISSN[$INDEX]) ? (string)$ISSN[$INDEX] : null,
//             'ISSNL' => isset($ISSNL[$INDEX]) ? (string)$ISSNL[$INDEX] : null,
//             'INCORRECTISSN' => isset($INCORRECTISSN[$INDEX]) ? (string)$INCORRECTISSN[$INDEX] : null,
//             'INCORRECTISSNL' => isset($INCORRECTISSNL[$INDEX]) ? (string)$INCORRECTISSNL[$INDEX] : null,
//             'ISBNSET' => isset($ISBNSET[$INDEX]) ? (string)$ISBNSET[$INDEX] : null,
//             'BRNO' => isset($BRNO[$INDEX]) ? (string)$BRNO[$INDEX] : null,
//             'DOI' => isset($DOI[$INDEX]) ? (string)$DOI[$INDEX] : null,
//             'NDLBIDID' => isset($NDLBIDID[$INDEX]) ? (string)$NDLBIDID[$INDEX] : null,
//             // $STANDARDNO = $xml->xpath('');
//             // $TOHANMARCNO = $xml->xpath('');
//             // $USMARCNO = $xml->xpath('');
//             // $NSMARCNO = $xml->xpath('');
//             // $UKMARCNO = $xml->xpath('');
//             // $RIS502 = $xml->xpath('');
//             // $OCLCNOS = $xml->xpath('');
//             // $RLINNO = $xml->xpath('');
//             // $KAKENHINO = $xml->xpath('');
//             // $TRNO = $xml->xpath('');
//             // $GPOBIBNO = $xml->xpath('');
//             // $NIIBIBNO = $xml->xpath('');
//             // $UNDS = $xml->xpath('');
//             // $UNSN = $xml->xpath('');
//             // $CHECKSUM = '';
//             'INSERTTIMES' => isset($INSERTTIMES[$INDEX]) ? (string)$INSERTTIMES[$INDEX] : null,
//             // $CODE = '';
//             // $CODE2 = '';
//             // $CODE3 = '';
//             // $CRTDATE = '';
//             // $UPDDATETIME ='';
//             'TITLETRANS' => isset($TITLETRANS[$INDEX]) ? (string)$TITLETRANS[$INDEX] : null,
            
//         ];

//         $results[] = $record;
//     }
// }
     
  
}
