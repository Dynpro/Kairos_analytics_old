@extends('layouts.app')
@section('content')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css">  
<style type="text/css">
nav.navbar.navbar-expand.navbar-white.navbar-light {
    border-bottom: 1px solid #d2cccc;
    position: fixed;
    z-index: 99999;
    width: 100%;    
    margin-top: -56px;
}
i.fas.fa-bars {
    display: none;
}
div#Document {
    width: 210mm;
    background-color: white;
    height: auto;
    margin-left: 25%;
    padding-left: 65px;
    padding-top: 90px;
    padding-right: 65px;
    font-family: arial;
}

.h2, h2 {
    font-size: 16px;
}
p {
    font-size: 13px;
}
.secTitle{
	font-size: 18px;
	font-family: sans-serif;
	font-weight: bold;
}
.img2
{
	margin-bottom: 20px;
}
button.multiselect.dropdown-toggle.btn.btn-default {
    height: 35px;
    margin-top: -5px;
    margin-left: 5px;
}
@media screen and (max-width: 600px) {
  .topnav{overflow: hidden;
    background-color: #f5f5f5;
    margin-top: 21px;
    margin-left: 0px;
    width: 100%;}
    i.fas.fa-bars {
    display: block;
}
}
</style>
<div class="content-wrapper">
	<!-- Content Header (Page pandurang123) -->
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
				<!-- <h5 class="mb-2 mt-4">Report</h5> -->
				</div>
				<!-- /.col -->
				<!--<div class="col-sm-6">
				<ol class="breadcrumb float-sm-right">
					<li class="breadcrumb-item"><a href="/home">Home</a></li>
					<li class="breadcrumb-item active">Dashboard</li>
				</ol>
					</div> -->
			</div>
			<!-- /.row -->
		</div>
		<!-- /.container-fluid -->
	</div>
	<!-- /.content-header -->
	<!-- Main content -->
	<section class="content">
		<div class="container-fluid">
			<form role="form" method="post" action="{{ route('storeReport') }}">
				@csrf
				<div class="row">
						<input id="name" type="hidden" name="name" required placeholder="Enter Report Name" />
						<input id="store_folder" type="hidden" name="store_folder">
					<div class="form-group col-3">
						<b>Client Name: </b>
						<select  name="client_id" id="client_id" style="width: 40%; height: 100%;" data-placeholder="Select a client" required >
							<option value="" >Select client</option>
							@foreach($folderChildArr as $folder)
							<option value="{{$folder->phm_folder_id}}/{{$folder->schema_name}}"  >{{$folder->folder_name}}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group col-3">
						<label for="firstName">Year : </label>
						<select id="multiple-checkboxes" multiple="multiple" name="years[]" >  
	        				<!-- <option value="2017">2017</option>
							<option value="2018">2018</option>
							<option value="2019">2019</option>
							<option value="2020">2020</option>
							<option value="2021">2021</option>
							<option value="2022">2022</option> -->
	    				</select>  
    				</div>
					<div class="form-group col-3">
						<b>Reporting Year: </b>
						<select  name="reporting_yr" id="reporting_yr" style="width: 40%; height: 100%;" data-placeholder="Select a Reporting year" required >
							<option value="" >Select Reporting Year</option>
							<option value="Service">Service</option>
							<option value="Paid">Paid</option>
						</select>
					</div>
					
    				<div class="form-group col-3">
						<b>Frequency: </b>
						<select  name="frequency" id="frequency" style="width: 40%; height: 100%;" data-placeholder="Select a client" required >
							<option value="" >Select Frequency</option> 
	        				<option value="1">Once</option>
							<option value="2">Weekly</option>
							<option value="3">Monthly</option>
							<option value="4">Quarterly</option>
						</select>
					</div>
				</div>
				<button type="submit" class="btn btn-primary">Save </button>
				<a href="\all_reports" class="btn btn-default" >Cancel</a>
				</form>
				<hr>
				
			
			
	</section>
	<script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
	<script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>	
	
	<!-- /.content -->
</div>
<script type="text/javascript">
	$( document ).ready(function() {
	   $.ajaxSetup({

	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	        }

	    });
	});

   $('#reservation,#reservation1').daterangepicker({ 
   
   locale: {
   	format: 'MM-DD-YYYY'
   },
   	 singleDatePicker: true,
    		 showDropdowns: true
   });  

   $(document).ready(function() {  
        $('#multiple-checkboxes').multiselect();  
    }); 
   $('#client_id').on('change',function(){
	   var optionsText = this.options[this.selectedIndex].text;
	   $('#name').val(optionsText);
	   var selectedId = this.value;
	   var split = selectedId.split("/");

	   var myArray = {2737: 2434, 2888: 2923, 2890: 2925, 2889: 2926, 2891: 2927, 2892: 2929, 2893: 2930, 2894: 2931, 2895: 2932, 2896:2934, 2924: 2933, 2897: 2928, 3208: 3224,3121:3123, 3087:3100, 3305:3306,3531:3534,3870:2926, 3945:2929, 4066:4067, 4601:4602};

	   if(myArray[split[0]])
	   {
	   $('#store_folder').val(myArray[split[0]]);	   	
	   }
	   $.ajax({
           type:'POST',
           url:'/report/get_base_years',
           data:{schema_name:split[1]},
           success:function(data){
           	var options = "";
            	for (let i = 0; i < 3; i++) {
                    options += "<option value='"+data.base_years[i]+"'>"+data.base_years[i]+"</option>";
                }

                $("#multiple-checkboxes").html(options);
        		$("#multiple-checkboxes").multiselect('rebuild');
                // $('#multiple-checkboxes').multiselect('updateOptions', options);
           }	
       });
	}); 
 //   	$('#reporting_yr').on('change',function(){
	//    var selectedId = $('#client_id').val(); 
	//    var split = selectedId.split("/");
	//    alert(selectedId);
	//    var yearsAll = $('#multiple-checkboxes').val();
	//    console.log(yearsAll);
	//    $.ajax({
 //           type:'POST',
 //           url:'/report/get_dates',
 //           data:{schema_name:split[1],years:yearsAll},
 //           success:function(data){
 //           	// console.log(data.dates['MAX_DATE_MED']);
 //           	console.log(JSON.stringify(data.dates));
 //           }	
 //        });
	// }); 
   
</script> 
@endsection
