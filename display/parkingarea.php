<html>
  
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>SegmentDisplay</title>
<style>
table, th, td {
    border: 2px solid black;
    padding: 10px;
    position: relative;
    float: left;
    margin-right: 16px;
    margin-left: 16px;
}
td {
	padding: 20px;
	height: 90px;
    margin-bottom: 20px
}
.box {
    padding: 90px 0px 0px 10px;
    display: inline-block;
    margin-left: 150px;
}
label#a {
    position: absolute;
    margin-top: -105px;
    margin-left: 77px;
    font-size: 100px;
    color: white;
}
label#b {
    position: absolute;
    margin-top: -105px;
    margin-left: 133px;
    font-size: 100px;
    color: white;
}
label#c {
	position: absolute;
	margin-top: -105px;
	margin-left: 185px;
	font-size: 100px;
	color: white;
}
#tableA td {
	background: green;
}
#tableB td {
	background: green;
}
#tableC td {
	background: green;
}
</style>
    
    <!--[if IE]>
      <script type="text/javascript" src="excanvas.js"></script>
    <![endif]-->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

    <script type="text/javascript" src="segment-display.js"></script>
    <script type="text/javascript">

      
      function animate() {
	  
        $.ajax({
		  url: '/melvin/?user=melvin&pass=r4h4s14&method=query&table=tbl_parking&args=WHERE%20parkingarea%20is%20not%20null%20and%20position%20is%20not%20null',
		  type: 'GET',
		  success: function(response){
			//alert (response);
			var obj = JSON.parse(response);

			//fill bg color according json data
			for(var i=0; i<=obj['Data'].length; i++){
				if(obj['Data'][i]['parkingarea']=="A"){
					parea = obj['Data'][i]['parkingarea']; // +"="+ 
					pos = obj['Data'][i]['position'];
					var tableA = document.getElementById("td"+parea+pos).style.backgroundColor = "red";
				}
				if(obj['Data'][i]['parkingarea']=="B"){
					parea = obj['Data'][i]['parkingarea']; // +"="+ 
					pos = obj['Data'][i]['position'];
					var tableB = document.getElementById("td"+parea+pos).style.backgroundColor = "red";
				}
				if(obj['Data'][i]['parkingarea']=="C"){
					parea = obj['Data'][i]['parkingarea']; // +"="+ 
					pos = obj['Data'][i]['position'];
					var tableC = document.getElementById("td"+parea+pos).style.backgroundColor = "red";
				}
			}

		  }
		 });
		window.setTimeout('animate()', 1000);
		setTimeout("location.href = 'http://sv01.inovindojayaabadi.co.id/melvin/display';", 20000);
      }
      animate();
    </script> 
    
	<style>
		#overlay {
			width: 100%;
			height: 100%;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			//background: transparent url(https://d2v9y0dukr6mq2.cloudfront.net/video/thumbnail/H8WuRINimqur8ud/technology-background-gear_rhfg-imn_thumbnail-full01.png);
			background-color: #263e68; //#4286f4
			z-index: 2;
			cursor: pointer;
		}
	</style>
	
  </head>
  
  <body id="overlay">
     <div class="box">
		<label id="c">C</label>
		<table id="tableC">
			<?php
			 $no =1;
			 for($i=1; $i<=5; $i++){ ?>
				<tr>
					<?php for($j=1; $j<=4; $j++){ ?>
						<td id="tdC<?php echo $no ?>"><?php echo $no++; ?></td>
					<?php } ?>
				</tr>
			<?php } ?>
		</table>

		<label id="b">B</label>
		<table id="tableB">
			<?php
			 $no =1;
			 for($i=1; $i<=5; $i++){ ?>
				<tr>
					<?php for($j=1; $j<=3; $j++){ ?>
						<td id="tdB<?php echo $no ?>"><?php echo $no++; ?></td>
					<?php } ?>
				</tr>
			<?php } ?>
		</table>

		<label id="a">A</label>
		<table id="tableA">
		<?php
		 	$no =1;
		 	for($i=1; $i<=4; $i++){ ?>
			<tr id="trA">
				<?php for($j=1; $j<=2; $j++){ ?>
					<td id="tdA<?php echo $no ?>"><?php echo $no++; ?></td>
				<?php } ?>
			</tr>
		<?php } ?>
		</table>
	</div>
  </body>
  
</html>