     
<style type="text/css">
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

</style>
     <div id="Document">
      <h3>report</h3>
      <?php
      
      echo '<img src="data:image/png;base64,' . base64_encode($lookerData) . '">';
      ?>
        <!-- <img src="data:image/png;base64,'.base64_encode({{$lookerData}}).'" / class="cropimg"> -->
      </div>