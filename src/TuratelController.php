<?php

namespace Frkcn\Turatel;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

/**
 * Class: TuratelController
 * @package Frkcn\Turatel
 * @author Faruk Can <frkcn@bil.omu.edu.tr>
 */
class TuratelController extends Controller
{

    private $messageBody;
    private $numbers;

    /**
     * @return mixed
     */
    public function getMessageBody()
    {
        return $this->messageBody;
    }

    /**
     * @param $messageBody
     * @return $this
     */
    public function setMessageBody($messageBody)
    {
        $this->messageBody = $messageBody;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumbers()
    {
        return $this->numbers;
    }

    /**
     * @param $numbers
     * @return $this
     */
    public function setNumbers($numbers)
    {
        $numbersArray = explode(',', $numbers);
        $numbersText = "";
        foreach ($numbersArray as $na ) {
            $numbersText.=",".$this->formatNumber($na, "international");
        }
        $this->numbers = ltrim($numbersText, ',');
        return $this;
    }

    /**
     * @param $data
     * @param $xml_data
     */
    public function array_to_xml( $data, &$xml_data ) {
        foreach( $data as $key => $value ) {
            if( is_numeric($key) ){
                $key = 'item'.$key; //dealing with <0/>..<n/> issues
            }
            if( is_array($value) ) {
                $subnode = $xml_data->addChild($key);
                array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }

    /**
     * @param $messageArray
     * @return mixed
     */
    public function createXml($messageArray) {
        $xml_data = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><MainmsgBody></MainmsgBody>');
        $this->array_to_xml($messageArray,$xml_data);
        $result = $xml_data->asXML();

        return $result;
    }

    /**
     * @param $xml
     * @return bool
     */
    public function isXMLValid($xml) {
        libxml_use_internal_errors( true );
        $doc = new \DOMDocument('1.0', 'utf-8');
        $doc->loadXML( $xml );
        $errors = libxml_get_errors();
        return empty( $errors );
    }

    /**
     * @return mixed
     */
    public function sendSms() {
        $messageArray = [
            "Command" => 0,
            "PlatformID" => 1,
            "ChannelCode" => config('turatel.channelCode'),
            "UserName" => config('turatel.username'),
            "PassWord" => config('turatel.password'),
            "Mesgbody" => $this->getMessageBody(),
            "Numbers" => $this->getNumbers(),
            "Type" => 1,
            "Originator" => config('turatel.originator')
        ];

        $messageXml = $this->createXml($messageArray);

        $xmlValid = $this->isXMLValid($messageXml);

        if($xmlValid) {
            $response = $this->sendCurl($messageXml);
            return $this->checkResult($response);
        } else {
            return $xmlValid;
        }
    }

    /**
     * @param $messageXml
     * @return mixed
     */
    public function sendCurl($messageXml) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config('turatel.apiUrl'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: text/xml"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $messageXml);
        $responseCode = curl_exec($ch);

        return $responseCode;
    }

    /**
     * @param $numbers
     * @param $message
     */
    public function send($numbers, $message) {
        $this->setMessageBody($message)
            ->setNumbers($numbers);

        echo $this->sendSms();
    }

    /**
     * @param $input
     * @param string $format
     * @return bool|string
     */
    public function formatNumber($input,$format="basic") {
        $input = preg_replace("/[^0-9+]/", "", $input);
        if($format=="basic"||$format=="simple"||$format=="7") {
            return substr($input,-7,7);
        } else if($format=="operator" || $format=="10") {
            if(strlen($input)>=10) {
                return substr($input,-10,10);
            } else {
                throw new ExceptionLogger("Cannot generate operator phone number with this number provided: ".$input);
                return false;
            }
        } else if($format=="international") {
            if(strlen($input)==10) {
                return "90".$input;
            } else if(strpos($input,"+")!==false) {
                return str_replace("+","",$input);
            } else {
                return $input;
            }
        } else if($format=="+") {
            if(strlen($input)==4) {
                return $input;
            } else if(strlen($input)==10) {
                return "+90".$input;
            } else if(strpos($input,"+")!==false) {
                return $input;
            } else {
                return "+".$input;
            }
        } else if($format=="14") {
            if(strlen($input)==4) {
                return $input;
            } else if(strlen($input)==10) {
                return "+90".$input;
            } else {
                return str_pad($input,"14","0",STR_PAD_LEFT);
            }
        } else {
            throw new ExceptionLogger("Unknown phone number format request: ".$format);
            return false;
        }
    }

    /**
     * @param $response
     */
    public function checkResult($response) {
        switch ($response) {
            case "00":
                echo "Sistem Hatası";
                break;
            case "01":
                echo "Kullanıcı Adı ve/veya Şifre Hatalı";
                break;
            case "02":
                echo "Kredisi yeterli değil";
                break;
            case "03":
                echo "Geçersiz içerik";
                break;
            case "04":
                echo "Bilinmeyen SMS tipi";
                break;
            case "05":
                echo "Hatalı gönderen ismi";
                break;
            case "06":
                echo "Mesaj metni ya da Alıcı bilgisi girilmemiş.";
                break;
            case "07":
                echo "Genel Hata";
                break;
            case "20":
                echo "Tanımsız Hata (XML formatını kontrol ediniz veya TURATEL’den destek alınız)";
                break;
            case "21":
                echo 'Hatalı XML Formatı (\n - carriage return – newline vb içeriyor olabilir)';
                break;
            case "22":
                echo "Kullanıcı Aktif Değil";
                break;
            case "23":
                echo "Kullanıcı Zaman Aşımında";
                break;
            default:
                echo $response;
                break;
        }
    }
}
