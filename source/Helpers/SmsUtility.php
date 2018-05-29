<?php
/**
 * Created by PhpStorm.
 * User: rndwiga
 * Date: 3/27/18
 * Time: 3:29 PM
 */

namespace Tyondo\Sms\Helpers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class SmsUtility
{

    private static $logger;

    /***
     * @param $dataToLog - json data to log
     * @param $fileName - name of the file
     * @param string $folderName - Each script calling this function can store the info in one specific folder
     * @param string $method - This can be [debug,error ]
     * protected static $levels = [
                                        self::DEBUG     => 'DEBUG',
                                        self::INFO      => 'INFO',
                                        self::NOTICE    => 'NOTICE',
                                        self::WARNING   => 'WARNING',
                                        self::ERROR     => 'ERROR',
                                        self::CRITICAL  => 'CRITICAL',
                                        self::ALERT     => 'ALERT',
                                        self::EMERGENCY => 'EMERGENCY',
                                   ];
     * @param int $maxNumberOfLines - Maximum number of lines in one log file
     */
    public static function logInfo($dataToLog, $fileName, $folderName = 'appLog', $method = 'debug', $maxNumberOfLines = 10000){
        self::$logger = new Logger('mifos');
        // Trim log file to a max length
        $path = storage_path(self::createStorage($folderName).'/'.$fileName.'.log');
        //$path = Storage::putFile(self::createStorage($folderName).'/'.$fileName.'.log');
        if (! file_exists($path)) {
            fopen($path, "w");
        }
        $lines = file($path);
        if (count($lines) >= $maxNumberOfLines) {
            file_put_contents($path, implode('', array_slice($lines, -$maxNumberOfLines, $maxNumberOfLines)));
        }

        // Define custom Monolog handler
        try {
            $handler = new StreamHandler($path, Logger::DEBUG);
        } catch (\Exception $e) {
        } //This will have both DEBUG and ERROR messages
        $handler->setFormatter(new LineFormatter(null, null, true, true));

        // Set defined handler and log the message
        self::$logger->setHandlers([$handler]);
        // self::$logger->pushHandler($handler);
        self::$logger->$method(json_encode($dataToLog));
    }

    public static function createStorage($folderName, $useDate = false)
    {
        /*
        * This function is for creating folders organized by date for the storage of files
        call this function before any file created to set the dependencies
        --this function can be enhanced to look at the name for slashes so as to create subdirectories automatically
        */
        $today = null;
        $folder = "Mifos/".$folderName; // setting the folder name
        if ($useDate){
            $today = date('Y-m-d'); //setting the date
        }

        if (!is_dir(storage_path($folder)))
        {
            mkdir(storage_path($folder), 0777, true); //creating the folder docs if it does not already exist
        }
        if (!is_dir(storage_path($folder).'/'. $today))
        {
            //creating folder based on day if it does not exist. If it does, it is not created
            if (!mkdir(storage_path($folder).'/'. $today, 0777, true)) {
                die('Failed to create folders...'); // Die if the function mkdir cannot run
            }
            return $folder.'/'.$today;
        } elseif (is_dir(storage_path($folder).'/'. $today)){ //check if the folder is created and return it
            return $folder.'/'.$today;
        } else {
            return $folder.'/'.$today;				// Return the folder if its already created in the file system
        }
    }

    /**
     * GZIPs a file on disk (appending .gz to the name)
     *
     * From http://stackoverflow.com/questions/6073397/how-do-you-create-a-gz-file-using-php
     * Based on function by Kioob at:
     * http://www.php.net/manual/en/function.gzwrite.php#34955
     *
     * @param string $source Path to file that should be compressed
     * @param integer $level GZIP compression level (default: 9)
     * @param bool $fileFormat
     * @return string New filename (with .gz appended) if success, or false if operation fails
     * @internal param bool|string $format
     */
    public static function gzCompressFile($source, $level = 9, $fileFormat = false){
        if ($fileFormat){
            $destination = $source . '.gz';
        }else{
            $destination = $source;
        }
        $mode = 'wb' . $level;
        $error = false;
        if ($fp_out = gzopen($destination, $mode)) {
            if ($fp_in = fopen($source,'rb')) {
                while (!feof($fp_in))
                    gzwrite($fp_out, fread($fp_in, 1024 * 512));
                fclose($fp_in);
            } else {
                $error = true;
            }
            gzclose($fp_out);
        } else {
            $error = true;
        }
        if ($error)
            return false;
        else
            return $destination;
    }

    public static function generateRandomId(){
        $time = time();
        $currentTime = $time;
        $random1= rand(0,99999);
        $random2 = mt_rand();
        $random = $random1 * $random2;
        $a= ($currentTime + $random);
        $un=  uniqid();
        $conct = $a . $un  . md5($a);
        $cashflowRandomId = sha1($conct.$un);
        return $cashflowRandomId;
    }

    public static function sendAdminEmail($to = null,$subject,$content,$senderName){

        if (is_null($to)){
            $to = env('SMS_ADMIN_EMAIL') ? env('SMS_ADMIN_EMAIL') : null;
        }

        Mail::raw(json_encode($content), function ($message) use (&$to,&$subject,&$senderName){
            $message->to($to);
            $message->subject($subject);
            $message->from(env('MAIL_USERNAME'), $senderName);
        });
    }
}