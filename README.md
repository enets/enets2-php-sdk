# enets2-php-sdk
This SDK for PHP is intended to assist you in your PHP project to integrate with ENETS 2.0 API. You still have the option to integrate directly without using this SDK.

## DISCLAIMER

The content of this API Library may only be used in connection with the services of NETS's eNETS product offering. Unless required by applicable law or agreed to in writing, the library is offered and/or distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. NETS does not warrant that the library or any content will be available uninterrupted or error free, that defects will be corrected, or that the library or its supporting systems are free of viruses or bugs.

## METHODS

**The folowing methods are available to set the variable that are needed to submit a transaction request to ENETS.**

*setUmid($value)*
Use this method to set UMID acording to the UMID associated to your merchant account. Please contact NETS for details if you do not have one.

*setTid($value)*
Use this method to set TID according to the TID associated to your merchant account. It is optional and unlikely you will need to use it in ENETS context.

*setSecretKey($value)*
Use this method to set Secret Key to be used in calculating HMAC value to sign your transation request, as well as to validate the response from NETS.

*setKeyId($value)*
Use this method to set Key ID, which is required when you submit your transaction request.

*setCurrency($value)*
Use this method to set currency according to ISO 4217 Alphabetic Code format. Defaulted to SGD

*setAmount($value)*
Use this method to set the amount you would like to charge. The float value is expected (eg. 15.80).

*setMerchantReference($value)*
Use this method to set the reference value that can be associated back to your own transaction record. Must be a unique value for each transaction, and used as reference value for transaction query purpose.

*setReturnUrl($value)*
Use this method to set return URL, which is the URL where the customer will be redirected to, upon completion of a transaction, regardless whether the transaction is successful or fail.

*setReturnUrlParam($value)*
Use this method to pass parameter to your return URL. The value will be echo back in the response.

*setNotifyUrl($value)*
Use this method to set notify URL, which is the URL where the result will be pushed directly from NETS system, upon completion of a transaction, regardless whether the transaction is successful or fail.

*setNotifyUrlParam($value)*
Use this method to pass parameter to your notify URL. The value will be echo back in the response.

*setSubmissionMode($value)*
Use this method to set submission method, either S (Server to Server) or B (Browser to Server). Defaulted to B.

*setPaymentType($value)*
Use this method to set payment type. Acceptable value are SALE, AUTH, CAPT, CRED, RSALE, RAUTH, RCRED. Defaulted to SALE.

*setPaymentMode($value)*
Use this method to set payment mode. Acceptable value are DD (Direct Debit), CC (Credit Card), QR (QR Code Payment). Payment Options will be displayed if this method is not used and if the merchant account is associated with multiple payment method.

*setClientType($value)*
Use this method to set client type. Acceptable value are S (SDK), W (Web), and M (Mobile Web). Defaulted to W.

*setMobileOs($value)*
Use this method to set Mobile OS to either ANDROID or IOS. Mandatory for client type S (SDK).

*setLanguage($value)*
Use this method to set language to either en (english) or zh_cn (chinese). Defaulted to english.

*setIpAddress($value)*
Use this method to set the IP address of the client device.

*setCardholderName($value)*
Use this method to set cardholder name. Mandatory for Server to Server submission type.

*setPan($value, $checkluhn = true)*
Use this method to set PAN (Primary Account Number). Mandatory for Server to Server submission type.

*setExpiryDate($value, $checkexpiry = true)*
Use this method to set expiry date of the card. Mandatory for Server to Server submission type.

*setCvv($value)*
Use this method to set CVV (Cardholder Verification Value) which is 3 digits of numeric code at the back of your Visa/Mastercard, or 4 digits of numeric code at the front of your Amexircan Express card. Mandatory for Server to Server submission type.

*setXid($value)*
Use this method to set XID if own MPI is used for 3D Secure transaction.

*setCavv($value)*
Use this method to set CAVV if own MPI is used for 3D Secure transaction.

*setEci($value)*
Use this method to set ECI value if own MPI is used for 3D Secure transaction.

*setAuthenticationStatus($value)*
Use this method to set Authentication Status if own MPI is used for 3D Secure transaction.

*setEnvironment($value)*
Use this method to set the development environment, whether TEST environment or LIVE environment.

**The following methods are available to get data for further processing upon receiving response from NETS system.**

*getNetsReference()*
Use this method to get the reference data returned by NETS.

*getNetsStatus()*
Use this method to get the status of transaction.

*getNetsMessage()*
Use this method to get the description of the transaction status.

*getAuthorizationDate()*
Use this method to get the authorization date and time of a transaction.

*getAuthorizationAmount()*
Use this method to get the authorization amount deducted from the customer.

*getAuthorizationCode()*
Use this method to get the authorization code deducted from the customer. Only available for Credit Card transaction.

*getStageResponseCode()*
Use this method to get Stage Response from NETS. List of possible Stage Responses are available in NETS Developer Portal.

*getTransactionRandom()*
Use this method to get Transaction Random.

*getActionCode()*
Use this method to get action code of the transaction.

**The following methods are used to perform action required to make a transaction request or to handle a transaction response.*

*getTransactionRequest()*
Use this method to get the raw transaction request in array format.

*getQueryRequest()*
Use this method to get the raw query request in array format.

*getPayload($request)*
Use this method to convert raw reqest in array format to a json format.

*getHmac($request_payload)*
Use this method to calculate HMAC from a json-formatted payload.

*query()*
Use this method to perform query to NETS system.

*run()*
Use this method to execute te script required to send transaction request to NETS system. 

*getFrontendResponse()*
Use those method to get the response data from redirection and return an array, containing the processing result, for further processing.

*getBackendResponse()*
Use those method to get the response data from backend notification and return an array, containing the processing result, for further processing.

## RELEASE NOTES

*V0.1. run() method can support only submission mode B, and query() method has not yet been fully tested and may not function properly in this release.*
