<meta name="csrf-token" content="{{ csrf_token() }}" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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
    /*margin-left: 25%;*/
   /* padding-left: 65px;*/
    padding-top: 90px;
    /*padding-right: 65px;*/
    font-family: arial;
    /*margin-left: 30%;*/
}

</style>
<div class="content-wrapper">
	<section class="content">
		<div class="container-fluid">
        <input type="hidden" name="report_id" id="report_id" value="{{$id}}">
    
		<div id="Document">
              @foreach($SectionData as $key => $section)
	              @if(isset($SubSectionData[$section->id]))
	                @foreach($SubSectionData[$section->id] as $keys => $Subsection)
	                    @if(isset($Subsection['look_img_url']) && $Subsection['look_img_url'] != "")
	                        <img src="{{$Subsection['look_img_url']}}">
	                    @endif
	                @endforeach
	              @endif
              @endforeach
        </div>
       
			
			
	</section>
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
    window.onload = function(){ 
        var report_id = $("#report_id").val();
        $.ajax({
           type:'POST',
           url:'/report/update_flag',
           data:{report_id:report_id, flag:2},
           success:function(data){
            
           }
        });
    }
</script>

