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
@media screen and (max-width: 600px) {
  .topnav{overflow: hidden;
    background-color: #f5f5f5;
    margin-top: 21px;
    margin-left: 0px;
    width: 100%;}
    i.fas.fa-bars {
    display: block;
}
</style>
<div class="content-wrapper">
	<section class="content">
		
		<div class="container-fluid">

		<div id="Document">

          <?php $i=1;?>
          @foreach($SectionData as $key => $section)
          <h1 class="secTitle">{{$i}}. {{$section->section_title}}</h1>
          <div class="SectionContent"><?php echo $section->section_text;?></div>
          <?php $i++;
          ?>
          @if(isset($SubSectionData[$section->id]))
            @foreach($SubSectionData[$section->id] as $keys => $Subsection)
                
                <div class="SubSectionContent"><?php echo $Subsection['sub_section_text']?></div>

                <div>
                	<img src="<?php if(isset($Subsection['look_img_url'])){echo $Subsection['look_img_url'];}?>" class="cropimg">                	
                </div>
                
            @endforeach
          @endif
          @endforeach
        </div>
				
			
			
	</section>
	<!-- /.content -->
</div>
