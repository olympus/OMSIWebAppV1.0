<style type="text/css">
	.table {
		border-collapse: collapse;
	  	border-radius: 10px;
  		overflow: hidden;
  		-webkit-box-shadow: 5px 5px 10px 0px rgba(0,0,0,0.75);
		-moz-box-shadow: 5px 5px 10px 0px rgba(0,0,0,0.75);
		box-shadow: 5px 5px 10px 0px rgba(0,0,0,0.75);
		height: auto;
		min-height: 200px;

	}
	p {
		-webkit-box-shadow: 5px 5px 10px 0px rgba(0,0,0,0.75);
		-moz-box-shadow: 5px 5px 10px 0px rgba(0,0,0,0.75);
		box-shadow: 5px 5px 10px 0px rgba(0,0,0,0.75);
	}
	td {
		vertical-align: middle !important;
	}
	
</style>
@php
$servicerequest = \App\Models\ServiceRequests::findOrFail($id);
$customer = \App\Models\Customers::findOrFail($servicerequest->customer_id);
$hospitals_list = \App\Models\Customers::where('id', $customer->id)->value('hospital_id');
$hospitals_list = \App\Models\Hospitals::whereIn('id',explode(',',$hospitals_list))->get();
$hospitals = \App\Models\Hospitals::find($servicerequest->hospital_id);
$hospital_name = $hospitals->hospital_name;	

@endphp

<div class="container" style="width:100%;">
<div class="row">



<div class="col-md-12">
<p class="text-center" style="background-color:#000080; padding:7px; color:white; border-radius:10px;"><font size=6><b>General Information</b></font></p>
<table class="table table-striped" border>
<col width="30%">
<col width="70%">
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Registered on</b></font></div>
<td>
<div align=center><font size=5>{{$servicerequest->created_at}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>My Voice ID</b></font></div>
<td>
<div align=center><font size=5>{{$servicerequest->cvm_id}}</font></div>

<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>SFDC ID</b></font></div>
<td>
<div align=center><font size=5>{{$servicerequest->sfdc_id}}</font></div></table></div>


<div class="col-md-12">
<p class="text-center" style="background-color:#000080; padding:7px; color:white; border-radius:10px;"><font size=6><b>Request Details</b></font></p>
<table class="table table-striped" border>
<col width="30%">
<col width="70%">
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Type of Request:</b></font></div>
<td>
<div align=center><font size=5>{{ucfirst($servicerequest->request_type)}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Issue:</b></font></div>
<td>
<div align=center><font size=5>{{$servicerequest->sub_type}}</font></div>
@if(isset($servicerequest->product_category) && ($servicerequest->product_category!=NULL))
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Product Category:</b></font></div>
<td>
<div align=center><font size=5>{{$servicerequest->product_category}}</font></div>
@endif
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Hospital Name:</b></font></div>
<td>
<div align=center><font size=5>{{$hospital_name}}</font></div>
@php
$depname = \App\Models\Departments::find($servicerequest->dept_id);
@endphp
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Department Name:</b></font></div>
<td>
<div align=center><font size=5>{{$depname->name}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Remarks</b></font></div>
<td>
<div align=center><font size=5>{{$servicerequest->remarks}}</font></div></table></div>



<div class="col-md-12">
<p class="text-center" style="background-color:#000080; padding:7px; color:white; border-radius:10px;"><font size=6><b>Profile</b></font></p>
<table class="table table-striped" border>
<col width="30%">
<col width="70%">
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Customer ID</b></font></div>
<td>
<div align=center><font size=5>{{sprintf('%08d',$servicerequest->customer_id)}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Title</b></font></div>
<td>
<div align=center><font size=5>{{$customer->title}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>First Name</b></font></div>
<td>
<div align=center><font size=5>{{$customer->first_name}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Last Name:</b></font></div>
<td>
<div align=center><font size=5>{{$customer->last_name}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Mobile Number</b></font></div>
<td>
<div align=center><font size=5>{{$customer->mobile_number}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Email ID:</b></font></div>
<td>
<div align=center><font size=5>{{$customer->email}}</font></div>
</table>
</div>



@php
	$assigned_person = \App\Models\EmployeeTeam::where('employee_code',$servicerequest->employee_code)->first();
@endphp
<div class="col-md-12">
<p class="text-center" style="background-color:#000080; padding:7px; color:white; border-radius:10px;"><font size=6><b>Assigned Olympus Executive</b></font></p>
<table class="table table-striped" border>
<col width="30%">
<col width="70%">
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Assigned on</b></font></div>
<td>
<div align=center><font size=5>{{$servicerequest->updated_at}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Name</b></font></div>
<td>
<div align=center><font size=5>{{$assigned_person->name}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Mobile Number</b></font></div>
<td>
<div align=center><font size=5>{{$assigned_person->mobile}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Email</b></font></div>
<td>
<div align=center><font size=5>{{$assigned_person->email}}</font></div>
</table>
</div>

@if(!is_null($oldData_employee_code))
@php
	$old_person = \App\Models\EmployeeTeam::where('employee_code',$oldData_employee_code)->first();
@endphp
<div class="col-md-12">
<p class="text-center" style="background-color:#000080; padding:7px; color:white; border-radius:10px;"><font size=6><b>Re-Assigned from Olympus Executive</b></font></p>
<table class="table table-striped" border>
<col width="30%">
<col width="70%">
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Assigned on</b></font></div>
<td>
<div align=center><font size=5>{{$servicerequest->updated_at}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Name</b></font></div>
<td>
<div align=center><font size=5>{{$old_person->name}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Mobile Number</b></font></div>
<td>
<div align=center><font size=5>{{$old_person->mobile}}</font></div>
<tr valign=top>
<td bgcolor=#000080>
<div align=center><font size=5 color=white><b>Email</b></font></div>
<td>
<div align=center><font size=5>{{$old_person->email}}</font></div>
</table>
</div>
@endif


<div class="col-md-12">
	<p class="text-center" style="background-color:#000080; padding:7px; color:white; border-radius:10px;"><font size=6><b>Request Timeline</b></font></p>
	<table class="table table-striped" border>
	<col width="50%">
	<col width="50%">
	<tr valign=top>
		<td bgcolor=#000080>
			<div align=center><font size=5 color=white><b>Status</b></font></div>
		<td bgcolor=#000080>
				<div align=center><font size=5 color=white><b>Assigned on</b></font></div>
@php
	$timelines = \App\Models\StatusTimeline::where('request_id', $servicerequest->id)->get();
@endphp
	@foreach($timelines as $timeline)
	<tr valign=top>
		<td>
			<div align=center><font size=5>{{ str_replace('_', ' ', $timeline->status) }}</font></div>
		<td>
			<div align=center><font size=5>{{ substr_replace($timeline->updated_at,"", -3) }}</font></div>
	@endforeach

</table>
</div>


@php
$count=0;
@endphp
@foreach($hospitals_list as $hospital_cust)
@php
$count++;
$depArr = explode(',',$hospital_cust->dept_id);
$deps = \App\Models\Departments::whereIn('id',$depArr)->pluck('name')->all();

    $deps_text = implode(',', $deps);
@endphp
<div class="col-md-12">
<p class="text-center" style="background-color:#000080; padding:7px; color:white; border-radius:10px; width:100%"><font size=6><b>Hospital {{$count}}</b></font></p>
<table class="table table-striped" border style="max-width:100%;">
<col width="30%">
<col width="70%">
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=5 color=white><b>Hospital Name</b></font></div>
<td><div align=center><font size=5>{{$hospital_cust->hospital_name}}</font>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=5 color=white><b>Department</b></font></div>
<td><div align=center><font size=5>{{$deps_text}}</font>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=5 color=white><b>Address:</b></font></div>
<td><div align=center><font size=5>{{$hospital_cust->address}}</font>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=5 color=white><b>City:</b></font></div>
<td><div align=center><font size=5>{{$hospital_cust->city}}</font>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=5 color=white><b>State:</b></font></div>
<td><div align=center><font size=5>{{$hospital_cust->state}}</font>
<tr valign=top>
<td bgcolor=#000080><div align=center><font size=5 color=white><b>Country:</b></font></div>
<td><div align=center><font size=5>{{$hospital_cust->country}}</font></table></div>
@endforeach
</div>
</div>
<script src="{{env('APP_URL')}}/vendor/adminlte/vendor/jquery/dist/jquery.min.js"></script>
<script src="{{env('APP_URL')}}/vendor/adminlte/vendor/bootstrap/dist/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="{{env('APP_URL')}}/vendor/adminlte/vendor/bootstrap/dist/css/bootstrap.min.css">