<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.debug.js" ></script>
<style type="text/css">
    body {
    background-color: whitesmoke;
}
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
    width: 180mm;
    background-color: white;
    height: auto;
    /*margin-left: 25%;*/
   /* padding-left: 65px;*/
    padding-top: 90px;
    /*padding-right: 65px;*/
    font-family: arial;
    margin-left: 25%;
    padding: 5%;
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
    width: 75%;
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
</style>
<div class="content-wrapper">
	<section class="content">
		<div class="container-fluid">
		<div id="Document">
            <div id="coverPage">        
                <img src="{{$ReportData[0]->phm_logo}}" class="logo">
                <h1 id="hd" style="text-align: center;">Population Health Management Report</h1>
                <h3 class="shd" style="text-align: center;">Time Period:</h3>
                <h3 class="shd1" style="text-align: center;">{{$ReportData[0]->year}}</h3>
                <h3 class="shd" style="text-align: center;">Prepared for</h3>
                <h3 class="shd1" style="text-align: center;">{{$ReportData[0]->folder_name}}</h3>
            </div>
            <div id="toc_container" style="margin-top:200px;">
                <p class="toc_title"><h3>Table of contents</h3></p>
                <ul class="toc_list">
                <?php $i=1;?>
                 @foreach($SectionData as $keysss => $sections)
                <li>{{$i}}. {{$sections->section_title}}</li>
                 <?php $i++;?>
                @endforeach
                </ul>
            </div>
            <?php $i=1;?>
              @foreach($SectionData as $key => $section)
              <h1 class="secTitle">{{$i}}. {{$section->section_title}}</h1>
              <div class="SectionContent"><?php echo $section->section_text;?></div>
              <?php $i++;
              ?>
              @if(isset($SubSectionData[$section->id]))
                @foreach($SubSectionData[$section->id] as $keys => $Subsection)
                    
                    <div class="SubSectionContent"><?php echo $Subsection['sub_section_text']?></div>
                    <!-- <img src="{{$Subsection['look_img_url']}}"> -->
                    @if(isset($Subsection['long_look']) && $Subsection['long_look'] == 0)
                        @if(isset($Subsection['look_img_url']))
                        <img src="{{$Subsection['look_img_url']}}" class="look_img">
                        @endif
                    @else
                        <div style="margin-top:10px;margin-bottom: 10px;pointer-events: none;" class="htmlLook"><?php echo $Subsection['look_img_url'];?></div>
                    @endif
                @endforeach
              @endif
              @endforeach
        </div>
				
			
			
	</section>
	<!-- /.content -->
</div>

