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
                            @if(isset($Subsection['chart_type']) && $Subsection['chart_type'] == 'studio')
                            <div style="margin:10px 0;padding:8px;border:1px solid #e0e0e0;background:#f9f9f9;border-radius:4px;">
                                <p style="margin:0 0 4px;font-size:11px;color:#555;font-weight:bold;">Interactive Chart (Looker Studio)</p>
                                <a href="{{ $Subsection['look_img_url'] }}" style="font-size:10px;color:#1a73e8;word-break:break-all;">{{ $Subsection['look_img_url'] }}</a>
                            </div>
                            @elseif(isset($Subsection['chart_type']) && ($Subsection['chart_type'] == "looker_bar" || $Subsection['chart_type'] == "looker_column"))
	                        <img src="{{$Subsection['look_img_url']}}">
	                        @endif
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

