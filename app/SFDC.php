<?php
namespace App;

use stdClass;

class SFDC
{
    public static function getToken(){
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_URL            => env("SFDC_LOGIN_URL"),
                CURLOPT_POST           => TRUE,
                CURLOPT_POSTFIELDS     => http_build_query(
                    array(
                        'grant_type'    => 'password',
                        'client_id'     => env("SFDC_ClientID"),
                        'client_secret' => env("SFDC_ClientSecret"),
                        'username'      => env("SFDC_UserName"),
                        'password'      => env("SFDC_Password")
                    )
                )
            )
        );
        $response = json_decode(curl_exec($curl));
        curl_close($curl);
        $access_token = (isset($response->access_token) && $response->access_token != "") ? $response->access_token : die("Error - access token missing from response!");
        $instance_url = (isset($response->instance_url) && $response->instance_url != "") ? $response->instance_url : "";
        return $access_token;
    }

    public static function createRequest($request, $customer, $hospital, $parent_sfdc_id=""){
        $accessToken = SFDC::getToken();
        $SFDCdata = new stdClass();
        if(!empty($parent_sfdc_id)){
            $SFDCdata->Parent_Case__C = $parent_sfdc_id;
        }
        $SFDCdata->MyVoice_Request_Id__C = $request->id;
        $SFDCdata->Myvoice_Customer_ID__C = $request->customer_id;
        $SFDCdata->SAP_customer_code__C = $customer->sap_customer_id;
        $SFDCdata->Department__C = $request->dept_id;
        $SFDCdata->Remarks__C = $request->remarks;
        $SFDCdata->Sub_type__C = $request->sub_type;
        $SFDCdata->Product_Category__C = \implode(";",  preg_split("/\s*,\s*/", trim($request->product_category), -1, PREG_SPLIT_NO_EMPTY) );
        $SFDCdata->status = "Received";
        $SFDCdata->origin = "web";
        $SFDCdata->Hospital_State__C = $hospital->state;
        $SFDCdata->Request_type__C = $request->request_type;
        $SFDCdata->Title__c =  $customer->title;
        $SFDCdata->Customer_Id__C =  $customer->id;
        $SFDCdata->First_Name__C = $customer->first_name;
        $SFDCdata->Middle_Name__C = $customer->middle_name;
        $SFDCdata->Last_Name__C = $customer->last_name;
        $SFDCdata->Mobile_Number__c = $customer->mobile_number;
        $SFDCdata->Customer_Email__C = $customer->email;

        $SFDCdata->Hospital_Dept_ID__C = $request->dept_id;
        $SFDCdata->Hospital_Address__C = $hospital->address;
        $SFDCdata->Hospital_city__C = $hospital->city;
        $SFDCdata->Hospital_Zip__C = $hospital->zip;
        $SFDCdata->Hospital_country__C = $hospital->country;
        $SFDCdata->Hospital_responsible_branch__C = $hospital->responsible_branch;
        $SFDCdata->Hospital_Name__C = $hospital->hospital_name;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ));

        curl_setopt($ch, CURLOPT_URL, env("SFDC_URL")."Case");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, \json_encode($SFDCdata));
        $result     = json_decode( curl_exec ($ch) );
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $result;
    }

    public static function createEscalation($request){
        $accessToken = SFDC::getToken();
        $SFDCdata = new stdClass();
        $SFDCdata->Case__c = $request->sfdc_id;
        $SFDCdata->Reasons__c = \implode(";",  preg_split("/\s*,\s*/", trim($request->escalation_reasons), -1, PREG_SPLIT_NO_EMPTY) );
        $SFDCdata->MyVoice_Request_Id__C = $request->id;
        $SFDCdata->Remarks__C = $request->remarks;
        $SFDCdata->Name = $request->escalation_count;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ));

        curl_setopt($ch, CURLOPT_URL, env("SFDC_URL")."Escalation__c");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, \json_encode($SFDCdata));
        $result     = json_decode( curl_exec ($ch) );
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $result;

    }

    public static function submitFeedback($feedback){
        $accessToken = SFDC::getToken();
        $SFDCdata = new stdClass();
        $SFDCdata->response_speed__c = $feedback->response_speed;
        $SFDCdata->quality_of_response__c = $feedback->quality_of_response;
        $SFDCdata->app_experience__c = $feedback->app_experience;
        $SFDCdata->olympus_staff_performance__c = $feedback->olympus_staff_performance;
        $SFDCdata->feedback_remarks__c = $feedback->remarks;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ));

        curl_setopt($ch, CURLOPT_URL, env("SFDC_URL")."Case/".$feedback->sfdc_id);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, \json_encode($SFDCdata));
        $result     = json_decode( curl_exec ($ch) );
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $result;
    }

    /*public static function acknowledgeRequest($acknowledgement_status_key, $request_id_key){
        $accessToken = SFDC::getToken();
        $SFDCdata = new \stdClass(); 
        $SFDCdata->MyVoice_Request_Id__C = $request_id_key;
        $SFDCdata->Equipment_Received_Acknowled__c = $acknowledgement_status_key; 
        $SFDCdata->Type__c = "Update"; 
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ));

        curl_setopt($ch, CURLOPT_URL, env("SFDC_URL")."Case");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, \json_encode($SFDCdata));
        $result     = json_decode( curl_exec ($ch) );
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $result;
    }*/

    // This function is not used for now
        public static function acknowledgeRequest($acknowledgement_status_key, $request_id_key){
            $accessToken = SFDC::getToken();
            $SFDCdata = new stdClass(); 
            //$SFDCdata->MyVoice_Request_Id__C = $request_id_key;
            $SFDCdata->Equipment_Received_Acknowledgement__c = $acknowledgement_status_key; 
            //$SFDCdata->Type__c = "Update";  

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ));

            curl_setopt($ch, CURLOPT_URL, env("SFDC_URL")."Case/".$request_id_key);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, \json_encode($SFDCdata));
            $result     = json_decode( curl_exec ($ch) );
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            return $result; 
        }

    public static function reminderRequest($message, $request_id_key){
        $accessToken = SFDC::getToken();
        $SFDCdata = new stdClass();  
        $SFDCdata->Reminder__c  = $message;  

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ));

        curl_setopt($ch, CURLOPT_URL, env("SFDC_URL")."Case/".$request_id_key);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, \json_encode($SFDCdata));
        $result     = json_decode( curl_exec ($ch) );
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $result; 
    }

    public static function acknowledgeRequestHappyCode($acknowledgement_status_key, $request_id_key){
        $accessToken = SFDC::getToken();
        $SFDCdata = new stdClass(); 
        //$SFDCdata->MyVoice_Request_Id__C = $request_id_key;
        $SFDCdata->Equipment_Received_Acknowledgement__c = $acknowledgement_status_key; 
        $SFDCdata->Closed_by__c = "Customer"; 
        //$SFDCdata->Type__c = "Update";  

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ));

        curl_setopt($ch, CURLOPT_URL, env("SFDC_URL")."Case/".$request_id_key);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, \json_encode($SFDCdata));
        $result     = json_decode( curl_exec ($ch) );
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $result; 
    }

    public static function sfdcDataPassUsingUrlInSFDC($id, $response){
        $accessToken = SFDC::getToken();
        $SFDCdata = new stdClass(); 
        //$SFDCdata->MyVoice_Request_Id__C = $request_id_key;
        $SFDCdata->id = $id; 
        $SFDCdata->response = $response;  

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ));

        curl_setopt($ch, CURLOPT_URL, env("SFDC_NEW_URL"));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, \json_encode($SFDCdata));
        $result     = json_decode( curl_exec ($ch) );
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return $result; 
    }
     
}
