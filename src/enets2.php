<?php

class Enets2
{
    protected $environment = "TEST"; // set to "LIVE" for live setup
    protected $transaction_date;
    protected $time_zone;
    protected $umid;
    protected $tid = "";
    protected $secret_key;
    protected $key_id;
    protected $currency = "SGD";
    protected $amount;
    protected $merchant_reference;
    protected $return_url;
    protected $return_url_param;
    protected $notify_url;
    protected $notify_url_param;
    protected $submission_mode = "B";
    protected $payment_type = "SALE";
    protected $payment_mode;
    protected $client_type = "W";
    protected $mid_indicator = "U";
    protected $mobile_os;
    protected $language = "en";
    protected $ip_address;
    protected $cardholder_name;
    protected $pan;
    protected $expiry_date;
    protected $cvv;
    // xid, cavv, eci, authentication_status are to be included in payload if own MPI is used
    protected $xid;
    protected $cavv;
    protected $eci;
    protected $authentication_status;
    // pareq, term_url, md, acs_url are to be provided if nets_status value is 5 in server to server scenario
    protected $pareq;
    protected $term_url;
    protected $md;
    protected $acs_url;
    protected $nets_reference;
    protected $nets_status;
    protected $nets_message;
    protected $authorization_date;
    protected $authorization_amount;
    protected $authorization_code;
    protected $stage_response_code;
    protected $transaction_random;
    protected $action_code;

    public function __construct()
    {
        // get current date and format it into yyyymmdd hh:mn:ss.ttt
        $timeStampData = microtime();
        list($msec, $sec) = explode(' ', $timeStampData);
        $msec = round($msec * 1000);
        $this->transaction_date = date("Ymd H:i:s").".".$msec;

        // get timezone
        $this->time_zone = date("P");

        // following the spec, need to remove leading zero from timezone
        // however it seems to be working even if leading zero is not removed
        //
        // if (substr($this->time_zone,1,1)=="0") {
        //     $this->time_zone = substr($this->time_zone,0,1).substr($this->time_zone,2);
        // }

        // set IP address
        $this->ip_address = $this->getClientIp();
    }

    private function checkLuhn($number)
    {
        $sum = 0;
        $numDigits = strlen($number)-1;
        $parity = $numDigits % 2;
        for ($i = $numDigits; $i >= 0; $i--) {
            $digit = substr($number, $i, 1);
            if (!$parity == ($i % 2)) {
                $digit <<= 1;
            }
            $digit = ($digit > 9) ? ($digit - 9) : $digit;
            $sum += $digit;
        }
        return (0 == ($sum % 10));
    }

    private function isExpired($number)
    {
        return (intval($number) < intval(date("y").date("m")));
    }

    private function getClientIp()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('HTTP_X_FORWARDED')) {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        } elseif(getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        } elseif(getenv('HTTP_FORWARDED')) {
            $ipaddress = getenv('HTTP_FORWARDED');
        } elseif(getenv('REMOTE_ADDR')) {
            $ipaddress = getenv('REMOTE_ADDR');
        } else {
            $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }

    private function getInitScript()
    {
        $script = "";
        if (strcasecmp($this->environment,"TEST") == 0) {
            $script = "
                <script src='https://uat2.enets.sg/GW2/js/jquery-3.1.1.min.js' type='text/javascript'>
                </script> 
                <script src='https://uat2.enets.sg/GW2/pluginpages/env.jsp'>
                </script> 
                <script type='text/javascript' src='https://uat2.enets.sg/GW2/js/apps.js'>
                </script>
                ";
        } else {
            if (strcasecmp($this->environment,"LIVE") == 0) {
                $script = "
                    <script src='https://www2.enets.sg/GW2/js/jquery-3.1.1.min.js' type='text/javascript'>
                    </script> 
                    <script src='https://www2.enets.sg/GW2/pluginpages/env.jsp'>
                    </script> 
                    <script type='text/javascript' src='https://www2.enets.sg/GW2/js/apps.js'>
                    </script>
                    ";
            }
        }
        return $script;
    }

    private function getQueryUrl()
    {
        $script = "";
        if (strcasecmp($this->environment,"TEST") == 0) {
            $url = "https://uat-api.nets.com.sg:9065/GW2/TxnQuery";
        } else {
            $url = "https://api.nets.com.sg/GW2/TxnQuery";
        }
        return $url;
    }

    public function setUmid($value)
    {
        if (isset($value)) {
            $this->umid = $value;
            return true;
        } else {
            throw new Exception("Invalid UMID");
        }
    }

    public function setTid($value)
    {
        if (isset($value)) {
            $this->tid = $value;
            return true;
        } else {
            throw new Exception("Invalid TID");
        }
    }

    public function setSecretKey($value)
    {
        if (isset($value)) {
            $this->secret_key = $value;
            return true;
        } else {
            throw new Exception("Invalid Secret Key");
        }
    }

    public function setKeyId($value)
    {
        if (isset($value)) {
            $this->key_id = $value;
            return true;
        } else {
            throw new Exception("Invalid Key ID");
        }
    }

    public function setCurrency($value)
    {
        if (isset($value)) {
            $this->currency = strtoupper($value);
            return true;
        } else {
            throw new Exception("Invalid Currency");
        }
    }

    // amount include decmal point
    public function setAmount($value)
    {
        if (isset($value)) {
            if (is_numeric($value)) {
                $this->amount = floor($value * 100);
                return true;
            } else {
                throw new Exception("Invalid Amount Format");
            }
        } else {
            throw new Exception("Invalid Amount Value");
        }
    }

    public function setMerchantReference($value)
    {
        if (isset($value)) {
            if (strlen($value) <= 20) {
                $this->merchant_reference = $value;
                return true;
            } else {
                throw new Exception("Merchant Reference length cannot exceed 20 characters");
            }
        } else {
            throw new Exception("Invalid Merchant Reference");
        }
    }

    public function setReturnUrl($value)
    {
        if (isset($value)) {
            if (filter_var($value, FILTER_VALIDATE_URL) === false) {
                throw new Exception("Invalid Return URL format");
            }
            if (strlen($value) <= 80) {
                $this->return_url = $value;
                return true;
            } else {
                throw new Exception("Return URL length cannot exceed 80 characters");
            }
        } else {
            throw new Exception("Invalid Return URL");
        }
    }

    public function setReturnUrlParam($value)
    {
        if (isset($value)) {
            if (strlen($value) <= 255) {
                $this->return_url_param = $value;
                return true;
            } else {
                throw new Exception("Return URL Parameter length cannot exceed 20 characters");
            }
        } else {
            throw new Exception("Invalid Return URL Parameter");
        }
    }

    public function setNotifyUrl($value)
    {
        if (isset($value)) {
            if (filter_var($value, FILTER_VALIDATE_URL) === false) {
                throw new Exception("Invalid Notify URL format");
            }
            if (strlen($value) <= 80) {
                $this->notify_url = $value;
                return true;
            } else {
                throw new Exception("Notify URL length cannot exceed 80 characters");
            }
        } else {
            throw new Exception("Invalid Notify URL");
        }
    }

    public function setNotifyUrlParam($value)
    {
        if (isset($value)) {
            if (strlen($value) <= 255) {
                $this->notify_url_param = $value;
                return true;
            } else {
                throw new Exception("Notify URL Parameter length cannot exceed 20 characters");
            }
        } else {
            throw new Exception("Invalid Notify URL Parameter");
        }
    }

    public function setSubmissionMode($value)
    {
        if (isset($value)) {
            if (strcasecmp($value,"B") == 0 || strcasecmp($value,"S") == 0) {
                $this->submission_mode = strtoupper($value);
                return true;
            } else {
                throw new Exception("Expected Value: 'B' or 'S'");
            }
        } else {
            throw new Exception("Invalid Submission Mode");
        }
    }

    public function setPaymentType($value)
    {
        if (isset($value)) {
            if (strcasecmp($value,"SALE") == 0 || strcasecmp($value,"AUTH") == 0 ||
                    strcasecmp($value,"CAPT") == 0 || strcasecmp($value,"CRED") == 0 ||
                    strcasecmp($value,"RSALE") == 0 || strcasecmp($value,"RAUTH") == 0 ||
                    strcasecmp($value,"RCRED") == 0) {
                $this->payment_type = strtoupper($value);
                return true;
            } else {
                throw new Exception("Expected Value: 'SALE', 'AUTH', 'CAPT', 'CRED', 'RSALE', 'RAUTH', or 'RCRED'");
            }
        } else {
            throw new Exception("Invalid Payment Type");
        }
    }

    public function setPaymentMode($value)
    {
        if (isset($value)) {
            if (strcasecmp($value,"CC") == 0 || strcasecmp($value,"DD") == 0 ||
                    strcasecmp($value,"QR") == 0 || strcasecmp($value,"") == 0) {
                $this->payment_mode = strtoupper($value);
                return true;
            } else {
                throw new Exception("Expected Value: 'CC', 'DD', 'QR', or ''");
            }
        } else {
            throw new Exception("Invalid Payment Mode");
        }
    }

    public function setClientType($value)
    {
        if (isset($value)) {
            if (strcasecmp($value,"W") == 0 || strcasecmp($value,"S") == 0 ||
                    strcasecmp($value,"M") == 0 || strcasecmp($value,"H") == 0) {
                $this->client_type = strtoupper($value);
                return true;
            } else {
                throw new Exception("Expected Value: 'W', 'S', or 'M'");
            }
        } else {
            throw new Exception("Invalid Client Type");
        }
    }

    public function setMobileOs($value)
    {
        if (isset($value)) {
            if (strcasecmp($value,"ANDROID") == 0 || strcasecmp($value,"IOS") == 0) {
                $this->mobile_os = strtoupper($value);
                return true;
            } else {
                throw new Exception("Expected Value: 'ANDROID', or 'IOS'");
            }
        } else {
            throw new Exception("Invalid Mobile OS");
        }
    }

    public function setLanguage($value)
    {
        if (isset($value)) {
            if (strcasecmp($value,"en") == 0 || strcasecmp($value,"zh_cn") == 0) {
                $this->language = strtolower($value);
                return true;
            } else {
                throw new Exception("Expected Value: 'en', or 'zh_cn'");
            }
        } else {
            throw new Exception("Invalid Language");
        }
    }

    public function setIpAddress($value)
    {
        if (isset($value)) {
            if (filter_var($value, FILTER_VALIDATE_IP) === false) {
                throw new Exception("Invalid IP Address format");
            }
            $this->ip_address = $value;
            return true;
        } else {
            throw new Exception("Invalid IP Address");
        }
    }

    public function setCardholderName($value)
    {
        if (isset($value)) {
            if (strlen($value) <= 255) {
                $this->cardholder_name = $value;
                return true;
            } else {
                throw new Exception("Cardholder Name length cannot exceed 255 characters");
            }
        } else {
            throw new Exception("Invalid Cardholder Name");
        }
    }

    public function setPan($value, $checkluhn = true)
    {
        if (isset($value)) {
            if ($checkluhn) {
                if (!$this->checkLuhn($value)) {
                    throw new Exception("PAN failed Luhn check test");
                }
            }
            if (strlen($value) <= 19) {
                if (!is_numeric($value)) {
                    throw new Exception("Invalid PAN format");
                }
                $this->pan = $value;
                return true;
            } else {
                throw new Exception("PAN length cannot exceed 19 characters");
            }
        } else {
            throw new Exception("Invalid PAN");
        }
    }

    public function setExpiryDate($value, $checkexpiry = true)
    {
        if (isset($value)) {
            if (strlen($value) == 4) {
                if (!is_numeric($value)) {
                    throw new Exception("Invalid Expiry Date format");
                }
                if ($checkexpiry && $this->isExpired($value)) {
                    throw new Exception("Expiry Date must be greater or equal then current date");
                }
                $this->expiry_date = $value;
                return true;
            } else {
                throw new Exception("Expiry Date length must be 4 characters");
            }
        } else {
            throw new Exception("Invalid Expiry Date");
        }
    }

    public function setCvv($value)
    {
        if (isset($value)) {
            if (strlen($value) <= 4) {
                $this->cvv = $value;
                return true;
            } else {
                throw new Exception("CVV length cannot exceed 4 characters");
            }
        } else {
            throw new Exception("Invalid CVV");
        }
    }

    public function setXid($value)
    {
        if (isset($value)) {
            if (strlen($value) <= 255) {
                $this->xid = $value;
                return true;
            } else {
                throw new Exception("XID length cannot exceed 4 characters");
            }
        } else {
            throw new Exception("Invalid XID");
        }
    }

    public function setCavv($value)
    {
        if (isset($value)) {
            if (strlen($value) <= 255) {
                $this->cavv = $value;
                return true;
            } else {
                throw new Exception("CAVV length cannot exceed 4 characters");
            }
        } else {
            throw new Exception("Invalid CAVV");
        }
    }

    public function setEci($value)
    {
        if (isset($value)) {
            if (strcasecmp($value,"05") == 0 || strcasecmp($value,"06") == 0 ||
                    strcasecmp($value,"07") == 0 || strcasecmp($value,"00") == 0 ||
                    strcasecmp($value,"01") == 0 || strcasecmp($value,"02") == 0) {
                $this->eci = $value;
                return true;
            } else {
                throw new Exception("Expected Value: '00', '01', '02' for Mastercard, 
                    '05', '06', '07' for Visa/Amex/JCB");
            }
        } else {
            throw new Exception("Invalid ECI");
        }
    }

    public function setAuthenticationStatus($value)
    {
        if (isset($value)) {
            if (strcasecmp($value,"Y") == 0 || strcasecmp($value,"N") == 0 ||
                    strcasecmp($value,"U") == 0 || strcasecmp($value,"A") == 0) {
                $this->authentication_status = strtoupper($value);
                return true;
            } else {
                throw new Exception("Expected Value: 'Y', 'N', 'U', or 'A'");
            }
        } else {
            throw new Exception("Invalid Authentication Status");
        }
    }

    public function setEnvironment($value)
    {
        if (isset($value)) {
            if (strcasecmp($value,"TEST") == 0 || strcasecmp($value,"LIVE") == 0) {
                $this->environment = strtoupper($value);
                return true;
            } else {
                throw new Exception("Expected Value: 'TEST', or 'LIVE'");
            }
        } else {
            throw new Exception("Invalid Environment");
        }
    }

    public function getNetsReference()
    {
        return $this->nets_reference;
    }

    public function getNetsStatus()
    {
        return $this->nets_status;
    }

    public function getNetsMessage()
    {
        return $this->nets_message;
    }

    public function getAuthorizationDate()
    {
        return $this->authorization_date;
    }

    public function getAuthorizationAmount()
    {
        return $this->authorization_amount;
    }

    public function getAuthorizationCode()
    {
        return $this->authorization_code;
    }

    public function getStageResponseCode()
    {
        return $this->stage_response_code;
    }

    public function getTransactionRandom()
    {
        return $this->transaction_random;
    }

    public function getActionCode()
    {
        return $this->action_code;
    }

    public function getTransactionRequest()
    {
        $request = array();
        if (isset($this->umid)) {
            $request["netsMid"] = $this->umid;
        } else {
            throw new Exception("umid is mandatory in the payload");
        }
        if (isset($this->tid)) {
            $request["tid"] = $this->tid;
        }
        if (isset($this->submission_mode)) {
            $request["submissionMode"] = $this->submission_mode;
        } else {
            throw new Exception("submission_mode is mandatory in the payload");
        }
        if (isset($this->currency)) {
            $request["currencyCode"] = $this->currency;
        } else {
            throw new Exception("currency is mandatory in the payload");
        }
        if (isset($this->amount)) {
            $request["txnAmount"] = $this->amount;
        } else {
            throw new Exception("amount is mandatory in the payload");
        }
        if (isset($this->merchant_reference)) {
            $request["merchantTxnRef"] = $this->merchant_reference;
        } else {
            throw new Exception("merchant_reference is mandatory in the payload");
        }
        if (isset($this->transaction_date)) {
            $request["merchantTxnDtm"] = $this->transaction_date;
        } else {
            throw new Exception("transaction_date is mandatory in the payload");
        }
        if (isset($this->time_zone)) {
            $request["merchantTimeZone"] = $this->time_zone;
        } else {
            throw new Exception("time_zone is mandatory in the payload");
        }
        if (isset($this->payment_type)) {
            if (isset($this->payment_mode) &&
                    (strcasecmp($this->payment_mode,"DD")==0 || strcasecmp($this->payment_mode,"QR")==0) &&
                    strcasecmp($this->payment_type,"SALE")<>0) {
                throw new Exception("payment_type value must be set to 'SALE' 
                    for direct debit or qr code payment mode");
            }
            $request["paymentType"] = $this->payment_type;
        } else {
            throw new Exception("payment_type is mandatory in the payload");
        }
        if (isset($this->payment_mode)) {
            $request["paymentMode"] = $this->payment_mode;
        }
        if (isset($this->return_url)) {
            $request["b2sTxnEndURL"] = $this->return_url;
        } else {
            if (isset($this->submission_mode) && strcasecmp($this->submission_mode,"B")==0) {
                throw new Exception("return_url is mandatory in the payload for non server to server submission type");
            }
        }
        if (isset($this->return_url_param)) {
            $request["b2sTxnEndURLParam"] = $this->return_url_param;
        }
        if (isset($this->notify_url)) {
            $request["s2sTxnEndURL"] = $this->notify_url;
        } else {
            if (isset($this->submission_mode) && strcasecmp($this->submission_mode,"B")==0) {
                throw new Exception("notify_url is mandatory in the payload for non server to server submission type");
            }
        }
        if (isset($this->notify_url_param)) {
            $request["s2sTxnEndURLParam"] = $this->notify_url_param;
        }
        if (isset($this->client_type)) {
            $request["clientType"] = $this->client_type;
        } else {
            if (isset($this->submission_mode) && strcasecmp($this->submission_mode,"B")==0) {
                throw new Exception("client_type is mandatory in the payload for non server to server submission type");
            }
        }
        if (isset($this->mid_indicator)) {
            $request["netsMidIndicator"] = $this->mid_indicator;
        }
        if (isset($this->mobile_os)) {
            $request["mobileOs"] = $this->mobile_os;
        } else {
            if (isset($this->client_type) && strcasecmp($this->client_type,"S")==0) {
                throw new Exception("mobile_os is mandatory in the payload for SDK client type");
            }
        }
        if (isset($this->ip_address)) {
            $request["ipAddress"] = $this->ip_address;
        }
        if (isset($this->language)) {
            $request["language"] = $this->language;
        }
        if (isset($this->cardholder_name)) {
            $request["cardholderName"] = $this->cardholder_name;
        } else {
            if (isset($this->submission_mode) && strcasecmp($this->submission_mode,"S")==0 &&
                    (strcasecmp($this->payment_type,"SALE")==0)) {
                throw new Exception("cardholder_name is mandatory in the payload 
                    for SALE or AUTH payment type on non server to server submission type");
            }
        }
        if (isset($this->pan)) {
            $request["pan"] = $this->pan;
        } else {
            if (isset($this->submission_mode) && strcasecmp($this->submission_mode,"S")==0 &&
                    (strcasecmp($this->payment_type,"SALE")==0)) {
                throw new Exception("pan is mandatory in the payload 
                    for SALE or AUTH payment type on non server to server submission type");
            }
        }
        if (isset($this->expiry_date)) {
            $request["expiryDate"] = $this->expiry_date;
        } else {
            if (isset($this->submission_mode) && strcasecmp($this->submission_mode,"S")==0 &&
                    (strcasecmp($this->payment_type,"SALE")==0)) {
                throw new Exception("expiry_date is mandatory in the payload 
                    for SALE or AUTH payment type on non server to server submission type");
            }
        }
        if (isset($this->cvv)) {
            $request["cvv"] = $this->cvv;
        }
        if (isset($this->xid)) {
            $request["purchaseXid"] = $this->xid;
        }
        if (isset($this->cavv)) {
            $request["cavv"] = $this->cavv;
        } else {
            if (isset($this->authentication_status) &&
                    (strcasecmp($this->authentication_status,"Y")==0 ||
                     strcasecmp($this->authentication_status,"A")==0)) {
                throw new Exception("cavv is mandatory in the payload 
                    for successful or attempted 3ds authentication_status");
            }
        }
        if (isset($this->eci)) {
            $request["eci"] = $this->eci;
        }
        if (isset($this->authentication_status)) {
            $request["status"] = $this->authentication_status;
        }
        return $request;
    }

    public function getQueryRequest()
    {
        $request = array();
        if (isset($this->umid)) {
            $request["netsMid"] = $this->umid;
        } else {
            throw new Exception("umid is mandatory in the payload");
        }
        if (isset($this->merchant_reference)) {
            $request["merchantTxnRef"] = $this->merchant_reference;
        } else {
            throw new Exception("merchant_reference is mandatory in the payload");
        }
        if (isset($this->mid_indicator)) {
            $request["netsMidIndicator"] = $this->mid_indicator;
        }
        return $request;
    }

    public function getPayload($request)
    {
        $payload = array();
        $payload["ss"] = "1";
        $payload["msg"] = $request;
        $json_payload = json_encode($payload, JSON_UNESCAPED_SLASHES);
        return $json_payload;
    }

    public function getHmac($request_payload)
    {
        $secret_key = $this->secret_key;
        $payload = $request_payload;
        $hashstring = hash("sha256", $payload.$secret_key);
        $base64string = base64_encode(hex2bin($hashstring));
        return $base64string;
    }

    public function query()
    {
        $data_string = $this->getPayload($this->getQueryRequest());
        $hmac = $this->getHmac($data_string);
        $ch = curl_init($this->getQueryUrl());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSLVERSION,0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string),
            'keyId: ' . $this->key_id,
            'hmac: ' . $hmac)
        );
        $payload = curl_exec($ch);
        //var_dump(curl_error($ch));
        //var_dump(curl_getinfo($ch));
        $payload_array = json_decode($payload,true);
        if (is_array($payload_array["msg"])) {
            $response = $payload_array["msg"];
            if (isset($response["netsTxnRef"])) {
                $this->nets_reference = $response["netsTxnRef"];
            }
            if (isset($response["netsTxnStatus"])) {
                $this->nets_status = $response["netsTxnStatus"];
            }
            if (isset($response["netsTxnMsg"])) {
                $this->nets_message = $response["netsTxnMsg"];
            }
            if (isset($response["netsTxnDtm"])) {
                $this->authorization_date = $response["netsTxnDtm"];
            }
            if (isset($response["netsAmountDeducted"])) {
                $this->authorization_amount = $response["netsAmountDeducted"] / 100;
            }
            if (isset($response["bankAuthId"])) {
                $this->authorization_code = $response["bankAuthId"];
            }
            if (isset($response["stageRespCode"])) {
                $this->stage_response_code = $response["stageRespCode"];
            }
            if (isset($response["txnRand"])) {
                $this->transaction_random = $response["txnRand"];
            }
            if (isset($response["actionCode"])) {
                $this->action_code = $response["actionCode"];
            }
            return $response;
        } else {
            return false;
        }
    }

    public function run()
    {
        if (strcasecmp($this->submission_mode,"B") == 0) {
            // if submission is from browser, generate and submit the HTML form
            $payload = $this->getPayload($this->getTransactionRequest());
            $hmac = $this->getHmac($payload);
            $htmlform = "
                <form>
                <input type='hidden' id='txnReq' name='txnReq' 
                    value='".$payload."'>
                <input type='hidden' id='keyId' name='keyId' value='".$this->key_id."'>
                <input type='hidden' id='hmac' name='hmac' 
                    value='".$hmac."'>
                </form>
                <div id='anotherSection'>
                <fieldset> 
                    <div id='ajaxResponse'></div>
                </fieldset>
                </div> 

                <script> 
                    window.onload = function() 
                    { 
                        var txnReq = document.forms[0].txnReq.value; 
                        var keyId = document.forms[0].keyId.value; 
                        var hmac = document.forms[0].hmac.value; 
                        sendPayLoad(txnReq, hmac, keyId); 
                    }; 
                </script>        
            ";
            $result["type"] = "HTML";
            $result["response"] = $this->getInitScript().$htmlform;
            echo $result["response"];
        } else {
            // curl to server to authorize
            // this is for server to server handling
            // (pending development)
            //
            if ($this->nets_status == "5") {
                // if status = 5, generate and submit HTML form to ACS URL for 3DS Authentication
                $result["type"] = "HTML";
                $result["response"] = "";
            } else {
                // return the authorization result as JSON
                $result["type"] = "JSON";
                $result["response"] = "";
            }
        }
        return $result;
    }

    public function getFrontendResponse()
    {
        $payload = "";
        if (isset($_POST["message"])) {
            $message = urldecode($_POST["message"]);
            if (isset($this->secret_key)) {
                $calculatedHmac = $this->getHmac($message);
            } else {
                throw new Exception("secret_key is required to validate the response");
            }
            if (isset($_POST["hmac"])) {
                $hmac = $_POST["hmac"];
            } else {
                throw new Exception("Missing HMAC in response");
            }
            if (strcasecmp($calculatedHmac,$hmac)<>0) {
                throw new Exception("HMAC mismatched");
            }
            $payload = json_decode($message, true);
        } else {
            throw new Exception("Missing Message in response");
        }
        if (is_array($payload["msg"])) {
            $response = $payload["msg"];
            if (isset($response["netsTxnRef"])) {
                $this->nets_reference = $response["netsTxnRef"];
            }
            if (isset($response["netsTxnStatus"])) {
                $this->nets_status = $response["netsTxnStatus"];
            }
            if (isset($response["netsTxnMsg"])) {
                $this->nets_message = $response["netsTxnMsg"];
            }
            if (isset($response["netsTxnDtm"])) {
                $this->authorization_date = $response["netsTxnDtm"];
            }
            if (isset($response["netsAmountDeducted"])) {
                $this->authorization_amount = $response["netsAmountDeducted"] / 100;
            }
            if (isset($response["bankAuthId"])) {
                $this->authorization_code = $response["bankAuthId"];
            }
            if (isset($response["stageRespCode"])) {
                $this->stage_response_code = $response["stageRespCode"];
            }
            if (isset($response["txnRand"])) {
                $this->transaction_random = $response["txnRand"];
            }
            if (isset($response["actionCode"])) {
                $this->action_code = $response["actionCode"];
            }
            return $response;
        } else {
            return false;
        }
    }

    public function getBackendResponse()
    {
        $message = file_get_contents("php://input");
        $payload = "";
        if (!empty($message)) {
            if (isset($this->secret_key)) {
                $calculatedHmac = $this->getHmac($message);
            } else {
                throw new Exception("secret_key is required to validate the response");
            }
            foreach ($_SERVER as $name => $value) {
                if (strtoupper(substr($name, 0, 9)) == 'HTTP_HMAC') {
                    $hmac = $value;
                }
            }
            if (!isset($hmac)) {
                throw new Exception("Missing HMAC in response");
            }
            if (strcasecmp($calculatedHmac,$hmac)<>0) {
                throw new Exception("HMAC mismatched");
            }
            $payload = json_decode($message, true);
        } else {
            throw new Exception("Missing Message in response");
        }
        if (is_array($payload["msg"])) {
            $response = $payload["msg"];
            if (isset($response["netsTxnRef"])) {
                $this->nets_reference = $response["netsTxnRef"];
            }
            if (isset($response["netsTxnStatus"])) {
                $this->nets_status = $response["netsTxnStatus"];
            }
            if (isset($response["netsTxnMsg"])) {
                $this->nets_message = $response["netsTxnMsg"];
            }
            if (isset($response["netsTxnDtm"])) {
                $this->authorization_date = $response["netsTxnDtm"];
            }
            if (isset($response["netsAmountDeducted"])) {
                $this->authorization_amount = $response["netsAmountDeducted"] / 100;
            }
            if (isset($response["bankAuthId"])) {
                $this->authorization_code = $response["bankAuthId"];
            }
            if (isset($response["stageRespCode"])) {
                $this->stage_response_code = $response["stageRespCode"];
            }
            if (isset($response["txnRand"])) {
                $this->transaction_random = $response["txnRand"];
            }
            if (isset($response["actionCode"])) {
                $this->action_code = $response["actionCode"];
            }
            return $response;
        } else {
            return false;
        }
    }
}

?>
