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
    font-size: 30px;
    margin-bottom: 40px;
}
h3.shd {
    font-size: 20px;
}
h3.shd1 {
    font-size: 20px;
    margin-bottom: 50px;
}
.time-period-block {
    text-align: center;
    margin: 16px 0 10px;
}
.time-period-block .tp-label {
    font-size: 16px;
    font-weight: bold;
    display: block;
    margin-bottom: 6px;
}
.time-period-block .tp-row {
    font-size: 14px;
    color: #333;
    display: block;
    margin: 3px 0;
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

                {{-- ── Time Period block ── --}}
                <div class="time-period-block">
                    <span class="tp-label">Time Period</span>
                    @if(!empty($ReportData[0]->medical_start_date) || !empty($ReportData[0]->medical_end_date))
                    <span class="tp-row">
                        Medical Data:
                        {{ $ReportData[0]->medical_start_date ?? '—' }}
                        &nbsp;To&nbsp;
                        {{ $ReportData[0]->medical_end_date ?? '—' }}
                    </span>
                    @endif
                    @if(!empty($ReportData[0]->pharmacy_start_date) || !empty($ReportData[0]->pharmacy_end_date))
                    <span class="tp-row">
                        Pharmacy Data:
                        {{ $ReportData[0]->pharmacy_start_date ?? '—' }}
                        &nbsp;To&nbsp;
                        {{ $ReportData[0]->pharmacy_end_date ?? '—' }}
                    </span>
                    @endif
                    @if(!empty($ReportData[0]->reporting_year))
                    <span class="tp-row" style="color:#555;font-size:13px;">
                        Reporting Year: {{ $ReportData[0]->reporting_year }}
                    </span>
                    @endif
                </div>

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
            {{-- Define the footer template --}}
            <htmlpagefooter name="page-footer" style="display:none">
                <table width="100%" style="border: none;">
                    <tr style="border: none;">
                        <td width="33%" style="border: none;"></td>
                        <td width="33%" align="center" style="font-weight: bold; border: none;">Page {PAGENO} of {nbpg}</td>
                        <td width="33%" style="border: none;"></td>
                    </tr>
                </table>
            </htmlpagefooter>
            {{-- Activate the footer on all pages --}}
            <sethtmlpagefooter name="page-footer" value="on" show-this-page="1" />

            <?php $i=1;?>
              @foreach($SectionData as $key => $section)
              <h1 class="secTitle" style="page-break-before: always;">{{$i}}. {{$section->section_title}}</h1>
              <div class="SectionContent"><?php echo $section->section_text;?></div>
              <?php $i++;
              ?>
              @if(isset($SubSectionData[$section->id]))
                @foreach($SubSectionData[$section->id] as $keys => $Subsection)

                    <div class="SubSectionContent"><?php echo $Subsection['sub_section_text']?></div>

                        @if(isset($Subsection['look_img_url']) && $Subsection['look_img_url'] != '')
                            @if($Subsection['chart_type'] == 'studio')
                                @if(!empty($Subsection['screenshot_url']))
                                {{-- Screenshot image is available — embed it directly --}}
                                <img src="{{ $Subsection['screenshot_url'] }}"
                                     class="look_img"
                                     style="width:85%;margin:20px auto;display:block;border:1px solid #e8e8e8;">
                                @else
                                {{-- No screenshot — show a clearly-labelled clickable link --}}
                                <div style="margin-top:12px;margin-bottom:12px;padding:10px;border:1px solid #e0e0e0;border-radius:4px;background:#f9f9f9;">
                                    <p style="margin:0 0 6px 0;font-size:11px;color:#555;font-weight:bold;">Interactive Chart (Looker Studio)</p>
                                    <p style="margin:0;font-size:10px;color:#1a73e8;word-break:break-all;">
                                        <a href="{{ $Subsection['look_img_url'] }}" style="color:#1a73e8;">
                                            {{ $Subsection['look_img_url'] }}
                                        </a>
                                    </p>
                                    <p style="margin:4px 0 0;font-size:9px;color:#888;">
                                        Add a screenshot URL in the Studio Dashboard admin to embed this chart as an image.
                                    </p>
                                </div>
                                @endif
                            @elseif($Subsection['chart_type'] == 'quickchart')
                            <img src="{{ $Subsection['look_img_url'] }}"
                                 class="look_img"
                                 style="width:85%;margin:16px auto;display:block;">
                            @elseif($Subsection['chart_type'] == "looker_bar" || $Subsection['chart_type'] == "looker_column")
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
