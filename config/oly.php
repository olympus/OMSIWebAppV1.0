<?php
// \Config::get('oly.requests_statuses')

return [

        'indian_all_states'  => [
            'north' => ['Haryana', 'Himachal Pradesh', 'Jammu and Kashmir', 'Punjab', 'Rajasthan', 'Uttarakhand', 'Uttaranchal', 'Uttar Pradesh', 'Chandigarh', 'Delhi'],
            'east' => ['Arunachal Pradesh', 'Assam', 'Bihar', 'Jharkhand', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Orissa', 'Sikkim', 'Tripura', 'West Bengal'],
            'south' => ['Andhra Pradesh', 'Karnataka', 'Kerala', 'Tamil Nadu', 'Telangana', 'Andaman and Nicobar Islands', 'Lakshadweep', 'Puducherry', 'Pondicherry'],
            'west' => ['Goa', 'Gujarat', 'Maharashtra', 'Dadra and Nagar Haveli', 'Daman and Diu', 'Madhya Pradesh', 'Chhattisgarh']
        ],
        'responsible_branches' =>[
            'Andaman and Nicobar Islands' => 'OMSI-CHENNAI',
            'Andhra Pradesh' => 'OMSI-HYDERABAD',
            'Arunachal Pradesh' => 'OMSI-KOLKATA',
            'Assam' => 'OMSI-KOLKATA',
            'Bihar' => 'OMSI-KOLKATA',
            'Chandigarh' => 'OMSI-GURGAON',
            'Chhattisgarh' => 'OMSI-AHMEDABAD',
            'Dadra and Nagar Haveli' => 'OMSI-MUMBAI',
            'Daman and Diu' => 'OMSI-MUMBAI',
            'Delhi' => 'OMSI-GURGAON',
            'Goa' => 'OMSI-MUMBAI',
            'Gujarat' => 'OMSI-AHMEDABAD',
            'Haryana' => 'OMSI-GURGAON',
            'Himachal Pradesh' => 'OMSI-GURGAON',
            'Jammu and Kashmir' => 'OMSI-GURGAON',
            'Jharkhand' => 'OMSI-KOLKATA',
            'Karnataka' => 'OMSI-BANGALORE',
            'Kerala' => 'OMSI-COCHIN',
            'Lakshadweep' => 'OMSI-COCHIN',
            'Madhya Pradesh' => 'OMSI-AHMEDABAD',
            'Maharashtra' => 'OMSI-MUMBAI',
            'Manipur' => 'OMSI-KOLKATA',
            'Meghalaya' => 'OMSI-KOLKATA',
            'Mizoram' => 'OMSI-KOLKATA',
            'Nagaland' => 'OMSI-KOLKATA',
            'Odisha' => 'OMSI-KOLKATA',
            'Orissa' => 'OMSI-KOLKATA',
            'Pondicherry' => 'OMSI-CHENNAI',
            'Puducherry' => 'OMSI-CHENNAI',
            'Punjab' => 'OMSI-GURGAON',
            'Rajasthan' => 'OMSI-GURGAON',
            'Sikkim' => 'OMSI-KOLKATA',
            'Tamil Nadu' => 'OMSI-CHENNAI',
            'Telangana' => 'OMSI-HYDERABAD',
            'Tripura' => 'OMSI-KOLKATA',
            'Uttar Pradesh' => 'OMSI-LUCKNOW',
            'Uttarakhand' => 'OMSI-LUCKNOW',
            'Uttaranchal' => 'OMSI-LUCKNOW',
            'West Bengal' => 'OMSI-KOLKATA',
        ],
        'requests_statuses'  => [
            'service_repair' => [
                "Received"=>"10",
                "Assigned"=>"20",
                "Attended"=>"30",
                "Received_At_Repair_Center"=>"40",
                "Quotation_Prepared"=>"50",
                "PO_Received"=>"60",
                "Repair_Started"=>"70",
                "Repair_Completed"=>"80",
                "Ready_To_Dispatch"=>"90",
                "Dispatched"=>"95",
                "Closed"=>"100"
            ],
            'service' => [
                "Received"=>"10",
                "Assigned"=>"25",
                "Attended"=>"50",
                "Closed"=>"100"
            ],
            'academic' => [
                "Received"=>"10",
                "Assigned"=>"25",
                "Attended"=>"50",
                "Closed"=>"100"
            ],
            'enquiry' => [
                "Received"=>"10",
                "Assigned"=>"25",
                "Attended"=>"50",
                "Closed"=>"100"
            ],
            'installation' => [
                "Received"=>"10",
                "Assigned"=>"25",
                "Attended"=>"50",
                "Installed"=>"75",
                "Closed"=>"100"
            ]
        ],

        'servicec_statuses' => [
            "Received_At_Repair_Center",
            "Quotation_Prepared",
            "PO_Received",
            "Repair_Started",
            "Repair_Completed",
            "Ready_To_Dispatch",
            "Dispatched"
        ],

        'enquiry_prod_categories' => [
            "accessory"=>["Accessory"],
            "capital"=>["Capital Product"],
            "other"=>["Other","Therapeutic Devices"]
        ],
        
        'default_responsible_branch' => 'GURGAON MAIN',
        
        'developer_email' => 'sarvar.kumar@lyxelandflamingo.com',
        'enq_acad_coordinator_email' => [
            'lalita.sharma@olympus.com',
            //'sonali.yadav@olympus.com',
        ],
        'service_coordinator_email' => 'komal.sen@olympus.com',
        //'service_admin' => 'vinod.madan@olympus.com',
        'olympus_admin' => 'ryo_nakadegawa@ot.olympus.co.jp',

        
        //'service_level_3_esc' => 'saurabh.shankar@olympus.com',
        
        'service_level_3_esc' => [
            'komal.sen@olympus.com',
            'sangeeta.gupta@olympus.com',
            //'ryo.nakadegawa@olympus.com',
            'saurabh.shankar@olympus.com',
        ],
        'service_level_4_esc' => 'manish.kumar@olympus.com',
        'enq_acad_level_3_esc' => 'umesh.shankar@olympus.com,indroneil.mukerjee@olympus.com,anurag.rastogi@olympus.com',

        'workshopmanagers_east' => 'biswanath.saha@olympus.com',
        'workshopmanagers_west' => 'vikas.mahajan@olympus.com',
        'workshopmanagers_south' => 'ganapathy.subramaniyam@olympus.com',
        'workshopmanagers_north' => 'rahul.khatri@olympus.com',

        'feedback_cc' => [
//            'radhika.rawat@olympus.com', 
            'saurabh.shankar@olympus.com',
        ],

        'escalation_cc' => [
            'radhika.rawat@olympus.com',
            'vinod.madan@olympus.com',
        ],
        
        'current_version_iOS' => '1.24',
        //'current_version_iOS' => '1.19',
        'current_version_android' => '1.1.41',
        //'current_version_android' => '1.0.28',
        'testing_url' => 'https://olympusmyvoice.ml',
    ];
