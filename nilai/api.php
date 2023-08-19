<?php 

include "../backend/includes/connection.php";

// REQUIRED 
// agar bisa di akses oleh android API
header('Access-Control-Allow-Origin: *');

// get ACTION 
$param = isset($_GET['a']) ? $_GET['a'] : ''; 

if("" != $param)
{
	switch($param)
	{

		case "ceksettingan":
 
			if($username !== $_GET['username'])
			{
				echo json_encode(['status' => 'error', 'messages' => 'Settingan username salah silahkan dicoba kembali']);

				return false;
			}

			if($password !== $_GET['password'])
			{
				echo json_encode(['status' => 'error', 'messages' => 'Settingan Password salah silahkan dicoba kembali']);

				return false;
			}

			if($nama_database !== $_GET['database'])
			{
				echo json_encode(['status' => 'error', 'messages' => 'Settingan Database salah silahkan dicoba kembali']);

				return false;
			}

			echo json_encode(['status' => 'success']);

		break;
		case "partai":
			echo partai();
		break;

		case "juri":
			echo juri();
		break;

		case "login":


			$id_juri = $_GET['id'];
			$password = md5($_GET['password']);

			$sql = "SELECT * FROM wasit_juri WHERE id_juri='{$id_juri}' and pass_juri='{$password}'";

			$exec = mysqli_query($koneksi,$sql);

			$row = mysqli_fetch_row($exec);
			
			if($row){
				echo json_encode(['status' => 'success']);
			}else{
				echo json_encode(['status' => 'error']);
			}
		break;
		case "jadwal":

			$id = $_GET['id_partai'];

			$sql = "SELECT * FROM jadwal_tanding WHERE id_partai='{$id}'";

			$exec = mysqli_query($koneksi,$sql);

			$row = mysqli_fetch_assoc($exec);

			if($row)
				echo json_encode($row);
			else
				echo json_encode([]);
		break;
		case "history":

			$id_juri 	= $_GET['id_juri'];
			$id_jadwal 	= $_GET['id_jadwal'];
			$sudut 		= $_GET['sudut'];
			$babak 		= $_GET['babak'];
			
			$sql = mysqli_query($koneksi,"SELECT nilai_tanding.*, w.nm_juri FROM nilai_tanding inner join wasit_juri w on w.id_juri=nilai_tanding.id_juri  WHERE id_jadwal='{$id_jadwal}' AND nilai_tanding.id_juri='{$id_juri}' AND sudut='{$sudut}' AND babak='{$babak}' ORDER BY id_nilai DESC");

			$key= 0;
			$data = [];
			while($result = mysqli_fetch_array($sql))
			{
				$data[$key] = $result;
				$key++;
			}

			if($data)
				echo json_encode($data);
			else
				echo json_encode([]);
		break;
		case "submit_skor":
			
			$id_jadwal 	= $_POST['id_jadwal'];
			$id_juri 	= $_POST['id_juri'];
			$button 	= $_POST['button'];
			$nilai		= $_POST['nilai'];
			$sudut 		= $_POST['sudut'];
			$babak 		= $_POST['babak'];
			
			// INSERT INTO `nilai_tanding` (`id_nilai`, `id_jadwal`, `id_juri`, `button`, `nilai`, `sudut`, `babak`) VALUES (NULL, 'idpartaisaatlogin', 'juriyglogin', '1+1', '2', 'MERAH/BIRU', 'babakygaktif');
			$result = mysqli_query($koneksi,"INSERT INTO nilai_tanding (id_nilai, id_jadwal, id_juri, button, nilai, sudut, babak) VALUES (NULL, '{$id_jadwal}','{$id_juri}','{$button}',{$nilai}, '{$sudut}','{$babak}')");

			if($result)
				echo json_encode(['message' => 'success']);
			else
				echo json_encode(['message' => 'error']);
		break;
		case "delete_nilai":
			// get id_nilai
			$id_nilai = $_GET['id_nilai'];

			$result = mysqli_query($koneksi,"DELETE FROM nilai_tanding WHERE id_nilai={$id_nilai}");

			if($result)
				echo json_encode(['status' => 'success']);
			else
				echo json_encode(['status' => 'error']);
		break;
		case "get_data_view_tanding":
			get_data_view_tanding();
		break;
		case "get_data_view_monitoring":
			get_data_view_monitoring();
		break;
	}
}

/**
 * [partai description]
 * @return [type] [description]
 */
function partai()
{
	include "../backend/includes/connection.php";
	$sql = "SELECT * FROM jadwal_tanding WHERE aktif='1' AND status='-' ORDER BY (0 + partai) ASC";

	$exec = mysqli_query($koneksi,$sql);

	$result = [];
	
	$key = 0;
	while($item = mysqli_fetch_array($exec)):
		$result[$key]['id'] = $item['id_partai'];
		$result[$key]['name'] = $item['partai'];
		$result[$key]['kelas'] = $item['kelas'];
		$result[$key]['gelanggang'] = $item['gelanggang'];
		$key++;
	endwhile;

	return json_encode($result);
}

function juri()
{
	include "../backend/includes/connection.php";
	$sql = "SELECT * FROM wasit_juri";

	$exec = mysqli_query($koneksi,$sql);

	$result = [];
	
	$key = 0;
	while($item = mysqli_fetch_array($exec)):
		$result[$key]['id'] = $item['id_juri'];
		$result[$key]['name'] = $item['nm_juri'];
		$key++;
	endwhile;

	return json_encode($result);
}

/**
 * [get_data_view_monitoring description]
 * @return [type] [description]
 */
function get_data_view_monitoring()
{
	include "../backend/includes/connection.php";
	//dapatkan ID jadwal pertandingan
	$id_partai = mysqli_real_escape_string($koneksi, $_GET["id_partai"]);
	
	ob_start();
	?>
<?php 
	
	$out1 = ob_get_contents();

	ob_end_clean();

	echo $out1;
}

/**
 * [get_data_view_tanding description]
 * @return [type] [description]
 */
function get_data_view_tanding()
{
	include "../backend/includes/connection.php";
	//dapatkan ID jadwal pertandingan
	$id_partai = mysqli_real_escape_string($koneksi, $_GET["id_partai"]);

	//Mencari data jadwal pertandingan
	$sqljadwal = "SELECT * FROM jadwal_tanding 
					WHERE id_partai='$id_partai'";
	$jadwal_tanding = mysqli_query($koneksi,$sqljadwal);
	$jadwal = mysqli_fetch_array($jadwal_tanding);

	
	//----------------- WASIT JURI 1 MERAH
	//Kueri nilai wasit juri 1, babak 1, sudut merah
	$sqljuri1babak1merah = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='1' AND
							babak='1' AND
							sudut='MERAH'";
	$juri1babak1merah = mysqli_query($koneksi,$sqljuri1babak1merah);
	$nilaijuri1babak1merah = mysqli_fetch_array($juri1babak1merah);

	//Kueri nilai wasit juri 1, babak 2, sudut merah
	$sqljuri1babak2merah = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='1' AND
							babak='2' AND
							sudut='MERAH'";
	$juri1babak2merah = mysqli_query($koneksi,$sqljuri1babak2merah);
	$nilaijuri1babak2merah = mysqli_fetch_array($juri1babak2merah);

	//Kueri nilai wasit juri 1, babak 3, sudut merah
	$sqljuri1babak3merah = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='1' AND
							babak='3' AND
							sudut='MERAH'";
	$juri1babak3merah = mysqli_query($koneksi,$sqljuri1babak3merah);
	$nilaijuri1babak3merah = mysqli_fetch_array($juri1babak3merah);
	//----------------- END WASIT JURI 1 MERAH

	//----------------- WASIT JURI 2 MERAH
	//Kueri nilai wasit juri 2, babak 1, sudut merah
	$sqljuri2babak1merah = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='2' AND
							babak='1' AND
							sudut='MERAH'";
	$juri2babak1merah = mysqli_query($koneksi,$sqljuri2babak1merah);
	$nilaijuri2babak1merah = mysqli_fetch_array($juri2babak1merah);

	//Kueri nilai wasit juri 2, babak 2, sudut merah
	$sqljuri2babak2merah = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='2' AND
							babak='2' AND
							sudut='MERAH'";
	$juri2babak2merah = mysqli_query($koneksi,$sqljuri2babak2merah);
	$nilaijuri2babak2merah = mysqli_fetch_array($juri2babak2merah);

	//Kueri nilai wasit juri 2, babak 3, sudut merah
	$sqljuri2babak3merah = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='2' AND
							babak='3' AND
							sudut='MERAH'";
	$juri2babak3merah = mysqli_query($koneksi,$sqljuri2babak3merah);
	$nilaijuri2babak3merah = mysqli_fetch_array($juri2babak3merah);
	//----------------- END WASIT JURI 2 MERAH


	//----------------- WASIT JURI 3 MERAH
	//Kueri nilai wasit juri 3, babak 1, sudut merah
	$sqljuri3babak1merah = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='3' AND
							babak='1' AND
							sudut='MERAH'";
	$juri3babak1merah = mysqli_query($koneksi,$sqljuri3babak1merah);
	$nilaijuri3babak1merah = mysqli_fetch_array($juri3babak1merah);

	//Kueri nilai wasit juri 3, babak 2, sudut merah
	$sqljuri3babak2merah = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='3' AND
							babak='2' AND
							sudut='MERAH'";
	$juri3babak2merah = mysqli_query($koneksi,$sqljuri3babak2merah);
	$nilaijuri3babak2merah = mysqli_fetch_array($juri3babak2merah);

	//Kueri nilai wasit juri 3, babak 3, sudut merah
	$sqljuri3babak3merah = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='3' AND
							babak='3' AND
							sudut='MERAH'";
	$juri3babak3merah = mysqli_query($koneksi,$sqljuri3babak3merah);
	$nilaijuri3babak3merah = mysqli_fetch_array($juri3babak3merah);
	//----------------- END WASIT JURI 3 MERAH


	//----------------- WASIT JURI 4 MERAH
	//Kueri nilai wasit juri 4, babak 1, sudut merah
	$sqljuri4babak1merah = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='4' AND
							babak='1' AND
							sudut='MERAH'";
	$juri4babak1merah = mysqli_query($koneksi,$sqljuri4babak1merah);
	$nilaijuri4babak1merah = mysqli_fetch_array($juri4babak1merah);

	//Kueri nilai wasit juri 4, babak 2, sudut merah
	$sqljuri4babak2merah = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='4' AND
							babak='2' AND
							sudut='MERAH'";
	$juri4babak2merah = mysqli_query($koneksi,$sqljuri4babak2merah);
	$nilaijuri4babak2merah = mysqli_fetch_array($juri4babak2merah);

	//Kueri nilai wasit juri 4, babak 3, sudut merah
	$sqljuri4babak3merah = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='4' AND
							babak='3' AND
							sudut='MERAH'";
	$juri4babak3merah = mysqli_query($koneksi,$sqljuri4babak3merah);
	$nilaijuri4babak3merah = mysqli_fetch_array($juri4babak3merah);
	//----------------- END WASIT JURI 4 MERAH


	//----------------- WASIT JURI 5 MERAH
	//Kueri nilai wasit juri 5, babak 1, sudut merah
	$sqljuri5babak1merah = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='5' AND
							babak='1' AND
							sudut='MERAH'";
	$juri5babak1merah = mysqli_query($koneksi,$sqljuri5babak1merah);
	$nilaijuri5babak1merah = mysqli_fetch_array($juri5babak1merah);

	//Kueri nilai wasit juri 5, babak 2, sudut merah
	$sqljuri5babak2merah = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='5' AND
							babak='2' AND
							sudut='MERAH'";
	$juri5babak2merah = mysqli_query($koneksi,$sqljuri5babak2merah);
	$nilaijuri5babak2merah = mysqli_fetch_array($juri5babak2merah);

	//Kueri nilai wasit juri 5, babak 3, sudut merah
	$sqljuri5babak3merah = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='5' AND
							babak='3' AND
							sudut='MERAH'";
	$juri5babak3merah = mysqli_query($koneksi,$sqljuri5babak3merah);
	$nilaijuri5babak3merah = mysqli_fetch_array($juri5babak3merah);
	//----------------- END WASIT JURI 5 MERAH


	//----------------- AREA BIRU --------------------------
	//------------------------------------------------------

	//----------------- WASIT JURI 1 BIRU
	//Kueri nilai wasit juri 1, babak 1, sudut biru
	$sqljuri1babak1biru = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='1' AND
							babak='1' AND
							sudut='BIRU'";
	$juri1babak1biru = mysqli_query($koneksi,$sqljuri1babak1biru);
	$nilaijuri1babak1biru = mysqli_fetch_array($juri1babak1biru);

	//Kueri nilai wasit juri 1, babak 2, sudut biru
	$sqljuri1babak2biru = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='1' AND
							babak='2' AND
							sudut='BIRU'";
	$juri1babak2biru = mysqli_query($koneksi,$sqljuri1babak2biru);
	$nilaijuri1babak2biru = mysqli_fetch_array($juri1babak2biru);

	//Kueri nilai wasit juri 1, babak 3, sudut biru
	$sqljuri1babak3biru = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='1' AND
							babak='3' AND
							sudut='BIRU'";
	$juri1babak3biru = mysqli_query($koneksi,$sqljuri1babak3biru);
	$nilaijuri1babak3biru = mysqli_fetch_array($juri1babak3biru);
	//----------------- END WASIT JURI 1 biru

	//----------------- WASIT JURI 2 biru
	//Kueri nilai wasit juri 2, babak 1, sudut biru
	$sqljuri2babak1biru = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='2' AND
							babak='1' AND
							sudut='BIRU'";
	$juri2babak1biru = mysqli_query($koneksi,$sqljuri2babak1biru);
	$nilaijuri2babak1biru = mysqli_fetch_array($juri2babak1biru);

	//Kueri nilai wasit juri 2, babak 2, sudut biru
	$sqljuri2babak2biru = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='2' AND
							babak='2' AND
							sudut='BIRU'";
	$juri2babak2biru = mysqli_query($koneksi,$sqljuri2babak2biru);
	$nilaijuri2babak2biru = mysqli_fetch_array($juri2babak2biru);

	//Kueri nilai wasit juri 2, babak 3, sudut biru
	$sqljuri2babak3biru = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='2' AND
							babak='3' AND
							sudut='BIRU'";
	$juri2babak3biru = mysqli_query($koneksi,$sqljuri2babak3biru);
	$nilaijuri2babak3biru = mysqli_fetch_array($juri2babak3biru);
	//----------------- END WASIT JURI 2 biru


	//----------------- WASIT JURI 3 biru
	//Kueri nilai wasit juri 3, babak 1, sudut biru
	$sqljuri3babak1biru = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='3' AND
							babak='1' AND
							sudut='BIRU'";
	$juri3babak1biru = mysqli_query($koneksi,$sqljuri3babak1biru);
	$nilaijuri3babak1biru = mysqli_fetch_array($juri3babak1biru);

	//Kueri nilai wasit juri 3, babak 2, sudut biru
	$sqljuri3babak2biru = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='3' AND
							babak='2' AND
							sudut='BIRU'";
	$juri3babak2biru = mysqli_query($koneksi,$sqljuri3babak2biru);
	$nilaijuri3babak2biru = mysqli_fetch_array($juri3babak2biru);

	//Kueri nilai wasit juri 3, babak 3, sudut biru
	$sqljuri3babak3biru = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='3' AND
							babak='3' AND
							sudut='BIRU'";
	$juri3babak3biru = mysqli_query($koneksi,$sqljuri3babak3biru);
	$nilaijuri3babak3biru = mysqli_fetch_array($juri3babak3biru);
	//----------------- END WASIT JURI 3 biru


	//----------------- WASIT JURI 4 biru
	//Kueri nilai wasit juri 4, babak 1, sudut biru
	$sqljuri4babak1biru = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='4' AND
							babak='1' AND
							sudut='BIRU'";
	$juri4babak1biru = mysqli_query($koneksi,$sqljuri4babak1biru);
	$nilaijuri4babak1biru = mysqli_fetch_array($juri4babak1biru);

	//Kueri nilai wasit juri 4, babak 2, sudut biru
	$sqljuri4babak2biru = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='4' AND
							babak='2' AND
							sudut='BIRU'";
	$juri4babak2biru = mysqli_query($koneksi,$sqljuri4babak2biru);
	$nilaijuri4babak2biru = mysqli_fetch_array($juri4babak2biru);

	//Kueri nilai wasit juri 4, babak 3, sudut biru
	$sqljuri4babak3biru = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='4' AND
							babak='3' AND
							sudut='BIRU'";
	$juri4babak3biru = mysqli_query($koneksi,$sqljuri4babak3biru);
	$nilaijuri4babak3biru = mysqli_fetch_array($juri4babak3biru);
	//----------------- END WASIT JURI 4 biru


	//----------------- WASIT JURI 5 biru
	//Kueri nilai wasit juri 5, babak 1, sudut biru
	$sqljuri5babak1biru = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='5' AND
							babak='1' AND
							sudut='BIRU'";
	$juri5babak1biru = mysqli_query($koneksi,$sqljuri5babak1biru);
	$nilaijuri5babak1biru = mysqli_fetch_array($juri5babak1biru);

	//Kueri nilai wasit juri 5, babak 2, sudut biru
	$sqljuri5babak2biru = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='5' AND
							babak='2' AND
							sudut='BIRU'";
	$juri5babak2biru = mysqli_query($koneksi,$sqljuri5babak2biru);
	$nilaijuri5babak2biru = mysqli_fetch_array($juri5babak2biru);

	//Kueri nilai wasit juri 5, babak 3, sudut biru
	$sqljuri5babak3biru = "SELECT SUM(nilai) FROM nilai_tanding
							WHERE id_jadwal='$id_partai' AND 
							id_juri='5' AND
							babak='3' AND
							sudut='BIRU'";
	$juri5babak3biru = mysqli_query($koneksi,$sqljuri5babak3biru);
	$nilaijuri5babak3biru = mysqli_fetch_array($juri5babak3biru);
	//----------------- END WASIT JURI 5 biru
	//

	// HITUNG HUKUMAN UNTUK MERAH
	$hukumanmerahj1 = mysqli_query($koneksi,"SELECT COUNT(button) AS hukmerahj1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=1 AND button LIKE '%-%' AND sudut='MERAH'");
	$hukmerahj1 = mysqli_fetch_array($hukumanmerahj1);

	$hukumanmerahj2 = mysqli_query($koneksi,"SELECT COUNT(button) AS hukmerahj2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=2 AND button LIKE '%-%' AND sudut='MERAH'");
	$hukmerahj2 = mysqli_fetch_array($hukumanmerahj2);

	$hukumanmerahj3 = mysqli_query($koneksi,"SELECT COUNT(button) AS hukmerahj3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=3 AND button LIKE '%-%' AND sudut='MERAH'");
	$hukmerahj3 = mysqli_fetch_array($hukumanmerahj3);

	$hukumanmerahj4 = mysqli_query($koneksi,"SELECT COUNT(button) AS hukmerahj4 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=4 AND button LIKE '%-%' AND sudut='MERAH'");
	$hukmerahj4 = mysqli_fetch_array($hukumanmerahj4);

	$hukumanmerahj5 = mysqli_query($koneksi,"SELECT COUNT(button) AS hukmerahj5 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=5 AND button LIKE '%-%' AND sudut='MERAH'");
	$hukmerahj5 = mysqli_fetch_array($hukumanmerahj5);

	// HITUNG HUKUMAN UNTUK BIRU
	$hukumanbiruj1 = mysqli_query($koneksi,"SELECT COUNT(button) AS hukbiruj1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=1 AND button LIKE '%-%' AND sudut='BIRU'");
	$hukbiruj1 = mysqli_fetch_array($hukumanbiruj1);

	$hukumanbiruj2 = mysqli_query($koneksi,"SELECT COUNT(button) AS hukbiruj2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=2 AND button LIKE '%-%' AND sudut='BIRU'");
	$hukbiruj2 = mysqli_fetch_array($hukumanbiruj2);

	$hukumanbiruj3 = mysqli_query($koneksi,"SELECT COUNT(button) AS hukbiruj3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=3 AND button LIKE '%-%' AND sudut='BIRU'");
	$hukbiruj3 = mysqli_fetch_array($hukumanbiruj3);

	$hukumanbiruj4 = mysqli_query($koneksi,"SELECT COUNT(button) AS hukbiruj4 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=4 AND button LIKE '%-%' AND sudut='BIRU'");
	$hukbiruj4 = mysqli_fetch_array($hukumanbiruj4);

	$hukumanbiruj5 = mysqli_query($koneksi,"SELECT COUNT(button) AS hukbiruj5 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=5 AND button LIKE '%-%' AND sudut='BIRU'");
	$hukbiruj5 = mysqli_fetch_array($hukumanbiruj5);
	//SELESAI HUKUMAN

	//COUNT NILAI MERAH
	// MERAH JURI 1
	$countmerahj1p1 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj1p1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=1 AND button='1' AND sudut='MERAH' ");
	$merahj1p1 = mysqli_fetch_array($countmerahj1p1);

	$countmerahj1p2 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj1p2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=1 AND button='2' AND sudut='MERAH' ");
	$merahj1p2 = mysqli_fetch_array($countmerahj1p2);

	$countmerahj1p3 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj1p3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=1 AND button='3' AND sudut='MERAH' ");
	$merahj1p3 = mysqli_fetch_array($countmerahj1p3);

	$countmerahj1p1plus1 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj1p1plus1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=1 AND button='1+1' AND sudut='MERAH' ");
	$merahj1p1plus1 = mysqli_fetch_array($countmerahj1p1plus1);

	$countmerahj1p1plus2 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj1p1plus2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=1 AND button='1+2' AND sudut='MERAH' ");
	$merahj1p1plus2 = mysqli_fetch_array($countmerahj1p1plus2);

	$countmerahj1p1plus3 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj1p1plus3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=1 AND button='1+3' AND sudut='MERAH' ");
	$merahj1p1plus3 = mysqli_fetch_array($countmerahj1p1plus3);

	// MERAH JURI 2
	$countmerahj2p1 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj2p1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=2 AND button='1' AND sudut='MERAH' ");
	$merahj2p1 = mysqli_fetch_array($countmerahj2p1);

	$countmerahj2p2 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj2p2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=2 AND button='2' AND sudut='MERAH' ");
	$merahj2p2 = mysqli_fetch_array($countmerahj2p2);

	$countmerahj2p3 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj2p3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=2 AND button='3' AND sudut='MERAH' ");
	$merahj2p3 = mysqli_fetch_array($countmerahj2p3);

	$countmerahj2p1plus1 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj2p1plus1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=2 AND button='1+1' AND sudut='MERAH' ");
	$merahj2p1plus1 = mysqli_fetch_array($countmerahj2p1plus1);

	$countmerahj2p1plus2 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj2p1plus2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=2 AND button='1+2' AND sudut='MERAH' ");
	$merahj2p1plus2 = mysqli_fetch_array($countmerahj2p1plus2);

	$countmerahj2p1plus3 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj2p1plus3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=2 AND button='1+3' AND sudut='MERAH' ");
	$merahj2p1plus3 = mysqli_fetch_array($countmerahj2p1plus3);

	// MERAH JURI 3
	$countmerahj3p1 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj3p1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=3 AND button='1' AND sudut='MERAH' ");
	$merahj3p1 = mysqli_fetch_array($countmerahj3p1);

	$countmerahj3p2 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj3p2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=3 AND button='2' AND sudut='MERAH' ");
	$merahj3p2 = mysqli_fetch_array($countmerahj3p2);

	$countmerahj3p3 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj3p3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=3 AND button='3' AND sudut='MERAH' ");
	$merahj3p3 = mysqli_fetch_array($countmerahj3p3);

	$countmerahj3p1plus1 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj3p1plus1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=3 AND button='1+1' AND sudut='MERAH' ");
	$merahj3p1plus1 = mysqli_fetch_array($countmerahj3p1plus1);

	$countmerahj3p1plus2 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj3p1plus2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=3 AND button='1+2' AND sudut='MERAH' ");
	$merahj3p1plus2 = mysqli_fetch_array($countmerahj3p1plus2);

	$countmerahj3p1plus3 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj3p1plus3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=3 AND button='1+3' AND sudut='MERAH' ");
	$merahj3p1plus3 = mysqli_fetch_array($countmerahj3p1plus3);

	// MERAH JURI 4
	$countmerahj4p1 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj4p1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=4 AND button='1' AND sudut='MERAH' ");
	$merahj4p1 = mysqli_fetch_array($countmerahj4p1);

	$countmerahj4p2 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj4p2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=4 AND button='2' AND sudut='MERAH' ");
	$merahj4p2 = mysqli_fetch_array($countmerahj4p2);

	$countmerahj4p3 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj4p3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=4 AND button='3' AND sudut='MERAH' ");
	$merahj4p3 = mysqli_fetch_array($countmerahj4p3);

	$countmerahj4p1plus1 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj4p1plus1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=4 AND button='1+1' AND sudut='MERAH' ");
	$merahj4p1plus1 = mysqli_fetch_array($countmerahj4p1plus1);

	$countmerahj4p1plus2 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj4p1plus2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=4 AND button='1+2' AND sudut='MERAH' ");
	$merahj4p1plus2 = mysqli_fetch_array($countmerahj4p1plus2);

	$countmerahj4p1plus3 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj4p1plus3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=4 AND button='1+3' AND sudut='MERAH' ");
	$merahj4p1plus3 = mysqli_fetch_array($countmerahj4p1plus3);

	// MERAH JURI 5
	$countmerahj5p1 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj5p1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=5 AND button='1' AND sudut='MERAH' ");
	$merahj5p1 = mysqli_fetch_array($countmerahj5p1);

	$countmerahj5p2 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj5p2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=5 AND button='2' AND sudut='MERAH' ");
	$merahj5p2 = mysqli_fetch_array($countmerahj5p2);

	$countmerahj5p3 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj5p3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=5 AND button='3' AND sudut='MERAH' ");
	$merahj5p3 = mysqli_fetch_array($countmerahj5p3);

	$countmerahj5p1plus1 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj5p1plus1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=5 AND button='1+1' AND sudut='MERAH' ");
	$merahj5p1plus1 = mysqli_fetch_array($countmerahj5p1plus1);

	$countmerahj5p1plus2 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj5p1plus2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=5 AND button='1+2' AND sudut='MERAH' ");
	$merahj5p1plus2 = mysqli_fetch_array($countmerahj5p1plus2);

	$countmerahj5p1plus3 = mysqli_query($koneksi,"SELECT COUNT(button) AS merahj5p1plus3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=5 AND button='1+3' AND sudut='MERAH' ");
	$merahj5p1plus3 = mysqli_fetch_array($countmerahj5p1plus3);
	// ------- **** ----------
	//END COUNT NILAI MERAH


	//COUNT NILAI BIRU
	// BIRU JURI 1
	$countbiruj1p1 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj1p1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=1 AND button='1' AND sudut='BIRU' ");
	$biruj1p1 = mysqli_fetch_array($countbiruj1p1);

	$countbiruj1p2 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj1p2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=1 AND button='2' AND sudut='BIRU' ");
	$biruj1p2 = mysqli_fetch_array($countbiruj1p2);

	$countbiruj1p3 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj1p3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=1 AND button='3' AND sudut='BIRU' ");
	$biruj1p3 = mysqli_fetch_array($countbiruj1p3);

	$countbiruj1p1plus1 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj1p1plus1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=1 AND button='1+1' AND sudut='BIRU' ");
	$biruj1p1plus1 = mysqli_fetch_array($countbiruj1p1plus1);

	$countbiruj1p1plus2 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj1p1plus2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=1 AND button='1+2' AND sudut='BIRU' ");
	$biruj1p1plus2 = mysqli_fetch_array($countbiruj1p1plus2);

	$countbiruj1p1plus3 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj1p1plus3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=1 AND button='1+3' AND sudut='BIRU' ");
	$biruj1p1plus3 = mysqli_fetch_array($countbiruj1p1plus3);

	// BIRU JURI 2
	$countbiruj2p1 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj2p1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=2 AND button='1' AND sudut='BIRU' ");
	$biruj2p1 = mysqli_fetch_array($countbiruj2p1);

	$countbiruj2p2 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj2p2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=2 AND button='2' AND sudut='BIRU' ");
	$biruj2p2 = mysqli_fetch_array($countbiruj2p2);

	$countbiruj2p3 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj2p3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=2 AND button='3' AND sudut='BIRU' ");
	$biruj2p3 = mysqli_fetch_array($countbiruj2p3);

	$countbiruj2p1plus1 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj2p1plus1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=2 AND button='1+1' AND sudut='BIRU' ");
	$biruj2p1plus1 = mysqli_fetch_array($countbiruj2p1plus1);

	$countbiruj2p1plus2 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj2p1plus2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=2 AND button='1+2' AND sudut='BIRU' ");
	$biruj2p1plus2 = mysqli_fetch_array($countbiruj2p1plus2);

	$countbiruj2p1plus3 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj2p1plus3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=2 AND button='1+3' AND sudut='BIRU' ");
	$biruj2p1plus3 = mysqli_fetch_array($countbiruj2p1plus3);

	// BIRU JURI 3
	$countbiruj3p1 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj3p1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=3 AND button='1' AND sudut='BIRU' ");
	$biruj3p1 = mysqli_fetch_array($countbiruj3p1);

	$countbiruj3p2 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj3p2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=3 AND button='2' AND sudut='BIRU' ");
	$biruj3p2 = mysqli_fetch_array($countbiruj3p2);

	$countbiruj3p3 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj3p3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=3 AND button='3' AND sudut='BIRU' ");
	$biruj3p3 = mysqli_fetch_array($countbiruj3p3);

	$countbiruj3p1plus1 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj3p1plus1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=3 AND button='1+1' AND sudut='BIRU' ");
	$biruj3p1plus1 = mysqli_fetch_array($countbiruj3p1plus1);

	$countbiruj3p1plus2 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj3p1plus2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=3 AND button='1+2' AND sudut='BIRU' ");
	$biruj3p1plus2 = mysqli_fetch_array($countbiruj3p1plus2);

	$countbiruj3p1plus3 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj3p1plus3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=3 AND button='1+3' AND sudut='BIRU' ");
	$biruj3p1plus3 = mysqli_fetch_array($countbiruj3p1plus3);

	// BIRU JURI 4
	$countbiruj4p1 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj4p1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=4 AND button='1' AND sudut='BIRU' ");
	$biruj4p1 = mysqli_fetch_array($countbiruj4p1);

	$countbiruj4p2 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj4p2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=4 AND button='2' AND sudut='BIRU' ");
	$biruj4p2 = mysqli_fetch_array($countbiruj4p2);

	$countbiruj4p3 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj4p3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=4 AND button='3' AND sudut='BIRU' ");
	$biruj4p3 = mysqli_fetch_array($countbiruj4p3);

	$countbiruj4p1plus1 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj4p1plus1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=4 AND button='1+1' AND sudut='BIRU' ");
	$biruj4p1plus1 = mysqli_fetch_array($countbiruj4p1plus1);

	$countbiruj4p1plus2 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj4p1plus2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=4 AND button='1+2' AND sudut='BIRU' ");
	$biruj4p1plus2 = mysqli_fetch_array($countbiruj4p1plus2);

	$countbiruj4p1plus3 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj4p1plus3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=4 AND button='1+3' AND sudut='BIRU' ");
	$biruj4p1plus3 = mysqli_fetch_array($countbiruj4p1plus3);

	// BIRU JURI 5
	$countbiruj5p1 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj5p1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=5 AND button='1' AND sudut='BIRU' ");
	$biruj5p1 = mysqli_fetch_array($countbiruj5p1);

	$countbiruj5p2 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj5p2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=5 AND button='2' AND sudut='BIRU' ");
	$biruj5p2 = mysqli_fetch_array($countbiruj5p2);

	$countbiruj5p3 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj5p3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=5 AND button='3' AND sudut='BIRU' ");
	$biruj5p3 = mysqli_fetch_array($countbiruj5p3);

	$countbiruj5p1plus1 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj5p1plus1 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=5 AND button='1+1' AND sudut='BIRU' ");
	$biruj5p1plus1 = mysqli_fetch_array($countbiruj5p1plus1);

	$countbiruj5p1plus2 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj5p1plus2 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=5 AND button='1+2' AND sudut='BIRU' ");
	$biruj5p1plus2 = mysqli_fetch_array($countbiruj5p1plus2);

	$countbiruj5p1plus3 = mysqli_query($koneksi,"SELECT COUNT(button) AS biruj5p1plus3 FROM nilai_tanding
									WHERE id_jadwal='$id_partai'
									AND id_juri=5 AND button='1+3' AND sudut='BIRU' ");
	$biruj5p1plus3 = mysqli_fetch_array($countbiruj5p1plus3);
	// ------- **** ----------
	//END COUNT NILAI BIRU


	ob_start();
	?>
		<tr class="text-center" style="font-size: 24px;">
	      <input type="hidden" value="<?php echo $nilaijuri1babak1merah[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri2babak1merah[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri3babak1merah[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri4babak1merah[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri5babak1merah[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri1babak1biru[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri2babak1biru[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri3babak1biru[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri4babak1biru[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri5babak1biru[0]; ?>">

	      <input type="hidden" value="<?php echo $nilaijuri1babak2merah[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri2babak2merah[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri3babak2merah[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri4babak2merah[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri5babak2merah[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri1babak2biru[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri2babak2biru[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri3babak2biru[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri4babak2biru[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri5babak2biru[0]; ?>">

	      <input type="hidden" value="<?php echo $nilaijuri1babak3merah[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri2babak3merah[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri3babak3merah[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri4babak3merah[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri5babak3merah[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri1babak3biru[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri2babak3biru[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri3babak3biru[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri4babak3biru[0]; ?>">
	      <input type="hidden" value="<?php echo $nilaijuri5babak3biru[0]; ?>">
	    
	      <input type="hidden" value="<?php
	    	//DEFINE Variabel truemerah
	    	$merahsatunyala = 0;
	    	$merahduanyala = 0;
	    	$merahtiganyala = 0;
	    	$merahempatnyala = 0;
	    	$merahlimanyala = 0;

	    	$birusatunyala = 0;
	    	$biruduanyala = 0;
	    	$birutiganyala = 0;
	    	$biruempatnyala = 0;
	    	$birulimanyala = 0;

	    	$skorakhirmerah = 0;
      		$skorakhirbiru = 0;


	    	//hitung total nilai masing-masing juri MERAH
	    	$totalwasitjuri1merah = $nilaijuri1babak1merah[0] + $nilaijuri1babak2merah[0] + $nilaijuri1babak3merah[0];
	    	$totalwasitjuri2merah = $nilaijuri2babak1merah[0] + $nilaijuri2babak2merah[0] + $nilaijuri2babak3merah[0];
	    	$totalwasitjuri3merah = $nilaijuri3babak1merah[0] + $nilaijuri3babak2merah[0] + $nilaijuri3babak3merah[0];
	    	$totalwasitjuri4merah = $nilaijuri4babak1merah[0] + $nilaijuri4babak2merah[0] + $nilaijuri4babak3merah[0];
	      	$totalwasitjuri5merah = $nilaijuri5babak1merah[0] + $nilaijuri5babak2merah[0] + $nilaijuri5babak3merah[0];
	      	
	      	//hitung total nilai masing-masing juri BIRU
	      	$totalwasitjuri1biru = $nilaijuri1babak1biru[0] + $nilaijuri1babak2biru[0] + $nilaijuri1babak3biru[0];
	      	$totalwasitjuri2biru = $nilaijuri2babak1biru[0] + $nilaijuri2babak2biru[0] + $nilaijuri2babak3biru[0];
	      	$totalwasitjuri3biru = $nilaijuri3babak1biru[0] + $nilaijuri3babak2biru[0] + $nilaijuri3babak3biru[0];
	      	$totalwasitjuri4biru = $nilaijuri4babak1biru[0] + $nilaijuri4babak2biru[0] + $nilaijuri4babak3biru[0];
	      	$totalwasitjuri5biru = $nilaijuri5babak1biru[0] + $nilaijuri5babak2biru[0] + $nilaijuri5babak3biru[0];
	      ?>">
	    </tr>  	
	    

	    <tr class="text-center" style="font-size: 250px; color: white;">
      		<td bgcolor="#FF0000" width="50%" colspan="5">
      			<b>
      			<?php
      				echo $totalwasitjuri1merah + $totalwasitjuri2merah + $totalwasitjuri3merah + $totalwasitjuri4merah;
      			?>
      			</b>
      		</td>
     		<td bgcolor="#0000FF" width="50%" colspan="5"><b>
     		  <?php
      				echo $totalwasitjuri1biru + $totalwasitjuri2biru + $totalwasitjuri3biru + $totalwasitjuri4biru;	
           		?>
     		</b></td>
    	</tr>
    	
	<?php 

	$out1 = ob_get_contents();

	ob_end_clean();

	echo $out1;
}
?>