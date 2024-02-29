<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use League\Csv\Writer;
use League\Csv\CharsetConverter;
use Carbon\Carbon;

class ExportAgent extends Command
{
    protected $TYPE;
    protected $ID;
    protected $QUERY;
    protected $STATUS;
    protected $EXCPOS;
    protected $NUMRECORD;
    protected $FIELDS;
    protected $emailTo;
    protected $usernameSendTo;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ExportAgent:cron';

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

//        $email = 'luongthienducst15@gmail.com';
//        $subjectEmail = 'test send mail';
//        try{
//            $a = Mail::raw('abc', function ($message) use ($email,$subjectEmail) {
//                $message->from('ltducst1997@gmail.com','test');
//                $message->to($email, 'xxxxxxxx')->subject($subjectEmail);
//            });
//            echo $a;
//        } catch (\Exception $e) {
//            var_dump($e->getMessage());
//        }
//
//        die();


        $command = new ExportAgent();
        $setting = DB::select(DB::raw(Constants::sqlQuery['getMaxRows']));

        if ($this->canRunAgent($command)){
            $maxRows = (int)reset($setting)->MAXROWS;
            $timesRun = (int) ($command->NUMRECORD / $maxRows);
            if($command->NUMRECORD % $maxRows > 0){
                $timesRun +=1;
            }

            echo "Number record in DB: " . $command->NUMRECORD;
            echo "Max row in DB: " . $maxRows;
            echo "Number CSV to export: " . $timesRun;
            // Create folder store csv file.
            $currentDirectory = storage_path('app/public/');
            // Get header CSV.

            $header = $this->getHeaderCsv($command);
            // Export CSV
            $filesToAdd = array();
            echo "Start get data then export CSV.";
            for($i = 0; $i < $timesRun; $i++) {
                $fileName = $this->exportDataToCsv($command, $currentDirectory, $maxRows, $timesRun, $i, $header);
							$filesToAdd[]= $fileName;
            }

            // Zip all CSV to ZIP file.
            $now = Carbon::now();
            $key = "NDL data_" . $now->format('Y_m_d_H_i_s') . ".zip";
            echo "Zip's file name: " . $key;
            $zipFile = $this->createZipFile($filesToAdd,$key);
            echo "Zip's path: " .$zipFile;

//            $data = DB::select(DB::raw($command->QUERY));
            echo "===.\n";
            var_dump($filesToAdd);
            echo "===.\n";
//            echo "===.\n";
//            echo $command->ID;
//            echo $command->TYPE;
//            echo $command->QUERY;
//            echo "===.\n";
//
        }

        echo '=================================';
    }

    public static function canRunAgent($command) {
        try {
            echo "Checking have agent running.\n";

            // Check if no request export is running.
            $rs = DB::select(DB::raw(Constants::sqlQuery['getRequestExportRunning']));
            foreach ($rs as $row) {
                echo "   Agent running: " . $row->ID . "\n";
                return false;
            }

            echo "   No agent running.\n";

            echo "Get agent to run.\n";
            // Get list of requests not run.
            $rs = DB::select(DB::raw(Constants::sqlQuery['getRequestExportNotRun']));

            foreach ($rs as $row) {

                $command->ID = $row->ID;
                echo "   ID of agent need export: " . $command->ID . "\n";
                $command->TYPE = $row->TYPE;
                $command->QUERY = $row->QUERY;
                $command->STATUS = $row->STATUS;
                $command->EXCPOS = $row->EXCPOS;
                $command->NUMRECORD = $row->NUMRECORD;
                if ($command->NUMRECORD == 0) {
                    $command->NUMRECORD = 1;
                }
                $command->FIELDS = $row->FIELDS;
                $command->emailTo = $row->EMAIL;
                $command->usernameSendTo = $row->NAME;

                //Set status running for item.
//                static::updateStatus(Constants::EXPPROGRESS_STATUS['Running'],$command->ID);

                return true;
            }
        } catch (\Exception $ex) {
            echo $ex->getMessage() . "\n";
        }

        echo "     No agent to run. Current connect string: \n";
        return false;
    }

    static function updateStatus($status, $id)
    {
        try {
            echo "     Update status of agent: " . $id . " to " . $status;
            $count = DB::table('EXPPROGRESS')
                ->where('id', $id)
                ->update(['status' => $status]);

            if ($count > 0) {
                echo "Update status success.\n";
            } else {
                echo "Update status fail.\n";
            }
        } catch (Exception $ex) {
            echo $ex->getMessage() . "\n";
        }
    }

    public static function getHeaderCsv($command) {
        $ar = [];

        echo "Start get header from DB.\n";
        echo "     Field in template: " . $command->FIELDS . "\n";
        try {
            $lstField = "";
            $parts = explode(Constants::charSplitFields, $command->FIELDS);
            foreach ($parts as $partStr) {
                if ($partStr != "") {
                    if ($lstField != "") {
                        $lstField .= ",";
                    }
                    $lstField .= "'" . $partStr . "'";
                }
            }

            // Thực hiện truy vấn
            $query = sprintf(Constants::sqlQuery['getListFields'], $lstField);
            $rs = DB::select(DB::raw($query));
            echo "     Query list header success.\n";

            $lstColumn = [];
            foreach ($rs as $row) {
                $lstColumn[] = $row->DBNAME . "_" . $row->JPNAME;
            }

            foreach ($parts as $partStr) {
                if ($partStr != "") {
                    $joinString = $partStr . "_";
                    $result = collect($lstColumn)->first(function ($item) use ($joinString) {
                        return Str::startsWith($item, $joinString);
                    });
                    if (!empty($result)) {
                        $result = str_replace($joinString, "", $result);
                        $ar[] = $result;
//                        echo "     Name of header: " . $result . "\n";
                    }
                }
            }
        } catch (\Exception $ex) {
            echo $ex->getMessage() . "\n";
        }

        return $ar;
    }

    private static function exportDataToCsv($command, $currentDirectory, $maxRows, $timesRun, $indexRun, $headerCsv)
    {
        echo "     Start export CSV " . ($indexRun + 1) . "\n";

        // Check split file name
        $splitFile = ($timesRun > 1) ? "_" . ($indexRun + 1) : "";
        $fileNameCsvToSave = $currentDirectory . "NDL data_" . date("Y_m_d") . $splitFile . ".csv";
        echo "          Path:" . $fileNameCsvToSave . "\n";
        $limitQuery = "";
        if ($indexRun == 0) {
            if ($timesRun > 1) {
                $limitQuery = " limit " . $maxRows;
            }
        } else {
            $limitQuery = " limit " . $indexRun * $maxRows . "," . $maxRows;
        }
        // Query data to export
        $query = $command->QUERY . $limitQuery;
        var_dump($query);
        die();
        $rs = DB::select(DB::raw($query));
        echo "          Query data success\n";

        var_dump($rs);
        die();
        $csv = Writer::createFromPath($fileNameCsvToSave, 'w+');
        $csv->setOutputBOM(Writer::BOM_UTF8); // Thêm BOM để đảm bảo mã UTF-8
        CharsetConverter::addTo($csv, 'UTF-8', 'SJIS-win');
        $csv->insertOne($headerCsv);
        // Print data
        foreach ($rs as $row) {
            $csv->insertOne((array) $row);
        }
        echo "          Export data success\n";
        return $fileNameCsvToSave;
    }
    private function createZipFile(array $csvFiles,$zipFilename): string
    {
        $zipPath = storage_path('app/public/' . $zipFilename);
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
            // Add each CSV file to the ZIP archive
            foreach ($csvFiles as $csvPath) {
                $csvFilename = basename($csvPath);
                $zip->addFile($csvPath, $csvFilename);
            }
            $zip->close();
        } else {
            $this->error('Failed to create ZIP file!');
        }
        return $zipFilename;
    }
}





