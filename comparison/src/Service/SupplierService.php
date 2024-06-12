<?php

namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;

class SupplierService
{
    private array $midoceanGlobalConfig;

    public function __construct(
        array                            $midoceanConfig,
        private readonly KernelInterface $kernel
    )
    {
        $this->midoceanGlobalConfig = $midoceanConfig;
    }

    public function connectToMidoceanForFiles(array $filesToDownload): void
    {
        $ftp = ftp_connect($this->midoceanGlobalConfig["url"]);
        $login_result = ftp_login($ftp, $this->midoceanGlobalConfig["login_reflect"],
            $this->midoceanGlobalConfig["password_reflect"]);

        if ($login_result) {
            ftp_pasv($ftp, true);
            foreach ($filesToDownload as $fileToDownload) {
                $path = $this->kernel->getProjectDir() . "/public/import/mob/" . $fileToDownload;
                if (!file_exists($path)) {
                    touch($path);
                }
                try {
                    ftp_get($ftp, $path, $fileToDownload, FTP_BINARY);
                } catch (Exception $e) {
                    die($e);
                }
            }
        } else {
            die('Failed to login to ftp!');
        }
        ftp_close($ftp);
    }

    public function connectToMidoceanCurl(string $url)
    {
        if ($url == 'urlPrintPricelist') {
            $input_xml = '<PRINT_PRICELIST_REQUEST>
                        <CUSTOMER_NUMBER>' . $this->midoceanGlobalConfig['customerNumber'] . '</CUSTOMER_NUMBER>
                        <LOGIN>' . $this->midoceanGlobalConfig['login'] . '</LOGIN> 
                        <PASSWORD>' . $this->midoceanGlobalConfig['password'] . '</PASSWORD>
                        </PRINT_PRICELIST_REQUEST>';
        } else {
            $input_xml = '<?xml version="1.0" encoding="utf-8"?>
                        <PRICELIST_REQUEST>
                        <CUSTOMER_NUMBER>' . $this->midoceanGlobalConfig['customerNumber'] . '</CUSTOMER_NUMBER>
                        <LOGIN>' . $this->midoceanGlobalConfig['login'] . '</LOGIN> 
                        <PASSWORD>' . $this->midoceanGlobalConfig['password'] . '</PASSWORD>
                        <TIMESTAMP>20110210103215</TIMESTAMP>
                        </PRICELIST_REQUEST>';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->midoceanGlobalConfig[$url]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: text/xml',
        ));

        curl_setopt($ch, CURLOPT_POSTFIELDS, "xmlReqeust=" . $input_xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        $data = curl_exec($ch);

        curl_close($ch);

        if ($url == 'urlPrintPricelist') {
            return simplexml_load_string($data);
        } else {
            return json_decode(json_encode(simplexml_load_string($data)), true);
        }
    }
}
