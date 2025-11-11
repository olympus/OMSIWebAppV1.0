<?php

namespace App\Http\Controllers\API\V2;

use Illuminate\Http\Request;
use Validator;

use App\Http\Controllers\Controller;
use App\SFDC;
class SFDCDataUpdateAPIController extends Controller
{
    
    public function sfdcDataUpdateAPIOld(Request $request){     
        $validator = Validator::make($request->all(), [
            'id' => 'required', 
            'response' => 'required', 
        ]);

        if ($validator->fails()) {
            return response($validator->errors()->first())->header('Content-Type', 'text/plain');   
        }

        $id = $request->id;
        $response = $request->response; 

        if(env("SFDC_ENABLED")){
            $SFDCCreateRequest = SFDC::sfdcDataPassUsingUrlInSFDC($id, $response);
            if($SFDCCreateRequest == "success"){  
                return response('Your response has been saved successfully')->header('Content-Type', 'text/plain');  
            }else{ 
                return response('Data not found')->header('Content-Type', 'text/plain');  
            }
        }else{ 
            return response('Data not found')->header('Content-Type', 'text/plain');
        } 
    }   

    public function sfdcDataUpdateAPI(Request $request)
    {     
        $validator = Validator::make($request->all(), [
            'id' => 'required', 
            'response' => 'required', 
        ]);

        if ($validator->fails()) {
            return response('The provided link is broken.')->header('Content-Type', 'text/plain');   
        }

        $id = $request->id;
        $response = $request->response; 

        if (env("SFDC_ENABLED")) {
            $SFDCCreateRequest = SFDC::sfdcDataPassUsingUrlInSFDC($id, $response);

            if ($SFDCCreateRequest === "success" || $SFDCCreateRequest === "duplicate") {
                $html = '
                <html>
                    <head>
                        <style>
                            .success-box {
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                background-color: #d4edda;
                                border: 1px solid #c3e6cb;
                                color: #155724;
                                padding: 15px;
                                border-radius: 4px;
                                font-size: 16px;
                                margin: 20px auto;
                                max-width: 500px;
                                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                            }
                            .success-box .icon {
                                display: flex;
                                align-items: center;
                            }
                            .success-box .icon i {
                                font-size: 24px;
                                margin-right: 10px;
                            }
                            .success-box .close {
                                font-size: 20px;
                                color: #155724;
                                cursor: pointer;
                            }
                        </style>
                        <script>
                            function closeBox() {
                                document.getElementById("successBox").style.display = "none";

                                // Attempt to close the window
                                window.close();

                                // Fallback: Redirect if window cant be closed
                                setTimeout(function() {
                                    window.location.href = "/";
                                }, 500);
                            }
                        </script>
                    </head>
                    <body>
                        <div id="successBox" class="success-box">
                            <div class="icon">
                                <i>&#10003;</i>
                                <strong>Success:</strong> Your response has been captured!
                            </div>
                            <div class="close" onclick="closeBox()">&times;</div>
                        </div>
                    </body>
                </html>';

                return response($html)->header('Content-Type', 'text/html');
            } elseif ($SFDCCreateRequest === "not_found") {
                return response('Requested details not found.')->header('Content-Type', 'text/plain');  
            } elseif ($SFDCCreateRequest === "failure") {
                return response('A failure has occurred or the server is temporarily down. Please try again later.')->header('Content-Type', 'text/plain');  
            } else {
                return response('Data not found')->header('Content-Type', 'text/plain');  
            }
        } else { 
            return response('Data not found')->header('Content-Type', 'text/plain');
        } 
    }

}
