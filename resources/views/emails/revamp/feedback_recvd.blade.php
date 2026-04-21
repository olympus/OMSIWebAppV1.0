<font size=4><b><u>Feedback from Customer:</u></b></font>
<table style="width:500px;" border>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Received on</b></font></div>
<td>
<div align=center><font size=3>{{$feedback->created_at}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Response Speed:</b></font></div>
	@php
	$item = '';
	for($i=0;$i<$feedback->response_speed;$i++){
	$item.='* ';
	}
	@endphp
<td>
<div align=center><font size=3>{{$item}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Quality of Response:</b></font></div>
	@php
	$item = '';
	for($i=0;$i<$feedback->quality_of_response;$i++){
	$item.='* ';
	}
	@endphp
<td>
<div align=center><font size=3>{{$item}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>App Experience:</b></font></div>
	@php
	$item = '';
	for($i=0;$i<$feedback->app_experience;$i++){
	$item.='* ';
	}
	@endphp
<td>
<div align=center><font size=3>{{$item}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Olympus Staff Performance:</b></font></div>
	@php
	$item = '';
	for($i=0;$i<$feedback->olympus_staff_performance;$i++){
	$item.='* ';
	}
	@endphp
<td>
<div align=center><font size=3>{{$item}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Remarks:</b></font></div>
	@php
	if(!is_null($feedback->remarks) && !empty($feedback->remarks)){
		$remarks = $feedback->remarks;
	}else{
		$remarks = '-';
	}
	@endphp
<td>
<div align=center><font size=3>{{$remarks}}</font></div>
</table>
<br>
<br><font size=4><b><u>General Information</u></b></font>
<table style="width:500px;" border>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Registered on</b></font></div>
<td>
<div align=center><font size=3>{{$servicerequest->created_at}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>My Voice ID</b></font></div>
<td>
<div align=center><font size=3>{{$servicerequest->cvm_id}}</font></div></table>
<br>
<br><font size=4><b><u>Request Details</u></b></font>
<table style="width:500px;" border>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Type of Request:</b></font></div>
<td>
<div align=center><font size=3>{{ucfirst($servicerequest->request_type)}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Issue:</b></font></div>
<td>
<div align=center><font size=3>{{$servicerequest->sub_type}}</font></div>
@if(isset($servicerequest->product_category) && ($servicerequest->product_category!=NULL))
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Product Category:</b></font></div>
<td>
<div align=center><font size=3>{{$servicerequest->product_category}}</font></div>
@endif
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Nature:</b></font></div>
<td>
<div align=center><font size=3>New Request</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Hospital Name:</b></font></div>
<td>
<div align=center><font size=3>{{$hospital_name}}</font></div>
@php
$depname = \App\Models\Departments::find($servicerequest->dept_id);
@endphp
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Department Name:</b></font></div>
<td>
<div align=center><font size=3>{{$depname->name}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Remarks:</b></font></div>
<td>
<div align=center><font size=3>{{$servicerequest->remarks}}</font></div></table>
<br>
<div><font size=3 color=black><b>Profile:</b></font></div>
<table style="width:500px;" border>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Customer ID</b></font></div>
<td>
<div align=center><font size=3>{{sprintf('%08d',$servicerequest->customer_id)}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Title</b></font></div>
<td>
<div align=center><font size=3>{{$customer->title}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>First Name</b></font></div>
<td>
<div align=center><font size=3>{{$customer->first_name}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Last Name:</b></font></div>
<td>
<div align=center><font size=3>{{$customer->last_name}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Mobile Number</b></font></div>
<td>
<div align=center><font size=3>{{$customer->mobile_number}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Email ID:</b></font></div>
<td>
<div align=center><font size=3>{{$customer->email}}</font></div>
</table>
<br>
@php
if($servicerequest->request_type =='service'){
	$assigned_person = \App\Models\EmployeeTeam::where('employee_code',$servicerequest->employee_code)->first();
}else{
	$assigned_person = \App\Models\EmployeeTeam::where('employee_code',$servicerequest->employee_code)->first();
}
@endphp
@if($servicerequest->request_type == 'service')
<div><font size=3 color=black><b>Assigned Olympus Engineer:</b></font></div>
<table style="width:500px;" border>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Assigned on</b></font></div>
<td>
<div align=center><font size=3>{{$servicerequest->updated_at}}</font></div>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=3 color=black><b>Name</b></font></div>
<td><font size=3>{{$assigned_person->name}}</font>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=3 color=white><b>Mobile Number</b></font></div>
<td><font size=3>{{$assigned_person->mobile}}</font>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=3 color=white><b>Email:</b></font></div>
<td><font size=3>{{$assigned_person->email}}</font>
</table>
@else
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=3 color=white><b>Assigned on</b></font></div>
<td>
<div align=center><font size=3>{{$servicerequest->updated_at}}</font></div>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=3 color=black><b>Name</b></font></div>
<td><font size=3>{{$assigned_person->name}}</font>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=3 color=white><b>Mobile Number</b></font></div>
<td><font size=3>{{$assigned_person->mobile}}</font>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=3 color=white><b>Email:</b></font></div>
<td><font size=3>{{$assigned_person->email}}</font>
</table>
@endif
@php
$count=0;
@endphp
@foreach($hospitals as $hospital_cust)
@php
$count++;
$depArr = explode(',',$hospital_cust->dept_id);
$deps = \App\Models\Departments::whereIn('id',$depArr)->pluck('name')->all();

    $deps_text = implode(',', $deps);
@endphp
<br><font size=3><br>
</font>
<br><font size=4><b><u>Hospital {{$count}}</u></b></font>
<table style="width:500px;" border>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=3 color=white><b>Hospital Name</b></font></div>
<td><font size=3>{{$hospital_cust->hospital_name}}</font>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=3 color=white><b>Department</b></font></div>
<td><font size=3>{{$deps_text}}</font>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=3 color=white><b>Address:</b></font></div>
<td><font size=3>{{$hospital_cust->address}}</font>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=3 color=white><b>City:</b></font></div>
<td><font size=3>{{$hospital_cust->city}}</font>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=3 color=white><b>State:</b></font></div>
<td><font size=3>{{$hospital_cust->state}}</font>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=3 color=white><b>Country:</b></font></div>
<td><font size=3>{{$hospital_cust->country}}</font></table>
@endforeach


<br>
<font size=4><b><u>Request Timeline:</u></b></font>
@php
$timelines = \App\Models\StatusTimeline::where('request_id', $servicerequest->id)->get();
@endphp
<table border>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=3 color=white><b>Status</b></font></div>
<td bgcolor=#000080><div align=center><font size=3 color=white><b>Updated Time</b></font></div>
</tr>

@foreach($timelines as $timeline)
<tr>
<td><div align=center><font size=3>{{ str_replace('_', ' ', $timeline->status) }}</font>
<td><div align=center><font size=3>{{ substr_replace($timeline->updated_at,"", -3) }}</font>
</tr>
@endforeach
</table>