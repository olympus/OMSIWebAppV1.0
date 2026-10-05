<!DOCTYPE html>
<html>
<head>
    <title>ROI Calculator enquiry</title>
</head>
<body>
    <p>Dear Team,</p>

    <p>A new ROI Calculator enquiry has been submitted with the following details:</p>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <td><strong>Name</strong></td>
            <td>{{ $roi->name }}</td>
        </tr>
        <tr>
            <td><strong>Email</strong></td>
            <td>{{ $roi->email }}</td>
        </tr>
        <tr>
            <td><strong>Mobile</strong></td>
            <td>{{ $roi->mobile }}</td>
        </tr>
        <tr>
            <td><strong>Hospital Name</strong></td>
            <td>{{ $roi->hospital_name ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>Speciality</strong></td>
            <td>{{ $roi->speciality ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>State</strong></td>
            <td>{{ $roi->state ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>City</strong></td>
            <td>{{ $roi->city ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>Pincode</strong></td>
            <td>{{ $roi->pincode ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>Customer Status</strong></td>
            <td>{{ $roi->customer_status ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>Processor Profile</strong></td>
            <td>{{ $roi->processor_profile ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>Endoscopy Suite</strong></td>
            <td>{{ $roi->endoscopy_suite ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>Procedure Performer</strong></td>
            <td>{{ $roi->procedure_performer ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>Procedures Performed</strong></td>
            <td>{{ $roi->procedures_performed ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>Submitted At</strong></td>
            <td>{{ $roi->created_at }}</td>
        </tr>
    </table>

    <br>
    <p>Best Regards,</p>
    <p>Olympus India</p>
</body>
</html>
