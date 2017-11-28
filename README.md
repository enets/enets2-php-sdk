# enets2-php-sdk
This SDK for PHP is intended to assist you in your PHP project to integrate with ENETS 2.0 API. You still have the option to integrate directly without using this SDK.

## DISCLAIMER

The content of this API Library may only be used in connection with the services of NETS's eNETS product offering. Unless required by applicable law or agreed to in writing, the library is offered and/or distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. NETS does not warrant that the library or any content will be available uninterrupted or error free, that defects will be corrected, or that the library or its supporting systems are free of viruses or bugs.

## METHODS

The folowing methods are available to set the variable that are needed to submit a transaction request to ENETS:

*setUmid($value)*
Use this method to set UMID acording to the UMID associated to your merchant account. Please contact NETS for details if you do not have one.

*setTid($value)*
Use this method to set TID according to the TID associated to your merchant account. It is optional and unlikely you will need to use it in ENETS context.

*setSecretKey($value)*
Use this method to set Secret Key to be used in calculating HMAC value to sign your transation request, as well as to validate the response from NETS.

*setKeyId($value)*
Use this method to se Key ID, which is required when you submit your transaction request.

*setCurrency($value)*
Defaulted to SGD

*setAmount($value)*
Use this method to set the amount you would like to charge. The float value is expected (eg. 15.80).

*setMerchantReference($value)*
Use this method to set the reference data that can be associated back to your own transaction record. Must be a unique value for each transaction.
