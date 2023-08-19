<?php 
	include "../backend/includes/connection.php";

	//dapatkan ID jadwal pertandingan
	$id_partai = mysqli_real_escape_string($koneksi, $_GET["id_partai"]);
	//echo $id_partai;

	//CEK dan UBAH status AKTIF PARTAI
	$sqljadwalpartai = "SELECT status FROM jadwal_tanding 
					WHERE id_partai='$id_partai'";
	$jadwalpartai = mysqli_query($koneksi,$sqljadwalpartai);
	$status_partai = mysqli_fetch_array($jadwalpartai);
	//echo $status_partai['status'];

	if($status_partai['status']=='-') {
		$update = mysqli_query($koneksi,"UPDATE jadwal_tanding SET aktif='1' WHERE id_partai='$id_partai'");
	}

	//Mencari data jadwal pertandingan
	$sqljadwal = "SELECT * FROM jadwal_tanding 
					WHERE id_partai='$id_partai'";
	$jadwal_tanding = mysqli_query($koneksi,$sqljadwal);
	$jadwal = mysqli_fetch_array($jadwal_tanding);
?>

<html>
<head>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
<div class="table-responsive">
  <table class="table">
    <thead>
      <tr>
        <td colspan="6" rowspan="3"></td>
      </tr>
      <tr class="text-right">
        <td width="38%" colspan="5" style="font-size: 20px; font-weight: bold;">GELANGGANG : <?php echo $jadwal['gelanggang']; ?></td>
      </tr>
      <tr class="text-right">
        <td colspan="5" style="font-size: 20px; font-weight: bold;"> PARTAI : <?php echo $jadwal['partai']." (".$jadwal['babak'].")"; ?> </td>
      </tr>
      <tr>
        <td colspan="6"></td>
        <td colspan="5" class="text-right" style="font-size: 20px; font-weight: bold;"><?php echo $jadwal['kelas']; ?></td>
      </tr>
      <tr>
        <td class="text-left" colspan="5" style="font-size: 20px; color:#FF0000; font-weight: bold;"><?php echo $jadwal['nm_merah']; ?> - <?php echo $jadwal['kontingen_merah']; ?></td>
        <td class="text-right" colspan="5" style="font-size: 20px; color:#0000FF; font-weight: bold;"><?php echo $jadwal['nm_biru']; ?> - <?php echo $jadwal['kontingen_biru']; ?></td>
      </tr>
    </thead>
  </table>
</div>
<div class="content_penilaian">

	<div class="text-center" style="font-size: 250px; color: white">
	<div class="row">
		<div class="col-5" style="background-color: #ff0000">
		<b><b><?=0;?></b></b>
		</div>
		<div class="col-2">
		<div class="row">
			<div class="col-12 mt-3">
			<button class="btn btn-primary" onclick="regp(1)">Babak 1</button>
			</div>
			<div class="col-12 mt-3">
			<button class="btn btn-primary" onclick="regp(2)">Babak 2</button>
			</div>
			<div class="col-12 mt-3">
			<button class="btn btn-primary" onclick="regp(3)">Babak 3</button>
			</div>
			<div class="col-12 mt-5">
				<input type="text text-center" class="form-control" value="BABAK 1" >
			</div>
		</div>
		</div>
		<div class="col-5" style="background-color: #0000ff">
		<b><b><?=0;?></b></b>
		</div>
	</div>
	</div>

</div>
<div class="table-responsive">
	<table class="table">
		<tr>
			<td class="text-left">
				<a href="index.php" class="btn btn-warning" role="button">KEMBALI</a>
			</td>

		  <td class="text-center">
				<input type="button" class="btn btn-info" value="1. RELOAD" onClick="history.go(0)" />
		  </td>

			<td class="text-right">
				<form name="SetorNilai" id="SetorNilai" method="POST" action="setor_nilai.php" onClick="return cek_selesai();">
					<input type="hidden" name="skorakhirmerah" id="skorakhirmerah" value="<?php //echo $skorakhirmerah; ?>">
					<input type="hidden" name="skorakhirbiru" id="skorakhirbiru" value="<?php //echo $skorakhirbiru; ?>">
					<input type="hidden" name="pemenang" id="pemenang" value="<?php //echo $pemenang; ?>">
					<input type="hidden" name="id_partai" id="id_partai" value="<?php echo $_GET['id_partai']; ?>">
					<input type="submit" class="btn btn-danger" value="2. SELESAI">
				</form>
			</td>
		</tr>
	</table>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
<script type="text/javascript">
	var uri='http://localhost/pencaksilat/nilai/api2.php?tag=1'
	setInterval(function(){
		$.ajax({
            url: uri, 
            data : {'a' : 'get_data_view_tanding', 'id_partai': <?=$_GET["id_partai"]?>},
            type: "GET",
            success: function(obj){
            	$('.content_penilaian').html(obj);
            	console.log('Request ... Done');
            }
        });
	}, 2000);

    var var_start_timer = true;
    var var_stop = false;
    var interval= "";
	var resume_time = function(){
		if(var_stop)
    	{
    		return false;
    	}
        if(var_start_timer)
        {
            $('.btn-stop').html(' RESUME');
            var_start_timer = false;
        }
        else
        {
            var_start_timer = true;
            $('.btn-stop').html(' PAUSE');
        }
    }
    function stop_time()
    {	
    	var_stop = true;
        $(".waktu").html("00:00");
        clearInterval(interval);
    }
    function init_start()
    {
        clearInterval(interval);
        var duration = 60 * 3.0; // 2 Menit         
        var_stop = false;
        var_start_timer = true;

    	start_time(duration)	
    }
    function start_time(duration) 
    {
        var timer = duration, minutes, seconds;

        interval = setInterval(function () {
        	if(var_stop)
	    	{
	    		return false;
	    	}

            if(var_start_timer == false)
            {
                return false;
            }

            minutes = parseInt(timer / 60, 10)
            seconds = parseInt(timer % 60, 10);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            if (--timer < 0) {
            	var_stop = true;
                $(".waktu").html("WAKTU HABIS");
        		clearInterval(interval);
            }
            else
            {
                $(".waktu").html(minutes + ":" + seconds);
            }
        }, 1000);
    }

</script>
<!-- SELESAI Confirm Function -->	
<script>
	function cek_selesai()
	{
		if(confirm('Apakah Anda Yakin Pertandingan Sudah Berakhir?')){
			return true;
		} else {
			return false;
		}
	}
</script>

<script>
    function regp(tag){
        uri="api2.php?a=get_data_view_tanding&id_partai=1&tag="+tag;
    }
</script>
</body>
</html>


