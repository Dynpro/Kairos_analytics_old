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
#Document {
    width: 180mm;
    background-color: white;
    height: auto;
    padding-top: 30mm;
    padding-bottom: 20mm;
    font-family: Arial, sans-serif;
    box-sizing: border-box;
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
.cropimg{
	margin-bottom: 30px;
}
#toc_container {
    /*background: #f9f9f9 none repeat scroll 0 0;*/
    /*border: 1px solid #aaa;*/
    display: table;
    font-size: 95%;
    margin-bottom: 1em;
    padding: 1px;
    width: auto;
    margin-bottom: 70px;
}

.toc_title {
    font-weight: 700;
    text-align: center;
}

#toc_container li, #toc_container ul, #toc_container ul li{
    list-style: outside none none !important;
    padding: 3px;
    margin-left: 21px;

}
img.logo {
    width: 60%;
    margin-left: 15%;
    margin-top: 15%;
    margin-bottom: 30px;
}
h1#hd {
    /*margin-left: 7%;*/
    font-size: 30px;
    margin-bottom: 80px;
}
h3.shd {
    /*margin-left: 40%;*/
    font-size: 22px;
}
h3.shd1 {
    /*margin-left: 23%;*/
    font-size: 22px;
    margin-bottom: 50px;
}
img.look_img {
    margin-bottom: 20px;
    margin-top: 20px;
}
a {
  pointer-events: none;
}

.htmlLook a
{
    pointer-events: none;
    text-decoration: none;
    color: black;
    cursor: default;
}
.footer {
    width: 100%;
    text-align: center;
    position: fixed;
}
.footer {
    bottom: 0px;
}
.pagenum:before {
    content: counter(page);
}
@page {
    size: A4;
    margin: 20mm;
}
</style>

<div class="content-wrapper">
	<section class="content">
		<div class="container-fluid">
        <input type="hidden" name="report_id" id="report_id" value="{{$id}}">
		<div id="Document">
            <div id="coverPage">
        
                <img src="{{$ReportData[0]->phm_logo}}" class="logo">
                <h1 id="hd" style="text-align: center;">Population Health Management Report</h1>
                <h3 class="shd" style="text-align: center;">Time Period:</h3>

         
                    
                <h3 class="shd" style="text-align: center;">Prepared for</h3>
                <h3 class="shd1" style="text-align: center;">{{$ReportData[0]->folder_name}}</h3>
            </div>
            <div id="toc_container" style="page-break-before: always;">
                <p class="toc_title"><h3>Table of contents</h3></p>
                <ul class="toc_list">
                <?php $i=1;?>
                 @foreach($SectionData as $keysss => $sections)
                <li>{{$i}}. {{$sections->section_title}}</li>
                 <?php $i++;?>
                @endforeach
                </ul>
            </div>
            <div class="footer">
                <htmlpagefooter name="page-footer">
                    <table width="100%" style="border: none;">
                        <tr style="border: none;">
                            <td width="33%"><span style="font-weight: bold; font-style: italic;border: none;"></span></td>
                            <td width="33%" align="center" style="font-weight: bold; border: none;">Page {PAGENO} of {nbpg}</td>
                            <td width="33%" style="text-align: right; border: none;"></td>
                        </tr>
                    </table>
                </htmlpagefooter>
            </div>
      
            <?php $i=1;?>
              @foreach($SectionData as $key => $section)
              <h1 class="secTitle" style="page-break-before: always;">{{$i}}. {{$section->section_title}}</h1>
              <div class="SectionContent"><?php echo $section->section_text;?></div>
              <?php $i++;
              ?>
              @if(isset($SubSectionData[$section->id]))
                @foreach($SubSectionData[$section->id] as $keys => $Subsection)
                    
                    <div class="SubSectionContent"><?php echo $Subsection['sub_section_text']?></div>

                        @if(isset($Subsection['look_img_url']))
                            @if($Subsection['chart_type'] == "looker_bar" || $Subsection['chart_type'] == "looker_column")
                            <img src="{{$Subsection['look_img_url']}}" class="look_img" style="height:50%;width: 85%;margin-left: auto;margin-right: auto;">
                            @else
                            <div style="margin-top:10px;margin-bottom: 10px;pointer-events: none;" class="htmlLook"><?php echo $Subsection['look_img_url'];?></div>
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
           data:{report_id:report_id, flag:5},
           success:function(data){
            
           }
        });
    }
</script>

